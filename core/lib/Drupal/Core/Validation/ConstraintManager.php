<?php

namespace Drupal\Core\Validation;

use Drupal\Component\Plugin\Discovery\StaticDiscoveryDecorator;
use Drupal\Core\Cache\CacheBackendInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Plugin\DefaultPluginManager;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Plugin\Validation\Constraint\EmailConstraint;
use Symfony\Component\Validator\Constraints\Bic;
use Symfony\Component\Validator\Constraints\Blank;
use Symfony\Component\Validator\Constraints\Callback;
use Symfony\Component\Validator\Constraints\CardScheme;
use Symfony\Component\Validator\Constraints\Choice;
use Symfony\Component\Validator\Constraints\Cidr;
use Symfony\Component\Validator\Constraints\CssColor;
use Symfony\Component\Validator\Constraints\Currency;
use Symfony\Component\Validator\Constraints\Date;
use Symfony\Component\Validator\Constraints\DateTime;
use Symfony\Component\Validator\Constraints\DivisibleBy;
use Symfony\Component\Validator\Constraints\GreaterThan;
use Symfony\Component\Validator\Constraints\GreaterThanOrEqual;
use Symfony\Component\Validator\Constraints\Hostname;
use Symfony\Component\Validator\Constraints\Iban;
use Symfony\Component\Validator\Constraints\IdenticalTo;
use Symfony\Component\Validator\Constraints\Image;
use Symfony\Component\Validator\Constraints\Ip;
use Symfony\Component\Validator\Constraints\Isbn;
use Symfony\Component\Validator\Constraints\IsFalse;
use Symfony\Component\Validator\Constraints\Isin;
use Symfony\Component\Validator\Constraints\Issn;
use Symfony\Component\Validator\Constraints\IsTrue;
use Symfony\Component\Validator\Constraints\Json;
use Symfony\Component\Validator\Constraints\Language;
use Symfony\Component\Validator\Constraints\LessThan;
use Symfony\Component\Validator\Constraints\LessThanOrEqual;
use Symfony\Component\Validator\Constraints\Locale;
use Symfony\Component\Validator\Constraints\Luhn;
use Symfony\Component\Validator\Constraints\Negative;
use Symfony\Component\Validator\Constraints\NegativeOrZero;
use Symfony\Component\Validator\Constraints\NotBlank;
use Symfony\Component\Validator\Constraints\NotEqualTo;
use Symfony\Component\Validator\Constraints\NotIdenticalTo;
use Symfony\Component\Validator\Constraints\Optional;
use Symfony\Component\Validator\Constraints\Positive;
use Symfony\Component\Validator\Constraints\PositiveOrZero;
use Symfony\Component\Validator\Constraints\Required;
use Symfony\Component\Validator\Constraints\Time;
use Symfony\Component\Validator\Constraints\Timezone;
use Symfony\Component\Validator\Constraints\Type;
use Symfony\Component\Validator\Constraints\Ulid;
use Symfony\Component\Validator\Constraints\Unique;
use Symfony\Component\Validator\Constraints\Url;

/**
 * Constraint plugin manager.
 *
 * Manages validation constraints based upon
 * \Symfony\Component\Validator\Constraint, whereas Symfony constraints are
 * added in manually during construction. Constraint options are passed on as
 * plugin configuration during plugin instantiation.
 *
 * While core does not prefix constraint plugins, modules have to prefix them
 * with the module name in order to avoid any naming conflicts; for example, a
 * "profile" module would have to prefix any constraints with "Profile".
 *
 * Constraint plugins may specify data types to which support is limited via the
 * 'type' key of plugin definitions. See
 * \Drupal\Core\Validation\Annotation\Constraint for details.
 *
 * @see \Drupal\Core\Validation\Annotation\Constraint
 */
class ConstraintManager extends DefaultPluginManager {

