<?php

namespace Drupal\wechat_login\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\social_auth\Form\SocialAuthSettingsForm;

/**
 * Class WechatLoginSettingsForm.
 */
class WechatLoginSettingsForm extends SocialAuthSettingsForm {
  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return array_merge(array('wechat.mini_program'), parent::getEditableConfigNames());
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'wechat_login_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('wechat.mini_program');

    $form['wechat_settings'] = array(
      '#type' => 'details',
      '#title' => $this->t('WeChat Client settings'),
      '#open' => TRUE,
    );

    $form['wechat_settings']['client_id'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Client ID'),
      '#default_value' => $config->get('app_id'),
      '#description' => $this->t('Copy the Client ID here'),
    );

    $form['wechat_settings']['client_secret'] = array(
      '#type' => 'textfield',
      '#required' => TRUE,
      '#title' => $this->t('Client Secret'),
      '#default_value' => $config->get('secret'),
      '#description' => $this->t('Copy the Client Secret here'),
    );

    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $values = $form_state->getValues();

    $this->config('wechat.mini_program')
      ->set('app_id', $values['client_id'])
      ->set('secret', $values['client_secret'])
      ->save();

    parent::submitForm($form, $form_state);
  }
}
