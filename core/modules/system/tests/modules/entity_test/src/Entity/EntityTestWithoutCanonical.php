<?php

namespace Drupal\entity_test\Entity;

/**
 * Test entity class without canonical.
 *
 * @ContentEntityType(
 *   id = "entity_test_without_canonical",
 *   label = @Translation("Entity Test without canonical"),
 *   base_table = "entity_test_without_canonical",
 *   handlers = {
 *     "route_provider" = {
 *       "html" = "Drupal\Core\Entity\Routing\DefaultHtmlRouteProvider",
 *     },
 *   },
 *   entity_keys = {
 *     "id" = "id",
 *     "label" = "name",
 *     "revision" = "revision_id",
 *   },
 *   links = {
 *     "canonical" = "/entity_test_without_canonical/{entity_test_without_canonical}",
 *   },
 * )
 */
class EntityTestWithoutCanonical extends EntityTest {

  /**
   * {@inheritdoc}
   */
  public function hasLinkTemplate($rel): bool {
    if ($rel === 'canonical') {
      return FALSE;
    }
    return parent::hasLinkTemplate($rel);
  }

}
