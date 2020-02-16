<?php


namespace Drupal\wechat_login\Plugin\Network;

use Drupal\social_api\Plugin\NetworkBase;
use Drupal\social_api\SocialApiException;
use Drupal\wechat\WechatFactory;

/**
 * Class WechatMiniProgramAuth
 * @package Drupal\wechat_login\Plugin\Network
 *
 * @Network(
 *   id = "social_auth_wechat_mini_program",
 *   social_network = "WeChat mini program",
 *   deriver = "Drupal\wechat_login\Plugin\Derivative\WechatAuth",
 *   type = "social_auth",
 *   handlers = {
 *      "settings": {
 *          "class": "\Drupal\wechat_login\Settings\WechatMiniProgramAuthSettings",
 *          "config_id": "wechat.mini_program"
 *      }
 *   }
 * )
 */
class WechatMiniProgramAuth extends NetworkBase {

  /**
   * Sets the underlying SDK library.
   *
   * @return mixed
   *   The initialized 3rd party library instance.
   *
   * @throws SocialApiException
   *   If the SDK library does not exist.
   */
  protected function initSdk() {
    $wechat_sdk = \Drupal::service('wechat.factory');
    $appId = $this->getDerivativeId();
    if ($wechat_sdk) {
      return $wechat_sdk->getInstance('mini_program',$appId);
    } else {
      throw new SocialApiException("wechat mini program load fail, please check settings is ok.");
    }
  }
}