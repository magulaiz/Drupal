<?php

namespace Drupal\comment\Plugin\views\field;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Handler for showing comment module's entity links.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("comment_entity_link")
 */
class EntityLink extends FieldPluginBase {

  /**
   * Stores the result of parent entities build for all rows to reuse it later.
   *
   * @var array
   */
  protected $build;

  /**
   * Constructs an EntityLink object.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Render\RendererInterface|null $renderer
   *   The renderer service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, RendererInterface $renderer = NULL) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    if (!$renderer) {
      @trigger_error('Calling ' . __METHOD__ . '() without the $renderer argument is deprecated in drupal:10.1.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/2876656', E_USER_DEPRECATED);
      $renderer = \Drupal::service('renderer');
    }
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition, $container->get('renderer'));
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();
    $options['teaser'] = ['default' => FALSE];
    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function buildOptionsForm(&$form, FormStateInterface $form_state) {
    $form['teaser'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Show teaser-style link'),
      '#default_value' => $this->options['teaser'],
      '#description' => $this->t('Show the comment link in the form used on standard entity teasers, rather than the full entity form.'),
    ];

    parent::buildOptionsForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function query() {}

  /**
   * {@inheritdoc}
   */
  public function preRender(&$values) {
    // Render all nodes, so you can grep the comment links.
    $entities = [];
    foreach ($values as $row) {
      $entity = $row->_entity;
      $entities[$entity->id()] = $entity;
    }
    if ($entities) {
      $entityTypeId = reset($entities)->getEntityTypeId();
      $viewMode = $this->options['teaser'] ? 'teaser' : 'full';
      $this->build = \Drupal::entityTypeManager()
        ->getViewBuilder($entityTypeId)
        ->viewMultiple($entities, $viewMode);
    }
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    $entity = $this->getEntity($values);

    // Only render the links, if they are defined.
    return !empty($this->build[$entity->id()]['links']['comment__comment']) ? $this->renderer->render($this->build[$entity->id()]['links']['comment__comment']) : '';
  }

}
