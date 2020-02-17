<?php

namespace Drupal\wechat\Service;

use EasyWeChat\Factory;

/**
 * Class WechatApp.
 */
class WechatApp implements WechatAppInterface {
  /**
   * @var \EasyWeChat\Kernel\ServiceContainer
   */
  private $instance;

  /**
   * Constructs a new WechatApp object.
   * @param $type
   *  The wechat type
   */
  public function __construct($type) {
    $config = \Drupal::config('wechat.' . $type);

    switch ($type) {
      case 'official_account':
        $this->instance = Factory::officialAccount($config->getRawData());
        break;
      case 'mini_program':
        $this->instance = Factory::miniProgram($config->getRawData());
        break;
      case 'open_platform':
        $this->instance = Factory::openPlatform($config->getRawData());
        break;
      case 'work':
        $this->instance = Factory::work($config->getRawData());
        break;
      case 'open_work':
        $this->instance = Factory::openWork($config->getRawData());
        break;
      case 'payment':
        $this->instance = Factory::payment($config->getRawData());
        break;
    }
  }

  /**
   * @return Wechat application instance
   */
  public function getInstance() {
    return $this->instance;
  }
}
