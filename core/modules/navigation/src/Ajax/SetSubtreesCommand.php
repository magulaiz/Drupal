<?php

namespace Drupal\navigation\Ajax;

use Drupal\Core\Ajax\CommandInterface;

/**
 * Defines an AJAX command that sets the navigation subtrees.
 *
 * @internal
 */
final class SetSubtreesCommand implements CommandInterface {

  /**
   * Constructs a SetSubtreesCommand object.
   *
   * @param $hash
   *   The navigation menu block hash.
   * @param string $subtrees
   *   The navigation menu block subtree that will be set.
   */
  public function __construct(
    protected string $hash,
    protected string $subtrees,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function render() {
    return [
      'command' => 'setNavigationSubtree',
      'hash' => $this->hash,
      'subtrees' => $this->subtrees,
    ];
  }

}
