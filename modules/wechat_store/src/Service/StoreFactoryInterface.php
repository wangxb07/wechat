<?php

namespace Drupal\wechat_store\Service;

use Drupal\wechat\Entity\WechatMerchant;

/**
 * Interface StoreFactoryInterface.
 */
interface StoreFactoryInterface {
  public function getWechatMerchantByStoreUuid(string $storeUuid):WechatMerchant;

}
