<?php

namespace Drupal\KernelTests\Core\Entity;

use Drupal\block_content\Entity\BlockContentType;
use Drupal\Core\Config\Entity\ConfigEntityInterface;
use Drupal\Core\TypedData\DataDefinition;
use Drupal\Core\TypedData\TypedDataManagerInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Validator\Exception\LogicException;
use Symfony\Component\Validator\Exception\UnexpectedValueException;

/**
 * @group Entity
 * @group Validation
 *
 * @covers \Drupal\Core\Config\Plugin\Validation\Constraint\ImmutableFieldsConstraint
 * @covers \Drupal\Core\Config\Plugin\Validation\Constraint\ImmutableFieldsConstraintValidator
 */
class ImmutableFieldsConstraintValidatorTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block_content'];

  /**
   * Tests that only config entities are accepted by the validator.
   */
  public function testValidatorRequiresAConfigEntity(): void {
    $definition = DataDefinition::createFromDataType('any')
      ->addConstraint('ImmutableFields', ['read_only']);
    $data = $this->container->get(TypedDataManagerInterface::class)
      ->create($definition, 39);
    $this->expectException(UnexpectedValueException::class);
    $this->expectExceptionMessage('Expected argument of type "' . ConfigEntityInterface::class . '", "int" given');
    $data->validate();
  }

  /**
   * Tests that entities without an ID will raise an exception.
   */
  public function testValidatedEntityMustHaveAnId(): void {
    $entity = $this->prophesize(ConfigEntityInterface::class);
    $entity->isNew()->willReturn(FALSE)->shouldBeCalled();
    $entity->getOriginalId()->shouldBeCalled();
    $entity->id()->shouldBeCalled();

    $definition = DataDefinition::createFromDataType('any')
      ->addConstraint('ImmutableFields', ['read_only']);
    $data = $this->container->get(TypedDataManagerInterface::class)
      ->create($definition, $entity->reveal());
    $this->expectException(LogicException::class);
    $this->expectExceptionMessage('The entity does not have an ID.');
    $data->validate();
  }

  /**
   * Tests that changing an immutable field of a config entity raises an error.
   */
  public function testImmutableFieldCannotBeChanged(): void {
    /** @var \Drupal\block_content\BlockContentTypeInterface $entity */
    $entity = BlockContentType::create([
      'id' => 'test',
      'label' => 'Test',
    ]);
    $entity->save();

    $entity->set('id', 'foo')->set('label', 'Testing!');

    $definition = DataDefinition::createFromDataType('entity:block_content_type')
      ->addConstraint('ImmutableFields', ['id']);
    $violations = $this->container->get(TypedDataManagerInterface::class)
      ->create($definition, $entity)
      ->validate();
    $this->assertCount(1, $violations);
    $this->assertSame("The 'id' property cannot be changed.", (string) $violations[0]->getMessage());
  }

}
