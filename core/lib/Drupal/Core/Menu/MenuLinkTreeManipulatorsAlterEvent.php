<?php

namespace Drupal\Core\Menu;

use Drupal\Component\EventDispatcher\Event;

/**
 * Represents menu link tree manipulator information as event.
 */
class MenuLinkTreeManipulatorsAlterEvent extends Event {

  /**
   * The menu tree to manipulate.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeElement[]
   */
  protected $tree;

  /**
   * The menu link tree manipulators.
   *
   * @var array
   */
  protected $manipulators;

  /**
   * The menu link tree.
   *
   * @var \Drupal\Core\Menu\MenuLinkTreeInterface
   */
  protected $menuLinkTree;

  /**
   * MenuLinkTreeManipulatorsAlterEvent constructor.
   *
   * @param \Drupal\Core\Menu\MenuLinkTreeElement[] $tree
   *   The menu tree to manipulate.
   * @param array $manipulators
   *   The menu link tree manipulators.
   * @param \Drupal\Core\Menu\MenuLinkTreeInterface $menuLinkTree
   *   The menu link tree.
   */
  public function __construct(array $tree, array &$manipulators, MenuLinkTreeInterface $menuLinkTree) {
    $this->tree = $tree;
    $this->manipulators = &$manipulators;
    $this->menuLinkTree = $menuLinkTree;
   }

  /**
   * The MenuLinkElement tree.
   *
   * @return \Drupal\Core\Menu\MenuLinkTreeElement[]
   *   The menu tree.
   */
  public function getTree(): array {
    return $this->tree;
  }

  /**
   * Get the manipulators for the tree.
   *
   * @return array
   *   The menu tree manipulators.
   */
  public function &getManipulators(): array {
    return $this->manipulators;
  }

  /**
   * Set the manipulators for the tree.
   *
   * @param array $manipulators
   *   The menu tree manipulators.
   */
  public function setManipulators(array $manipulators): void {
    $this->manipulators = $manipulators;
  }

  /**
   * The MenuLinkTree.
   *
   * @return \Drupal\Core\Menu\MenuLinkTreeInterface
   *   The menu link tree.
   */
  public function getMenuLinkTree(): MenuLinkTreeInterface {
    return $this->menuLinkTree;
  }

}
