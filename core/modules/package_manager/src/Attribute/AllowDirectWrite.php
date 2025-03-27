<?php

declare(strict_types=1);

namespace Drupal\package_manager\Attribute;

/**
 * Identifies stages which can operate directly on the running code base.
 *
 * Package Manager normally creates and operates on a fully separate, sandboxed
 * copy of the site. This is pretty safe, but not always necessary for certain
 * kinds of operations (e.g., adding a new module to the site). StageBase
 * subclasses with this attribute are allowed to skip the sandboxing and operate
 * directly on the live site, but ONLY if the
 * `package_manager_allow_direct_write` setting is set to TRUE.
 *
 * When operating in direct-write mode, the PreApplyEvent and PostApplyEvent
 * events are not dispatched. Event subscribers that want to prevent a stage
 * from moving through its lifecycle in direct-write mode should do that by
 * subscribing to PreCreateEvent or StatusCheckEvent.
 *
 * @see \Drupal\package_manager\StageBase::isDirectWrite()
 */
#[\Attribute(\Attribute::TARGET_CLASS)]
final class AllowDirectWrite {
}
