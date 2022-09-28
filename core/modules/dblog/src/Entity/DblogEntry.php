<?php

namespace Drupal\dblog\Entity;

use Drupal\Core\Entity\ContentEntityBase;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\dblog\DblogFormatterInterface;
use Drupal\Component\Render\MarkupInterface;

/**
 * Defines the dblog entity class.
 *
 * Dblog entries should be considered read only entities. Drupal support for
 * read only entities is limited at this point, therefore this entity type
 * is set as internal. This will disable integration for jsonapi, rest and hal
 * modules, which assume all entities can be saved.
 *
 * @ContentEntityType(
 *   id = "dblog",
 *   label = @Translation("Dblog entry"),
 *   label_collection = @Translation("Dblog entries"),
 *   label_singular = @Translation("Dblog entry"),
 *   label_plural = @Translation("Dblog entries"),
 *   label_count = @PluralTranslation(
 *     singular = "@count Dblog entry",
 *     plural = "@count Dblog entries",
 *   ),
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\AdminHtmlRouteProvider",
 *     },
 *     "storage" = "Drupal\dblog\DblogEntryStorage",
 *     "access" = "Drupal\dblog\DblogEntryAccessControllerHandler",
 *     "views_data" = "Drupal\dblog\DblogViewsData",
 *     "view_builder" = "Drupal\dblog\DblogEntryViewBuilder",
 *     "list_builder" = "Drupal\dblog\DblogEntryListBuilder",
 *   },
 *   admin_permission = "access site reports",
 *   base_table = "watchdog",
 *   persistent_cache = FALSE,
 *   translatable = FALSE,
 *   internal = TRUE,
 *   entity_keys = {
 *     "id" = "wid",
 *   },
 *   links = {
 *     "canonical" = "/admin/reports/dblog/event/{dblog}",
 *     "collection" = "/admin/reports/dblog",
 *   }
 * )
 */
final class DblogEntry extends ContentEntityBase implements DblogEntryInterface {

  /**
   * {@inheritdoc}
   */
  public static function baseFieldDefinitions(EntityTypeInterface $entity_type) {
    $fields = [];
    $fields['wid'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('ID'))
      ->setDescription(new TranslatableMarkup('Primary Key: Unique watchdog event ID.'))
      ->setReadOnly(TRUE)
      ->setSetting('unsigned', TRUE);

    $fields['uid'] = BaseFieldDefinition::create('entity_reference')
      ->setLabel(new TranslatableMarkup('User'))
      ->setDescription(new TranslatableMarkup('The user who triggered the event.'))
      ->setSetting('target_type', 'user')
      ->setReadOnly(TRUE)
      ->setLabel(t('User'))
      ->setDisplayOptions('view', [
        'label' => 'hidden',
        'type' => 'author',
        'weight' => 0,
      ]);

    $fields['type'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Type'))
      ->setDescription(new TranslatableMarkup('Type of log message, for example "user" or "page not found."'))
      ->setReadOnly(TRUE);

    $fields['message'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Message'))
      ->setDescription(new TranslatableMarkup('Text of log message to be passed into the t() function.'))
      ->setReadOnly(TRUE);

    $fields['variables'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Variables'))
      ->setDescription(new TranslatableMarkup('Serialized array of variables that match the message string and that is passed into the t() function.'))
      ->setReadOnly(TRUE);

    $fields['severity'] = BaseFieldDefinition::create('integer')
      ->setLabel(new TranslatableMarkup('Severity'))
      ->setDescription(new TranslatableMarkup('The severity level of the event; ranges from 0 (Emergency) to 7 (Debug)'))
      ->setReadOnly(TRUE);

    $fields['link'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Link'))
      ->setDescription(new TranslatableMarkup('Link to view the result of the event.'))
      ->setReadOnly(TRUE);

    $fields['location'] = BaseFieldDefinition::create('string_long')
      ->setLabel(new TranslatableMarkup('Location'))
      ->setDescription(new TranslatableMarkup('URL of the origin of the event.'))
      ->setReadOnly(TRUE);

    $fields['referer'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Referer'))
      ->setDescription(new TranslatableMarkup('URL of referring page.'))
      ->setReadOnly(TRUE);

    $fields['hostname'] = BaseFieldDefinition::create('string')
      ->setLabel(new TranslatableMarkup('Hostname'))
      ->setDescription(new TranslatableMarkup('Hostname of the user who triggered the event.'))
      ->setReadOnly(TRUE);

    $fields['timestamp'] = BaseFieldDefinition::create('created')
      ->setLabel(new TranslatableMarkup('Timestamp'))
      ->setDescription(new TranslatableMarkup('Unix timestamp of when event occurred.'))
      ->setReadOnly(TRUE);

    return $fields;
  }

  /**
   * {@inheritdoc}
   */
  public function getHostname() : string {
    return $this->get('hostname')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getLink() : string {
    return $this->get('link')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getLocation() : string {
    return $this->get('location')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getReferer() : string {
    return $this->get('referer')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getSeverity() : int {
    return $this->get('severity')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getTimestamp() : int {
    return $this->get('timestamp')->value;
  }

  /**
   * {@inheritdoc}
   */
  public function getType() : string {
    return $this->get('type')->getString();
  }

  /**
   * {@inheritdoc}
   */
  public function getUid() : int {
    return $this->get('uid')->target_id;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormattedMessage(DblogFormatterInterface $formatter) : MarkupInterface {
    $message = $this->get('message')->getString();
    $variables = @unserialize($this->get('variables')->getString());

    if ($variables === NULL) {
      $variables = [];
    }

    if (!is_array($variables)) {
      // Failed to unserialize variables.
      return $formatter->format($message, NULL, NULL);
    }

    $backtrace_string = NULL;
    if (isset($variables['@backtrace_string'])) {
      $backtrace_string = '@backtrace_string';
    }
    return $formatter->format($message, $variables, $backtrace_string);
  }

}
