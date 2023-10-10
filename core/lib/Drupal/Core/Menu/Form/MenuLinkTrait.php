<?php

namespace Drupal\Core\Menu\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

trait MenuLinkTrait {

  use StringTranslationTrait;

  /**
   * All menu links.
   */
  protected $allMenuLinks;

  /**
   * Helper function to the list of parent link select list.
   */
  public function getParentLinkSelectList(array $all_menu_links, string $selected_menu) {
    $selected_parent_menu = '';
    foreach ($all_menu_links as $key => $value) {
      if ($key === $selected_menu) {
        $selected_parent_menu = $key;
      }
    }

    $menu_of_selected_type = [];
    foreach ($all_menu_links as $key => $value) {
      if (strpos($key, $selected_parent_menu) === 0) {
        $menu_of_selected_type[$key] = $value;
      }
    }
    unset($menu_of_selected_type[$selected_parent_menu]);
    return $menu_of_selected_type;
  }

  /**
   * Submit handler for the non-JS case.
   */
  public function updateParentLinksNonJs(array $form, FormStateInterface $form_state) {
    $selected_menu = $form_state->getValue('menu_parent_menu');

    $menu_of_selected_type = $this->getParentLinkSelectList($form_state->getValue('menu_parent'), $selected_menu);
    $form_state->set('updated_child_list', $menu_of_selected_type);
    $form_state->setRebuild();
  }

  /**
   * Callback function for updating the parent link select list.
   */
  public function updateParentLinks(array $form, FormStateInterface $form_state) {
    $selected_menu = $form_state->getValue('menu_parent_menu');

    $menu_of_selected_type = $this->getParentLinkSelectList($this->allMenuLinks, $selected_menu);
    $form_state->set('updated_child_list', array_key_first($menu_of_selected_type));
    $form['menu_parent']['#options'] = $menu_of_selected_type;
    return $form['menu_parent'];
  }

  /**
   * Helper function to build the select form elements.
   */
  protected function buildMenuFormElements(array $form, FormStateInterface $form_state, array $all_menu_links, string $menu_parent) {

    $this->allMenuLinks = $all_menu_links;
    $parent_menu_links = [];
    foreach ($this->allMenuLinks as $key => $value) {
      if (strpos($value, '<') === 0) {
        $parent_menu_links[$key] = $value;
      }
    }
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
      '#value' => $this->t('Update parent link'),
      '#submit' => ['::updateParentLinksNonJs'],
      '#attributes' => ['class' => ['js-hide']],
      '#ajax' => [
        'callback' => [$this, 'updateParentLinks'],
        'wrapper' => 'ajax-updated-section',
      ],
    ];
    $menu_of_selected_type = ($form_state->get('updated_child_list') !== NULL) ? $form_state->get('updated_child_list') : $this->getParentLinkSelectList($this->allMenuLinks, $menu_parent);

    $form['menu_parent']['#weight'] = 10;
    $form['menu_parent']['#options'] = $menu_of_selected_type;
    $form['menu_parent']['#title'] = $this->t('Parent link');
    $form['menu_parent']['#value'] = $this->allMenuLinks;
    $form['menu_parent']['#description'] = $this->t('The maximum depth for a link and all its children is fixed. Some menu links may not be available as parents if selecting them would exceed this limit.');
    $form['menu_parent']['#attributes']['class'][] = 'menu-title-select';
    $form['menu_parent']['#prefix'] = '<div id="ajax-updated-section">';
    $form['menu_parent']['#suffix'] = '</div>';

    return $form;
  }

}
