<?php

namespace Drupal\Core\Menu\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Menu\MenuLinkInterface;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Menu\MenuParentFormSelectorInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;

/**
 * Provides an edit form for static menu links.
 *
 * @see \Drupal\Core\Menu\MenuLinkDefault
 */
class MenuLinkDefaultForm implements MenuLinkFormInterface, ContainerInjectionInterface {

  use StringTranslationTrait;
  use DependencySerializationTrait;

  /**
   * The edited menu link.
   *
   * @var \Drupal\Core\Menu\MenuLinkInterface
   */
  protected $menuLink;

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * The parent form selector service.
   *
   * @var \Drupal\Core\Menu\MenuParentFormSelectorInterface
   */
  protected $menuParentSelector;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new \Drupal\Core\Menu\Form\MenuLinkDefaultForm.
   *
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link manager.
   * @param \Drupal\Core\Menu\MenuParentFormSelectorInterface $menu_parent_selector
   *   The menu parent form selector service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler;
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface|null $entityTypeManager
   *   The entity type manager service.
   */
  public function __construct(MenuLinkManagerInterface $menu_link_manager, MenuParentFormSelectorInterface $menu_parent_selector, TranslationInterface $string_translation, ModuleHandlerInterface $module_handler, protected ?EntityTypeManagerInterface $entityTypeManager = NULL) {
    $this->menuLinkManager = $menu_link_manager;
    $this->menuParentSelector = $menu_parent_selector;
    $this->stringTranslation = $string_translation;
    $this->moduleHandler = $module_handler;
    if ($this->entityTypeManager === NULL) {
      $this->entityTypeManager = \Drupal::service('entity_type.manager');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('plugin.manager.menu.link'),
      $container->get('menu.parent_form_selector'),
      $container->get('string_translation'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function setMenuLinkInstance(MenuLinkInterface $menu_link) {
    $this->menuLink = $menu_link;
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $form['#title'] = $this->t('Edit menu link %title', ['%title' => $this->menuLink->getTitle()]);

    $provider = $this->menuLink->getProvider();
    $form['info'] = [
      '#type' => 'item',
      '#title' => $this->t('This link is provided by the @name module. The title and path cannot be edited.', ['@name' => $this->moduleHandler->getName($provider)]),
    ];
    $form['id'] = [
      '#type' => 'value',
      '#value' => $this->menuLink->getPluginId(),
    ];
    $link = [
      '#type' => 'link',
      '#title' => $this->menuLink->getTitle(),
      '#url' => $this->menuLink->getUrlObject(),
    ];
    $form['path'] = [
      'link' => $link,
      '#type' => 'item',
      '#title' => $this->t('Link'),
    ];

    $form['enabled'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Enable menu link'),
      '#description' => $this->t('Menu links that are not enabled will not be listed in any menu.'),
      '#default_value' => $this->menuLink->isEnabled(),
    ];
    $form['#attached']['library'] = [
      'core/drupal.ajax',
    ];

    $form['expanded'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show as expanded'),
      '#description' => $this->t('If selected and this menu link has children, the menu will always appear expanded. This option may be overridden for the entire menu tree when placing a menu block.'),
      '#default_value' => $this->menuLink->isExpanded(),
    ];

    $menu_parent = $this->menuLink->getMenuName() . ':' . $this->menuLink->getParent();
    $default_menu_id = $this->menuLink->getMenuName();
    $default_menu = $this->entityTypeManager->getStorage('menu')->load($default_menu_id);

    $form += $this->menuParentSelector->parentSelectElement($form_state->getValue('menu') ?: $default_menu_id, '', NULL, $form_state->getValue('menu') ?: $menu_parent . ':');

    $form['menu_parent_fieldset']['menu_parent_wrapper']['menu_parent'] = $this->menuParentSelector->parentSelectElement($form_state->getValue('menu') ?: $menu_parent, $this->menuLink->getPluginId(), $form_state->getValue('menus') ?: [$default_menu_id => $default_menu->label()]);
    $form['menu_parent_fieldset']['menu_parent_wrapper']['menu_parent']['#title'] = $this->t('Parent link');
    $form['menu_parent_fieldset']['menu_parent_wrapper']['menu_parent']['#weight'] = 10;
    $form['menu_parent_fieldset']['menu_parent_wrapper']['menu_parent']['#description'] = $this->t("Links located in a menu's maximum depth will not be available.");
    $form['menu_parent_fieldset']['menu_parent_wrapper']['menu_parent']['#attributes']['class'][] = 'menu-title-select';

    $delta = max(abs($this->menuLink->getWeight()), 50);
    $form['weight'] = [
      '#type' => 'number',
      '#min' => -$delta,
      '#max' => $delta,
      '#default_value' => $this->menuLink->getWeight(),
      '#title' => $this->t('Weight'),
      '#description' => $this->t('Link weight among links in the same menu at the same depth. In the menu, the links with high weight will sink and links with a low weight will be positioned nearer the top.'),
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function extractFormValues(array &$form, FormStateInterface $form_state) {
    // Start from the complete, original, definition.
    $new_definition = $this->menuLink->getPluginDefinition();
    // Since the ID may not be present in the definition used to construct the
    // plugin, add it here so it's available to any consumers of this method.
    $new_definition['id'] = $form_state->getValue('id');
    $new_definition['enabled'] = $form_state->getValue('enabled') ? 1 : 0;
    $new_definition['weight'] = (int) $form_state->getValue('weight');
    $new_definition['expanded'] = $form_state->getValue('expanded') ? 1 : 0;
    [$menu_name, $parent] = explode(':', $form_state->getValue('menu_parent'), 2);
    if (!empty($menu_name)) {
      $new_definition['menu_name'] = $menu_name;
    }
    if (isset($parent)) {
      $new_definition['parent'] = $parent;
    }
    return $new_definition;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $new_definition = $this->extractFormValues($form, $form_state);

    return $this->menuLinkManager->updateDefinition($this->menuLink->getPluginId(), $new_definition);
  }

}
