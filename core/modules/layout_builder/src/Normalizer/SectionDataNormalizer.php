<?php

namespace Drupal\layout_builder\Normalizer;

use Drupal\layout_builder\Plugin\DataType\SectionData;
use Drupal\layout_builder\Section;
use Drupal\serialization\Normalizer\TypedDataNormalizer;

/**
 * Normalizes section data.
 */
class SectionDataNormalizer extends TypedDataNormalizer {

  /**
   * {@inheritdoc}
   */
  protected $supportedInterfaceOrClass = SectionData::class;

  /**
   * {@inheritdoc}
   */
  public function normalize($object, $format = NULL, array $context = []) {
    $value = parent::normalize($object, $format, $context);
    return $value->toArray();
  }

  /**
   * {@inheritdoc}
   */
  public function denormalize($data, $class, $format = NULL, array $context = []) {
    return Section::fromArray($data);
  }

}
