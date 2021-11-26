<?php

namespace Drupal\user\Plugin\views\filter;

use Drupal\Core\Entity\Element\EntityAutocomplete;
use Drupal\Core\Form\FormStateInterface;
use Drupal\user\Entity\User;
use Drupal\views\Attribute\ViewsFilter;
use Drupal\views\Plugin\views\filter\InOperator;

/**
 * Filter handler for usernames.
 *
 * @ingroup views_filter_handlers
 */
#[ViewsFilter("user_name")]
class Name extends InOperator {

  /**
   * Stores the exposed input for this filter.
   */
  public $validated_exposed_input;

  protected $alwaysMultiple = TRUE;

  /**
   * The validated exposed input.
   */
  // phpcs:ignore Drupal.NamingConventions.ValidVariableName.LowerCamelName, Drupal.Commenting.VariableComment.Missing
  protected array $validated_exposed_input;

  protected function valueForm(&$form, FormStateInterface $form_state) {
    $users = $this->value ? User::loadMultiple($this->value) : [];
    $default_value = EntityAutocomplete::getEntityLabels($users);
    $form['value'] = [
      '#type' => 'entity_autocomplete',
      '#title' => $this->t('Usernames'),
      '#description' => $this->t('Enter a comma separated list of user names.'),
      '#target_type' => 'user',
      '#tags' => TRUE,
      '#default_value' => $default_value,
      '#process_default_value' => FALSE,
    ];

    $user_input = $form_state->getUserInput();
    if ($form_state->get('exposed') && !isset($user_input[$this->options['expose']['identifier']])) {
      $user_input[$this->options['expose']['identifier']] = $default_value;
      $form_state->setUserInput($user_input);
    }
  }

  protected function valueValidate($form, FormStateInterface $form_state) {
    // Autocomplete puts the values in target_id. Move the values to the
    // expected depth.
    // @todo Consider creating a trait/whatever to avoid duplicate code here
    // and \Drupal\taxonomy\Plugin\views\filter\TaxonomyIndexTid.
    if ($this->isAGroup()) {
      if ($group_values = $form_state->getValue(['options', 'group_info', 'group_items'])) {
        foreach ($group_values as $group_id => $item) {
          $uids = [];
          if (!empty($item['value'])) {
            foreach ($item['value'] as $value) {
              $uids[] = $value['target_id'];
            }
          }
          $form_state->setValue(['options', 'group_info', 'group_items', $group_id, 'value'], $uids);
        }
      }
    }
    else {
      $uids = [];
      if (!empty($form_state->getValue(['options', 'value']))) {
        foreach ($form_state->getValue(['options', 'value']) as $value) {
          $uids[] = $value['target_id'];
        }
        sort($uids);
      }
      $form_state->setValue(['options', 'value'], $uids);
    }
  }

  public function acceptExposedInput($input) {
    $rc = parent::acceptExposedInput($input);

    if ($rc) {
      // If we have previously validated input, override.
      if (isset($this->validated_exposed_input)) {
        $this->value = $this->validated_exposed_input;
      }
    }

    return $rc;
  }

  public function validateExposed(&$form, FormStateInterface $form_state) {
    if (empty($this->options['exposed'])) {
      return;
    }

    if (empty($this->options['expose']['identifier'])) {
      return;
    }

    $identifier = $this->options['expose']['identifier'];
    $input = $form_state->getValue($identifier);

    if ($this->options['is_grouped'] && isset($this->options['group_info']['group_items'][$input])) {
      $this->operator = $this->options['group_info']['group_items'][$input]['operator'];
      $this->validated_exposed_input = $this->options['group_info']['group_items'][$input]['value'];
      return;
    }

    $uids = [];
    if ($input && (!$this->options['is_grouped'] || ($this->options['is_grouped'] && ($input != 'All')))) {
      foreach ($input as $value) {
        $uids[] = $value['target_id'];
      }
    }

    if ($uids) {
      $this->validated_exposed_input = $uids;
    }
  }

  protected function valueSubmit($form, FormStateInterface $form_state) {
    // Prevent array filter from removing our anonymous user.
  }

  /**
   * {@inheritdoc}
   */
  public function getValueOptions() {
    return $this->valueOptions;
  }

  public function adminSummary() {
    // Set up $this->valueOptions for the parent summary
    $this->valueOptions = [];

    if ($this->value) {
      $result = \Drupal::entityTypeManager()->getStorage('user')
        ->loadByProperties(['uid' => $this->value]);
      foreach ($result as $account) {
        if ($account->id()) {
          $this->valueOptions[$account->id()] = $account->label();
        }
        else {
          // Intentionally NOT translated.
          $this->valueOptions[$account->id()] = 'Anonymous';
        }
      }
    }

    return parent::adminSummary();
  }

  /**
   * {@inheritdoc}
   */
  public function buildExposedFiltersGroupForm(&$form, FormStateInterface $form_state) {
    // Rewrite the numeric values for textfields to entity labels for
    // autocomplete.
    foreach ($this->options['group_info']['group_items'] as $key => $item) {
      if (!empty($item['value'])) {
        $users = User::loadMultiple(($item['value']));
        $this->options['group_info']['group_items'][$key]['value'] = EntityAutocomplete::getEntityLabels($users);
      }
    }
    parent::buildExposedFiltersGroupForm($form, $form_state);
  }

}
