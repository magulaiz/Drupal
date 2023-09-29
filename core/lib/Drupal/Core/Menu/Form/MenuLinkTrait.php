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
   * Callback function for updating the parent link select list.
   */
  public function updateParentLinks(array $form, FormStateInterface $form_state) {
    $selected_menu = $form_state->getValue('menu_parent_menu');

    $selected_parent_menu = '';
    foreach ($this->allMenuLinks as $key => $value) {
      if ($value === $selected_menu) {
        $selected_parent_menu = $key;
      }
    }

    $menu_of_selected_type = [];
    foreach ($this->allMenuLinks as $key => $value) {
      if (strpos($key, $selected_parent_menu) === 0) {
        $menu_of_selected_type[$key] = $value;
      }
    }

    $form['menu_parent']['#options'] = $menu_of_selected_type;
    return $form['menu_parent'];
  }

  /**
   * Helper function to build the select form elements.
   */
  protected function buildMenuFormElements(array $form, array $all_menu_links) {

    $this->allMenuLinks = $all_menu_links;
    $parent_menu_links = [];
    foreach ($this->allMenuLinks as $menu_link) {
      if (strpos($menu_link, '<') === 0) {
        $parent_menu_links[$menu_link] = $menu_link;
      }
    }
    $form['menu_parent_menu'] = [
      '#type' => 'select',
      '#title' => $this->t('Menu'),
      '#description' => $this->t('Select the menu'),
      '#options' => $parent_menu_links,
      '#ajax' => [
        'callback' => [$this, 'updateParentLinks'],
        'wrapper' => 'ajax-updated-section',
      ],
    ];
    $form['menu_parent'] = [
      '#type' => 'select',
      '#title' => $this->t('Parent link'),
      '#description' => $this->t('The maximum depth for a link and all its children is fixed. Some menu links may not be available as parents if selecting them would exceed this limit.'),
      '#options' => $this->allMenuLinks,
      '#prefix' => '<div id="ajax-updated-section">',
      '#suffix' => '</div>',
    ];

    return $form;
  }

}
