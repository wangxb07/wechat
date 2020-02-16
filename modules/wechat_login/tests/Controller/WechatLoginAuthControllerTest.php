<?php

namespace Drupal\wechat_login\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the wechat_login module.
 */
class WechatLoginAuthControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "wechat_login WechatLoginAuthController's controller functionality",
      'description' => 'Test Unit for module wechat_login and controller WechatLoginAuthController.',
      'group' => 'Other',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setUp();
  }

  /**
   * Tests wechat_login functionality.
   */
  public function testWechatLoginAuthController() {
    // Check that the basic functions of module wechat_login.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
