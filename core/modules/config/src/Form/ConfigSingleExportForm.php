<?php

namespace Drupal\config\Form;

use Drupal\Component\Serialization\Yaml;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\Config\StorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Http\HttpMethod;
use Drupal\Core\Render\Hypermedia\Htmx;
use Drupal\Core\Render\Hypermedia\Operations\Replace;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Template\Attribute;
use Drupal\Core\Template\HtmxAttribute;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for exporting a single configuration file.
 *
 * @internal
 */
class ConfigSingleExportForm extends FormBase {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The config storage.
   *
   * @var \Drupal\Core\Config\StorageInterface
   */
  protected $configStorage;

  /**
   * Tracks the valid config entity type definitions.
   *
   * @var \Drupal\Core\Entity\EntityTypeInterface[]
   */
  protected $definitions = [];

  /**
   * Constructs a new ConfigSingleImportForm.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Config\StorageInterface $config_storage
   *   The config storage.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, StorageInterface $config_storage) {
    $this->entityTypeManager = $entity_type_manager;
    $this->configStorage = $config_storage;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('config.storage')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'config_single_export_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, ?string $config_type = NULL, ?string $config_name = NULL) {
    // Default types allow us to extend the form signature but Drupal actually
    // passes NULL if the parameters are not in the route.  Insure we have a
    // string.
    $config_type = $config_type ?? '';
    $config_name = $config_name ?? '';
    $trigger = $this->getHtmxTrigger();
    $form['#prefix'] = '<div id="js-config-form-wrapper">';
    $form['#suffix'] = '</div>';
    foreach ($this->entityTypeManager->getDefinitions() as $entity_type => $definition) {
      if ($definition->entityClassImplements(ConfigEntityInterface::class)) {
        $this->definitions[$entity_type] = $definition;
      }
    }

    $entity_types = array_map(function (EntityTypeInterface $definition) {
      return $definition->getLabel();
    }, $this->definitions);
    // Sort the entity types by label, then add the simple config to the top.
    uasort($entity_types, 'strnatcasecmp');
    $config_types = [
      'system.simple' => $this->t('Simple configuration'),
    ] + $entity_types;

    // Prepare an Htmx object for each dynamic select.
    $config_type_htmx = new Htmx();
    $config_name_htmx = new Htmx();

    /*
     * - Send a POST request to the form URL.
     * - Select the config_name <select> element from the response.
     * - Target the config_name <select> in the rendered form for replacement.
     * - Replace uses the outerHTML strategy, which is to replace the whole tag.
     */
    $form_url = Url::fromRoute(
      route_name: 'config.export_single',
      route_parameters: ['config_type' => $config_type, 'config_name' => $config_name],
    );
    $config_type_htmx->setRequestOperation(
      new Replace(
        select: 'select[data-drupal-selector="edit-config-name"]',
        target: 'select[data-drupal-selector="edit-config-name"]',
        url: $form_url,
        method: HttpMethod::Post
      )
    );
    $form['config_type'] = [
      '#title' => $this->t('Configuration type'),
      '#type' => 'select',
      '#options' => $config_types,
      '#default_value' => $config_type,
      '#htmx' => $config_type_htmx,
    ];

    $default_type = $form_state->getValue('config_type', $config_type);
    $config_name_htmx->setRequestOperation(
      new Replace(
        select: '#edit-export-wrapper',
        target: '#edit-export-wrapper',
        url: $form_url,
        method: HttpMethod::Post
      )
    );
    $form['config_name'] = [
      '#title' => $this->t('Configuration name'),
      '#type' => 'select',
      '#options' => $this->findConfiguration($default_type, $form_state),
      '#empty_value' => '',
      '#default_value' => $config_name,
      '#htmx' => $config_name_htmx,
    ];

