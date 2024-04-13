<?php

namespace Drupal\layout_builder\Plugin\SectionStorage;

use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Plugin\Context\Context;
use Drupal\Core\Plugin\Context\ContextDefinition;
use Drupal\Core\Plugin\ContextAwarePluginTrait;
use Drupal\Core\Plugin\PluginBase;
use Drupal\layout_builder\Routing\LayoutBuilderRoutesTrait;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionStorageInterface;
use Drupal\layout_builder\TempStoreIdentifierInterface;

/**
 * Provides a base class for Section Storage types.
 */
abstract class SectionStorageBase extends PluginBase implements SectionStorageInterface, TempStoreIdentifierInterface, CacheableDependencyInterface {

  use ContextAwarePluginTrait;
  use LayoutBuilderRoutesTrait;

  /**
   * Gets the section list.
   *
   * @return \Drupal\layout_builder\SectionListInterface
   *   The section list.
   */
  abstract protected function getSectionList();

  /**
   * {@inheritdoc}
   */
  public function getStorageType() {
    return $this->getPluginId();
  }

  /**
   * {@inheritdoc}
   */
  public function count(): int {
    return $this->getSectionList()->count();
  }

  /**
   * {@inheritdoc}
   */
  public function getSections(bool $key_by_uuid = FALSE) {
    return $this->getSectionList()->getSections($key_by_uuid);
  }

  /**
   * {@inheritdoc}
   */
  public function getSection($uuid) {
    return $this->getSectionList()->getSection($uuid);
  }

  /**
   * {@inheritdoc}
   */
  public function appendSection(Section $section) {
    $this->getSectionList()->appendSection($section);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function insertSection($delta, Section $section) {
    $this->getSectionList()->insertSection($delta, $section);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeSection($uuid) {
    $this->getSectionList()->removeSection($uuid);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeAllSections($set_blank = FALSE) {
    $this->getSectionList()->removeAllSections($set_blank);
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getContextsDuringPreview() {
    $contexts = $this->getContexts();

    // view_mode is a required context, but SectionStorage plugins are not
    // required to return it (for example, the layout_library plugin provided
    // in the Layout Library module. In these instances, explicitly create a
    // view_mode context with the value "default".
    if (!isset($contexts['view_mode']) || $contexts['view_mode']->validate()->count() || !$contexts['view_mode']->getContextValue()) {
      $contexts['view_mode'] = new Context(new ContextDefinition('string'), 'default');
    }
    return $contexts;
  }

  /**
   * {@inheritdoc}
   */
  public function getTempstoreKey() {
    return $this->getStorageId();
  }

}
