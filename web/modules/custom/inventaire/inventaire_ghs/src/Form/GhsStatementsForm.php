<?php

namespace Drupal\inventaire_ghs\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\taxonomy\Entity\Term;

/**
 * Implements an inventaire_ghs form.
 */
class GhsStatementsForm extends FormBase {

  public $mhchemUrl = 'https://mhchem.github.io/hpstatements/clp/hpstatements-fr.json';
  public $mhchemFilename = 'hpstatements-fr.json';
  public $ghs_statements_vid_h = 'ghs_statements_h';
  public $ghs_statements_vid_p = 'ghs_statements_p';

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'inventaire_ghs_statements_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['ghs_statements_import'] = [
      '#type' => 'item',
      '#title' => 'Update/Create the taxonomy terms of the GHS Statements vocabulary',
      '#description' => 'Terms will be updated from <i>module_path/json/'.$this->mhchemFilename.'</i> (<a href="'.$this->mhchemUrl.'" target="_blank">reference file</a>).',
    ];
    $form['from_internet'] = array(
      '#type' => 'checkbox',
      '#title' => t('Update from internet (<i>@url</i>).',['@url' => $this->mhchemUrl]),
    );
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Import'),
      '#button_type' => 'primary',
    ];
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    if( $form_state->getValue('from_internet') == 0 ){
      $filepath = realpath(drupal_get_path('module','inventaire_ghs').'/json/'.$this->mhchemFilename);
      if( $filepath === false ){
        $this->messenger()->addError($this->t('File @filename doesn\'t exists.', ['@filename' => $this->mhchemFilename ]));
        return;
      }
      $json = json_decode(file_get_contents($filepath));
    }else{
      $json = json_decode(file_get_contents($this->mhchemUrl));
    }

    $documentVersions = end($json->documentVersions);
    $language = $json->languages[0];
    $created = 0;
    $updated = 0;
    foreach( $json->codes as $code ){

      $vid = $this->ghs_statements_vid_h;
      if( substr($code, 0, 1) == 'P'){
        $vid = $this->ghs_statements_vid_p;
      }

      $statement = $json->statements->{$documentVersions.'/'.$language.'/'.$code};
      if( $statement == NULL ){
        continue;
      }
      $statement = str_replace(['<','>'], ['&lt;','&gt;'], $statement);

      $term = \Drupal::entityTypeManager()->getStorage('taxonomy_term')->loadByProperties(['name' => $code, 'vid' => $vid]);
      $term_tid = array_keys($term)[0];

      if( $term_tid ){
        $term = $term[$term_tid];
        $term->description->setValue($statement);
        $term->save();
        $updated++;
      }else{
        $term = Term::create([
          'vid' => $vid,
          'name' => $code,
          'description' => $statement,
        ]);
        
        $term->enforceIsNew();
        $term->save();
        $created++;
      }
    }
    $this->messenger()->addStatus($this->t('Created : @number', ['@number' => $created]));
    $this->messenger()->addStatus($this->t('Updated : @number', ['@number' => $updated]));
  }

}