<?php

namespace Drupal\node\Plugin\Action;

use Drupal\Core\Action\ConfigurableActionBase;
use Drupal\Core\Action\Attribute\Action;
use Drupal\Core\Database\Connection;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\user\Entity\User;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Assigns ownership of a node to a user.
 */
#[Action(
  id: 'node_assign_owner_action',
  label: new TranslatableMarkup('Change the author of content'),
  type: 'node'
)]
class AssignOwnerNode extends ConfigurableActionBase implements ContainerFactoryPluginInterface {

  /**
   * The database connection.
   *
   * @var \Drupal\Core\Database\Connection
   */
  protected $connection;

  /**
   * Constructs a new AssignOwnerNode action.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Database\Connection $connection
   *   The database connection.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, Connection $connection) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->connection = $connection;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static($configuration, $plugin_id, $plugin_definition,
      $container->get('database')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function execute($entity = NULL) {
    /** @var \Drupal\node\NodeInterface $entity */
    $entity->setOwnerId($this->configuration['owner_uid'])->save();
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'owner_uid' => '',
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function buildConfigurationForm(array $form, FormStateInterface $form_state) {
    $description = $this->t('The username of the user to which you would like to assign ownership.');
    $count = $this->connection->query("SELECT COUNT(*) FROM {users}")->fetchField();

    // Use dropdown for fewer than 200 users; textbox for more than that.
    if (intval($count) < 200) {
      $options = [];
      if ($this->connection->driver() == 'mongodb') {
        $rows = $this->connection->select('users')
          ->fields('users', ['user_translations'])
          ->condition('uid', 0, '>')
          ->execute()
          ->fetchAll();
        $result_unsorted = [];
        foreach ($rows as $row) {
          foreach ($row as $user_translations) {
            foreach ($user_translations as $user_translation) {
              if (isset($user_translation['uid']) && isset($user_translation['name']) && isset($user_translation['default_langcode']) && ($user_translation['default_langcode'] === TRUE)) {
                $uid = $user_translation['uid'];
                $name = $user_translation['name'];
                $result_unsorted[$uid] = $name;
              }
            }
          }
        }
        // Sort the users.
        ksort($result_unsorted);

        $result = [];
        foreach ($result_unsorted as $key => $value) {
          $result[] = (object) [
            'uid' => $key,
            'name' => $value,
          ];
        }
      }
      else {
        $result = $this->connection->query("SELECT [uid], [name] FROM {users_field_data} WHERE [uid] > 0 AND [default_langcode] = 1 ORDER BY [name]");
      }
      foreach ($result as $data) {
        $options[$data->uid] = $data->name;
      }
      $form['owner_uid'] = [
        '#type' => 'select',
        '#title' => $this->t('Username'),
        '#default_value' => $this->configuration['owner_uid'],
        '#options' => $options,
        '#description' => $description,
      ];
    }
    else {
      $form['owner_uid'] = [
        '#type' => 'entity_autocomplete',
        '#title' => $this->t('Username'),
        '#target_type' => 'user',
        '#selection_settings' => [
          'include_anonymous' => FALSE,
        ],
        '#default_value' => User::load($this->configuration['owner_uid']),
        // Validation is done in static::validateConfigurationForm().
        '#validate_reference' => FALSE,
        '#size' => '6',
        '#maxlength' => '60',
        '#description' => $description,
      ];
    }
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateConfigurationForm(array &$form, FormStateInterface $form_state) {
    if ($this->connection->driver() == 'mongodb') {
      $user_translations = $this->connection->select('users')
        ->fields('users', ['user_translations'])
        ->condition('uid', (int) $form_state->getValue('owner_uid'))
        ->execute()
        ->fetchField();

      $exists = FALSE;
      foreach ($user_translations as $user_translation) {
        if (isset($user_translation['default_langcode']) && ($user_translation['default_langcode'] === TRUE)) {
          $exists = TRUE;
        }
      }
    }
    else {
      $exists = (bool) $this->connection->queryRange('SELECT 1 FROM {users_field_data} WHERE [uid] = :uid AND [default_langcode] = 1', 0, 1, [':uid' => $form_state->getValue('owner_uid')])->fetchField();
    }
    if (!$exists) {
      $form_state->setErrorByName('owner_uid', $this->t('Enter a valid username.'));
    }
  }

  /**
   * {@inheritdoc}
   */
  public function submitConfigurationForm(array &$form, FormStateInterface $form_state) {
    $this->configuration['owner_uid'] = $form_state->getValue('owner_uid');
  }

  /**
   * {@inheritdoc}
   */
  public function access($object, AccountInterface $account = NULL, $return_as_object = FALSE) {
    /** @var \Drupal\node\NodeInterface $object */
    $result = $object->access('update', $account, TRUE)
      ->andIf($object->getOwner()->access('edit', $account, TRUE));

    return $return_as_object ? $result : $result->isAllowed();
  }

}
