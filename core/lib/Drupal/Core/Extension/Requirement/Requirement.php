<?php

declare(strict_types=1);

namespace Drupal\Core\Extension\Requirement;

/**
 * Provides a Requirement value object.
 */
final class Requirement implements \ArrayAccess {

  /**
   * The requirement severity.
   */
  protected RequirementSeverity $severity = RequirementSeverity::OK;

  /**
   * The requirement title.
   */
  protected string $title;

  /**
   * The requirement value.
   */
  protected string $value;

  /**
   * The requirement description.
   */
  protected string $description;

  /**
   * Create a new requirement.
   */
  public static function create(): Requirement {
    return new Requirement();
  }

  /**
   * This class should not be instantiated directly.
   */
  private function __construct() {}

  /**
   * {@inheritdoc}
   *
   * This is for BC support only.
   */
  public function offsetExists($offset): bool {
    return in_array($offset, [
      'description',
      'title',
      'value',
      'severity',
    ], TRUE);
  }

  /**
   * {@inheritdoc}
   *
   * This is for BC support only.
   */
  public function offsetGet($offset): mixed {
    return match ($offset) {
      'description' => $this->getDescription(),
      'title' => $this->getTitle(),
      'value' => $this->getValue(),
      'severity' => $this->getSeverity(),
      default => NULL,
    };
  }

  /**
   * Gets the description.
   */
  public function getDescription(): string {
    return $this->description;
  }

  /**
   * Sets the description.
   */
  public function setDescription(?string $description): Requirement {
    $this->description = $description;
    return $this;
  }

  /**
   * Gets the title.
   */
  public function getTitle(): string {
    return $this->title;
  }

  /**
   * Sets the title.
   */
  public function setTitle(?string $title): Requirement {
    $this->title = $title;
    return $this;
  }

  /**
   * Gets the value.
   */
  public function getValue(): string {
    return $this->value;
  }

  /**
   * Sets the value.
   */
  public function setValue(?string $value): Requirement {
    $this->value = $value;
    return $this;
  }

  /**
   * Gets the severity.
   */
  public function getSeverity(): RequirementSeverity {
    return $this->severity;
  }

  /**
   * Sets the requirement severity.
   */
  public function setSeverity(RequirementSeverity $severity): Requirement {
    $this->severity = $severity;
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * This is for BC support only.
   */
  public function offsetSet($offset, $value): void {
    switch ($offset) {
      case 'description':
        $this->setDescription($value);
        break;

      case 'title':
        $this->setTitle($value);
        break;

      case 'value':
        $this->setValue($value);
        break;

      case 'severity':
        $this->setSeverity($value);
    }
  }

  /**
   * {@inheritdoc}
   *
   * This is for BC support only.
   */
  public function offsetUnset($offset): void {
    switch ($offset) {
      case 'description':
        $this->setDescription(NULL);
        break;

      case 'title':
        $this->setTitle(NULL);
        break;

      case 'value':
        $this->setValue(NULL);
        break;

      case 'severity':
        $this->setSeverity(RequirementSeverity::OK);
    }
  }

}
