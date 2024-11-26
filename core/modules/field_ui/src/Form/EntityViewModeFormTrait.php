<?php

declare(strict_types=1);

namespace Drupal\field_ui\Form;

use Drupal\Core\Entity\EntityViewModeInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Routing\RouteBuilderInterface;

/**
 * Defines a trait for entity view mode forms.
 *
 * @phpstan-require-implements \Drupal\Core\Entity\EntityFormInterface
 */
trait EntityViewModeFormTrait {

  /**
   * Adds the path field.
   *
   * @param array $form
   *   Form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   Form state.
   */
  protected function addPathField(array &$form, FormStateInterface $form_state): void {
    \assert($this->entity instanceof EntityViewModeInterface);
    $entity_type_id = $this->entity->getTargetType();
    $entity_type = $this->entityTypeManager->getDefinition($entity_type_id);

    $canonical_template = $entity_type->getLinkTemplate('canonical');
    if ($canonical_template === FALSE) {
      return;
    }
    $display_id = NULL;
    if (!$this->entity->isNew()) {
      [, $display_id] = \explode('.', $this->entity->id());
    }
    if ($display_id === 'full') {
      return;
    }
    $path = $this->entity->getPath();
    $form['path'] = [
      '#type' => 'details',
      '#title' => t('Page display'),
      '#group' => 'additional_settings',
    ];

    $form['path']['page'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Page display'),
      '#value' => $path !== NULL,
      '#description' => t('Add a page-display for this view-mode.'),
      // This should not make it into submitted values.
      '#input' => FALSE,
    ];

    $form['path']['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#field_prefix' => $canonical_template . '/',
      '#description' => $this->t('Path to use for this view-mode.'),
      '#default_value' => $path,
      '#states' => [
        // We can't use :input format here because the page checkbox field has
        // no name.
        'visible' => ['[data-drupal-selector="edit-page"]' => ['checked' => TRUE]],
      ],
    ];
  }

  /**
   * Mark routes as needing rebuild.
   *
   * @param bool $rebuild
   *   TRUE to rebuild.
   */
  protected function markRouteRebuild(bool $rebuild): void {
    if ($rebuild) {
      \Drupal::service(RouteBuilderInterface::class)->setRebuildNeeded();
    }
  }

  /**
   * Set page display on view displays.
   *
   * @param string $entity_type_id
   *   Entity type ID.
   * @param string $view_mode
   *   View mode.
   * @param bool $page_display
   *   Value to set for page displays.
   */
  protected function setPageDisplayOnViewDisplays(string $entity_type_id, string $view_mode, bool $page_display): void {
    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    $display_ids = $storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('mode', $view_mode)
      ->condition('targetEntityType', $entity_type_id)
      ->condition('pageDisplay', $page_display, '<>')
      ->execute();
    if (\count($display_ids) === 0) {
      return;
    }
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
    foreach ($storage->loadMultiple($display_ids) as $display) {
      $display->setPageDisplay($page_display)->save();
    }
  }

}
