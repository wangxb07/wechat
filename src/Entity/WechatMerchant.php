<?php

namespace Drupal\wechat\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;

/**
 * Defines the Wechat merchant entity.
 *
 * @ConfigEntityType(
 *   id = "wechat_merchant",
 *   label = @Translation("Wechat merchant"),
 *   handlers = {
 *     "view_builder" = "Drupal\Core\Entity\EntityViewBuilder",
 *     "list_builder" = "Drupal\wechat\WechatMerchantListBuilder",
 *     "form" = {
 *       "add" = "Drupal\wechat\Form\WechatMerchantForm",
 *       "edit" = "Drupal\wechat\Form\WechatMerchantForm",
 *       "delete" = "Drupal\wechat\Form\WechatMerchantDeleteForm"
 *     },
 *     "route_provider" = {
 *       "html" = "Drupal\wechat\WechatMerchantHtmlRouteProvider",
 *     },
 *   },
 *   config_prefix = "wechat_merchant",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "label",
 *     "uuid" = "uuid"
 *   },
 *   links = {
 *     "canonical" = "/admin/structure/wechat_merchant/{wechat_merchant}",
 *     "add-form" = "/admin/structure/wechat_merchant/add",
 *     "edit-form" = "/admin/structure/wechat_merchant/{wechat_merchant}/edit",
 *     "delete-form" = "/admin/structure/wechat_merchant/{wechat_merchant}/delete",
 *     "collection" = "/admin/structure/wechat_merchant"
 *   }
 * )
 */
class WechatMerchant extends ConfigEntityBase implements WechatMerchantInterface {

  /**
   * The Wechat merchant ID.
   *
   * @var string
   */
  protected $id;

  /**
   * The Wechat merchant label.
   *
   * @var string
   */
  protected $label;
  protected $mini_program_app_id;
  protected $mini_program_secret;

  protected $official_account_app_id;
  protected $official_account_secret;

  protected $ui_config;

  /**
   * @return mixed
   */
  public function getMiniProgramAppId() {
    return $this->mini_program_app_id;
  }

  /**
   * @param mixed $mini_program_app_id
   */
  public function setMiniProgramAppId($mini_program_app_id): void {
    $this->mini_program_app_id = $mini_program_app_id;
  }

  /**
   * @return mixed
   */
  public function getMiniProgramSecret() {
    return $this->mini_program_secret;
  }

  /**
   * @param mixed $mini_program_secret
   */
  public function setMiniProgramSecret($mini_program_secret): void {
    $this->mini_program_secret = $mini_program_secret;
  }

  /**
   * @return mixed
   */
  public function getOfficialAccountAppId() {
    return $this->official_account_app_id;
  }

  /**
   * @param mixed $official_account_app_id
   */
  public function setOfficialAccountAppId($official_account_app_id): void {
    $this->official_account_app_id = $official_account_app_id;
  }

  /**
   * @return mixed
   */
  public function getOfficialAccountSecret() {
    return $this->official_account_secret;
  }

  /**
   * @param mixed $official_account_secret
   */
  public function setOfficialAccountSecret($official_account_secret): void {
    $this->official_account_secret = $official_account_secret;
  }

  /**
   * @return mixed
   */
  public function getUiConfig() {
    return $this->ui_config;
  }

  /**
   * @param mixed $ui_config
   */
  public function setUiConfig($ui_config): void {
    $this->ui_config = $ui_config;
  }


}
