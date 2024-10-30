<?php

namespace Drupal\node\Controller;

use Drupal\Core\DependencyInjection\AutowireTrait;
use Drupal\Core\Entity\Controller\EntityViewController;
use Drupal\Core\Entity\EntityDisplayRepositoryInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Defines a controller to render a single node.
 */
class NodeViewController extends EntityViewController {

  use AutowireTrait;

  public function __construct(
    EntityTypeManagerInterface $entity_type_manager,
    RendererInterface $renderer,
    protected readonly EntityRepositoryInterface $entityRepository,
    protected readonly EntityDisplayRepositoryInterface $entityDisplayRepository,
  ) {
    parent::__construct($entity_type_manager, $renderer);
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $node, $view_mode = 'full', $langcode = NULL) {
    $display = $this->entityDisplayRepository->getViewDisplay($node->getEntityTypeId(), $node->bundle(), $view_mode);
    if (!$display->isNew() && !$display->hasPageDisplay()) {
      throw new NotFoundHttpException();
    }
    return parent::view($node, $view_mode);
  }

  /**
   * The _title_callback for the page that renders a single node.
   *
   * @param \Drupal\Core\Entity\EntityInterface $node
   *   The current node.
   *
   * @return string
   *   The page title.
   */
  public function title(EntityInterface $node) {
    return $this->entityRepository->getTranslationFromContext($node)->label();
  }

}