    $form['export'] = [
      '#title' => $this->t('Here is your configuration:'),
      '#type' => 'textarea',
      '#rows' => 24,
      '#prefix' => '<div id="edit-export-wrapper">',
      '#suffix' => '</div>',
    ];
    if ($trigger === 'edit-config-type') {
      // Type has changed.
      $form['export']['#value'] = NULL;
      // Also replace the wrapped export element when the response is returned.
      // See https://htmx.org/attributes/hx-swap-oob/ for a detailed description
      // of selection and targeting with ::swapOob.
      // Since this is placed directly in the prefix string, use an
      // HtmxAttribute object directly and merge it with standard attributes.
      $attributes = new Attribute(['id' => 'edit-export-wrapper']);
      $export_htmx_attributes = new HtmxAttribute();
      $export_htmx_attributes->swapOob('outerHTML:#edit-export-wrapper');
      $attributes = $attributes->merge($export_htmx_attributes);
      $form['export']['#prefix'] = '<div  ' . $attributes . '>';
    }
    elseif ($trigger === 'edit-config-name') {
      // A name is selected.
      $default_name = $form_state->getValue('config_name', $config_name);
      $form['export'] = $this->updateExport($form['export'], $default_type, $default_name);
      // Also update the browser URL.
      $push = Url::fromRoute(
        route_name: 'config.export_single',
        route_parameters: ['config_type' => $default_type, 'config_name' => $default_name],
      );
      $config_name_htmx->headers->pushUrl($push);
    }
    elseif ($config_type && $config_name) {
      $form['export'] = $this->updateExport($form['export'], $config_type, $config_name);
    }
    return $form;
  }

  /**
   * Handles switching the export textarea.
   *
   * @param array $element
   *   The export element render array.
   * @param string $config_type
   *   The config type ID.
   * @param string $config_name
   *   The config name ID.
   *
   * @return array
   */
  public function updateExport(array $element, string $config_type, string $config_name) {
    // Determine the full config name for the selected config entity.
    if (!empty($config_type) && $config_type !== 'system.simple' && !empty($config_name)) {
      $definition = $this->entityTypeManager->getDefinition($config_type);
      $name = $definition->getConfigPrefix() . '.' . $config_name;
    }
    // The config name is used directly for simple configuration.
    else {
      $name = $config_name;
    }
    // Read the raw data for this config name, encode it, and display it.
    $exists = $this->configStorage->exists($name);
    $element['#value'] = !$exists ? NULL : Yaml::encode($this->configStorage->read($name));
    $element['#description'] = !$exists ? NULL : $this->t('Filename: %name', ['%name' => $name . '.yml']);
    return $element;
  }

  /**
   * Handles switching the configuration type selector.
   *
   * @param $config_type
   *   The selected configuration type.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   */
  protected function findConfiguration($config_type, FormStateInterface $form_state) {
    $names = [];
    // For a given entity type, load all entities.
    if ($config_type !== 'system.simple' && !empty($config_type)) {
      $entity_storage = $this->entityTypeManager->getStorage($config_type);
      foreach ($entity_storage->loadMultiple() as $entity) {
        $entity_id = $entity->id();
        if ($label = $entity->label()) {
          $names[$entity_id] = new TranslatableMarkup('@id (@label)', ['@label' => $label, '@id' => $entity_id]);
        }
        else {
          $names[$entity_id] = $entity_id;
        }
      }
    }
    // Handle simple configuration.
    else {
      // Gather the config entity prefixes.
      $config_prefixes = array_map(function (EntityTypeInterface $definition) {
        return $definition->getConfigPrefix() . '.';
      }, $this->definitions);

      // Find all config, and then filter our anything matching a config prefix.
      $names += $this->configStorage->listAll();
      $names = array_combine($names, $names);
      foreach ($names as $config_name) {
        foreach ($config_prefixes as $config_prefix) {
          if (str_starts_with($config_name, $config_prefix)) {
            unset($names[$config_name]);
          }
        }
      }
    }
    return $names;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Nothing to submit.
  }

}
