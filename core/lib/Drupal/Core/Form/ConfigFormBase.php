<?php

namespace Drupal\Core\Form;

use Drupal\Core\Config\Config;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Render\Element;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Base class for implementing system configuration forms.
 *
 * Subclasses of this form can choose to use config validation instead of form-
 * -specific validation logic. To do that, override copyFormValuesToConfig().
 */
abstract class ConfigFormBase extends FormBase {
  use ConfigFormBaseTrait;

  /**
   * Constructs a \Drupal\system\ConfigFormBase object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The factory for configuration objects.
   * @param \Drupal\Core\Config\TypedConfigManagerInterface|null $typedConfigManager
   *   The typed config manager.
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    protected ?TypedConfigManagerInterface $typedConfigManager = NULL,
  ) {
    $this->setConfigFactory($config_factory);
    if ($this->typedConfigManager === NULL) {
      @trigger_error('Calling ConfigFormBase::__construct() without the $typedConfigManager argument is deprecated in drupal:10.2.0 and will be required in drupal:11.0.0. See https://www.drupal.org/node/3373502', E_USER_DEPRECATED);
      $this->typedConfigManager = \Drupal::service('config.typed');
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $form['actions']['#type'] = 'actions';
    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Save configuration'),
      '#button_type' => 'primary',
    ];

    // By default, render the form using system-config-form.html.twig.
    $form['#theme'] = 'system_config_form';

    // Load default values from config into any element with a #config_target
    // property.
    $form['#process'][] = '::loadDefaultValuesFromConfig';
    $form['#after_build'][] = '::storeConfigKeyToFormElementMap';

    return $form;
  }

  /**
   * Process callback to recursively load default values from #config_target.
   *
   * @param array $element
   *   The form element.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The form element, with its default value populated.
   */
  public function loadDefaultValuesFromConfig(array $element, FormStateInterface $form_state): array {
    if (array_key_exists('#config_target', $element) && !array_key_exists('#default_value', $element)) {
      $config_target = $element['#config_target'];
      if (is_array($config_target)) {
        [$config_target, $transformation] = $config_target;
      }

      [$config_name, $property] = explode(':', $config_target, 2);
      $value = $this->config($config_name)->get($property);
      if (isset($transformation)) {
        $transformation = $form_state->prepareCallback($transformation);
        $value = $transformation($value);
      }
      $element['#default_value'] = $value;
    }

    foreach (Element::children($element) as $key) {
      $element[$key] = $this->loadDefaultValuesFromConfig($element[$key], $form_state);
    }
    return $element;
  }

  /**
   * After-build callback which stores a map of element names to config keys.
   *
   * This will store an array in the form state whose keys are strings in the
   * form of `CONFIG_NAME:PROPERTY_PATH`, and whose values are arrays containing
   * three elements, in order:
   * - The #name of the element which maps to that config value.
   * - The #parents of the element which maps to that config value,
   * - A transformation callback to apply to the submitted or loaded value, or
   *   NULL if no transformation is needed.
   *
   * @param array $element
   *   The element being processed.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The processed element.
   */
  public function storeConfigKeyToFormElementMap(array $element, FormStateInterface $form_state): array {
    if (array_key_exists('#config_target', $element)) {
      $map = $form_state->get('config_key_to_form_element_map') ?? [];

      $config_target = $element['#config_target'];
      $transformation = NULL;

      if (is_array($config_target)) {
        [$config_target, $transformation] = $config_target;
      }
      $map[$config_target] = [
        $element['#name'],
        $element['#parents'],
        $transformation,
      ];
      $form_state->set('config_key_to_form_element_map', $map);
    }
    foreach (Element::children($element) as $key) {
      $element[$key] = $this->storeConfigKeyToFormElementMap($element[$key], $form_state);
    }
    return $element;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    assert($this->typedConfigManager instanceof TypedConfigManagerInterface);

    $map = $form_state->get('config_key_to_form_element_map') ?? [];

    foreach ($this->getEditableConfigNames() as $config_name) {
      $config = $this->config($config_name);
      try {
        static::copyFormValuesToConfig($config, $form_state);
      }
      catch (\BadMethodCallException $e) {
        // Nothing to do: this config form does not yet use validation
        // constraints. Continue trying the other editable config, to allow
        // partial adoption.
        continue;
      }
      $typed_config = $this->typedConfigManager->createFromNameAndData($config_name, $config->getRawData());

      $violations = $typed_config->validate();
      // Rather than immediately applying all violation messages to the
      // corresponding form elements, first collect the messages. The structure
      // of the form may cause a single form element to contain multiple config
      // property paths thanks to `type: sequence`. Common example: a <textarea>
      // with one line per sequence item.
      // @see \Drupal\Core\Config\Schema\Sequence
      // @see \Drupal\Core\Config\Schema\SequenceDataDefinition
      $violations_per_form_element = [];
      /** @var \Symfony\Component\Validator\ConstraintViolationInterface $violation */
      foreach ($violations as $violation) {
        $property_path = $violation->getPropertyPath();
        // Default to index 0.
        $index = 0;
        // Detect if this is a sequence property path, and if so, determine the
        // actual sequence index.
        $matches = [];
        if (preg_match("/.*\.(\d+)$/", $property_path, $matches) === 1) {
          $index = intval($matches[1]);
          // The property path as known in the config key-to-form element map
          // will not have the sequence index in it.
          $property_path = rtrim($property_path, '0123456789.');
        }
        $form_element_name = $map["$config_name:$property_path"][0];
        $violations_per_form_element[$form_element_name][$index] = $violation;
      }

      // Now that we know how many constraint violation messages exist per form
      // element, set them. This is crucial because FormState::setErrorByName()
      // only allows a single validation error message per form element.
      // @see \Drupal\Core\Form\FormState::setErrorByName()
      foreach ($violations_per_form_element as $form_element_name => $violations) {
        // When only a single message exists, just set it.
        if (count($violations) === 1) {
          $form_state->setErrorByName($form_element_name, reset($violations)->getMessage());
          continue;
        }

        // However, if multiple exist, that implies it's a single form element
        // containing a `type: sequence`.
        $form_state->setErrorByName($form_element_name, $this->formatMultipleViolationsMessage($form_element_name, $violations));
      }
    }
  }

