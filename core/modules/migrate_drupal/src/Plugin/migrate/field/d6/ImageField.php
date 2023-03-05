<?php

namespace Drupal\migrate_drupal\Plugin\migrate\field\d6;

// cspell:ignore imagefield

/**
 * @MigrateField(
 *   id = "imagefield",
 *   type_map = {
 *     "imagefield" = "image",
 *   },
 *   core = {6},
 *   source_module = "imagefield",
 *   destination_module = "image"
 * )
 */
class ImageField extends FileField {}
