<?php

namespace Drupal\wechat_coupon\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the wechat_coupon module.
 */
class DefaultControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "wechat_coupon DefaultController's controller functionality",
      'description' => 'Test Unit for module wechat_coupon and controller DefaultController.',
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
   * Tests wechat_coupon functionality.
   */
  public function testDefaultController() {
    // Check that the basic functions of module wechat_coupon.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
