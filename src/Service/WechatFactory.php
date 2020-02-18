<?php


namespace Drupal\wechat\Service;

use Drupal\wechat\Entity\WechatMerchant;
use EasyWeChat\Factory;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

class WechatFactory implements WechatFactoryInterface {
  private $wechatMerchant;
  private $storeStorage;
  private $miniProgramConfig;
  private $officialAccountConfig;

  public function __construct() {
    $entity_type_manager = \Drupal::entityTypeManager();
    $this->wechatMerchant = $entity_type_manager->getStorage('wechat_merchant');
    $this->storeStorage = $entity_type_manager->getStorage('commerce_store');
  }

  /**
   * @param string $type
   * @param string $appId
   * @return \EasyWeChat\MiniProgram\Application|\EasyWeChat\OfficialAccount\Application|null
   */
  public function getInstance(string $type, string $appId) {
    switch ($type) {
      case 'mini_program':
        if (empty($this->miniProgramConfig)) {
          $this->setConfig($type, $appId);
        }
        return Factory::miniProgram($this->miniProgramConfig);
        break;
      case 'official_account':
        if (empty($this->officialAccountConfig)) {
          $this->setConfig($type, $appId);
        }
        return Factory::officialAccount($this->officialAccountConfig);
        break;
    }
    return null;
  }

  /**
   * @param Request $request
   * @param string $type
   * @return WechatMerchant
   */
  public function getWechatMerchantByRequest(Request $request, string $type = 'mini_program'): WechatMerchant {
    //通过store拿到app_id
    $storeUuid = $request->headers->get('X-BEEHPLUS-STORE-ID');
    if (empty($storeUuid)) {
      throw new HttpException(422, 'store uuid is required.');
    }
    $wechatMerchant = \Drupal::service('store.factory')->getWechatMerchantByStoreUuid($storeUuid);

    //如果通过store找到了商户，那么这个type参数便是无用的，直接返回wechatMerchant
    if (!empty($wechatMerchant)) {
      return $wechatMerchant;
    }

    //TODO:暂时做兼容处理,假设传入的有可能是appId,获取wechatMerchat的方式有可能会改变
    $wechatMerchant = $this->wechatMerchant->loadByProperties([
      $type . '_app_id' => $storeUuid
    ]);

    if (empty($wechatMerchant)) {
      throw new HttpException(422, 'this wechat merchant is not exist.');
    }
    return current($wechatMerchant);
  }

  /**
   * @param string $type
   * @param string $appId
   * @return |null
   */
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