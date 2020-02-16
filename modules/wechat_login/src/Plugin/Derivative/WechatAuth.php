<?php

/** 
 * @file
 * Contains \Drupal\wechat_login\Plugin\Derivative\WechatAuth.php.
 */
namespace Drupal\wechat_login\Plugin\Derivative;

use Drupal\Component\Plugin\Derivative\DeriverBase;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\Discovery\ContainerDeriverInterface;
use Drupal\wechat\Entity\WechatMerchant;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides block plugin definitions.
 *
 * @see \Drupal\wechat_login\Plugin\Block\WechatAuth
 */
class WechatAuth extends DeriverBase implements ContainerDeriverInterface
{

  /**
   * The node storage.
   *
   * @var \Drupal\Core\Entity\EntityStorageInterface
   */
  protected $wechatMerchant;
  /**
   * Creates a new NodeBlock.
   *
   * @param \Drupal\Core\Entity\EntityStorageInterface $node_storage
   *   The node storage.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager)
  {
    $this->wechatMerchant = $entity_type_manager->getStorage('wechat_merchant');
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, $base_plugin_id) 
  {
    return new static(
      $container->get('entity_type.manager'),
    );
  }
  /**
   * {@inheritdoc}
   */
  public function getDerivativeDefinitions($base_plugin_definition) 
  {
    $wechatMerchants = $this->wechatMerchant->loadByProperties([]);
    foreach ($wechatMerchants as $merchant) {
      $this->derivatives[$merchant->id()] = $base_plugin_definition;
      $this->derivatives[$merchant->id()]['social_network'] = t('Wechat app: ') . $merchant->getMiniProgramAppId();
    }
    return $this->derivatives;
  }

}
