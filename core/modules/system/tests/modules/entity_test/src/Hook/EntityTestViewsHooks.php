<?php

namespace Drupal\entity_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class EntityTestViewsHooks
{
    /**
     * Implements hook_views_data_alter().
     */
    #[Hook('views_data_alter')]
    public function viewsDataAlter(&$data)
    {
        $data['entity_test']['name_alias'] = $data['entity_test']['name'];
        $data['entity_test']['name_alias']['field']['real field'] = 'name';
    }
}
