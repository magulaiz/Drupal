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

    // Don't add path on add form or full mode.
    if (!$this->entity->isNew() && $this->entity->getMode() === EntityViewModeInterface::FULL_MODE) {
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
      '#description' => t('Add a page display for this view mode.'),
      // This should not make it into submitted values.
      '#input' => FALSE,
    ];

    $form['path']['path'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Path'),
      '#field_prefix' => $canonical_template . '/',
      '#description' => $this->t('Path to use for this view mode.'),
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
   */
  protected function rebuildRoute(): void {
    \Drupal::service(RouteBuilderInterface::class)->setRebuildNeeded();
  }

  /**
   * Enables or disables a page on all bundles.
   *
   * @param string $entityTypeId
   *   Entity type ID.
   * @param string $viewMode
   *   View mode.
   * @param bool $enablePageDisplay
   *   Whether to enable page display for all bundles.
   */
  protected function setPageDisplayOnViewDisplays(string $entityTypeId, string $viewMode, bool $enablePageDisplay): void {
    $storage = $this->entityTypeManager->getStorage('entity_view_display');
    $display_ids = $storage
      ->getQuery()
      ->accessCheck(FALSE)
      ->condition('targetEntityType', $entityTypeId)
      ->condition('mode', $viewMode)
      ->condition('pageDisplay', $enablePageDisplay, '<>')
      ->execute();
    /** @var \Drupal\Core\Entity\Display\EntityViewDisplayInterface $display */
    foreach ($storage->loadMultiple($display_ids) as $display) {
      $display->setPageDisplay($enablePageDisplay)->save();
    }
  }

}
