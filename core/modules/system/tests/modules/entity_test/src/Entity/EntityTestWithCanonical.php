<?php

namespace Drupal\entity_test\Entity;

/**
 * Test entity class with canonical.
 *
 * @ContentEntityType(
 *   id = "entity_test_with_canonical",
 *   label = @Translation("Entity Test with canonical"),
 *   base_table = "entity_test_with_canonical",
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
 *     "canonical" = "/entity_test_with_canonical/{entity_test_with_canonical}",
 *   },
 * )
 */
class EntityTestWithCanonical extends EntityTest {

  /**
   * {@inheritdoc}
   */
  public function hasLinkTemplate($rel): bool {
    if ($rel === 'canonical') {
      return TRUE;
    }
    return parent::hasLinkTemplate($rel);
  }

}
