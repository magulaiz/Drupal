<?php

namespace Drupal\Core\TypedData\Validation;

// phpcs:ignoreFile Portions of this file are a direct copy of
// \Symfony\Component\Validator\Violation\ConstraintViolationBuilder.

use Drupal\Core\Validation\TranslatorInterface;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintViolation;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\Util\PropertyPath;
use Symfony\Component\Validator\Violation\ConstraintViolationBuilderInterface;

/**
 * Defines a constraint violation builder for the Typed Data validator.
 *
 * We do not use the builder provided by Symfony as it is marked internal.
 *
 */
class ConstraintViolationBuilder implements ConstraintViolationBuilderInterface {

  /**
   * The list of violations.
   *
   * @var \Symfony\Component\Validator\ConstraintViolationList
   */
  protected $violations;

  /**
   * The message parameters.
   *
   * @var array
   */
  protected $parameters;

  /**
   * The translator.
   *
   * @var \Drupal\Core\Validation\TranslatorInterface
   */
  protected $translator;

  /**
   * The number used
   * @var int|null
   */
  protected $plural;

  /**
   * @var Constraint
   */
  protected $constraint;

  /**
   * @var mixed
   */
  protected $code;

  /**
   * @var mixed
   */
  protected $cause;

  /**
   * Constructs a new ConstraintViolationBuilder instance.
   *
   * @param \Symfony\Component\Validator\ConstraintViolationList $violations
   *   The violation list.
   * @param \Symfony\Component\Validator\Constraint $constraint
   *   The constraint.
   * @param string $message
   *   The message.
   * @param array $parameters
   *   The message parameters.
   * @param mixed $root
   *   The root.
   * @param string $propertyPath
   *   The property string.
   * @param mixed $invalidValue
   *   The invalid value.
   * @param \Drupal\Core\Validation\TranslatorInterface $translator
   *   The translator.
   * @param null $translationDomain
   *   (optional) The translation domain.
   */
  public function __construct(ConstraintViolationList $violations, Constraint $constraint, protected $message, array $parameters, protected $root, protected $propertyPath, protected $invalidValue, TranslatorInterface $translator, protected ?string|bool $translationDomain = null)
    {
      $this->violations = $violations;
      $this->parameters = $parameters;
      $this->translator = $translator;
      $this->constraint = $constraint;
    }

    /**
     * {@inheritdoc}
     */
    public function atPath($path): static
    {
      $this->propertyPath = PropertyPath::append($this->propertyPath, $path);

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameter($key, $value): static
    {
      $this->parameters[$key] = $value;

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setParameters(array $parameters): static
    {
      $this->parameters = $parameters;

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setTranslationDomain($translationDomain): static
    {
      $this->translationDomain = $translationDomain;

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setInvalidValue($invalidValue): static
    {
      $this->invalidValue = $invalidValue;

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setPlural($number): static
    {
      $this->plural = $number;

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCode($code): static
    {
      $this->code = $code;

      return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function setCause($cause): static
    {
      $this->cause = $cause;

      return $this;
    }

    /**
     * {@inheritdoc}
     *
     * phpcs:ignore Drupal.Commenting.FunctionComment.VoidReturn
     * @return void
     */
    public function addViolation()
    {
      if (null === $this->plural) {
        $translatedMessage = $this->translator->trans(
          $this->message,
          $this->parameters,
          $this->translationDomain
        );
      } else {
        try {
          $translatedMessage = $this->translator->transChoice(
            $this->message,
            $this->plural,
            $this->parameters,
            $this->translationDomain#
          );
        } catch (\InvalidArgumentException $e) {
          $translatedMessage = $this->translator->trans(
            $this->message,
            $this->parameters,
            $this->translationDomain
          );
        }
      }

      $this->violations->add(new ConstraintViolation(
        $translatedMessage,
        $this->message,
        $this->parameters,
        $this->root,
        $this->propertyPath,
        $this->invalidValue,
        $this->plural,
        $this->code,
        $this->constraint,
        $this->cause
      ));
    }

  /**
   * {@inheritdoc}
   */
  public function disableTranslation(): static
  {
    $this->translationDomain = false;

    return $this;
  }

}
