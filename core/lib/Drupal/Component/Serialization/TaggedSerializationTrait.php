<?php

namespace Drupal\Component\Serialization;

/**
 * Trait TaggedSerializationTrait.
 *
 * Trait for tagged serialization elements.
 */
trait TaggedSerializationTrait {

  /**
   * Associative array of tag callbacks, keyed by tag.
   */
  protected static array $tagCallbacks;

  /**
   * Adds a tag callback.
   *
   * @param string $tag
   *   The tag name.
   * @param callable $callback
   *   The callback to perform on a following value when the tag is encountered.
   */
  public static function addTagCallback(string $tag, callable $callback): void {
    $callbacks = static::getTagCallbacks();
    $callbacks[$tag] = $callback;
    static::setTagCallbacks($callbacks);
  }

  /**
   * Executes a tag callback.
   *
   * @param mixed $value
   *   The parsed value.
   * @param string $tag
   *   The tag name.
   *
   * @return mixed
   *   Result from the callback or original $value if no callback exists.
   */
  public static function executeTagCallback(mixed $value, string $tag): mixed {
    $callbacks = static::getTagCallbacks();

    // Prepend tag with ! (in cases where it's stripped from the name).
    if ($tag[0] !== '!') {
      $tag = "!$tag";
    }

    // Immediately return the original value if there is no callback.
    if (!isset($callbacks[$tag])) {
      return $value;
    }

    return $callbacks[$tag]($value, $tag);
  }

  /**
   * Provides the default tag callbacks.
   *
   * @return array
   *   An associative array where the key is the tag and the value is the
   *   callback.
   */
  public static function getDefaultTagCallbacks(): array {
    return [];
  }

  /**
   * Retrieves a map of tag callbacks.
   *
   * @return array
   *   An associative array where the key is the tag and the value is the
   *   callback.
   */
  public static function getTagCallbacks(): array {
    if (!isset(static::$tagCallbacks)) {
      static::$tagCallbacks = static::getDefaultTagCallbacks();
    }
    return static::$tagCallbacks;
  }

  /**
   * Merges tag callbacks from this serializer onto a target serializer.
   *
   * @param \Drupal\Component\Serialization\TaggedSerializationInterface|string $serializer
   *   The target serializer to merge tag callbacks onto.
   */
  protected static function mergeTagCallbacks(TaggedSerializationInterface|string $serializer): void {
    if ($serializer instanceof TaggedSerializationInterface || is_subclass_of($serializer, TaggedSerializationInterface::class)) {
      $serializer::setTagCallbacks(array_merge(
        $serializer::getTagCallbacks(),
        static::getTagCallbacks()
      ));
    }
  }

  /**
   * Removes a tag callback.
   *
   * @param string $tag
   *   The tag name.
   *
   * @return callable|null
   *   The callback that was assigned to $tag or NULL if $tag didn't exist.
   */
  public static function removeTagCallback(string $tag): ?callable {
    $callback = NULL;
    if (isset(static::$tagCallbacks[$tag])) {
      $callback = static::$tagCallbacks[$tag];
      unset(static::$tagCallbacks[$tag]);
    }
    return $callback;
  }

  /**
   * Sets a map of tag callbacks.
   *
   * @param array|null $callbacks
   *   An associative array where the key is the tag and the value is the
   *   callback.
   */
  public static function setTagCallbacks(?array $callbacks = NULL): void {
    static::$tagCallbacks = $callbacks;
  }

}
