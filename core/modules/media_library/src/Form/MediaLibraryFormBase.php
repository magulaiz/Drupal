<?php

namespace Drupal\media_library\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\BaseFormIdInterface;
use Drupal\Core\Security\TrustedCallbackInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\media_library\MediaLibraryUiBuilder;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Render\Element;
use Drupal\media\MediaTypeInterface;

/**
 * Base class for media library forms.
 */
abstract class MediaLibraryFormBase extends FormBase implements BaseFormIdInterface, TrustedCallbackInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * The media library UI builder.
   *
   * @var \Drupal\media_library\MediaLibraryUiBuilder
   */
  protected $libraryUiBuilder;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $form = parent::create($container);
    $form->setEntityTypeManager($container->get('entity_type.manager'));
    $form->setMediaLibraryUiBuilder($container->get('media_library.ui_builder'));
    return $form;
  }

  /**
   * Set entity type manager service.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entityTypeManager
   *   The entity type manager service.
   */
  protected function setEntityTypeManager(EntityTypeManagerInterface $entityTypeManager) {
    $this->entityTypeManager = $entityTypeManager;
  }

  /**
   * Set media library UI builder service.
   *
   * @param \Drupal\media_library\MediaLibraryUiBuilder $mediaLibraryUiBuilder
   *   The media library UI builder service.
   */
  protected function setMediaLibraryUiBuilder(MediaLibraryUiBuilder $mediaLibraryUiBuilder) {
    $this->libraryUiBuilder = $mediaLibraryUiBuilder;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['preRenderAddedMedia'];
  }

  /**
   * Get the media library state from the form state.
   *
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return \Drupal\media_library\MediaLibraryState
   *   The media library state.
   *
   * @throws \InvalidArgumentException
   *   If the media library state is not present in the form state.
   */
  protected function getMediaLibraryState(FormStateInterface $form_state) {
    $state = $form_state->get('media_library_state');
    if (!$state) {
      throw new \InvalidArgumentException('The media library state is not present in the form state.');
    }
    return $state;
  }

  /**
   * Converts the set of newly added media into an item list for rendering.
   *
   * @param array $element
   *   The render element to transform.
   *
   * @return array
   *   The transformed render element.
   */
  public function preRenderAddedMedia(array $element) {
    // Transform the element into an item list for rendering.
    $element['#theme'] = 'item_list__media_library_add_form_media_list';
    $element['#list_type'] = 'ul';

    foreach (Element::children($element) as $delta) {
      $element['#items'][$delta] = $element[$delta];
      unset($element[$delta]);
    }
    return $element;
  }

  /**
   * Returns the name of the source field for a media type.
   *
   * @param \Drupal\media\MediaTypeInterface $media_type
   *   The media type to get the source field name for.
   *
   * @return string
   *   The name of the media type's source field.
   */
  protected function getSourceFieldName(MediaTypeInterface $media_type) {
    return $media_type->getSource()
      ->getSourceFieldDefinition($media_type)
      ->getName();
  }

}
