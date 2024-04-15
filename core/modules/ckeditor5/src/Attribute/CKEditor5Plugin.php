<?php

declare(strict_types = 1);

namespace Drupal\ckeditor5\Attribute;

use Drupal\ckeditor5\Plugin\CKEditor5PluginDefinition;
use Drupal\Component\Plugin\Attribute\Plugin;

/**
 * The CKEditor5Plugin attribute.
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
class CKEditor5Plugin extends Plugin {

  /**
   * Constructs a CKEditor5Plugin attribute.
   *
   * @param string $id
   *   The plugin ID.
   * @param \Drupal\ckeditor5\Attribute\CKEditor5AspectsIfCKEditor5Plugin $ckeditor5
   *   The CKEditor 5 aspects of the plugin definition.
   * @param \Drupal\ckeditor5\Attribute\DrupalAspectsOfCKEditor5Plugin $drupal
   *   The Drupal aspects of the plugin definition.
   */
  public function __construct(
    public readonly string $id,
    public readonly CKEditor5AspectsIfCKEditor5Plugin $ckeditor5,
    public readonly DrupalAspectsOfCKEditor5Plugin $drupal,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function getClass(): string {
    return $this->drupal->getClass();
  }

  /**
   * {@inheritdoc}
   */
  public function setClass($class): void {
    $this->drupal->setClass($class);
  }

  /**
   * {@inheritdoc}
   */
  public function get(): CKEditor5PluginDefinition {
    return new CKEditor5PluginDefinition([
      'id' => $this->id,
      'ckeditor' => $this->ckeditor5,
      'drupal' => $this->drupal,
    ]);
  }

}
