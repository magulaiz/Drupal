<?php

namespace Drupal\path\Plugin\Validation\Constraint;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;

/**
 * Constraint validator for changing path aliases in pending revisions.
 */
class PathAliasOverrideConstraintValidator extends ConstraintValidator implements ContainerInjectionInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Current user.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface
   */
  protected $currentUser;

  /**
   * Settings.
   *
   * @var \Drupal\Core\Site\Settings
   */
  protected $settings;

  /**
   * Creates a new PathAliasConstraintValidator instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   * @param \Drupal\Core\Session\AccountProxyInterface $current_user
   *   Current user.
   * @param \Drupal\Core\Site\Settings $settings
   *   Drupal settings.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager, AccountProxyInterface $current_user, Settings $settings) {
    $this->entityTypeManager = $entity_type_manager;
    $this->currentUser = $current_user;
    $this->settings = $settings;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager'),
      $container->get('current_user'),
      $container->get('settings')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function validate($value, Constraint $constraint) {
    if (empty($value->alias)) {
      return;
    }
    if ($this->currentUser->hasPermission('override url aliases')) {
      return;
    }
    $path_parts = explode('/', $value->alias, 2);
    if (empty($path_parts[1])) {
      // This path would probably fail validation in other ways than this one.
      return;
    }
    $restricted_paths = $this->settings->get('path_restricted_paths',
      [
        'node',
        'taxonomy',
        'user',
      ],
    );
    if (in_array($path_parts[1], $restricted_paths)) {
      return;
    }
    $this->context->addViolation($constraint->message);
  }

}