  /**
   * Overrides \Drupal\Component\Plugin\PluginManagerBase::__construct().
   *
   * @param \Traversable $namespaces
   *   An object that implements \Traversable which contains the root paths
   *   keyed by the corresponding namespace to look for plugin implementations.
   * @param \Drupal\Core\Cache\CacheBackendInterface $cache_backend
   *   Cache backend instance to use.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler to invoke the alter hook with.
   */
  public function __construct(\Traversable $namespaces, CacheBackendInterface $cache_backend, ModuleHandlerInterface $module_handler) {
    $this->factory = new ConstraintFactory($this);
    parent::__construct('Plugin/Validation/Constraint', $namespaces, $module_handler, NULL, 'Drupal\Core\Validation\Annotation\Constraint');
    $this->alterInfo('validation_constraint');
    $this->setCacheBackend($cache_backend, 'validation_constraint_plugins');
  }

  /**
   * {@inheritdoc}
   */
  protected function getDiscovery() {
    if (!isset($this->discovery)) {
      $this->discovery = parent::getDiscovery();
      $this->discovery = new StaticDiscoveryDecorator($this->discovery, [$this, 'registerDefinitions']);
    }
    return $this->discovery;
  }

  /**
   * Creates a validation constraint.
   *
   * @param string $name
   *   The name or plugin id of the constraint.
   * @param mixed $options
   *   The options to pass to the constraint class. Required and supported
   *   options depend on the constraint class.
   *
   * @return \Symfony\Component\Validator\Constraint
   *   A validation constraint plugin.
   */
  public function create($name, $options) {
    if (!is_array($options)) {
      // Plugins need an array as configuration, so make sure we have one.
      // The constraint classes support passing the options as part of the
      // 'value' key also.
      $options = isset($options) ? ['value' => $options] : [];
    }
    return $this->createInstance($name, $options);
  }

