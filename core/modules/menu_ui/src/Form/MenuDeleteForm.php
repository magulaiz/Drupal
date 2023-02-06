<?php

namespace Drupal\menu_ui\Form;

use Drupal\Core\Database\Connection;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\EntityDeleteForm;
use Drupal\Core\Menu\MenuLinkManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Defines a confirmation form for deletion of a custom menu.
 *
 * @internal
 */
class MenuDeleteForm extends EntityDeleteForm {

  /**
   * The menu link manager.
   *
   * @var \Drupal\Core\Menu\MenuLinkManagerInterface
   */
  protected $menuLinkManager;

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new MenuDeleteForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Menu\MenuLinkManagerInterface $menu_link_manager
   *   The menu link manager.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, MenuLinkManagerInterface $menu_link_manager, Connection $connection) {
    $this->entityTypeManager = $entity_type_manager;
    $this->menuLinkManager = $menu_link_manager;
    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('plugin.manager.menu.link'),
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getDescription() {
    $caption = '';
    $num_links = $this->menuLinkManager->countMenuLinks($this->entity->id());
    if ($num_links) {
      $caption .= '<p>' . $this->formatPlural($num_links, '<strong>Warning:</strong> There is currently 1 menu link in %title. It will be deleted (system-defined links will be reset).', '<strong>Warning:</strong> There are currently @count menu links in %title. They will be deleted (system-defined links will be reset).', ['%title' => $this->entity->label()]) . '</p>';
    }
    $caption .= '<p>' . $this->t('This action cannot be undone.') . '</p>';
    return $caption;
  }

  /**
   * {@inheritdoc}
   */
  protected function logDeletionMessage() {
    $this->logger('menu')->notice('Deleted custom menu %title and all its menu links.', ['%title' => $this->entity->label()]);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Locked menus may not be deleted.
    if ($this->entity->isLocked()) {
      return;
    }

    // Delete all links to the overview page for this menu.
    // @todo Add a more generic helper function to the menu link plugin
    //   manager to remove links to an entity or other ID used as a route
    //   parameter that is being removed. Also, consider moving this to
    //   menu_ui.module as part of a generic response to entity deletion.
    //   https://www.drupal.org/node/2310329
    $menu_links = $this->menuLinkManager->loadLinksByRoute('entity.menu.edit_form', ['menu' => $this->entity->id()], TRUE);
    foreach ($menu_links as $id => $link) {
      $this->menuLinkManager->removeDefinition($id);
    }

    // Removing from associated content types' storage.
    $menu_id = $this->entity->id();
    $content_types = $this->entityTypeManager->getStorage('node_type')->loadMultiple();
    foreach ($content_types as $content_type) {
      $third_party_settings = $content_type->getThirdPartySettings('menu_ui');
      if (isset($third_party_settings['available_menus']) && in_array($menu_id, $third_party_settings['available_menus'])) {
        $key = array_search($menu_id, $third_party_settings['available_menus']);
        if ($key !== FALSE) {
          unset($third_party_settings['available_menus'][$key]);
        }
        $content_type->setThirdPartySetting('menu_ui', 'available_menus', $third_party_settings['available_menus']);
        $content_type->save();
      }
    }

    parent::submitForm($form, $form_state);
  }

}
