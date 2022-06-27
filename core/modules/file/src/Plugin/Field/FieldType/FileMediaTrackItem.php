<?php

namespace Drupal\file\Plugin\Field\FieldType;

use Drupal\Component\Utility\Random;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\File\Exception\FileException;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Language\LanguageManager;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\file\Entity\File;

/**
 * Plugin implementation of the 'media_track' field type.
 *
 * @FieldType(
 *   id = "media_track",
 *   label = @Translation("Media track"),
 *   description = @Translation("This field stores the ID of a media track file as an integer value."),
 *   category = @Translation("Reference"),
 *   default_widget = "media_track",
 *   default_formatter = "file_default",
 *   column_groups = {
 *     "file" = {
 *       "label" = @Translation("File"),
 *       "columns" = {
 *         "target_id"
 *       },
 *       "require_all_groups_for_translation" = TRUE
 *     },
 *     "label" = {
 *       "label" = @Translation("Track label"),
 *       "translatable" = FALSE,
 *     },
 *     "kind" = {
 *       "label" = @Translation("Kind"),
 *       "translatable" = FALSE
 *     },
 *     "srclang" = {
 *       "label" = @Translation("SRC Language"),
 *       "translatable" = FALSE
 *     },
 *     "default" = {
 *       "label" = @Translation("Default"),
 *       "translatable" = FALSE
 *     },
 *   },
 *   list_class = "\Drupal\file\Plugin\Field\FieldType\FileFieldItemList",
 *   constraints = {"ReferenceAccess" = {}, "FileValidation" = {}}
 * )
 */
class FileMediaTrackItem extends FileItem {

