<?php

namespace Drupal\Core\Extension\Requirement;

/**
 * Provides a Requirement value object.
 */
abstract class BaseRequirement implements RequirementInterface {

  /**
   * The requirement severity.
   *
   * @var int
   */
  protected $severity = self::SEVERITY_OK;

  /**
   * The requirement title.
   *
   * @var string
   */
  protected $title;

  /**
   * The requirement value.
   *
   * @var string
   */
  protected $value;

  /**
   * The requirement description.
   *
   * @var string
   */
  protected $description;

  /**
   * Create a new requirement.
   *
   * @return $this
   */
  public static function create() {
    return new static();
  }

  /**
   * Gets the severity.
   *
   * @return int
   */
  public function getSeverity() {
    return $this->severity;
  }

  /**
   * Gets the title.
   *
   * @return string
   */
  public function getTitle() {
    return $this->title;
  }

  /**
   * Sets the title.
   *
   * @param string $title
   *
   * @return $this
   */
  public function setTitle($title) {
    $this->title = $title;
    return $this;
  }

  /**
   * Gets the value.
   *
   * @return string
   */
  public function getValue() {
    return $this->value;
  }

  /**
   * Sets the value.
   *
   * @param string $value
   *
   * @return $this
   */
  public function setValue($value) {
    $this->value = $value;
    return $this;
  }

  /**
   * Gets the description.
   *
   * @return string
   */
  public function getDescription() {
    return $this->description;
  }

  /**
   * Sets the description.
   *
   * @param string $description
   *
   * @return $this
   */
  public function setDescription($description) {
    $this->description = $description;
    return $this;
  }

  /**
   * {@inheritdoc}
   *
   * This is for BC support only.
   */
  public function offsetExists($offset) {
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
  public function offsetGet($offset) {
    switch ($offset) {
      case 'description':
        return $this->getDescription();
      case 'title':
        return $this->getTitle();
      case 'value':
        return $this->getValue();
      case 'severity':
        return $this->getSeverity();
      default:
        return NULL;
    }
  }

  /**
   * {@inheritdoc}
   *
   * This is for BC support only.
   */
  public function offsetSet($offset, $value) {
    switch ($offset) {
      case 'description':
        $this->setDescription($value);
        return NULL;
      case 'title':
        $this->setTitle($value);
        return NULL;
      case 'value':
        $this->setValue($value);
        return NULL;
    }
  }

  /**
   * {@inheritdoc}
   *
   * This is for BC support only.
   */
  public function offsetUnset($offset) {
    switch ($offset) {
      case 'description':
        $this->setDescription(NULL);
        return NULL;
      case 'title':
        $this->setTitle(NULL);
        return NULL;
      case 'value':
        $this->setValue(NULL);
        return NULL;
      case 'severity':
        $this->severity = self::SEVERITY_OK;
        return NULL;
    }
  }

}
