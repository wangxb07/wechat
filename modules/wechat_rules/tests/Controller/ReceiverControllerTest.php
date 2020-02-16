<?php

namespace Drupal\wechat_rules\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Provides automated tests for the wechat_rules module.
 */
class ReceiverControllerTest extends WebTestBase {


  /**
   * {@inheritdoc}
   */
  public static function getInfo() {
    return [
      'name' => "wechat_rules ReceiverController's controller functionality",
      'description' => 'Test Unit for module wechat_rules and controller ReceiverController.',
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
   * Tests wechat_rules functionality.
   */
  public function testReceiverController() {
    // Check that the basic functions of module wechat_rules.
    $this->assertEquals(TRUE, TRUE, 'Test Unit Generated via Drupal Console.');
  }

}