  /**
   * Formats multiple violation messages associated with a single form element.
   *
   * Validation constraints only know the internal data structure (the
   * configuration schema structure), but this need not be a disadvantage:
   * rather than informing the user some values are wrong, it is possible
   * guide them directly to the Nth entry in the sequence.
   *
   * To further improve the user experience, it is possible to override
   * method in subclasses to use specific knowledge about the structure of the
   * form and the nature of the data being validated, to instead generate more
   * precise and/or shortened violation messages.
   *
   * @param string $form_element_name
   *   The form element for which to format multiple violation messages.
   * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
   *   The list of constraint violations that apply to this form element.
   *
   * @return \Drupal\Core\StringTranslation\TranslatableMarkup
   */
  protected function formatMultipleViolationsMessage(string $form_element_name, array $violations): TranslatableMarkup {
    $transformed_message_parts = [];
    foreach ($violations as $index => $violation) {
      // Note that `@validation_error_message` (should) already contain a
      // trailing period, hence it is intentionally absent here.
      $transformed_message_parts[] = $this->t('Entry @human_index: @validation_error_message', [
        // Humans start counting from 1, not 0.
        '@human_index' => $index + 1,
        // Translators may not necessarily know what "violation constraint
        // messages" are, but they definitely know "validation errors".
        '@validation_error_message' => $violation->getMessage(),
      ]);
    }
    return $this->t(implode("\n", $transformed_message_parts));
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    foreach ($this->getEditableConfigNames() as $config_name) {
      $config = $this->config($config_name);
      try {
        static::copyFormValuesToConfig($config, $form_state);
        $config->save();
      }
      catch (\BadMethodCallException $e) {
        // Nothing to do: this config form does not yet use validation
        // constraints. Continue trying the other editable config, to allow
        // partial adoption.
        continue;
      }
    }
    $this->messenger()->addStatus($this->t('The configuration options have been saved.'));
  }

  /**
   * Copies form values to Config keys.
   *
   * This should not change existing Config key-value pairs that are not being
   * edited by this form.
   *
   * @param \Drupal\Core\Config\Config $config
   *   The configuration being edited.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current state of the form.
   *
   * @see \Drupal\Core\Entity\EntityForm::copyFormValuesToEntity()
   */
  protected static function copyFormValuesToConfig(Config $config, FormStateInterface $form_state): void {
    $map = $form_state->get('config_key_to_form_element_map') ?? [];

    foreach ($map as $config_target => [$name, $parents, $transformation]) {
      if (str_starts_with($config_target, $config->getName() . ':')) {
        $value = $form_state->getValue($parents);
        if ($transformation) {
          $transformation = $form_state->prepareCallback($transformation);
          $value = $transformation($value);
        }

        [, $property_path] = explode(':', $config_target, 2);
        $config->set($property_path, $value);
      }
    }
  }

}
