<?php

declare(strict_types=1);

namespace Drupal\navigation;

use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityViewBuilder;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\ContextAwarePluginInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\Security\TrustedCallbackInterface;
use Drupal\navigation\Entity\NavigationBlock;

/**
 * Provides a Navigation block view builder.
 */
class NavigationBlockViewBuilder extends EntityViewBuilder implements TrustedCallbackInterface {

  /**
   * {@inheritdoc}
   */
  public function buildComponents(array &$build, array $entities, array $displays, $view_mode) {
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    $build = $this->viewMultiple([$entity], $view_mode, $langcode);
    return reset($build);
  }

  /**
   * {@inheritdoc}
   */
  public function viewMultiple(array $entities = [], $view_mode = 'full', $langcode = NULL) {
    /** @var \Drupal\navigation\NavigationBlockInterface[] $entities */
    $build = [];
    foreach ($entities as $entity) {
      $entity_id = $entity->id();
      $plugin = $entity->getPlugin();

      $cache_tags = Cache::mergeTags($this->getCacheTags(), $entity->getCacheTags());
      $cache_tags = Cache::mergeTags($cache_tags, $plugin->getCacheTags());

      // Create the render array for the navigation_block as a whole.
      // @see template_preprocess_navigation_block().
      $build[$entity_id] = [
        '#cache' => [
          'keys' => ['entity_view', 'navigation_block', $entity->id()],
          'contexts' => Cache::mergeContexts(
            $entity->getCacheContexts(),
            $plugin->getCacheContexts()
          ),
          'tags' => $cache_tags,
          'max-age' => $plugin->getCacheMaxAge(),
        ],
        '#weight' => $entity->getWeight(),
      ];

      // Allow altering of cacheability metadata or setting #create_placeholder.
      // @todo Document hook_navigation_block_build_alter and
      //   hook_navigation_block_build_BASE_NAVIGATION_BLOCK_ID_alter.
      $this->moduleHandler->alter(['navigation_block_build', "navigation_block_build_" . $plugin->getBaseId()], $build[$entity_id], $plugin);

      // Assign a #lazy_builder callback, which will generate a #pre_render-
      // able navigation_block lazily (when necessary).
      $build[$entity_id] += [
        '#lazy_builder' => [static::class . '::lazyBuilder', [$entity_id, $view_mode, $langcode]],
      ];
    }

    return $build;
  }

  /**
   * Builds a #pre_render-able navigation_block render array.
   *
   * @param \Drupal\navigation\NavigationBlockInterface $entity
   *   A navigation_block config entity.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   *
   * @return array
   *   A render array with a #pre_render callback to render the
   *   navigation_block.
   */
  protected static function buildPreRenderableNavigationBlock(NavigationBlockInterface $entity, ModuleHandlerInterface $module_handler) {
    $plugin = $entity->getPlugin();
    $plugin_id = $plugin->getPluginId();
    $base_id = $plugin->getBaseId();
    $derivative_id = $plugin->getDerivativeId();
    $configuration = $plugin->getConfiguration();

    // Inject runtime contexts.
    if ($plugin instanceof ContextAwarePluginInterface) {
      $contexts = \Drupal::service('context.repository')->getRuntimeContexts($plugin->getContextMapping());
      \Drupal::service('context.handler')->applyContextMapping($plugin, $contexts);
    }

    // Create the render array for the navigation_block as a whole.
    // @see template_preprocess_navigation_block().
    $build = [
      '#theme' => 'navigation_block',
      '#attributes' => [],
      '#weight' => $entity->getWeight(),
      '#configuration' => $configuration,
      '#plugin_id' => $plugin_id,
      '#base_plugin_id' => $base_id,
      '#derivative_plugin_id' => $derivative_id,
      '#id' => $entity->id(),
      '#pre_render' => [
        static::class . '::preRender',
      ],
      // Add the entity so that it can be used in the #pre_render method.
      '#navigation_block' => $entity,
    ];

    // If an alter hook wants to modify the navigation_block contents, it can
    // append another #pre_render hook.
    $module_handler->alter(['navigation_block_view', "navigation_block_view_$base_id"], $build, $plugin);

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRender', 'lazyBuilder'];
  }

  /**
   * The #lazy_builder callback; builds a #pre_render-able navigation_block.
   *
   * @param int|string $entity_id
   *   A navigation_block config entity ID.
   * @param string $view_mode
   *   The view mode the navigation_block is being viewed in.
   *
   * @return array
   *   A render array with a #pre_render callback to render the
   *   navigation_block.
   */
  public static function lazyBuilder(int|string $entity_id, string $view_mode) {
    return static::buildPreRenderableNavigationBlock(NavigationBlock::load($entity_id), \Drupal::service('module_handler'));
  }

  /**
   * The #pre_render callback for building a navigation_block.
   *
   * Renders the content using the provided navigation_block plugin, and then:
   * - if there is no content, aborts rendering, and makes sure the
   *   navigation_block won't be rendered.
   * - if there is content, moves the contextual links from the
   *   navigation_block content to the navigation_block itself.
   */
  public static function preRender($build) {
    $content = $build['#navigation_block']->getPlugin()->build();
    // Remove the navigation_block entity from the render array, to ensure
    // that navigation_blocks can be rendered without the navigation_block
    // config entity.
    unset($build['#navigation_block']);
    if ($content !== NULL && !Element::isEmpty($content)) {
      // Place the $content returned by the navigation_block plugin into a
      // 'content' child element, as a way to allow the plugin to have complete
      // control of its properties and rendering (for instance, its own #theme)
      // without conflicting with the properties used above, or alternate ones
      // used by alternate navigation_block rendering approaches in contrib.
      // However, the use of a child element is an implementation detail of this
      // particular navigation_block rendering approach. Semantically, the
      // content returned by the plugin "is the" navigation_block, and in
      // particular, #attributes and #contextual_links is information about the
      // *entire* navigation_block. Therefore, we must move these properties
      // from $content and merge them into the top-level element.
      foreach (['#attributes', '#contextual_links'] as $property) {
        if (isset($content[$property])) {
          $build[$property] += $content[$property];
          unset($content[$property]);
        }
      }
      $build['content'] = $content;
    }
    // Either the navigation_block's content is completely empty, or it
    // consists only of cacheability metadata.
    else {
      // Abort rendering: render as the empty string and ensure this
      // navigation_block is render cached, so we can avoid the work of having
      // to repeatedly determine whether the navigation_block is empty. For
      // instance, modifying or adding entities could cause the
      // navigation_block to no longer be empty.
      $build = [
        '#markup' => '',
        '#cache' => $build['#cache'],
      ];
      // If $content is not empty, then it contains cacheability metadata, and
      // we must merge it with the existing cacheability metadata. This allows
      // navigation_blocks to be empty, yet still bubble cacheability
      // metadata, to indicate,why they are empty.
      if (!empty($content)) {
        CacheableMetadata::createFromRenderArray($build)
          ->merge(CacheableMetadata::createFromRenderArray($content))
          ->applyTo($build);
      }
    }
    return $build;
  }

}
