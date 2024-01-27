<?php

namespace Drupal\Core\Field;

/**
 * Interface for fields, being lists of field items, with computed values.
 *
 * This allows fields to identify themselves as computed values, so that other
 * parts of the system, for example serialization, can treat them accordingly.
 */
interface ComputedFieldItemListInterface extends FieldItemListInterface { }
