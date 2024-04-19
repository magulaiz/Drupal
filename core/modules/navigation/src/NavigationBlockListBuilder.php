<?php

declare(strict_types=1);

namespace Drupal\navigation;

use Drupal\block\BlockListBuilder;
use Drupal\Core\Form\FormInterface;
use Drupal\Core\Form\FormStateInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Provides a listing of block entities used for navigation toolbar.
 */
class NavigationBlockListBuilder extends BlockListBuilder implements FormInterface {

  /**
   * {@inheritdoc}
   */
  public function render($theme = NULL, Request $request = NULL) {
    $this->request = $request;
    return $this->formBuilder->getForm($this);
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'navigation_block_admin_display_form';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form = parent::buildForm($form, $form_state);
    $form['actions']['submit']['#value'] = $this->t('Save navigation blocks');

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  protected function getEntityIds() {
    $query = $this->getEntityListQuery();
    $condition = $query->orConditionGroup();
    $condition->notExists('theme');
    $condition->condition('theme', '');
    $condition->condition('theme', NULL);
    return $query->condition($condition)->execute();
  }

  protected function getRegionList(): array {
    return  [
      NavigationBlockRepositoryInterface::REGION_CONTENT => $this->t('Content'),
    ];
  }

}
