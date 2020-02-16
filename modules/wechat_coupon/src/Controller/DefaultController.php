<?php

namespace Drupal\wechat_coupon\Controller;

use Drupal\commerce_promotion\CouponCodeGeneratorInterface;
use Drupal\commerce_promotion\CouponCodePattern;
use Drupal\commerce_promotion\Entity\Coupon;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\wechat\WechatFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;

/**
 * Class DefaultController.
 */
class DefaultController extends ControllerBase {

  private $wechatFactory;
  private $promotionStorge;
  private $storeStorge;
  private $couponCodeGenerator;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, WechatFactory $wechat_factory, CouponCodeGeneratorInterface $coupon_code_generator) {
    $this->wechatFactory = $wechat_factory;
    $this->promotionStorge = $entity_type_manager->getStorage('commerce_promotion');
    $this->storeStorge = $entity_type_manager->getStorage('commerce_store');
    $this->couponCodeGenerator = $coupon_code_generator;

  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('wechat.factory'),
      $container->get('commerce_promotion.coupon_code_generator'),
      );
  }

  public function bindCoupon(Request $request) {
    $postData = json_decode($request->getContent(), true);
    if (!array_key_exists('extend', $postData) || !array_key_exists('discount_name', $postData)) {
      throw new HttpException(422, 'extend and discount_name is required');
    }
    $extend = $postData['extend'];
    if (!array_key_exists('card_id', $extend)) {
      throw new HttpException(422, 'card_id not found in extend');
    }

    //通过store拿到app_id
    $storeUuid = $request->headers->get('X-BEEHPLUS-STORE-ID');
    $store = $this->storeStorge->loadByProperties([
      'uuid' => $storeUuid
    ]);
    if (empty($store)) {
      //TODO:暂时做兼容处理
      $appId = $storeUuid;
    }else{
      $store = current($store);
      $wechatAppConfig = $store->get('field_wechat_app')->referencedEntities();
      if (!empty($wechatAppConfig)) {
        $wechatAppConfig = current($wechatAppConfig);
      }
      $appId = $wechatAppConfig->getOfficialAccountAppId();
    }

    $promotion_uuid = $postData['discount_name'];
    $card_id = $extend['card_id'];

    //get card instance
    $instance = $this->wechatFactory->getInstance('official_account', $appId);
    $card = $instance->card;

    //get promotion by discountName
    $promotion = $this->promotionStorge->loadByProperties([
      'uuid' => $promotion_uuid
    ]);
    if (empty($promotion)) {
      throw new HttpException(422, 'this promotion is not exist');
    }
    $promotion = current($promotion);

    //create a new coupon_code and bind to promotion
    $pattern = new CouponCodePattern('alphanumeric', '', '', 8);
    $codeResult = $this->couponCodeGenerator->generateCodes($pattern, 1);
    $code = $codeResult[0];
    $coupon = Coupon::create([
      'code' => $code,
      'promotion_id' => $promotion->id(),
      'usage_limit' => 1
    ]);
    $coupon->save();

    //generate card
    $cards = [
      [
        'card_id' => $card_id,
        'outer_id' => $code
      ]
    ];

    $json = $card->jssdk->assign($cards);
    return new Response($json, 200);

  }

  /**
   * @param Request $request
   * @throws \Drupal\Core\Entity\EntityStorageException
   */
  public function generateCoupon(Request $request) {
    $postData = json_decode($request->getContent(), true);
    if (!array_key_exists('coupon_code', $postData) || !array_key_exists('discount_name', $postData) || !array_key_exists('card_id', $postData)) {
      throw new HttpException(422, 'coupon_code or discount_name is required');
    }
    $card_id = $postData['card_id'];
    $coupon_code = $postData['coupon_code'];
    $promotion_uuid = $postData['discount_name'];

    //get promotion by promotion_uuid
    $promotion = $this->promotionStorge->loadByProperties([
      'uuid' => $promotion_uuid
    ]);
    if (empty($promotion)) {
      throw new HttpException(422, 'this promotion is not exist');
    }
    $promotion = current($promotion);
    //set card_id
    $data = ['card_id' => $card_id];

    //save coupon
    $coupon = Coupon::create([
      'code' => $coupon_code,
      'promotion_id' => $promotion->id(),
      'data' => json_encode($data),
      'usage_limit' => 1
    ]);

    $coupon->save();
  }

}