  /**
   * Callback for registering definitions for constraints shipped with Symfony.
   *
   * @see ConstraintManager::__construct()
   */
  public function registerDefinitions() {
    $this->getDiscovery()->setDefinition('Callback', [
      'label' => new TranslatableMarkup('Callback'),
      'class' => Callback::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Blank', [
      'label' => new TranslatableMarkup('Blank'),
      'class' => Blank::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Bic', [
      'label' => new TranslatableMarkup('Business Identifier Code'),
      'class' => Bic::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('CardScheme', [
      'label' => new TranslatableMarkup('Card scheme'),
      'class' => CardScheme::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Cidr', [
      'label' => new TranslatableMarkup('CIDR'),
      'class' => Cidr::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('CssColor', [
      'label' => new TranslatableMarkup('CSS color'),
      'class' => CssColor::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Currency', [
      'label' => new TranslatableMarkup('Currency'),
      'class' => Currency::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Date', [
      'label' => new TranslatableMarkup('Date'),
      'class' => Date::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('DateTime', [
      'label' => new TranslatableMarkup('DateTime'),
      'class' => DateTime::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('DivisibleBy', [
      'label' => new TranslatableMarkup('Divisible by'),
      'class' => DivisibleBy::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('GreaterThan', [
      'label' => new TranslatableMarkup('Greater than'),
      'class' => GreaterThan::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('GreaterThanOrEqual', [
      'label' => new TranslatableMarkup('Greater than or equal'),
      'class' => GreaterThanOrEqual::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Hostname', [
      'label' => new TranslatableMarkup('Hostname'),
      'class' => Hostname::class,
      'type' => 'string',
    ]);
    $this->getDiscovery()->setDefinition('Iban', [
      'label' => new TranslatableMarkup('IBAN'),
      'class' => Iban::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('IdenticalTo', [
      'label' => new TranslatableMarkup('Identical to'),
      'class' => IdenticalTo::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Image', [
      'label' => new TranslatableMarkup('Image'),
      'class' => Image::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Ip', [
      'label' => new TranslatableMarkup('IP address'),
      'class' => Ip::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Isbn', [
      'label' => new TranslatableMarkup('ISBN'),
      'class' => Isbn::class,
      'type' => 'string',
    ]);
    $this->getDiscovery()->setDefinition('IsFalse', [
      'label' => new TranslatableMarkup('False'),
      'class' => IsFalse::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Isin', [
      'label' => new TranslatableMarkup('ISIN'),
      'class' => Isin::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Issn', [
      'label' => new TranslatableMarkup('ISSN'),
      'class' => Issn::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('IsTrue', [
      'label' => new TranslatableMarkup('True'),
      'class' => IsTrue::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Json', [
      'label' => new TranslatableMarkup('JSON'),
      'class' => Json::class,
      'type' => 'string',
    ]);
    $this->getDiscovery()->setDefinition('Language', [
      'label' => new TranslatableMarkup('Language'),
      'class' => Language::class,
      'type' => 'string',
    ]);
    $this->getDiscovery()->setDefinition('LessThan', [
      'label' => new TranslatableMarkup('Less than'),
      'class' => LessThan::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('LessThanOrEqual', [
      'label' => new TranslatableMarkup('Less than or equal'),
      'class' => LessThanOrEqual::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Locale', [
      'label' => new TranslatableMarkup('Locale'),
      'class' => Locale::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Luhn', [
      'label' => new TranslatableMarkup('Luhn'),
      'class' => Luhn::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Negative', [
      'label' => new TranslatableMarkup('Negative'),
      'class' => Negative::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('NegativeOrZero', [
      'label' => new TranslatableMarkup('Negative or zero'),
      'class' => NegativeOrZero::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('NotBlank', [
      'label' => new TranslatableMarkup('Not blank'),
      'class' => NotBlank::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('NotEqualTo', [
      'label' => new TranslatableMarkup('Not equal to'),
      'class' => NotEqualTo::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('NotIdenticalTo', [
      'label' => new TranslatableMarkup('Not identical to'),
      'class' => NotIdenticalTo::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Optional', [
      'label' => new TranslatableMarkup('Optional'),
      'class' => Optional::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Positive', [
      'label' => new TranslatableMarkup('Positive'),
      'class' => Positive::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('PositiveOrZero', [
      'label' => new TranslatableMarkup('Positive or zero'),
      'class' => PositiveOrZero::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Required', [
      'label' => new TranslatableMarkup('Required'),
      'class' => Required::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Time', [
      'label' => new TranslatableMarkup('Time'),
      'class' => Time::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Timezone', [
      'label' => new TranslatableMarkup('Timezone'),
      'class' => Timezone::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Type', [
      'label' => new TranslatableMarkup('Type'),
      'class' => Type::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Ulid', [
      'label' => new TranslatableMarkup('ULID'),
      'class' => Ulid::class,
      'type' => 'string',
    ]);
    $this->getDiscovery()->setDefinition('Unique', [
      'label' => new TranslatableMarkup('Unique'),
      'class' => Unique::class,
      'type' => FALSE,
    ]);
    $this->getDiscovery()->setDefinition('Url', [
      'label' => new TranslatableMarkup('URL'),
      'class' => Url::class,
      'type' => 'string',
    ]);
    $this->getDiscovery()->setDefinition('Email', [
      'label' => new TranslatableMarkup('E-mail'),
      'class' => EmailConstraint::class,
      'type' => ['string'],
    ]);
    $this->getDiscovery()->setDefinition('Choice', [
      'label' => new TranslatableMarkup('Choice'),
      'class' => Choice::class,
      'type' => FALSE,
    ]);
  }

  /**
   * {@inheritdoc}
   */
  public function processDefinition(&$definition, $plugin_id) {
    // Make sure 'type' is set and either an array or FALSE.
    if ($definition['type'] !== FALSE && !is_array($definition['type'])) {
      $definition['type'] = [$definition['type']];
    }
  }

  /**
   * Returns a list of constraints that support the given type.
   *
   * @param string $type
   *   The type to filter on.
   *
   * @return array
   *   An array of constraint plugin definitions supporting the given type,
   *   keyed by constraint name (plugin ID).
   */
  public function getDefinitionsByType($type) {
    $definitions = [];
    foreach ($this->getDefinitions() as $plugin_id => $definition) {
      if ($definition['type'] === FALSE || in_array($type, $definition['type'])) {
        $definitions[$plugin_id] = $definition;
      }
    }
    return $definitions;
  }

}
