<?php

declare(strict_types = 1);

namespace Drupal\Core\DependencyInjection\Compiler;

use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;

/**
 * Compiler pass to rename a tag.
 *
 * If multiple tags need to be renamed, multiple instances of this pass should
 * be created.
 */
class RenameTagsPass implements CompilerPassInterface {

  /**
   * Constructor.
   *
   * @param string $oldTagName
   *   Old tag name.
   * @param string $newTagName
   *   New tag name.
   */
  public function __construct(
    private readonly string $oldTagName,
    private readonly string $newTagName,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function process(ContainerBuilder $container) {
    foreach ($container->findTaggedServiceIds($this->oldTagName, TRUE) as $id => $tags) {
      $definition = $container->getDefinition($id);
      foreach ($tags as $tag) {
        $definition->addTag($this->newTagName, $tag);
      }
      $definition->clearTag($this->oldTagName);
    }
  }

}
