<?php

namespace Drupal\inventaire_conteneur\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;

/**
 * Configure Inventaire Conteneur settings for this site.
 */
class SettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'inventaire_conteneur_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['inventaire_conteneur.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Activate Conteneur features.'),
      '#default_value' => $this->config('inventaire_conteneur.settings')->get('enabled'),
    ];
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    // if ($form_state->getValue('enabled') != 'enabled') {
    //   $form_state->setErrorByName('enabled', $this->t('The value is not correct.'));
    // }
    parent::validateForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    $this->config('inventaire_conteneur.settings')
      ->set('enabled', $form_state->getValue('enabled'))
      ->save();
    parent::submitForm($form, $form_state);
  }

}
