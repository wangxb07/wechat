<?php


namespace Drupal\wechat\Controller;


use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\wechat\Service\WechatFactory;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Information about the client
 * Class ClientController
 * @package Drupal\wechat\Controller
 */
class ClientController extends ControllerBase {
  private $storeStorage;
  private $wechatFactory;

  public function __construct(EntityTypeManagerInterface $entity_type_manager, WechatFactory $wechat_factory) {
    $this->storeStorage = $entity_type_manager->getStorage('commerce_store');
    $this->wechatFactory = $wechat_factory;
  }

  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('wechat.factory')

    );
  }

  /**
   * @param Request $request
   * @return JsonResponse|Response
   */
  public function getUiConfig(Request $request) {
    $wechatMerchant = $this->wechatFactory->getWechatMerchantByRequest($request);
    $uiConfig = $wechatMerchant->getUiConfig();
    return new JsonResponse($uiConfig);
  }

}