<?php


namespace Drupal\wechat_login\Settings;


use Drupal\social_api\Settings\SettingsBase;

class WechatMiniProgramAuthSettings extends SettingsBase implements WechatMiniProgramAuthSettingsInterface {

  /**
   * Client ID.
   *
   * @var string
   */
  protected $clientId;

  /**
   * Client secret.
   *
   * @var string
   */
  protected $clientSecret;

  /**
   * {@inheritdoc}
   */
  public function getClientId() {
    if (!$this->clientId) {
      $this->clientId = $this->config->get('app_id');
    }
    return $this->clientId;
  }

  /**
   * {@inheritdoc}
   */
  public function getClientSecret() {
    if (!$this->clientSecret) {
      $this->clientSecret = $this->config->get('secret');
    }
    return $this->clientSecret;
  }
}