<?php


namespace Drupal\wechat\Service;


use Drupal\wechat\Entity\WechatMerchant;
use Symfony\Component\HttpFoundation\Request;

interface WechatFactoryInterface {
  public function getWechatMerchantByRequest(Request $request, string $type = 'mini_program'): WechatMerchant;
  public function getInstance(string $type, string $appId);
}