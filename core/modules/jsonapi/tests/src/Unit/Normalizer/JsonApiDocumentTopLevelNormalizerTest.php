<?php

declare(strict_types=1);

namespace Drupal\Tests\jsonapi\Unit\Normalizer;

use Drupal\Core\Entity\EntityFieldManagerInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Entity\FieldableEntityInterface;
use Drupal\Core\Field\FieldStorageDefinitionInterface;
use Drupal\Core\TypedData\DataReferenceTargetDefinition;
use Drupal\jsonapi\ResourceType\ResourceType;
use Drupal\jsonapi\Normalizer\JsonApiDocumentTopLevelNormalizer;
use Drupal\jsonapi\ResourceType\ResourceTypeField;
use Drupal\Tests\UnitTestCase;
use Prophecy\Argument;
use Prophecy\Prophet;
use Symfony\Component\HttpKernel\Exception\UnprocessableEntityHttpException;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Drupal\jsonapi\ResourceType\ResourceTypeRepository;

/**
 * @coversDefaultClass \Drupal\jsonapi\Normalizer\JsonApiDocumentTopLevelNormalizer
 * @group jsonapi
 *
 * @internal
 */
class JsonApiDocumentTopLevelNormalizerTest extends UnitTestCase {

  /**
   * The normalizer under test.
   *
   * @var \Drupal\jsonapi\Normalizer\JsonApiDocumentTopLevelNormalizer
   */
  protected $normalizer;

  /**
   * Test entities.
   *
   * These must be statically stored because ::setUp() and data providers are
   * run on different instances of this class, however to test object
   * equivalence the objects in the test data must be the same objects as
   * returned by the repository mock.
   *
   * @var \Drupal\Core\Entity\EntityInterface[]
   */
  protected static array $entities = [];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $resource_type_repository = $this->prophesize(ResourceTypeRepository::class);

    $resource_type_repository
      ->getByTypeName(Argument::any())
      ->willReturn(new ResourceType('node', 'article', NULL));

    $entity_storage = $this->prophesize(EntityStorageInterface::class);
    $self = $this;
    $entity_storage->loadByProperties(Argument::type('array'))
      ->will(function ($args) use ($self) {
        $mockedEntities = $self->getMockEntities();
        $result = [];
        foreach ($args[0]['uuid'] as $uuid) {
          $result[$uuid] = $mockedEntities[$uuid];
        }
        return $result;
      });
    $entity_type_manager = $this->prophesize(EntityTypeManagerInterface::class);
    $entity_type_manager->getStorage('node')->willReturn($entity_storage->reveal());
    $entity_type = $this->prophesize(EntityTypeInterface::class);
    $entity_type->getKey('uuid')->willReturn('uuid');
    $entity_type_manager->getDefinition('node')->willReturn($entity_type->reveal());
    $entity_field_manager = $this->prophesize(EntityFieldManagerInterface::class);
    $field_storage_definition = $this->prophesize(FieldStorageDefinitionInterface::class);
    $property_definition = $this->prophesize(DataReferenceTargetDefinition::class);
    $property_definition->isInternal()->willReturn(FALSE);
    $property_definition->isReadOnly()->willReturn(FALSE);
    $field_storage_definition->getPropertyDefinition('target_id')->willReturn($property_definition->reveal());
    $field_storage_definition->getPropertyDefinition('other_property')->willReturn(NULL);
    $entity_field_manager->getFieldStorageDefinitions('node')->willReturn(['field_dummy' => $field_storage_definition->reveal()]);

    $this->normalizer = new JsonApiDocumentTopLevelNormalizer(
      $entity_type_manager->reveal(),
      $resource_type_repository->reveal(),
      $entity_field_manager->reveal(),
    );

    $serializer = $this->prophesize(DenormalizerInterface::class);
    $serializer->willImplement(SerializerInterface::class);
    $serializer->denormalize(
      Argument::type('array'),
      Argument::type('string'),
      Argument::type('string'),
      Argument::type('array')
    )->willReturnArgument(0);

