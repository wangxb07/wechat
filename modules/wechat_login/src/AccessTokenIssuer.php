<?php


namespace Drupal\wechat_login;

use DateInterval;
use DateTime;
use Drupal\consumers\MissingConsumer;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\simple_oauth\Entities\AccessTokenEntity;
use Drupal\simple_oauth\Entities\ClientEntityInterface;
use Drupal\simple_oauth\Repositories\AccessTokenRepository;
use Drupal\simple_oauth\Repositories\ClientRepository;
use Drupal\simple_oauth\Repositories\RefreshTokenRepository;
use Drupal\simple_oauth\Repositories\ScopeRepository;
use League\OAuth2\Server\CryptKey;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Exception\OAuthServerException;
use League\OAuth2\Server\Exception\UniqueTokenIdentifierConstraintViolationException;
use League\OAuth2\Server\Grant\AbstractGrant;

class AccessTokenIssuer {

  private $entityTypeManager;
  private $clientRepository;
  private $accessTokenRepository;
  private $scopeRepository;
  private $oauthSettings;
  /**
   * @var RefreshTokenRepository
   */
  private $refreshTokenRepository;

  public function __construct(
    EntityTypeManagerInterface $entityTypeManager,
    ClientRepository $clientRepository,
    AccessTokenRepository $accessTokenRepository,
    RefreshTokenRepository $refreshTokenRepository,
    ScopeRepository $scopeRepository,
    ConfigFactoryInterface $configFactory) {

    $this->entityTypeManager = $entityTypeManager;
    $this->clientRepository = $clientRepository;
    $this->accessTokenRepository = $accessTokenRepository;
    $this->scopeRepository = $scopeRepository;
    $this->oauthSettings = $configFactory->get('simple_oauth.settings');
    $this->refreshTokenRepository = $refreshTokenRepository;
  }

  /**
   * @return ClientEntityInterface
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   * @throws MissingConsumer
   */
  public function loadDefaultClient() {
    // Find the default consumer.
    $storage = $this->entityTypeManager->getStorage('consumer');
    $results = $storage->getQuery()
      ->condition('is_default', TRUE)
      ->execute();

    $consumer_id = reset($results);
    if (!$consumer_id) {
      // Throw if there is no default consumer..
      throw new MissingConsumer('Unable to find the default consumer.');
    }

    $consumer = $storage->load($consumer_id);
    return $this->clientRepository->getClientEntity($consumer->uuid(), NULL, NULL, false);
  }

  /**
   * @param $uid
   * @param array $scope_keys
   * @param bool $toJWT
   * @return AccessTokenEntity
   * @throws MissingConsumer
   * @throws UniqueTokenIdentifierConstraintViolationException
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function issueAccessToken($uid, $scope_keys = []) {
    // get default client
    $client = $this->loadDefaultClient();

    // TODO 可以通过配置来指定哪些scope会在用户登陆的时候被指定
    $scopes = [];
    foreach ($scope_keys as $value) {
      $scopes[] = $this->scopeRepository->getScopeEntityByIdentifier($value);
    }

    /** @var AccessTokenEntity $accessToken */
    $accessToken = $this->accessTokenRepository->getNewToken($client, $scopes, $uid);

    $accessToken->setClient($client);
    $accessToken->setUserIdentifier($uid);
    $accessToken->setExpiryDateTime((new DateTime())->add(new DateInterval(sprintf('PT%dS', $this->oauthSettings->get('access_token_expiration')))));

    $maxGenerationAttempts = AbstractGrant::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;
    while ($maxGenerationAttempts-- > 0) {
      $accessToken->setIdentifier($this->generateUniqueIdentifier());
      try {
        $this->accessTokenRepository->persistNewAccessToken($accessToken);
        $maxGenerationAttempts = 0;
      } catch (UniqueTokenIdentifierConstraintViolationException $e) {
        if ($maxGenerationAttempts === 0) {
          throw $e;
        }
      }
    }

    return $accessToken;
  }

  public function convertToJWT($accessToken) {
    $privateKeyPath = $this->oauthSettings->get('private_key');
    $privateKey = new CryptKey(realpath($privateKeyPath));
    return $accessToken->convertToJWT($privateKey);
  }

  /**
   * @param AccessTokenEntityInterface $accessToken
   *
   * @throws OAuthServerException
   * @throws UniqueTokenIdentifierConstraintViolationException
   *
   * @return RefreshTokenEntityInterface|null
   */
  public function issueRefreshToken(AccessTokenEntityInterface $accessToken)
  {
    $refreshToken = $this->refreshTokenRepository->getNewRefreshToken();

    if ($refreshToken === null) {
      return null;
    }

    $refreshTokenTTL = new \DateInterval(sprintf("PT%dS", $this->oauthSettings->get('refresh_token_expiration')));

    $refreshToken->setExpiryDateTime((new DateTime())->add($refreshTokenTTL));
    $refreshToken->setAccessToken($accessToken);

    $maxGenerationAttempts = AbstractGrant::MAX_RANDOM_TOKEN_GENERATION_ATTEMPTS;

    while ($maxGenerationAttempts-- > 0) {
      $refreshToken->setIdentifier($this->generateUniqueIdentifier());
      try {
        $this->refreshTokenRepository->persistNewRefreshToken($refreshToken);

        return $refreshToken;
      } catch (UniqueTokenIdentifierConstraintViolationException $e) {
        if ($maxGenerationAttempts === 0) {
          throw $e;
        }
      }
    }

    return $refreshToken;
  }

  /**
   * Generate a new unique identifier.
   *
   * @param int $length
   *
   * @return string
   * @throws \Exception
   *
   */
  protected function generateUniqueIdentifier($length = 40) {
    try {
      return bin2hex(random_bytes($length));
      // @codeCoverageIgnoreStart
    } catch (TypeError $e) {
      throw OAuthServerException::serverError('An unexpected error has occurred', $e);
    } catch (Error $e) {
      throw OAuthServerException::serverError('An unexpected error has occurred', $e);
    } catch (Exception $e) {
      // If you get this message, the CSPRNG failed hard.
      throw OAuthServerException::serverError('Could not generate a random string', $e);
    }
    // @codeCoverageIgnoreEnd
  }
}