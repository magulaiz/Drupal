<?php

namespace Drupal\layout_builder_extra_field_test\Hook;

use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Hook\Attribute\Hook;
class LayoutBuilderExtraFieldTestHooks
{
    /**
     * Implements hook_entity_extra_field_info().
     */
    #[Hook('entity_extra_field_info')]
    public function entityExtraFieldInfo()
    {
        $extra['node']['bundle_with_section_field']['display']['layout_builder_extra_field_test'] = ['label' => \t('New Extra Field'), 'description' => \t('New Extra Field description'), 'weight' => 0];
        return $extra;
    }
}
