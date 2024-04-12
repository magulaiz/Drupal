<?php

declare(strict_types=1);

namespace Drupal\navigation\Entity;

use Drupal\Core\Condition\ConditionPluginCollection;
use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Entity\EntityWithPluginCollectionInterface;
use Drupal\navigation\NavigationBlockInterface;
use Drupal\navigation\NavigationBlockPluginCollection;

/**
 * Defines the navigation block entity type.
 *
 * @ConfigEntityType(
 *   id = "navigation_block",
 *   label = @Translation("Navigation block"),
 *   label_collection = @Translation("Navigation blocks"),
 *   label_singular = @Translation("navigation block"),
 *   label_plural = @Translation("navigation blocks"),
 *   label_count = @PluralTranslation(
 *     singular = "@count navigation block",
 *     plural = "@count navigation blocks",
 *   ),
 *   handlers = {
 *     "access" = "Drupal\navigation\NavigationBlockAccessControlHandler",
 *     "view_builder" = "Drupal\navigation\NavigationBlockViewBuilder",
 *     "list_builder" = "Drupal\navigation\NavigationBlockListBuilder",
 *     "form" = {
 *       "default" = "Drupal\navigation\Form\NavigationBlockForm",
 *       "delete" = "Drupal\navigation\Form\NavigationBlockDeleteForm",
 *     },
 *   },
 *   admin_permission = "administer navigation_block",
 *   entity_keys = {
 *     "id" = "id",
 *     "status" = "status",
 *   },
 *   links = {
 *     "delete-form" = "/admin/config/user-interface/navigation-block/manage/{navigation_block}/delete",
 *     "edit-form" = "/admin/config/user-interface/navigation-block/manage/{navigation_block}",
 *     "enable" = "/admin/config/user-interface/navigation-block/manage/{navigation_block}/enable",
 *     "disable" = "/admin/config/user-interface/navigation-block/manage/{navigation_block}/disable",
 *   },
 *   config_export = {
 *     "id",
 *     "region",
 *     "weight",
 *     "provider",
 *     "plugin",
 *     "settings",
 *     "visibility",
 *   },
 * )
 */
class NavigationBlock extends ConfigEntityBase implements NavigationBlockInterface, EntityWithPluginCollectionInterface {

  /**
   * The ID of the navigation_block.
   *
   * @var string
   */
  protected string $id;

  /**
   * The plugin instance settings.
   *
   * @var array
   */
  protected array $settings = [];

  /**
   * The region this navigation_block is placed in.
   *
   * @var string|null
   */
  protected ?string $region = NULL;

  /**
   * The navigation_block weight.
   *
   * @var int
   */
  protected int $weight;

  /**
   * The plugin instance ID.
   *
   * @var string
   */
  protected string $plugin;

  /**
   * The visibility settings for this navigation_block.
   *
   * @var array
   */
  protected array $visibility = [];

  /**
   * The plugin collection that holds the navigation_block plugin.
   *
   * @var \Drupal\block\BlockPluginCollection
   */
  protected $pluginCollection;

  /**
   * The available contexts for this block and its visibility conditions.
   *
   * @var array
   */
  protected $contexts = [];

  /**
   * The visibility collection.
   *
   * @var \Drupal\Core\Condition\ConditionPluginCollection
   */
  protected $visibilityCollection;

  /**
   * The condition plugin manager.
   *
   * @var \Drupal\Core\Executable\ExecutableManagerInterface
   */
  protected $conditionPluginManager;

  /**
   * The theme that includes the block plugin for this entity.
   *
   * @var string
   */
  protected $theme;

  /**
   * {@inheritdoc}
   */
  public function getPlugin() {
    return $this->getPluginCollection()->get($this->plugin);
  }

  /**
   * Encapsulates the creation of the navigation_block's LazyPluginCollection.
   *
   * @return \Drupal\Component\Plugin\LazyPluginCollection
   *   The navigation_block's plugin collection.
   */
  protected function getPluginCollection() {
    if (!$this->pluginCollection) {
      $this->pluginCollection = new NavigationBlockPluginCollection(\Drupal::service('plugin.manager.navigation_block'), $this->plugin, $this->get('settings'), $this->id());
    }
    return $this->pluginCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginCollections() {
    return [
      'settings' => $this->getPluginCollection(),
      'visibility' => $this->getVisibilityConditions(),
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getPluginId() {
    return $this->plugin;
  }

  /**
   * {@inheritdoc}
   */
  public function getRegion() {
    return $this->region;
  }

  /**
   * {@inheritdoc}
   */
  public function getWeight() {
    return $this->weight;
  }

  /**
   * {@inheritdoc}
   */
  public function label() {
    $settings = $this->get('settings');
    if ($settings['label']) {
      return $settings['label'];
    }
    else {
      $definition = $this->getPlugin()->getPluginDefinition();
      return $definition['admin_label'];
    }
  }

  /**
   * Sort helper.
   *
   * Sorts active navigation_blocks by weight; sorts inactive
   * navigation_blocks by name.
   */
  public static function sort(ConfigEntityInterface $a, ConfigEntityInterface $b) {
    // Separate enabled from disabled.
    $status = (int) $b->status() - (int) $a->status();
    if ($status !== 0) {
      return $status;
    }

    // Sort by weight.
    $weight = $a->getWeight() - $b->getWeight();
    if ($weight) {
      return $weight;
    }

    // Sort by label.
    return strcmp($a->label(), $b->label());
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibility() {
    return $this->getVisibilityConditions()->getConfiguration();
  }

  /**
   * {@inheritdoc}
   */
  public function setVisibilityConfig($instance_id, array $configuration) {
    $conditions = $this->getVisibilityConditions();
    if (!$conditions->has($instance_id)) {
      $configuration['id'] = $instance_id;
      $conditions->addInstanceId($instance_id, $configuration);
    }
    else {
      $conditions->setInstanceConfiguration($instance_id, $configuration);
    }
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibilityConditions() {
    if (!isset($this->visibilityCollection)) {
      $this->visibilityCollection = new ConditionPluginCollection($this->conditionPluginManager(), $this->get('visibility'));
    }
    return $this->visibilityCollection;
  }

  /**
   * {@inheritdoc}
   */
  public function getVisibilityCondition($instance_id) {
    return $this->getVisibilityConditions()->get($instance_id);
  }

  /**
   * Gets the condition plugin manager.
   *
   * @return \Drupal\Core\Executable\ExecutableManagerInterface
   *   The condition plugin manager.
   */
  protected function conditionPluginManager() {
    if (!isset($this->conditionPluginManager)) {
      $this->conditionPluginManager = \Drupal::service('plugin.manager.condition');
    }
    return $this->conditionPluginManager;
  }

  /**
   * {@inheritdoc}
   */
  public function setRegion($region) {
    $this->region = $region;
    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function setWeight($weight) {
    $this->weight = $weight;
    return $this;
  }

}
