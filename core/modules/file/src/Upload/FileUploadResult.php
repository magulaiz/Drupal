<?php

namespace Drupal\file\Upload;

use Drupal\Component\Render\MarkupInterface;
use Drupal\file\FileInterface;
use Symfony\Component\Validator\ConstraintViolationInterface;
use Symfony\Component\Validator\ConstraintViolationList;
use Symfony\Component\Validator\ConstraintViolationListInterface;

/**
 * Value object for a file upload result.
 */
class FileUploadResult {

  /**
   * If the filename was renamed for security reasons.
   */
  protected bool $securityRename = FALSE;

  /**
   * The sanitized filename.
   */
  protected ?string $sanitizedFilename;

  /**
   * The original filename.
   */
  protected ?string $originalFilename;

  /**
   * The File entity.
   */
  protected ?FileInterface $file = NULL;

  /**
   * The error message.
   */
  protected ?MarkupInterface $error = NULL;

  /**
   * The constraint violations.
   *
   * @var \Symfony\Component\Validator\ConstraintViolationListInterface
   */
  protected ConstraintViolationListInterface $violations;

  /**
   * Creates a new FileUploadResult.
   */
  public function __construct() {
    $this->violations = new ConstraintViolationList();
  }

  /**
   * Flags the result as having had a security rename.
   *
   * @return $this
   */
  public function setSecurityRename(): FileUploadResult {
    $this->securityRename = TRUE;
    return $this;
  }

  /**
   * Sets the sanitized filename.
   *
   * @param string $sanitizedFilename
   *   The sanitized filename.
   *
   * @return $this
   */
  public function setSanitizedFilename(string $sanitizedFilename): FileUploadResult {
    $this->sanitizedFilename = $sanitizedFilename;
    return $this;
  }

  /**
   * Gets the original filename.
   *
   * @return string
   */
  public function getOriginalFilename(): string {
    return $this->originalFilename;
  }

  /**
   * Sets the original filename.
   *
   * @param string $originalFilename
   *   The original filename.
   *
   * @return $this
   */
  public function setOriginalFilename(string $originalFilename): FileUploadResult {
    $this->originalFilename = $originalFilename;
    return $this;
  }

  /**
   * Sets the File entity.
   *
   * @param \Drupal\file\FileInterface $file
   *   A file entity.
   *
   * @return $this
   */
  public function setFile(FileInterface $file): FileUploadResult {
    $this->file = $file;
    return $this;
  }

  /**
   * Returns if there was a security rename.
   *
   * @return bool
   */
  public function isSecurityRename(): bool {
    return $this->securityRename;
  }

  /**
   * Returns if there was a file rename.
   *
   * @return bool
   */
  public function isRenamed(): bool {
    return $this->originalFilename !== $this->sanitizedFilename;
  }

  /**
   * Gets the sanitized filename.
   *
   * @return string
   */
  public function getSanitizedFilename(): string {
    return $this->sanitizedFilename;
  }

  /**
   * Gets the File entity.
   *
   * @return \Drupal\file\FileInterface
   */
  public function getFile(): ?FileInterface {
    return $this->file;
  }

  /**
   * Adds a constraint violation.
   *
   * @param \Symfony\Component\Validator\ConstraintViolationInterface $violation
   *   The constraint violation.
   */
  public function addViolation(ConstraintViolationInterface $violation): void {
    $this->violations->add($violation);
  }

  /**
   * Adds constraint violations.
   *
   * @param \Symfony\Component\Validator\ConstraintViolationListInterface $violations
   *   The constraint violations.
   */
  public function addViolations(ConstraintViolationListInterface $violations): void {
    $this->violations->addAll($violations);
  }

  /**
   * Gets the constraint violations.
   *
   * @return \Symfony\Component\Validator\ConstraintViolationListInterface
   *   The constraint violations.
   */
  public function getViolations(): ConstraintViolationListInterface {
    return $this->violations;
  }

  /**
   * Returns TRUE if there are constraint violations.
   */
  public function hasViolations(): bool {
    return $this->violations->count() > 0;
  }

  /**
   * Returns TRUE if there is an error.
   */
  public function hasError(): bool {
    return isset($this->error);
  }

  /**
   * Sets the error.
   */
  public function setError(MarkupInterface $error): FileUploadResult {
    $this->error = $error;
    return $this;
  }

  /**
   * Gets the error.
   */
  public function getError(): ?MarkupInterface {
    return $this->error;
  }

}
