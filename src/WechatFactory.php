<?php


namespace Drupal\wechat;

use EasyWeChat\Factory;

class WechatFactory {
  private $wechatMerchant;
  private $miniProgramConfig;
  private $officialAccountConfig;

  public function __construct() {
    $entity_type_manager = \Drupal::entityTypeManager();
    $this->wechatMerchant = $entity_type_manager->getStorage('wechat_merchant');
  }

  public function getInstance(string $type, string $appId) {
    switch ($type) {
      case 'mini_program':
        if (empty($this->miniProgramConfig)){
          $this->setConfig($type,$appId);
        }
        return Factory::miniProgram($this->miniProgramConfig);
        break;
      case 'official_account':
        if (empty($this->officialAccountConfig)){
          $this->setConfig($type,$appId);
        }
        return Factory::officialAccount($this->officialAccountConfig);
        break;
    }
    return null;
  }

  private function setConfig(string $type, string $appId) {
    //根据appId获取secret
    $wechatMerchant = $this->wechatMerchant->loadByProperties([
      $type . '_app_id' => $appId
    ]);
    if (empty($wechatMerchant)) {
      return null;
    }
    $wechatMerchant = current($wechatMerchant);
    $this->miniProgramConfig = [
      'app_id' => $wechatMerchant->getMiniProgramAppId(),
      'secret' => $wechatMerchant->getMiniProgramSecret()
    ];
    $this->officialAccountConfig = [
      'app_id' => $wechatMerchant->getOfficialAccountAppId(),
      'secret' => $wechatMerchant->getOfficialAccountSecret()
    ];
  }

}