  /**
   * {@inheritdoc}
   */
  public static function defaultFieldSettings() {
    $settings = [
      'file_extensions' => 'vtt',
      'languages' => 'installed',
      'kinds' => ['subtitles', 'captions'],
    ] + parent::defaultFieldSettings();

    unset($settings['description_field']);
    return $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function schema(FieldStorageDefinitionInterface $field_definition) {
    return [
      'columns' => [
        'target_id' => [
          'description' => 'The ID of the file entity.',
          'type' => 'int',
          'unsigned' => TRUE,
        ],
        'label' => [
          'description' => "Label of track, for the track's 'label' attribute.",
          'type' => 'varchar',
          'length' => 128,
        ],
        'kind' => [
          'description' => "Type of track, for the track's 'kind' attribute.",
          'type' => 'varchar',
          'length' => 20,
        ],
        'srclang' => [
          'description' => "Language of track, for the track's 'srclang' attribute.",
          'type' => 'varchar',
          'length' => 20,
        ],
        'default' => [
          'description' => "Flag to indicate whether to use this as the default track of this kind.",
          'type' => 'int',
          'size' => 'tiny',
          'unsigned' => TRUE,
          'default' => 0,
        ],
      ],
      'indexes' => [
        'target_id' => ['target_id'],
      ],
      'foreign keys' => [
        'target_id' => [
          'table' => 'file_managed',
          'columns' => ['target_id' => 'fid'],
        ],
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public static function propertyDefinitions(FieldStorageDefinitionInterface $field_definition) {
    $properties = parent::propertyDefinitions($field_definition);

    unset($properties['display']);
    unset($properties['description']);

    $properties['label'] = DataDefinition::create('string')
      ->setLabel(t('Label'))
      ->setDescription(t("Label of track, for the track's 'label' attribute."));

    $properties['kind'] = DataDefinition::create('string')
      ->setLabel(t('Track kind'))
      ->setDescription(t("Type of track, for the track's 'kind' attribute."));

    $properties['srclang'] = DataDefinition::create('string')
      ->setLabel(t('SRC Language'))
      ->setDescription(t("Language of track, for the track's 'srclang' attribute."));

    $properties['default'] = DataDefinition::create('boolean')
      ->setLabel(t('Default'))
      ->setDescription(t("Flag to indicate whether to use this as the default track of this kind."));

    return $properties;
  }

  /**
   * {@inheritdoc}
   */
  public function storageSettingsForm(array &$form, FormStateInterface $form_state, $has_data) {
    $element = [];

    $scheme_options = \Drupal::service('stream_wrapper_manager')->getNames(StreamWrapperInterface::WRITE_VISIBLE);
    $element['uri_scheme'] = [
      '#type' => 'radios',
      '#title' => t('Upload destination'),
      '#options' => $scheme_options,
      '#default_value' => $this->getSetting('uri_scheme'),
      '#description' => t('Select where the final files should be stored. Private file storage has significantly more overhead than public files, but allows restricted access to files within this field.'),
    ];

    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function fieldSettingsForm(array $form, FormStateInterface $form_state) {
    // Get base form from FileItem.
    $element = parent::fieldSettingsForm($form, $form_state);
    $settings = $this->getSettings();

    // Remove the description option.
    unset($element['description_field']);

    $element['languages'] = [
      '#type' => 'radios',
      '#title' => $this->t('Available languages'),
      '#description' => $this->t("Allow the user to choose from all languages, or only the currently installed languages, when selecting a value for the <code>&lt;track&gt;</code> elements <em>srclang</em> attribute."),
      '#options' => [
        'all' => $this->t('All languages'),
        'installed' => $this->t('Currently installed languages'),
      ],
      '#default_value' => $settings['languages'] ?: 'installed',
    ];

    $element['kinds'] = [
      '#type' => 'checkboxes',
      '#title' => $this->t('Available kinds'),
      '#description' => $this->t("Allow the user to choose from the selected options when selecting a value for the <code>&lt;track&gt;</code> elements <em>kind</em> attribute."),
      '#options' => self::getKindOptions(),
      '#default_value' => $settings['kinds'] ?: ['subtitles', 'captions'],
    ];

    return $element;
  }

  /**
   * Get a list of possible values for a track's 'kind' attribute.
   *
   * @return array
   *   Associative array where the key is the value to use for the 'kind'
   *   attribute and the value is the human readable label.
   */
  public static function getKindOptions() {
    return [
        'subtitles' => t('Subtitles'),
        'captions' => t('Captions'),
        'descriptions' => t('Descriptions'),
        'chapters' => t('Chapters'),
        'metadata' => t('Metadata'),
      ];
  }

  /**
   * {@inheritdoc}
   */
  public static function generateSampleValue(FieldDefinitionInterface $field_definition) {
    $random = new Random();
    $settings = $field_definition->getSettings();
    static $generatedFiles = [];

    // Generate a random .vtt file, or reuse an existing one if we already have
    // more than 5 random ones.
    if (count($generatedFiles) <= 5) {
      // How many cues should the file contain?
      $utterances = rand(20, 60);
      $min = 2;
      $max = 5;
      $multiplier = 100;
      $timecode = 0;
      $content = 'WEBVTT - ' . $random->sentences(3);
      $content .= PHP_EOL . PHP_EOL;
      for ($i = 0; $i < $utterances; $i++) {
        $start = $timecode;
        $end = $timecode + mt_rand($min * $multiplier, $max * $multiplier) / $multiplier;
        $timecode = $end;
        // Cue number.
        $content .= $i . PHP_EOL;
        // Timecode.
        $content .= self::secondsToTimecode($start) . ' --> ' . self::secondsToTimecode($end) . PHP_EOL;
        // Text.
        $content .= $random->sentences(8) . PHP_EOL . PHP_EOL;
      }

      /** @var \Drupal\Core\File\FileSystemInterface $file_system */
      $file_system = \Drupal::service('file_system');
      $tmp_file = $file_system->tempnam('temporary://', 'generateImage_');
      $destination = $tmp_file . '.vtt';
      try {
        $file_system->move($tmp_file, $destination);
      }
      catch (FileException $e) {
        // Ignore failed move.
      }

      $path = $file_system->realpath($destination);
      file_put_contents($path, $content);

      $track = File::create();
      $track->setFileUri($path);
      $track->setOwnerId(\Drupal::currentUser()->id());
      $track->setMimeType('text/vtt');
      $track->setFileName($file_system->basename($path));
      $destination_dir = static::doGetUploadLocation($settings);
      $file_system->prepareDirectory($destination_dir, FileSystemInterface::CREATE_DIRECTORY);
      $destination = $destination_dir . '/' . basename($path);
      $file = \Drupal::service('file.repository')->move($track, $destination);
      $generatedFiles[$file->id()] = $file;
    }
    else {
      // Pick one of the already generated files.
      $file = $generatedFiles[array_rand($generatedFiles)];
    }

    // Random track kind based on field settings.
    $kind_options = FileMediaTrackItem::getKindOptions();
    foreach (array_diff(array_keys($kind_options), $settings['kinds']) as $key) {
      unset($kind_options[$key]);
    }
    $kinds = array_keys($kind_options);

    // Random track srclang based on field settings.
    if ($settings['languages'] === 'all') {
      $languages = LanguageManager::getStandardLanguageList();
    }
    else {
      $languages = \Drupal::languageManager()->getLanguages();
    }

    $srclang_options = array_keys($languages);
    $srclang = $srclang_options[array_rand($srclang_options)];

    return [
      'target_id' => $file->id(),
      'label' => $random->string(),
      'kind' => $kinds[array_rand($kinds)],
      'srclang' => $srclang,
      // @todo: Should we also generate random default values? Careful with this
      // requires complex validation.
      'default' => 0,
    ];
  }

  /**
   * Convert seconds to WebVTT timecode format.
   *
   * @param float $initial
   *   Number of seconds.
   *
   * @return string
   *   WebVTT compatible timecode in the hours:minutes:seconds.milliseconds
   *   format.
   */
  private static function secondsToTimecode($initial) {
    $seconds = floor($initial);
    $milliseconds = round(($initial - $seconds) * 1000);
    $milliseconds = str_pad($milliseconds, 3, '0', STR_PAD_LEFT);

    $hours = round($seconds / 3600);
    $hours = str_pad($hours, 2, '0', STR_PAD_LEFT);
    $minutes = round(($seconds / 60) % 60);
    $minutes = str_pad($minutes, 2, '0', STR_PAD_LEFT);
    $seconds = round($seconds % 60);
    $seconds = str_pad($seconds, 2, '0', STR_PAD_LEFT);

    return $hours . ':' . $minutes . ':' . $seconds . '.' . $milliseconds;
  }

  /**
   * {@inheritdoc}
   */
  public function isDisplayed() {
    return TRUE;
  }

}
