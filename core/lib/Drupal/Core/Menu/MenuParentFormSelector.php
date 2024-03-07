<?php

namespace Drupal\Core\Menu;

use Drupal\Component\Utility\Html;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;

/**
 * Default implementation of the menu parent form selector service.
 *
 * The form selector is a list of all appropriate menu links.
 */
class MenuParentFormSelector implements MenuParentFormSelectorInterface {
  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The menu link tree service.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * The entity type manager service.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a \Drupal\Core\Menu\MenuParentFormSelector.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menu_link_tree
   *   The menu link tree service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(MenuLinkTreeInterface $menu_link_tree, EntityTypeManagerInterface $entity_type_manager, TranslationInterface $string_translation) {
    $this->menuLinkTree = $menu_link_tree;
    $this->entityTypeManager = $entity_type_manager;
    $this->stringTranslation = $string_translation;
  }

  /**
   * {@inheritdoc}
   */
  public function getMenuSelectOptions(array $menus = NULL) {
    if (!isset($menus)) {
      $menus = $this->getMenuOptions();
    }

    $options = [];
    foreach ($menus as $menu_name => $menu_title) {
      $options[$menu_name . ':'] = $menu_title;
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function getParentSelectOptions($id = '', array $menus = NULL, CacheableMetadata &$cacheability = NULL) {
    if (!isset($menus)) {
      $menus = $this->getMenuOptions();
    }

    $options = [];
    $depth_limit = $this->getParentDepthLimit($id);
    foreach ($menus as $menu_name => $menu_title) {
      $options[$menu_name . ':'] = $menu_title;

      $parameters = new MenuTreeParameters();
      $parameters->setMaxDepth($depth_limit);
      $tree = $this->menuLinkTree->load($menu_name, $parameters);
      $manipulators = [
        ['callable' => 'menu.default_tree_manipulators:checkNodeAccess'],
        ['callable' => 'menu.default_tree_manipulators:checkAccess'],
        ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
      ];
      $tree = $this->menuLinkTree->transform($tree, $manipulators);
      $this->parentSelectOptionsTreeWalk($tree, $menu_name, '--', $options, $id, $depth_limit, $cacheability);
    }
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function parentSelectElement($menu_parent, $id = '', array $menus = NULL, $menu_name = 'parent_select') {
    $options_cacheability = new CacheableMetadata();
    $options = $this->getParentSelectOptions($id, $menus, $options_cacheability);
    // If no options were found, there is nothing to select.
    if ($options && $menu_name === 'parent_select') {
      $element = [
        '#type' => 'select',
        '#options' => $options,
      ];
      if (!isset($options[$menu_parent])) {
        // The requested menu parent cannot be found in the menu anymore. Try
        // setting it to the top level in the current menu.
        [$menu_name] = explode(':', $menu_parent, 2);
        $menu_parent = $menu_name . ':';
      }
      if (isset($options[$menu_parent])) {
        // Only provide the default value if it is valid among the options.
        $element += ['#default_value' => $menu_parent];
      }
      $options_cacheability->applyTo($element);
      return $element;
    }
    else {
      $options = $this->getMenuSelectOptions($menus);
      if ($options) {
        $menu_parent_wrapper = Html::getUniqueId('menu-parent-wrapper');
        $elements['menu'] = [
          '#title' => $this->t('Menu'),
          '#type' => 'select',
          '#options' => $options,
          '#attributes' => ['class' => ['menu-title-select']],
          '#ajax' => [
            'callback' => [$this, 'updateParentLinks'],
            'wrapper' => $menu_parent_wrapper,
            'trigger_as' => ['name' => 'update_parent_links'],
            'event' => 'change',
          ],
        ];
        if (isset($options[$menu_parent])) {
          // Only provide the default value if it is valid among the options.
          $elements['menu'] += ['#default_value' => $menu_parent];
        }
        $elements['menu_submit'] = [
          '#type' => 'submit',
          '#name' => 'update_parent_links',
          '#value' => $this->t('Change menu'),
          '#submit' => [[$this, 'updateParentLinksSubmit']],
          '#attributes' => ['class' => ['js-hide']],
          '#ajax' => [
            'callback' => [$this, 'updateParentLinks'],
            'wrapper' => $menu_parent_wrapper,
          ],
        ];
        $elements['menu_parent'] = [];
        $elements_wrapper = [
          'menu_parent_fieldset' => [
            '#type' => 'fieldset',
            '#title' => '<span class="visually-hidden">Title</span>',
            'menu_parent_wrapper' => [
              '#type' => 'container',
              '#attributes' => ['id' => $menu_parent_wrapper],
              'menu_parent' => $elements['menu_parent'],
              'menu' => $elements['menu'],
              'submit' => $elements['menu_submit'],
              '#ajax' => [
                'callback' => [$this, 'updateParentLinks'],
                'wrapper' => $menu_parent_wrapper,
              ],
            ],
          ],
        ];
        return $elements_wrapper;
      }
    }
    return [];
  }

  /**
   * AJAX callback for updating menu parent options.
   */
  public function updateParentLinks(array $form, FormStateInterface $form_state) : array {
    return $form['menu_parent_fieldset']['menu_parent_wrapper'];
  }

  /**
   * Submit handler for the 'Change menu' element.
   */
  public function updateParentLinksSubmit(array $form, FormStateInterface $form_state) : void {
    // @todo why do we need to rtrim?
    $menu_name = rtrim($form_state->getValue('menu'), ':');
    $form_state->setValue('menus', $this->getMenuOptions([$menu_name]));
    $form_state->setRebuild();
  }

  /**
   * Returns the maximum depth of the possible parents of the menu link.
   *
   * @param string $id
   *   The menu link plugin ID or an empty value for a new link.
   *
   * @return int
   *   The depth related to the depth of the given menu link.
   */
  protected function getParentDepthLimit($id) {
    if ($id) {
      $limit = $this->menuLinkTree->maxDepth() - $this->menuLinkTree->getSubtreeHeight($id);
    }
    else {
      $limit = $this->menuLinkTree->maxDepth() - 1;
    }
    return $limit;
  }

  /**
   * Iterates over all items in the tree to prepare the parents select options.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The menu tree.
   * @param string $menu_name
   *   The menu name.
   * @param string $indent
   *   The indentation string used for the label.
   * @param array $options
   *   The select options.
   * @param string $exclude
   *   An excluded menu link.
   * @param int $depth_limit
   *   The maximum depth of menu links considered for the select options.
   * @param \Drupal\Core\Cache\CacheableMetadata|null &$cacheability
   *   The object to add cacheability metadata to, if not NULL.
   */
  protected function parentSelectOptionsTreeWalk(array $tree, $menu_name, $indent, array &$options, $exclude, $depth_limit, CacheableMetadata &$cacheability = NULL) {
    foreach ($tree as $element) {
      if ($element->depth > $depth_limit) {
        // Don't iterate through any links on this level.
        break;
      }

      // Collect the cacheability metadata of the access result, as well as the
      // link.
      if ($cacheability) {
        $cacheability = $cacheability
          ->merge(CacheableMetadata::createFromObject($element->access))
          ->merge(CacheableMetadata::createFromObject($element->link));
      }

      // Only show accessible links.
      if (!$element->access->isAllowed()) {
        continue;
      }

      $link = $element->link;
      if ($link->getPluginId() != $exclude) {
        $title = $indent . ' ' . Unicode::truncate($link->getTitle(), 30, TRUE, FALSE);
        if (!$link->isEnabled()) {
          $title .= ' (' . $this->t('disabled') . ')';
        }
        $options[$menu_name . ':' . $link->getPluginId()] = $title;
        if (!empty($element->subtree)) {
          $this->parentSelectOptionsTreeWalk($element->subtree, $menu_name, $indent . '--', $options, $exclude, $depth_limit, $cacheability);
        }
      }
    }
  }

  /**
   * Gets a list of menu names for use as options.
   *
   * @param array $menu_names
   *   (optional) Array of menu names to limit the options, or NULL to load all.
   *
   * @return array
   *   Keys are menu names (ids) values are the menu labels.
   */
  protected function getMenuOptions(array $menu_names = NULL) {
    $menus = $this->entityTypeManager->getStorage('menu')->loadMultiple($menu_names);
    $options = [];
    /** @var \Drupal\system\MenuInterface[] $menus */
    foreach ($menus as $menu) {
      $options[$menu->id()] = $menu->label();
    }
    return $options;
  }

}
