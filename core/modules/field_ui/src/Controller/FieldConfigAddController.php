<?php

declare(strict_types=1);

namespace Drupal\field_ui\Controller;

use Drupal\Core\Ajax\AjaxHelperTrait;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\OpenModalDialogCommand;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\TempStore\PrivateTempStore;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * Controller for building the field instance form.
 *
 * @internal
 */
final class FieldConfigAddController extends ControllerBase {
  use AjaxHelperTrait;

  /**
   * FieldConfigAddController constructor.
   *
   * @param \Drupal\Core\TempStore\PrivateTempStore $tempStore
   *   The private tempstore.
   */
  public function __construct(
    protected readonly PrivateTempStore $tempStore,
  ) {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('tempstore.private')->get('field_ui'),
    );
  }

  /**
   * Builds the field config instance form.
   *
   * @param string $entity_type
   *   The entity type.
   * @param string $field_name
   *   The name of the field to create.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   The field instance edit form.
   */
  public function fieldConfigAddConfigureForm(string $entity_type, string $field_name) {
    $temp_storage = $this->tempStore->get($entity_type . ':' . $field_name);
    if (!$temp_storage) {
      throw new NotFoundHttpException();
    }

    /** @var \Drupal\Core\Field\FieldConfigInterface $entity */
    $entity = $this->entityTypeManager()->getStorage('field_config')->create([
      ...$temp_storage['field_config_values'],
      'field_storage' => $temp_storage['field_storage'],
    ]);

    $edit_form = $this->entityFormBuilder()->getForm($entity, 'default', [
      'default_options' => $temp_storage['default_options'],
    ]);

    if ($this->isAjax()) {
      $dialog_options['modal'] = TRUE;
      $response = new AjaxResponse();
      $response->addCommand(new OpenModalDialogCommand('Field settings', $edit_form, $dialog_options));
    }
    else {
      $response = $edit_form;
    }
    return $response;
  }

}
