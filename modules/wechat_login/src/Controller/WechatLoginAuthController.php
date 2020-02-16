<?php

namespace Drupal\wechat_login\Controller;

use Drupal\Component\Plugin\PluginBase;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\TempStore\PrivateTempStoreFactory;
use Drupal\Core\TempStore\TempStoreException;
use Drupal\social_auth\User\UserAuthenticator;
use Drupal\wechat\WechatApp;
use Drupal\wechat\WechatFactory;
use Drupal\wechat_login\AccessTokenIssuer;
use EasyWeChat\Kernel\Exceptions\DecryptException;
use EasyWeChat\MiniProgram\Encryptor;
use Exception;
use GuzzleHttp\Exception\GuzzleException;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class WechatLoginAuthController.
 */
class WechatLoginAuthController extends ControllerBase {

  private $wechatApp;
  private $userAuthenticator;
  private $tempStore;
  private $appId;
  private $storeStorage;
  /**
   * @var AccessTokenIssuer
   */
  private $tokenIssuer;

  private $wechatFactory;

  /**
   * WechatLoginAuthController constructor.
   * @param WechatApp $wechat_app
   * @param UserAuthenticator $user_authenticator
   * @param MessengerInterface $messenger
   * @param PrivateTempStoreFactory $temp_store_factory
   * @param AccessTokenIssuer $tokenIssuer
   */
  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    WechatApp $wechat_app,
    WechatFactory $wechat_factory,
    UserAuthenticator $user_authenticator,
    MessengerInterface $messenger,
    PrivateTempStoreFactory $temp_store_factory,
    AccessTokenIssuer $tokenIssuer) {
    $this->wechatApp = $wechat_app;
    $this->userAuthenticator = $user_authenticator;
    $this->messenger = $messenger;
    $this->tempStore = $temp_store_factory->get('wechat_login');
    $this->storeStorage = $entity_type_manager->getStorage('commerce_store');
    $this->wechatFactory = $wechat_factory;
    $this->tokenIssuer = $tokenIssuer;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('wechat.mini_program'),
      $container->get('wechat.factory'),
      $container->get('social_auth.user_authenticator'),
      $container->get('messenger'),
      $container->get('user.private_tempstore'),
      $container->get('wechat_login.access_token_issuer')
    );
  }

  /**
   * Authenticate by openid that from code2session
   *
   * @param Request $request
   * @return JsonResponse|Response
   * @throws Exception
   */
  public function loginByCode(Request $request) {
    $code = $request->request->get('code');
    $request_session = $request->getSession();

    if (empty($code)) {
      return new Response("code is required", 400);
    }

    $logger = $this->getLogger('wechat_login');
    $instance = $this->getWechatInstance($request);
    if (empty($instance)) {
      return new Response("appID not exist", 403);
    }
    try {
      $session = $instance->auth->session($code);
      $logger->info("code2session success: @session", ['@session' => print_r($session, true)]);

      if (!isset($session['openid'])) {
        return new Response("code2session failure: " . $session['errmsg'], 403);
      }

      try {
        $this->tempStore->set('wechat_session', $session);
      } catch (TempStoreException $e) {
        $logger->error('set session to temp store failure, @message', ['@message' => $e->getMessage()]);
        return new Response("set session to store fail: " . $e->getMessage(), 500);
      }
    } catch (Exception $e) {
      return new Response("Http error in session request: " . $e->getMessage(), 500);
    }

    $drupal_uid = $this->userAuthenticator->checkProviderIsAssociated($session['openid']);

    if ($drupal_uid) {
      if ($this->userAuthenticator->authenticateWithProvider($drupal_uid)) {
        $current_user = $this->currentUser();
        return $this->buildLoggedResponse($current_user, $request_session);
      } else {
        return new Response("Wechat openid is associated, but drupal user not found", 401);
      }
    }

    return new JsonResponse([
      'user' => [
        'id' => 0,
      ],
      'sess_id' => $request_session->getId(),
      'sess_name' => $request_session->getName()
    ], 200);
  }

  /**
   * Post code and encrypted data from wechat mini program getPhoneNumber API, and authenticate user
   *
   * @param Request $request
   * @return \Symfony\Component\HttpFoundation\RedirectResponse|Response
   * @throws \EasyWeChat\Kernel\Exceptions\InvalidConfigException
   */
  public function phoneQuickLogin(Request $request) {
    $code = $request->request->get('code');
    $iv = $request->request->get('iv');
    $encrypted_data = $request->request->get('encrypted_data');

    if (empty($encrypted_data) || empty($iv)) {
      return new Response("miss required arguments", 400);
    }

    $logger = $this->getLogger('wechat_login');

    $instance = $this->getWechatInstance($request);
    if (empty($instance)) {
      return new Response("appID not exist", 403);
    }

    if (!empty($code)) {
      try {
        $session = $instance->auth->session($code);
      } catch (GuzzleException $e) {
        return new Response("Http error in session request: " . $e->getMessage(), 422);
      }

      $logger->info("phone quick login code2session success: @session", ['@session' => print_r($session, true)]);
    } else {
      $session = $this->tempStore->get('wechat_session');
    }

    if ($session && $session['session_key']) {
      $encryptor = new Encryptor($this->appId);

      try {
        $decrypt_data = $encryptor->decryptData($session['session_key'], urldecode($iv), urldecode($encrypted_data));
        $logger->info("decrypt data: <pre>@data</pre>", ['@data' => print_r($decrypt_data, true)]);
      } catch (DecryptException $e) {
        $logger->error("encrypted data: @data, session key: @session, iv: @iv",
          ['@data' => urldecode($encrypted_data), '@session' => $session['session_key'], '@iv' => urldecode($iv)]);

        return new Response("Decrypt fail: " . $e->getMessage(), 500);
      }

      if ($decrypt_data['phoneNumber']) {
        $this->userAuthenticator->authenticateUser(
          $decrypt_data['purePhoneNumber'],
          $decrypt_data['purePhoneNumber'] . '@wechat.fake',
          $session['openid'],
          $session['session_key']
        );

        $current_user = $this->currentUser();
        $request_session = $request->getSession();
        return $this->buildLoggedResponse($current_user, $request_session);
      }
    }

    $logger->error("code2session invoke failed or session not in temp store", []);
    return new Response("code2session failed", 500);
  }

  /**
   * The helper function for build the logged response
   *
   * @param AccountInterface $current_user
   * @param SessionInterface $request_session
   * @return JsonResponse
   * @throws \Exception
   */
  private function buildLoggedResponse(AccountInterface $current_user, SessionInterface $request_session) {
    $accessToken = $this->tokenIssuer->issueAccessToken($current_user->id(), ['coupon', 'authenticated']);
    $refresh_token = $this->tokenIssuer->issueRefreshToken($accessToken);
    return new JsonResponse([
      'user' => [
        'id' => $current_user->id(),
        'username' => $current_user->getAccountName(),
        'display_name' => $current_user->getDisplayName(),
        'roles' => $current_user->getRoles(),
      ],
      'sess_id' => $request_session->getId(),
      'sess_name' => $request_session->getName(),
      'jwt' => (string)$this->tokenIssuer->convertToJWT($accessToken),
      'refresh_token' => $refresh_token->getIdentifier(),
    ], 200);
  }

  private function getWechatInstance(Request $request, string $type = 'mini_program') {
    //通过store拿到app_id
    $storeUuid = $request->headers->get('X-BEEHPLUS-STORE-ID');
    $store = $this->storeStorage->loadByProperties([
      'uuid' => $storeUuid
    ]);
    if (empty($store)) {
      //TODO:暂时做兼容处理
      $appId = $storeUuid;
    }else{
      $store = current($store);
      $wechatAppConfig = $store->get('field_wechat_app')->referencedEntities();
      if (!empty($wechatAppConfig)) {
        $wechatAppConfig = current($wechatAppConfig);
      }
      $appId = $wechatAppConfig->getMiniProgramAppId();
    }

    $this->userAuthenticator->setPluginId('social_auth_wechat_mini_program' . PluginBase::DERIVATIVE_SEPARATOR . $appId);
    $this->appId = $appId;
    return $this->wechatFactory->getInstance($type, $appId);
  }
}
