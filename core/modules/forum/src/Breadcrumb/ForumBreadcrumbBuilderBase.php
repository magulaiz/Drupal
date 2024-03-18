<?php

namespace Drupal\forum\Breadcrumb;

use Drupal\Core\Breadcrumb\BreadcrumbBuilderInterface;
use Drupal\Core\Breadcrumb\Breadcrumb;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Link;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\StringTranslation\TranslationInterface;
use Drupal\forum\ForumManagerInterface;

/**
 * Provides a forum breadcrumb base class.
 *
 * This just holds the dependency-injected config, entity type manager, and
 * forum manager objects.
 */
abstract class ForumBreadcrumbBuilderBase implements BreadcrumbBuilderInterface {
  use StringTranslationTrait;

  /**
   * The config factory.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected $configFactory;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The forum manager service.
   *
   * @var \Drupal\forum\ForumManagerInterface
   */
  protected $forumManager;

  /**
   * Constructs a forum breadcrumb builder object.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The configuration factory.
   * @param \Drupal\forum\ForumManagerInterface $forum_manager
   *   The forum manager service.
   * @param \Drupal\Core\StringTranslation\TranslationInterface $string_translation
   *   The string translation service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, ConfigFactoryInterface $config_factory, ForumManagerInterface $forum_manager, TranslationInterface $string_translation) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configFactory = $config_factory;
    $this->forumManager = $forum_manager;
    $this->setStringTranslation($string_translation);
  }

  /**
   * {@inheritdoc}
   */
  public function build(RouteMatchInterface $route_match) {
    $breadcrumb = new Breadcrumb();
    $breadcrumb->addCacheContexts(['route']);
    $config = $this->configFactory->get('forum.settings');

    $links[] = Link::createFromRoute($this->t('Home'), '<front>');

    $vocabulary = $this->entityTypeManager
      ->getStorage('taxonomy_vocabulary')
      ->load($config->get('vocabulary'));
    $breadcrumb->addCacheableDependency($vocabulary);
    $links[] = Link::createFromRoute($vocabulary->label(), 'forum.index');

    return $breadcrumb->setLinks($links);
  }

}
