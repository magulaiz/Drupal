<?php

declare(strict_types = 1);

namespace Drupal\Core\Entity\Plugin\Validation\Constraint;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeHandlerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

/**
 * Validates the ConfigDependencies constraint.
 */
class ConfigDependenciesConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The config factory service.
   *
   * @var \Drupal\Core\Config\ConfigFactoryInterface
   */
  protected ConfigFactoryInterface $configFactory;

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected ModuleHandlerInterface $moduleHandler;

  /**
   * The theme handler service.
   *
   * @var \Drupal\Core\Extension\ThemeHandlerInterface
   */
  protected ThemeHandlerInterface $themeHandler;

  /**
   * Constructs a ConfigDependenciesConstraintValidator.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config_factory
   *   The config factory service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Extension\ThemeHandlerInterface $theme_handler
   *   The theme handler service.
   */
  public function __construct(ConfigFactoryInterface $config_factory, ModuleHandlerInterface $module_handler, ThemeHandlerInterface $theme_handler) {
    $this->configFactory = $config_factory;
    $this->moduleHandler = $module_handler;
    $this->themeHandler = $theme_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('theme_handler'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate(mixed $dependencies, Constraint $constraint) {
    if (!is_array($dependencies)) {
      throw new UnexpectedTypeException($dependencies, 'array');
    }

    if (array_key_exists('config', $dependencies)) {
      $missing = array_diff($dependencies['config'], $this->configFactory->listAll());
      foreach ($missing as $name) {
        $this->context->addViolation($constraint->unknownConfigMessage, [
          '@name' => $name,
        ]);
      }
    }

    if (array_key_exists('module', $dependencies)) {
      $missing = array_diff($dependencies['module'], array_keys($this->moduleHandler->getModuleList()));
      foreach ($missing as $name) {
        $this->context->addViolation($constraint->unknownModuleMessage, [
          '@name' => $name,
        ]);
      }
    }

    if (array_key_exists('theme', $dependencies)) {
      $missing = array_diff($dependencies['theme'], array_keys($this->themeHandler->listInfo()));
      foreach ($missing as $name) {
        $this->context->addViolation($constraint->unknownThemeMessage, [
          '@name' => $name,
        ]);
      }
    }
  }

}
