<?php


namespace Drupal\wechat\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Information about the client
 * Class ClientController
 * @package Drupal\wechat\Controller
 */
class ClientController extends ControllerBase {
  private $storeStorage;

  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
    $this->storeStorage = $entity_type_manager->getStorage('commerce_store');
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * @param Request $request
   * @return JsonResponse|Response
   */
  public function getUiConfig(Request $request) {
    //通过store拿到app_id
    $storeUuid = $request->headers->get('X-BEEHPLUS-STORE-ID');
    if (empty($storeUuid)) {
      return new Response("this store uuid is required.", 402);
    }
    $store = $this->storeStorage->loadByProperties([
      'uuid' => $storeUuid
    ]);
    if (empty($store)) {
      return new Response("this store is not exist.", 403);
    } else {
      $store = current($store);
      $wechatAppConfig = $store->get('field_wechat_app')->referencedEntities();
      if (!empty($wechatAppConfig)) {
        $wechatAppConfig = current($wechatAppConfig);
      }
      $uiConfig = $wechatAppConfig->getUiConfig();
    };
    return new JsonResponse($uiConfig);
  }

}