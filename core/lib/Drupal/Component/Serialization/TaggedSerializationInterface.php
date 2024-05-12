<?php

namespace Drupal\Component\Serialization;

/**
 * Class TaggedSerializationInterface.
 *
 * Interface for tagged serialization elements.
 */
interface TaggedSerializationInterface extends SerializationInterface {

  /**
   * Adds a tag callback.
   *
   * @param string $tag
   *   The tag name.
   * @param callable $callback
   *   The callback to perform on a following value when the tag is encountered.
   */
  public static function addTagCallback(string $tag, callable $callback): void;

  /**
   * Provides the default tag callbacks of the serializer.
   *
   * @return array
   *   An associative array where the key is the tag and the value is the
   *   callback.
   */
  public static function getDefaultTagCallbacks(): array;

  /**
   * Retrieves a map of tag callbacks.
   *
   * @return array
   *   An associative array where the key is the tag and the value is the
   *   callback.
   */
  public static function getTagCallbacks(): array;

  /**
   * Removes a tag callback.
   *
   * @param string $tag
   *   The tag name.
   *
   * @return callable|null
   *   The callback that was assigned to $tag or NULL if $tag didn't exist.
   */
  public static function removeTagCallback(string $tag): ?callable;

  /**
   * Sets a map of tag callbacks.
   *
   * @param array|null $callbacks
   *   Optional. An associative array where the key is the tag and the value is
   *   the callback. If not provided, the callbacks will be reset.
   */
  public static function setTagCallbacks(?array $callbacks = NULL): void;

}
