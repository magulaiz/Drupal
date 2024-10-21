<?php

namespace Drupal\views_entity_test\Hook;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Field\BaseFieldDefinition;
use Drupal\Core\Field\FieldDefinitionInterface;
use Drupal\Core\Field\FieldItemListInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Hook\Attribute\Hook;
class ViewsEntityTestHooks
{
    /**
     * Implements hook_entity_field_access().
     *
     * @see \Drupal\system\Tests\Entity\FieldAccessTest::testFieldAccess()
     */
    #[Hook('entity_field_access')]
    public function entityFieldAccess($operation, \Drupal\Core\Field\FieldDefinitionInterface $field_definition, \Drupal\Core\Session\AccountInterface $account, ?\Drupal\Core\Field\FieldItemListInterface $items = \NULL)
    {
        if ($field_definition->getName() == 'test_text_access') {
            if ($items) {
                if ($items->value == 'no access value') {
                    return \Drupal\Core\Access\AccessResult::forbidden()->addCacheableDependency($items->getEntity());
                }
            }
        }
        // No opinion.
        return \Drupal\Core\Access\AccessResult::neutral();
    }
    /**
     * Implements hook_entity_load().
     *
     * @see \Drupal\Tests\views\Kernel\Handler\FieldFieldTest::testSimpleExecute()
     */
    #[Hook('entity_load')]
    public function entityLoad(array $entities, $entity_type_id)
    {
        if ($entity_type_id === 'entity_test') {
            // Cast the value of an entity field to be something else than a string so
            // we can check that
            // \Drupal\views\Tests\ViewResultAssertionTrait::assertIdenticalResultsetHelper()
            // takes care of converting all field values to strings.
            foreach ($entities as $entity) {
                $entity->user_id->target_id = (int) $entity->user_id->target_id;
            }
        }
    }
}
