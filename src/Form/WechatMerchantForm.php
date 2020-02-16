<?php

namespace Drupal\wechat\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Entity\EntityForm;
use Drupal\Core\Form\FormStateInterface;

/**
 * Class WechatMerchantForm.
 */
class WechatMerchantForm extends EntityForm {

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    $form = parent::form($form, $form_state);

    $wechatMerchant = $this->entity;
    $form['label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Label'),
      '#maxlength' => 255,
      '#default_value' => $wechatMerchant->label(),
      '#description' => $this->t("Label for the Wechat merchant."),
      '#required' => TRUE,
    ];

    $form['id'] = [
      '#type' => 'machine_name',
      '#default_value' => $wechatMerchant->id(),
      '#machine_name' => [
        'exists' => '\Drupal\wechat\Entity\WechatMerchant::load',
      ],
      '#disabled' => !$wechatMerchant->isNew(),
    ];

    $form['mini_program_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('mini_program_app_id'),
      '#default_value' => $wechatMerchant->getMiniProgramAppId(),
    ];

    $form['mini_program_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('mini_program_secret'),
      '#default_value' => $wechatMerchant->getMiniProgramSecret(),
    ];

    $form['official_account_app_id'] = [
      '#type' => 'textfield',
      '#title' => $this->t('official_account_app_id'),
      '#default_value' => $wechatMerchant->getOfficialAccountAppId(),
    ];

    $form['official_account_secret'] = [
      '#type' => 'textfield',
      '#title' => $this->t('official_account_secret'),
      '#default_value' => $wechatMerchant->getOfficialAccountSecret(),
    ];

    $config = $wechatMerchant->getUiConfig();
    $configText = Yaml::encode($config);

    $form['ui_config'] = [
      '#type' => 'textarea',
      '#title' => t('Configuration'),
      '#attributes' => [
        'data-yaml-editor' => 'true',
      ],
      '#default_value' => $configText
    ];

    /* You will need additional form elementsT for your custom properties. */

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $configText = $form_state->getValue('ui_config');

    try {
      $form_state->set('ui_config', Yaml::decode($configText));
    } catch (InvalidDataTypeException $e) {
      $form_state->setErrorByName('ui_config', $e->getMessage());
    }

    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    $uiConfig = $form_state->get('ui_config');
    $wechatMerchant = $this->entity;
    $wechatMerchant->set('ui_config', $uiConfig);
    $status = $wechatMerchant->save();

    switch ($status) {
      case SAVED_NEW:
        $this->messenger()->addMessage($this->t('Created the %label Wechat merchant.', [
          '%label' => $wechatMerchant->label(),
        ]));
        break;

      default:
        $this->messenger()->addMessage($this->t('Saved the %label Wechat merchant.', [
          '%label' => $wechatMerchant->label(),
        ]));
    }
    $form_state->setRedirectUrl($wechatMerchant->toUrl('collection'));
  }

}