<?php

namespace Drupal\Core\Menu\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Provides a helper to build select elements for menu link forms.
 *
 * @internal
 */
trait MenuLinkTrait {

  use StringTranslationTrait;

  /**
   * Helper function to list the parent link select list.
   *
   * @param array $all_menu_links
   *   An array containing list of all menu links.
   * @param string $selected_menu
   *   The selected parent menu.
   */
  public function getParentLinkSelectList(array $all_menu_links, string $selected_menu) : array {
    $menu_of_selected_type = [];
    foreach ($all_menu_links as $key => $value) {
      if (strpos($key, $selected_menu) === 0) {
        $menu_of_selected_type[$key] = $value;
      }
    }
    return $menu_of_selected_type;
  }

  /**
   * Submit handler for the non-JS case.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function updateParentLinksNonJs(array $form, FormStateInterface $form_state) : void {
    $selected_menu = $form_state->getValue('menu_parent_menu');

    $menu_of_selected_type = $this->getParentLinkSelectList($form_state->getValue('all_menu_links'), $selected_menu);
    $form_state->set('updated_child_list', $menu_of_selected_type);
    $form_state->setRebuild();
  }

  /**
   * Callback function for updating the parent link select list.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   */
  public function updateParentLinks(array $form, FormStateInterface $form_state) : array {
    $menu_of_selected_type = $form_state->get('updated_child_list');
    $form['menu_parent']['#options'] = $menu_of_selected_type;
    return $form['menu_parent'];
  }

  /**
   * Helper function to build the select form elements.
   *
   * @param array $form
   *   An associative array containing the structure of the form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   * @param array $all_menu_links
   *   An array containing list of all menu links.
   * @param string $menu_parent
   *   The selected parent menu.
   */
  protected function buildMenuFormElements(array $form, FormStateInterface $form_state, array $all_menu_links, string $menu_parent) : array {
    $parent_menu_links = [];
    foreach ($all_menu_links as $key => $value) {
      // Building the list of parent menus.
      if (explode(':', $key, 2)[1] === '') {
        $parent_menu_links[$key] = $value;
      }
    }
    $form['all_menu_links'] = [
      '#type' => 'value',
      '#value' => $all_menu_links,
    ];

    $form['menu_parent_menu']['#weight'] = 10;
    $form['menu_parent_menu']['#options'] = $parent_menu_links;
    $form['menu_parent_menu']['#title'] = $this->t('Menu');
    $form['menu_parent_menu']['#description'] = $this->t('Select menu');
    $form['menu_parent_menu']['#attributes']['class'][] = 'menu-title-select';
    $form['menu_parent_menu']['#ajax'] = [
      'callback' => [$this, 'updateParentLinks'],
      'wrapper' => 'ajax-updated-section',
      'trigger_as' => ['name' => 'update_parent_links'],
      'event' => 'change',
    ];

    $form['menu_parent_submit'] = [
      '#type' => 'submit',
      '#name' => 'update_parent_links',
      '#value' => $this->t('Change selected menu'),
      '#submit' => ['::updateParentLinksNonJs'],
      '#attributes' => ['class' => ['js-hide']],
      '#ajax' => [
        'callback' => [$this, 'updateParentLinks'],
        'wrapper' => 'ajax-updated-section',
      ],
      '#weight' => 11,
    ];

    $menu_of_selected_type = ($form_state->get('updated_child_list') !== NULL) ? $form_state->get('updated_child_list') : $this->getParentLinkSelectList($all_menu_links, $menu_parent);

    $form['menu_parent']['#weight'] = 12;
    $form['menu_parent']['#options'] = $menu_of_selected_type;
    $form['menu_parent']['#title'] = $this->t('Parent link');
    $form['menu_parent']['#description'] = $this->t('The maximum depth for a link and all its children is fixed. Some menu links may not be available as parents if selecting them would exceed this limit.');
    $form['menu_parent']['#attributes']['class'][] = 'menu-title-select';
    $form['menu_parent']['#prefix'] = '<div id="ajax-updated-section">';
    $form['menu_parent']['#suffix'] = '</div>';

    return $form;
  }

}
