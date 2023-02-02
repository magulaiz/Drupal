<?php

namespace Drupal\Component\Serialization;

/**
 * Class TaggedSerializationInterface.
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
  public static function addTagCallback($tag, callable $callback);

  /**
   * Provides the default tag callbacks of the serializer.
   *
   * @return array
   *   An associative array where the key is the tag and the value is the
   *   callback.
   */
  public static function getDefaultTagCallbacks();

  /**
   * Retrieves a map of tag callbacks.
   *
   * @return array
   *   An associative array where the key is the tag and the value is the
   *   callback.
   */
  public static function getTagCallbacks();

  /**
   * Removes a tag callback.
   *
   * @param string $tag
   *   The tag name.
   *
   * @return callable|null
   *   The callback that was assigned to $tag or NULL if $tag didn't exist.
   */
  public static function removeTagCallback($tag);

  /**
   * Sets a map of tag callbacks.
   *
   * @param array $callbacks
   *   Optional. An associative array where the key is the tag and the value is
   *   the callback. If not provided, the callbacks will be reset.
   */
  public static function setTagCallbacks(array $callbacks = NULL);

}
