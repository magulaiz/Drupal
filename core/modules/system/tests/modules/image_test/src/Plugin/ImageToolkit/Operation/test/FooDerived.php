<?php

declare(strict_types=1);

namespace Drupal\image_test\Plugin\ImageToolkit\Operation\test;

/**
 * Builds an image toolkit operation.
 *
 * @ImageToolkitOperation(
 *   id = "foo_derived",
 *   toolkit = "test:derived_toolkit",
 *   operation = "blur",
 *   label = @Translation("Blur Derived"),
 *   description = @Translation("Foo derived.")
 * )
 */
class FooDerived extends OperationBase {}
