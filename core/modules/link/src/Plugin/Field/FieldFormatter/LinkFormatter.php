<?php

namespace Drupal\link\Plugin\Field\FieldFormatter;

use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Field\FormatterBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Path\PathValidatorInterface;
use Drupal\Core\TypedData\TranslatableInterface;
use Drupal\Core\Url;
use Drupal\link\LinkItemInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Plugin implementation of the 'link' formatter.
 *
 * @FieldFormatter(
 *   id = "link",
 *   label = @Translation("Link"),
 *   field_types = {
 *     "link"
 *   }
 * )
 */
class LinkFormatter extends FormatterBase {

  /**
   * The path validator service.
   *
   * @var \Drupal\Core\Path\PathValidatorInterface
   */
  protected $pathValidator;

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The entity repository service.
   *
   * @var \Drupal\Core\Entity\EntityRepositoryInterface
   */
  protected $entityRepository;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $plugin_id,
      $plugin_definition,
      $configuration['field_definition'],
      $configuration['settings'],
      $configuration['label'],
      $configuration['view_mode'],
      $configuration['third_party_settings'],
      $container->get('path.validator'),
      $container->get('entity_type.manager'),
      $container->get('entity.repository')
    );
  }

  /**
   * Constructs a new LinkFormatter.
   *
   * @param string $plugin_id
   *   The plugin_id for the formatter.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Field\FieldDefinitionInterface $field_definition
   *   The definition of the field to which the formatter is associated.
   * @param array $settings
   *   The formatter settings.
   * @param string $label
   *   The formatter label display setting.
   * @param string $view_mode
   *   The view mode.
   * @param array $third_party_settings
   *   Third party settings.
   * @param \Drupal\Core\Path\PathValidatorInterface $path_validator
   *   The path validator service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   * @param \Drupal\Core\Entity\EntityRepositoryInterface $entity_repository
   *   The entity repository.
   */
  public function __construct($plugin_id, $plugin_definition, FieldDefinitionInterface $field_definition, array $settings, $label, $view_mode, array $third_party_settings, PathValidatorInterface $path_validator, EntityTypeManagerInterface $entity_type_manager, EntityRepositoryInterface $entity_repository) {
    parent::__construct($plugin_id, $plugin_definition, $field_definition, $settings, $label, $view_mode, $third_party_settings);
    $this->pathValidator = $path_validator;
    $this->entityTypeManager = $entity_type_manager;
    $this->entityRepository = $entity_repository;
  }

  /**
   * {@inheritdoc}
   */
  public static function defaultSettings() {
    return [
      'trim_length' => '80',
      'url_only' => '',
      'url_plain' => '',
      'rel' => '',
      'target' => '',
    ] + parent::defaultSettings();
  }

  /**
   * {@inheritdoc}
   */
  public function settingsForm(array $form, FormStateInterface $form_state) {
    $elements = parent::settingsForm($form, $form_state);

    $elements['trim_length'] = [
      '#type' => 'number',
      '#title' => t('Trim link text length'),
      '#field_suffix' => t('characters'),
      '#default_value' => $this->getSetting('trim_length'),
      '#min' => 1,
      '#description' => t('Leave blank to allow unlimited link text lengths.'),
    ];
    $elements['url_only'] = [
      '#type' => 'checkbox',
      '#title' => t('URL only'),
      '#default_value' => $this->getSetting('url_only'),
      '#access' => $this->getPluginId() == 'link',
    ];
    $elements['url_plain'] = [
      '#type' => 'checkbox',
      '#title' => t('Show URL as plain text'),
      '#default_value' => $this->getSetting('url_plain'),
      '#access' => $this->getPluginId() == 'link',
      '#states' => [
        'visible' => [
          ':input[name*="url_only"]' => ['checked' => TRUE],
        ],
      ],
    ];
    $elements['rel'] = [
      '#type' => 'checkbox',
      '#title' => t('Add rel="nofollow" to links'),
      '#return_value' => 'nofollow',
      '#default_value' => $this->getSetting('rel'),
    ];
    $elements['target'] = [
      '#type' => 'checkbox',
      '#title' => t('Open link in new window'),
      '#return_value' => '_blank',
      '#default_value' => $this->getSetting('target'),
    ];

    return $elements;
  }

  /**
   * {@inheritdoc}
   */
  public function settingsSummary() {
    $summary = [];

    $settings = $this->getSettings();

    if (!empty($settings['trim_length'])) {
      $summary[] = t('Link text trimmed to @limit characters', ['@limit' => $settings['trim_length']]);
    }
    else {
      $summary[] = t('Link text not trimmed');
    }
    if ($this->getPluginId() == 'link' && !empty($settings['url_only'])) {
      if (!empty($settings['url_plain'])) {
        $summary[] = t('Show URL only as plain-text');
      }
      else {
        $summary[] = t('Show URL only');
      }
    }
    if (!empty($settings['rel'])) {
      $summary[] = t('Add rel="@rel"', ['@rel' => $settings['rel']]);
    }
    if (!empty($settings['target'])) {
      $summary[] = t('Open link in new window');
    }

    return $summary;
  }

  /**
   * {@inheritdoc}
   */
  public function viewElements(FieldItemListInterface $items, $langcode) {
    $element = [];
    $entity = $items->getEntity();
    $settings = $this->getSettings();

    foreach ($items as $delta => $item) {
      // By default use the full URL as the link text.
      $url = $this->buildUrl($item);
      $link_title = $url->toString();

      // If the title field value is available, use it for the link text.
      if (empty($settings['url_only']) && !empty($item->title)) {
        // Unsanitized token replacement here because the entire link title
        // gets auto-escaped during link generation in
        // \Drupal\Core\Utility\LinkGenerator::generate().
        $link_title = \Drupal::token()->replace($item->title, [$entity->getEntityTypeId() => $entity], ['clear' => TRUE]);
      }

      // Trim the link text to the desired length.
      if (!empty($settings['trim_length'])) {
        $link_title = Unicode::truncate($link_title, $settings['trim_length'], FALSE, TRUE);
      }

      if (!empty($settings['url_only']) && !empty($settings['url_plain'])) {
        $element[$delta] = [
          '#plain_text' => $link_title,
        ];

        if (!empty($item->_attributes)) {
          // Piggyback on the metadata attributes, which will be placed in the
          // field template wrapper, and set the URL value in a content
          // attribute.
          // @todo Does RDF need a URL rather than an internal URI here?
          // @see \Drupal\Tests\rdf\Kernel\Field\LinkFieldRdfaTest.
          $content = str_replace('internal:/', '', $item->uri);
          $item->_attributes += ['content' => $content];
        }
      }
      else {
        $element[$delta] = [
          '#type' => 'link',
          '#title' => $link_title,
          '#options' => $url->getOptions(),
        ];

        $element[$delta]['#url'] = $url;

        if (!empty($item->_attributes)) {
          $element[$delta]['#options'] += ['attributes' => []];
          $element[$delta]['#options']['attributes'] += $item->_attributes;
          // Unset field item attributes since they have been included in the
          // formatter output and should not be rendered in the field template.
          unset($item->_attributes);
        }
      }

      if ($url->isRouted() && preg_match('/^entity\.(\w+)\.canonical$/', $url->getRouteName(), $matches)) {
        // Check access to the canonical entity route.
        $link_entity_type = $matches[1];
        if (!empty($url->getRouteParameters()[$link_entity_type])) {
          $link_entity = NULL;
          $link_entity_param = $url->getRouteParameters()[$link_entity_type];
          if ($link_entity_param instanceof EntityInterface) {
            $link_entity = $link_entity_param;
          }
          elseif (is_string($link_entity_param) || is_numeric($link_entity_param)) {
            try {
              $link_entity_type_storage = $this->entityTypeManager->getStorage($link_entity_type);
              $link_entity = $link_entity_type_storage->load($link_entity_param);
            }
            catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
            }
          }
          // Set the entity in the correct language for display.
          if ($link_entity instanceof TranslatableInterface) {
            $link_entity = $this->entityRepository->getTranslationFromContext($link_entity, $langcode);
          }
          if ($link_entity instanceof EntityInterface) {
            $access = $link_entity->access('view', NULL, TRUE);
            // Add the access result's cacheability, ::view() needs it.
            $item->_accessCacheability = CacheableMetadata::createFromObject($access);
            if (!$access->isAllowed()) {
              // Remove the link if the user has no access to it.
              unset($element[$delta]);
            }
          }
        }
      }
    }

    return $element;
  }

  /**
   * {@inheritdoc}
   *
   * @see ::viewElements()
   */
  public function view(FieldItemListInterface $items, $langcode = NULL) {
    $elements = parent::view($items, $langcode);

    $field_level_access_cacheability = new CacheableMetadata();

    // Try to map the cacheability of the access result that was set at
    // _accessCacheability in viewElements() to the corresponding render
    // subtree. If no such subtree is found, then merge it with the field-level
    // access cacheability.
    foreach ($items as $delta => $item) {
      // Ignore items for which access cacheability could not be determined in
      // viewElements().
      if (!empty($item->_accessCacheability)) {
        if (isset($elements[$delta])) {
          CacheableMetadata::createFromRenderArray($elements[$delta])
            ->merge($item->_accessCacheability)
            ->applyTo($elements[$delta]);
        }
        else {
          $field_level_access_cacheability = $field_level_access_cacheability->merge($item->_accessCacheability);
        }
      }
    }

    // Apply the cacheability metadata for the inaccessible entities and the
    // entities for which the corresponding render subtree could not be found.
    // This causes the field to be rendered (and cached) according to the cache
    // contexts by which the access results vary, to ensure only users with
    // access to this field can view it. It also tags this field with the cache
    // tags on which the access results depend, to ensure users that cannot view
    // this field at the moment will gain access once any of those cache tags
    // are invalidated.
    $field_level_access_cacheability->merge(CacheableMetadata::createFromRenderArray($elements))
      ->applyTo($elements);

    return $elements;
  }

  /**
   * Builds the \Drupal\Core\Url object for a link field item.
   *
   * @param \Drupal\link\LinkItemInterface $item
   *   The link field item being rendered.
   *
   * @return \Drupal\Core\Url
   *   A Url object.
   */
  protected function buildUrl(LinkItemInterface $item) {
    $url = $item->getUrl() ?: Url::fromRoute('<none>');

    $settings = $this->getSettings();
    $options = $item->options;
    $options += $url->getOptions();

    // Add optional 'rel' attribute to link options.
    if (!empty($settings['rel'])) {
      $options['attributes']['rel'] = $settings['rel'];
    }
    // Add optional 'target' attribute to link options.
    if (!empty($settings['target'])) {
      $options['attributes']['target'] = $settings['target'];
    }
    $url->setOptions($options);

    return $url;
  }

}
