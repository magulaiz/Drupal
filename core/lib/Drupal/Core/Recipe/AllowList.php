<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

use Drupal\Core\Config\StorageInterface;

/**
 * A read-only storage wrapper that only allows access to certain config names.
 */
final class AllowList implements StorageInterface {

  public function __construct(
    private readonly StorageInterface $decorated,
    private readonly array $names,
  ) {}

  /**
   * {@inheritdoc}
   */
  public function exists($name): bool {
    return in_array($name, $this->listAll(), TRUE);
  }

  /**
   * {@inheritdoc}
   */
  public function read($name): array|false {
    return $this->exists($name) ? $this->decorated->read($name) : FALSE;
  }

  /**
   * {@inheritdoc}
   */
  public function readMultiple(array $names): array {
    $names = array_intersect($names, $this->names);
    return $this->decorated->readMultiple($names);
  }

  /**
   * {@inheritdoc}
   */
  public function write($name, array $data): never {
    throw new \BadMethodCallException('This storage is read-only.');
  }

  /**
   * {@inheritdoc}
   */
  public function delete($name): never {
    throw new \BadMethodCallException('This storage is read-only.');
  }

  /**
   * {@inheritdoc}
   */
  public function rename($name, $new_name): never {
    throw new \BadMethodCallException('This storage is read-only.');
  }

  /**
   * {@inheritdoc}
   */
  public function encode($data): string {
    return $this->decorated->encode($data);
  }

  /**
   * {@inheritdoc}
   */
  public function decode($raw): array {
    return $this->decorated->decode($raw);
  }

  /**
   * {@inheritdoc}
   */
  public function listAll($prefix = ''): array {
    return array_intersect($this->decorated->listAll($prefix), $this->names);
  }

  /**
   * {@inheritdoc}
   */
  public function deleteAll($prefix = ''): never {
    throw new \BadMethodCallException('This storage is read-only.');
  }

  /**
   * {@inheritdoc}
   */
  public function createCollection($collection): static {
    return new static(
      $this->decorated->createCollection($collection),
      $this->names,
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getAllCollectionNames(): array {
    return $this->decorated->getAllCollectionNames();
  }

  /**
   * {@inheritdoc}
   */
  public function getCollectionName(): string {
    return $this->decorated->getCollectionName();
  }

}
