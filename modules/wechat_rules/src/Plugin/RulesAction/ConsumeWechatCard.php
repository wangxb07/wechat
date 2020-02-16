<?php

namespace Drupal\wechat_rules\Plugin\RulesAction;

use Drupal\commerce_order\Entity\OrderInterface;
use Drupal\rules\Core\RulesActionBase;
use Drupal\wechat\WechatFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'ConsumeWechatCard' action.
 *
 * @RulesAction(
 *  id = "consume_wechat_card",
 *  label = @Translation("Consume wechat card"),
 *  category = @Translation("consume_wechat_card"),
 *  context = {
 *     "commerce_order" = @ContextDefinition("entity:commerce_order",
 *       label = @Translation("commerce_order"),
 *       description = @Translation("use for consume wechat")
 *     ),
 *  }
 * )
 */
class ConsumeWechatCard extends RulesActionBase {
  protected $wechatFactory;
  public function __construct(array $configuration, $plugin_id, $plugin_definition) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->wechatFactory = \Drupal::service('wechat.factory');
  }
  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function doExecute(OrderInterface $order,WechatFactory $wechatFactory) {
    $coupons = $order->getCoupons();
    foreach ($coupons as $coupon){
      //获取card_id
      $code = $coupon->getCode();
      //通过order拿到store，通过store拿到appid,拿到微信实例
      $store = $order->getStore();
      $wechatAppConfig = $store->get('field_wechat_app')->referencedEntities();
      if (!empty($wechatAppConfig)){
        $wechatAppConfig = current($wechatAppConfig);
      }

      $appId = $wechatAppConfig->getOfficialAccountAppId();
      $instance = $this->wechatFactory->getInstance("official_account",$appId);

      //核销微信卡券
      $wechatCard = $instance->card;
      $wechatCard->code->consume($code);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function autoSaveContext() {
      // Insert code here.
      return [];
  }

}
