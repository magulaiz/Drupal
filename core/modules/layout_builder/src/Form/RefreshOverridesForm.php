<?php

namespace Drupal\layout_builder\Form;

use Drupal\Core\Form\ConfirmFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Messenger\MessengerInterface;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\OverridesSectionStorageInterface;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Refresh the overridden layout with the defaults.
 *
 * @internal
 *   Form classes are internal.
 */
class RefreshOverridesForm extends ConfirmFormBase {

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * The messenger service.
   *
   * @var \Drupal\Core\Messenger\MessengerInterface
   */
  protected $messenger;

  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\SectionStorageInterface
   */
  protected $sectionStorage;

  /**
   * Constructs a new RefreshOverridesForm.
   *
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\Core\Messenger\MessengerInterface $messenger
   *   The messenger service.
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, MessengerInterface $messenger) {
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->messenger = $messenger;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('messenger')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_builder_refresh_overrides';
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to refresh this layout with the defaults?');
  }

  /**
   * {@inheritdoc}
   */
  public function getConfirmText() {
    return $this->t('Refresh defaults');
  }

  /**
   * {@inheritdoc}
   */
  public function getCancelUrl() {
    return $this->sectionStorage->getLayoutBuilderUrl();
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL) {
    if (!$section_storage instanceof OverridesSectionStorageInterface) {
      throw new \InvalidArgumentException(sprintf('The section storage with type "%s" and ID "%s" does not provide overrides', $section_storage->getStorageType(), $section_storage->getStorageId()));
    }

    $this->sectionStorage = $section_storage;
    // Mark this as an administrative page for JavaScript ("Back to site" link).
    $form['#attached']['drupalSettings']['path']['currentPathIsAdmin'] = TRUE;
    return parent::buildForm($form, $form_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Refresh all sections with the defaults.
    if ($this->sectionStorage instanceof OverridesSectionStorageInterface) {

      $layoutSections = $this->sectionStorage->getSections();
      $defaultSections = $this->sectionStorage->getDefaultSectionStorage()->getSections();

      // Keep track of the delta when the layout sections are refreshed.
      $refresh_layout_delta = array_key_first($layoutSections);
      $refresh_default_delta = array_key_first($defaultSections);

      // Find the default sections in the layout, ...
      foreach ($layoutSections as $layout_delta => $section) {

        $layoutSettings = $section->getLayoutSettings();
        $section_label = !empty($layoutSettings['label']) ? $layoutSettings['label'] : sprintf('Section %d', $layout_delta + 1);

        // ... looking from the last refreshed default section, ...
        for ($default_delta = $refresh_default_delta; $default_delta < count($defaultSections); $default_delta++) {

          $defaultSection = $defaultSections[$default_delta];

          $defaultLayoutSettings = $defaultSection->getLayoutSettings();
          $default_section_label = !empty($defaultLayoutSettings['label']) ? $defaultLayoutSettings['label'] : sprintf('Section %d', $default_delta + 1);

          // and when a default section is found in the layout, ...
          if ($section_label === $default_section_label) {

            // ...remove the current default section, ...
            $this->sectionStorage->removeSection($refresh_layout_delta);

            // ...insert all previous defaults before this one, ...
            $previous_default_sections = array_slice($defaultSections, $refresh_default_delta, $default_delta - $refresh_default_delta, TRUE);

            foreach ($previous_default_sections as $previousDefaultSection) {
              $this->sectionStorage->insertSection($refresh_layout_delta, $previousDefaultSection);
              $refresh_default_delta++;
              $refresh_layout_delta++;
            }

            // ...re-insert the current default section, ...
            $this->sectionStorage->insertSection($refresh_layout_delta, $defaultSection);
            $refresh_default_delta++;

            // ... defaults added, so continue with another layout section, ...
            break;
          }
        }

        // ...and refresh layout delta for the next section.
        $refresh_layout_delta++;
      }

      // Update the layout section storage with the new re-ordering.
      $this->sectionStorage
        ->save();
      $this->layoutTempstoreRepository->set($this->sectionStorage);

      $this->messenger->addMessage($this->t('The layout has been refreshed with the defaults.'));
    }
    else {
      $this->messenger->addMessage($this->t('The layout has no available defaults to refresh.'));
    }

    $form_state->setRedirectUrl($this->sectionStorage->getRedirectUrl());
  }

}
