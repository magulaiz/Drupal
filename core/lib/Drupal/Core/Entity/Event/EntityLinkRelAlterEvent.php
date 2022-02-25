<?php

namespace Drupal\Core\Entity\Event;

use Drupal\Core\Entity\ContentEntityInterface;
use Symfony\Component\EventDispatcher\Event;

/**
 * Defines a core entity  skip event.
 *
 * @see \Drupal\Core\Entity\Event\EntityEvents
 */
class EntityLinkRelAlterEvent extends Event {

  /**
   * The entity a path is generated for.
   *
   * @var \Drupal\Core\Entity\ContentEntityInterface
   */
  protected $entity;

  /**
   * The link relation, i.e. 'revision' or 'canonical'.
   *
   * @var string
   */
  protected $rel;

  /**
   * Constructs a new PathautoSkipEvent.
   *
   * @param \Drupal\Core\Entity\ContentEntityInterface $entity
   *   The entity a path is generated for.
   * @param bool $rel
   *   The link relation, i.e. 'revision' or 'canonical'.
   */
  public function __construct(ContentEntityInterface $entity, $rel) {
    $this->entity = $entity;
    $this->rel = $rel;
  }

  /**
   * Gets the entity a path is generated for.
   *
   * @return \Drupal\Core\Entity\EntityInterface
   *   The entity a path is generated for.
   */
  public function getEntity() {
    return $this->entity;
  }

  /**
   * Sets the relation for the generation of the URL.
   *
   * @param string $rel
   *   The link relation, i.e. 'revision' or 'canonical'.
   */
  public function setRel($rel) {
    $this->rel = $rel;
  }

  /**
   * Gets the relation for the generation of the URL.
   *
   * @return string
   *   The link relation, i.e. 'revision' or 'canonical'.
   */
  public function getRel() {
    return $this->rel;
  }

}
