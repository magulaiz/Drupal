<?php

declare(strict_types=1);

namespace Drupal\Core\Recipe;

interface InputCollectorInterface {

  public function collectValue(string $name, string|\Stringable $description, array $definition, mixed $default_value): mixed;

}
