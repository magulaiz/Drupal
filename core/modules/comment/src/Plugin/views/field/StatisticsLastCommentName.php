<?php

namespace Drupal\comment\Plugin\views\field;

use Drupal\Core\Render\RendererInterface;
use Drupal\user\Entity\User;
use Drupal\views\Plugin\views\field\FieldPluginBase;
use Drupal\views\ResultRow;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Field handler to present the name of the last comment poster.
 *
 * @ingroup views_field_handlers
 *
 * @ViewsField("comment_ces_last_comment_name")
 */
class StatisticsLastCommentName extends FieldPluginBase {

  /**
   * The users table.
   */
  protected ?string $user_table;

  /**
   * The user name field.
   */
  protected string $user_field;

  /**
   * The user id.
   */
  public string $uid;

  /**
   * Constructs an StatisticsLastCommentName object.
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
  public function query() {
    // last_comment_name only contains data if the user is anonymous. So we
    // have to join in a specially related user table.
    $this->ensureMyTable();
    // join 'users' to this table via vid
    $definition = [
      'table' => 'users_field_data',
      'field' => 'uid',
      'left_table' => 'comment_entity_statistics',
      'left_field' => 'last_comment_uid',
      'extra' => [
        [
          'field' => 'uid',
          'operator' => '!=',
          'value' => '0',
        ],
      ],
    ];
    $join = \Drupal::service('plugin.manager.views.join')->createInstance('standard', $definition);

    // nes_user alias so this can work with the sort handler, below.
    $this->user_table = $this->query->ensureTable('ces_users', $this->relationship, $join);

    $this->field_alias = $this->query->addField(NULL, "COALESCE($this->user_table.name, $this->tableAlias.$this->field)", $this->tableAlias . '_' . $this->field);

    $this->user_field = $this->query->addField($this->user_table, 'name');
    $this->uid = $this->query->addField($this->tableAlias, 'last_comment_uid');
  }

  /**
   * {@inheritdoc}
   */
  protected function defineOptions() {
    $options = parent::defineOptions();

    $options['link_to_user'] = ['default' => TRUE];

    return $options;
  }

  /**
   * {@inheritdoc}
   */
  public function render(ResultRow $values) {
    if (!empty($this->options['link_to_user'])) {
      $account = User::create();
      $account->name = $this->getValue($values);
      $account->uid = $values->{$this->uid};
      $username = [
        '#theme' => 'username',
        '#account' => $account,
      ];
      return $this->renderer->render($username);
    }
    else {
      return $this->sanitizeValue($this->getValue($values));
    }
  }

}