    $this->normalizer->setSerializer($serializer->reveal());
  }

  /**
   * @covers ::denormalize
   * @dataProvider denormalizeProvider
   */
  public function testDenormalize($input, $expected) {
    $resource_type = $this->prophesize(ResourceType::class);
    $resource_type->getRelatableResourceTypes()->willReturn([]);
    $resource_type->getDeserializationTargetClass()->willReturn(FieldableEntityInterface::class);
    $resource_type_field = $this->prophesize(ResourceTypeField::class);
    $resource_type_field->getInternalName()->willReturn('field_dummy');
    $resource_type->getFieldByPublicName('field_dummy')->willReturn($resource_type_field->reveal());
    $resource_type->getFieldByPublicName('field_unknown')->willReturn(NULL);
    $resource_type->getEntityTypeId()->willReturn('node');

    $context = ['resource_type' => $resource_type->reveal()];
    $denormalized = $this->normalizer->denormalize($input, NULL, 'api_json', $context);
    $this->assertSame($expected, $denormalized);
  }

  /**
   * Generate mocked entities.
   *
   * @return \Drupal\Core\Entity\EntityInterface[]
   *   Mocked entities.
   */
  protected static function getMockEntities(): array {
    if (empty(static::$entities)) {
      $uuid_to_id = [
        '76dd5c18-ea1b-4150-9e75-b21958a2b836' => 1,
        'fcce1b61-258e-4054-ae36-244d25a9e04c' => 2,
      ];
      // Can't use $this->prophesize() in static context.
      $prophet = new Prophet();
      foreach ($uuid_to_id as $uuid => $id) {
        $entity = $prophet->prophesize(EntityInterface::class);
        $entity->uuid()->willReturn($uuid);
        $entity->id()->willReturn($id);
        static::$entities[$uuid] = $entity->reveal();
      }
    }
    return static::$entities;
  }

  /**
   * Data provider for the denormalize test.
   *
   * @return array
   *   The data for the test method.
   */
  public static function denormalizeProvider() {
    $mockEntities = static::getMockEntities();
    return [
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => 'e1a613f6-f2b9-4e17-9d33-727eb6509d8b',
            'attributes' => ['title' => 'dummy_title'],
          ],
        ],
        [
          'title' => 'dummy_title',
          'uuid' => 'e1a613f6-f2b9-4e17-9d33-727eb6509d8b',
        ],
      ],
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => '0676d1bf-55b3-4bbc-9fbc-3df10f4599d5',
            'relationships' => ['field_dummy' => ['data' => ['type' => 'node', 'id' => '76dd5c18-ea1b-4150-9e75-b21958a2b836']]],
          ],
        ],
        [
          'uuid' => '0676d1bf-55b3-4bbc-9fbc-3df10f4599d5',
          'field_dummy' => [
            [
              'entity' => $mockEntities['76dd5c18-ea1b-4150-9e75-b21958a2b836'],
            ],
          ],
        ],
      ],
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => '535ba297-8d79-4fc1-b0d6-dc2f047765a1',
            'relationships' => [
              'field_dummy' => [
                'data' => [
                  [
                    'type' => 'node',
                    'id' => '76dd5c18-ea1b-4150-9e75-b21958a2b836',
                  ],
                  [
                    'type' => 'node',
                    'id' => 'fcce1b61-258e-4054-ae36-244d25a9e04c',
                  ],
                ],
              ],
            ],
          ],
        ],
        [
          'uuid' => '535ba297-8d79-4fc1-b0d6-dc2f047765a1',
          'field_dummy' => [
            ['entity' => $mockEntities['76dd5c18-ea1b-4150-9e75-b21958a2b836']],
            ['entity' => $mockEntities['fcce1b61-258e-4054-ae36-244d25a9e04c']],
          ],
        ],
      ],
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => '535ba297-8d79-4fc1-b0d6-dc2f047765a1',
            'relationships' => [
              'field_dummy' => [
                'data' => [
                  [
                    'type' => 'node',
                    'id' => '76dd5c18-ea1b-4150-9e75-b21958a2b836',
                    'meta' => [
                      // This is not necessarily the target ID, but this
                      // demonstrates that meta data is set in the field value.
                      'target_id' => 1,
                      'other_property' => TRUE,
                    ],
                  ],
                  [
                    'type' => 'node',
                    'id' => 'fcce1b61-258e-4054-ae36-244d25a9e04c',
                  ],
                ],
              ],
            ],
          ],
        ],
        [
          'uuid' => '535ba297-8d79-4fc1-b0d6-dc2f047765a1',
          'field_dummy' => [
            [
              'entity' => $mockEntities['76dd5c18-ea1b-4150-9e75-b21958a2b836'],
              'target_id' => 1,
            ],
            ['entity' => $mockEntities['fcce1b61-258e-4054-ae36-244d25a9e04c']],
          ],
        ],
      ],
      [
        [
          'data' => [
            'type' => 'lorem',
            'id' => '535ba297-8d79-4fc1-b0d6-dc2f047765a1',
            'relationships' => [
              // This field is unknown during initial denormalization.
              'field_unknown' => [
                'data' => [
                  [
                    'type' => 'node',
                    'id' => '76dd5c18-ea1b-4150-9e75-b21958a2b836',
                    'meta' => [
                      'target_id' => 1,
                      'other_property' => TRUE,
                    ],
                  ],
                  [
                    'type' => 'node',
                    'id' => 'fcce1b61-258e-4054-ae36-244d25a9e04c',
                  ],
                ],
              ],
            ],
          ],
        ],
        [
          'uuid' => '535ba297-8d79-4fc1-b0d6-dc2f047765a1',
          'field_unknown' => [
            [
              'entity' => $mockEntities['76dd5c18-ea1b-4150-9e75-b21958a2b836'],
              'target_id' => 1,
              'other_property' => TRUE,
            ],
            ['entity' => $mockEntities['fcce1b61-258e-4054-ae36-244d25a9e04c']],
          ],
        ],
      ],
    ];
  }

  /**
   * Ensures only valid UUIDs can be specified.
   *
   * @param string $id
   *   The input UUID. May be invalid.
   * @param bool $expect_exception
   *   Whether to expect an exception.
   *
   * @covers ::denormalize
   * @dataProvider denormalizeUuidProvider
   */
  public function testDenormalizeUuid($id, $expect_exception) {
    $data['data'] = (isset($id)) ?
      ['type' => 'node--article', 'id' => $id] :
      ['type' => 'node--article'];

    if ($expect_exception) {
      $this->expectException(UnprocessableEntityHttpException::class);
      $this->expectExceptionMessage('IDs should be properly generated and formatted UUIDs as described in RFC 4122.');
    }

    $denormalized = $this->normalizer->denormalize($data, NULL, 'api_json', [
      'resource_type' => new ResourceType(
        'node',
        'article',
        FieldableEntityInterface::class
      ),
    ]);

    if (isset($id)) {
      $this->assertSame($id, $denormalized['uuid']);
    }
    else {
      $this->assertArrayNotHasKey('uuid', $denormalized);
    }
  }

  /**
   * Provides test cases for testDenormalizeUuid.
   */
  public static function denormalizeUuidProvider() {
    return [
      'valid' => ['76dd5c18-ea1b-4150-9e75-b21958a2b836', FALSE],
      'missing' => [NULL, FALSE],
      'invalid_empty' => ['', TRUE],
      'invalid_alpha' => ['invalid', TRUE],
      'invalid_numeric' => [1234, TRUE],
      'invalid_alphanumeric' => ['abc123', TRUE],
    ];
  }

}
