<?php

namespace Drupal\media_library\Form;

use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\media\MediaInterface;
use Drupal\Core\Entity\Entity\EntityFormDisplay;
use Drupal\Core\Ajax\AjaxResponse;
use Drupal\Core\Ajax\ReplaceCommand;
use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Ajax\SetDialogTitleCommand;
use Drupal\media_library\MediaLibraryUiBuilder;

/**
 * Provides a base class for editing media items from within the media library.
 */
class EditForm extends MediaLibraryFormBase {

  /**
   * {@inheritdoc}
   */
  public function getBaseFormId() {
    return 'media_library_edit_form';
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->getBaseFormId();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {

    // @todo Remove the ID when we can use selectors to replace content via
    //   AJAX in https://www.drupal.org/project/drupal/issues/2821793.
    $form['#prefix'] = '<div id="media-library-edit-form-wrapper">';
    $form['#suffix'] = '</div>';

    // The form is posted via AJAX. When there are messages set during the
    // validation or submission of the form, the messages need to be shown to
    // the user.
    $form['status_messages'] = [
      '#type' => 'status_messages',
    ];

    $form['media'] = [
      '#pre_render' => [
        [$this, 'preRenderAddedMedia'],
      ],
    ];

    /** @var \Drupal\media_library\MediaLibraryState $state */
    $state = $this->getMediaLibraryState($form_state);

    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->entityTypeManager->getStorage('media')->load($state->get('media_library_edit'));
    $form['media'][0] = $this->buildEntityFormElement($media, $form, $form_state);

    $form['actions'] = $this->buildActions($form, $form_state);
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {

    /** @var \Drupal\media_library\MediaLibraryState $state */
    $state = $this->getMediaLibraryState($form_state);

    /** @var \Drupal\media\MediaInterface $media */
    $media = $this->entityTypeManager->getStorage('media')->load($state->get('media_library_edit'));

    $display = EntityFormDisplay::collectRenderDisplay($media, 'media_library');
    $display->extractFormValues($media, $form['media'][0]['fields'], $form_state);
    $media->save();

    $form_state->setRebuild();
  }

  /**
   * Builds the sub-form for setting required fields on a new media item.
   *
   * @param \Drupal\media\MediaInterface $media
   *   A new, unsaved media item.
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   The element containing the required fields sub-form.
   */
  protected function buildEntityFormElement(MediaInterface $media, array $form, FormStateInterface $form_state) {
    $element = [
      'preview' => [
        '#type' => 'container',
        '#weight' => 10,
      ],
      'fields' => [
        '#type' => 'container',
        '#weight' => 20,
        // The '#parents' are set here because the entity form display needs it
        // to build the entity form fields.
        '#parents' => ['media', 0, 'fields'],
      ],
    ];
    // @todo Make the image style configurable in
    //   https://www.drupal.org/node/2988223
    $source = $media->getSource();
    $plugin_definition = $source->getPluginDefinition();
    if ($thumbnail_uri = $source->getMetadata($media, $plugin_definition['thumbnail_uri_metadata_attribute'])) {
      $element['preview']['thumbnail'] = [
        '#theme' => 'image_style',
        '#style_name' => 'media_library',
        '#uri' => $thumbnail_uri,
      ];
    }

    $form_display = EntityFormDisplay::collectRenderDisplay($media, 'media_library');
    // When the name is not added to the form as an editable field, output
    // the name as a fixed element to confirm the right file was uploaded.
    if (!$form_display->getComponent('name')) {
      $element['fields']['name'] = [
        '#type' => 'item',
        '#title' => $this->t('Name'),
        '#markup' => $media->getName(),
      ];
    }
    $form_display->buildForm($media, $element['fields'], $form_state);

    // Add source field name so that it can be identified in form alter and
    // widget alter hooks.
    $element['fields']['#source_field_name'] = $this->getSourceFieldName($media->bundle->entity);

    // The revision log field is currently not configurable from the form
    // display, so hide it by changing the access.
    // @todo Make the revision_log_message field configurable in
    //   https://www.drupal.org/project/drupal/issues/2696555
    if (isset($element['fields']['revision_log_message'])) {
      $element['fields']['revision_log_message']['#access'] = FALSE;
    }
    return $element;
  }

  /**
   * Returns an array of supported actions for the form.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return array
   *   An actions element containing the actions of the form.
   */
  protected function buildActions(array $form, FormStateInterface $form_state) {
    return [
      '#type' => 'actions',
      'save' => [
        '#type' => 'submit',
        '#button_type' => 'primary',
        '#value' => $this->t('Save'),
        '#ajax' => [
          'callback' => '::buildMediaLibrary',
          'wrapper' => 'media-library-wrapper',
          'url' => Url::fromRoute('media_library.ui'),
          'options' => [
            'query' => $this->getMediaLibraryState($form_state)->all() + [
              FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
            ],
          ],
        ],
      ],
      'cancel' => [
        '#type' => 'button',
        '#value' => $this->t('Cancel'),
        '#limit_validation_errors' => [],
        '#ajax' => [
          'callback' => '::buildMediaLibrary',
          'wrapper' => 'media-library-wrapper',
          'url' => Url::fromRoute('media_library.ui'),
          'options' => [
            'query' => $this->getMediaLibraryState($form_state)->all() + [
              FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
            ],
          ],
        ],
      ],
    ];
  }

  /**
   * AJAX callback to send the overview back to the media library.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   *
   * @return \Drupal\Core\Ajax\AjaxResponse
   *   An AJAX response with the media library view.
   */
  public function buildMediaLibrary(array &$form, FormStateInterface $form_state) {
    $response = new AjaxResponse();
    if ($form_state::hasAnyErrors()) {
      $response->addCommand(new ReplaceCommand('#media-library-edit-form-wrapper', $form));
      return $response;
    }

    $state = $this->getMediaLibraryState($form_state);
    $state->remove('media_library_edit');
    $state->remove('media_library_content');

    $form = $this->libraryUiBuilder->buildUi($state);

    $triggering_element = $form_state->getTriggeringElement();
    $response->addCommand(new ReplaceCommand('#' . $triggering_element['#ajax']['wrapper'], $form));

    $dialogOptions = MediaLibraryUiBuilder::dialogOptions();
    $response->addCommand(new SetDialogTitleCommand(NULL, $dialogOptions['title']));
    return $response;
  }

}
