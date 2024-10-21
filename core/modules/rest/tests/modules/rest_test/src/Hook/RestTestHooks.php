<?php

namespace Drupal\rest_test\Hook;

use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Hook\Attribute\Hook;
class RestTestHooks
{
    /**
     * Implements hook_entity_field_access().
     *
     * @see \Drupal\Tests\rest\Functional\EntityResource\EntityResourceTestBase::setUp()
     */
    #[Hook('entity_field_access')]
    public function entityFieldAccess($operation, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, \Drupal\Core\Session\AccountInterface $account, ?\Drupal\Core\Field\FieldItemListInterface $items = \NULL)
    {
        // @see \Drupal\Tests\rest\Functional\EntityResource\EntityResourceTestBase::testPost()
        // @see \Drupal\Tests\rest\Functional\EntityResource\EntityResourceTestBase::testPatch()
        if ($field_definition->getName() === 'field_rest_test') {
            switch ($operation) {
                case 'view':
                    // Never ever allow this field to be viewed: this lets
                    // EntityResourceTestBase::testGet() test in a "vanilla" way.
                    return \Drupal\Core\Access\AccessResult::forbidden();
                case 'edit':
                    return \Drupal\Core\Access\AccessResult::forbidden();
            }
        }
        // @see \Drupal\Tests\rest\Functional\EntityResource\EntityResourceTestBase::testGet()
        // @see \Drupal\Tests\rest\Functional\EntityResource\EntityResourceTestBase::testPatch()
        if ($field_definition->getName() === 'field_rest_test_multivalue') {
            switch ($operation) {
                case 'view':
                    // Never ever allow this field to be viewed: this lets
                    // EntityResourceTestBase::testGet() test in a "vanilla" way.
                    return \Drupal\Core\Access\AccessResult::forbidden();
            }
        }
        // @see \Drupal\Tests\rest\Functional\EntityResource\EntityResourceTestBase::testGet()
        // @see \Drupal\Tests\rest\Functional\EntityResource\EntityResourceTestBase::testPatch()
        if ($field_definition->getName() === 'rest_test_validation') {
            switch ($operation) {
                case 'view':
                    // Never ever allow this field to be viewed: this lets
                    // EntityResourceTestBase::testGet() test in a "vanilla" way.
                    return \Drupal\Core\Access\AccessResult::forbidden();
            }
        }
        // No opinion.
        return \Drupal\Core\Access\AccessResult::neutral();
    }
    /**
     * Implements hook_entity_base_field_info().
     */
    #[Hook('entity_base_field_info')]
    public function entityBaseFieldInfo(\Drupal\Core\Entity\EntityTypeInterface $entity_type)
    {
        $fields = [];
        $fields['rest_test_validation'] = \Drupal\Core\Field\BaseFieldDefinition::create('string')->setLabel(\t('REST test validation field'))->setDescription(\t('A text field with some special validations attached used for testing purposes'))->addConstraint('rest_test_validation');
        return $fields;
    }
}
