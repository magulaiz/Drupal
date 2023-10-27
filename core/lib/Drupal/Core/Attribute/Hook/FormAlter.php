<?php

declare(strict_types = 1);

namespace Drupal\Core\Attribute\Hook;

/**
 * Attribute for form alter hooks.
 *
 * @see \hook_form_alter()
 * @see \hook_form_FORM_ID_alter()
 * @see \hook_form_BASE_FORM_ID_alter()
 */
#[\Attribute(\Attribute::TARGET_METHOD | \Attribute::IS_REPEATABLE)]
class FormAlter extends Hook {

  /**
   * Constructor.
   *
   * @param string|null $form_id
   *   Form id, or NULL.
   * @param string|null $module
   *   Module, or NULL to use the module from class name or service tag.
   * @param int $weight
   *   Weight.
   * @param array|string|null $before
   *   Module(s) before which this implementation should run.
   * @param array|string|null $after
   *   Module(s) after which this implementation should run.
   */
  public function __construct(
    string $form_id = NULL,
    string $module = NULL,
    int $weight = 0,
    array|string $before = NULL,
    array|string $after = NULL,
  ) {
    parent::__construct(
      ($form_id !== NULL) ? 'form_' . $form_id . '_alter' : 'form_alter',
      $module,
      $weight,
      $before,
      $after,
    );
  }

}
