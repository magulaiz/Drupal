<?php

namespace Drupal\Core\Extension;

#[\Attribute(\Attribute::TARGET_CLASS | \Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class Hook
{
    public function __construct(
        public string $hook,
        public string $method = '',
        public ?int $priority = NULL,
        public ?string $module = NULL,
    ) {
    }

    public function setMethod(string $method): static {
      $this->method = $method;
      return $this;
    }
}
