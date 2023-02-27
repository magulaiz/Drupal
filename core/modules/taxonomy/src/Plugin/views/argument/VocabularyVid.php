<?php

namespace Drupal\taxonomy\Plugin\views\argument;

use Drupal\views\Plugin\views\argument\NumericArgument;
use Drupal\taxonomy\VocabularyStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Argument handler to accept a vocabulary id.
 *
 * @ingroup views_argument_handlers
 *
 * @ViewsArgument("vocabulary_vid")
 */
class VocabularyVid extends NumericArgument {

  /**
   * Constructs the VocabularyVid object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\taxonomy\VocabularyStorageInterface $vocabularyStorage
   *   The vocabulary storage.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, protected VocabularyStorageInterface $vocabularyStorage) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')->getStorage('taxonomy_vocabulary')
    );
  }

  /**
   * Override the behavior of title(). Get the name of the vocabulary.
   */
  public function title() {
    $vocabulary = $this->vocabularyStorage->load($this->argument);
    if ($vocabulary) {
      return $vocabulary->label();
    }

    return $this->t('No vocabulary');
  }

}
