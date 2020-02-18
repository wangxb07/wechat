<?php

namespace Drupal\wechat_store\Service;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\wechat\Entity\WechatMerchant;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class StoreFactory.
 */
class StoreFactory implements StoreFactoryInterface {

  private $storeStorage;

  /**
   * Constructs a new StoreFactory object.
   */
  public function __construct() {
    $entity_type_manager = \Drupal::entityTypeManager();
    $this->storeStorage = $entity_type_manager->getStorage('commerce_store');
  }

  public function getWechatMerchantByStoreUuid(string $storeUuid): WechatMerchant {
    $store = $this->storeStorage->loadByProperties([
      'uuid' => $storeUuid
    ]);
    if (empty($store)) {
      throw new HttpException(422, 'this store uuid does not exist.');
    }
    $store = current($store);

    $wechatMerchant = $store->get('field_wechat_app')->referencedEntities();
    return current($wechatMerchant);
  }

}
