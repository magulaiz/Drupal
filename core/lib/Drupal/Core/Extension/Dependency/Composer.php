<?php

namespace Drupal\Core\Extension\Dependency;

use Composer\Semver\Semver;

/**
 * Defines a dependency from a composer require definition.
 */
class Composer implements DependencyInterface {

  /**
   * Constraint string.
   *
   * @var string
   */
  protected $constraint;

  /**
   * Dependency name.
   *
   * @var string
   */
  protected $name;

  /**
   * Constructs a new Composer.
   *
   * @param string $constraint
   *   String constraint.
   * @param string $name
   *   Dependency name.
   */
  public function __construct($constraint, $name) {
    $this->constraint = $constraint;
    $this->name = $name;
  }

  /**
   * {@inheritdoc}
   */
  public function getName() {
    return $this->name;
  }

  /**
   * {@inheritdoc}
   */
  public function isCompatible($version) {
    return Semver::satisfies($version, $this->constraint);
  }

  /**
   * {@inheritdoc}
   */
  public function getConstraintString() {
    return $this->constraint;
  }

}
