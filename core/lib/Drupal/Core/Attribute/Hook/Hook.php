<?php

declare(strict_types = 1);

namespace Drupal\Core\Attribute\Hook;

/**
 * Attribute for hook implementations.
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Hook extends HookBase {

  /**
   * Constructor.
   *
   * @param string $hook
   *   Hook name, or pattern.
   * @param string|null $module
   *   (optional) Module name on behalf of which the hook is implemented.
   * @param int $weight
   *   (optional) Weight to let this implementation run early or late.
   * @param array|string|null $before
   *   (optional) Module name(s) before which this implementation should run.
   *   This works even if these modules do not implement the hook.
   * @param array|string|null $after
   *   (optional) Module name(s) after which this implementation should run.
   *   This works even if these modules do not implement the hook.
   */
  public function __construct(
    string $hook,
    string $module = NULL,
    int $weight = 0,
    array|string $before = NULL,
    array|string $after = NULL,
  ) {
    if (str_contains($hook, '(')) {
      $hooks = self::splitHookPattern($hook);
    }
    else {
      $hooks = [$hook];
    }
    parent::__construct(
      $hooks,
      array_filter([
        'module' => $module,
        'weight' => $weight,
        'before' => $before,
        'after' => $after,
      ]),
    );
  }

  /**
   * Splits a hook name pattern.
   *
   * @param string $pattern
   *   Pattern like '(node|comment)_(update|create)'.
   *
   * @return list<string>
   *   Hook names, e.g. ['node_update', 'node_create', comment_update', ..].
   *
   * @throws \Exception
   *   Invalid pattern.
   */
  public static function splitHookPattern(string $pattern): array {
    if (preg_match('@^(\w*)\((\w*(?:\|\w*)*)\)(.*)$@', $pattern, $m)) {
      [, $before, $branch_expression, $after] = $m;
      if ($after !== '') {
        $after_alternatives = self::splitHookPattern($after);
      }
      else {
        $after_alternatives = [''];
      }
      $names = [];
      foreach (explode('|', $branch_expression) as $part) {
        foreach ($after_alternatives as $after_alternative) {
          $names[] = $before . $part . $after_alternative;
        }
      }
      return $names;
    }
    if (preg_match('@^\w+$@', $pattern)) {
      return [$pattern];
    }
    throw new \Exception("Invalid hook name pattern found: '$pattern'.");
  }

}
