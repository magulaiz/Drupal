<?php

namespace Drupal\layout_builder\Form;

use Drupal\Component\Utility\Html;
use Drupal\Core\Ajax\AjaxFormHelperTrait;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\SubformState;
use Drupal\Core\Layout\LayoutInterface;
use Drupal\Core\Layout\LayoutPluginManagerInterface;
use Drupal\Core\Plugin\PluginFormFactoryInterface;
use Drupal\Core\Plugin\PluginFormInterface;
use Drupal\Core\Plugin\PluginWithFormsInterface;
use Drupal\layout_builder\Controller\LayoutRebuildTrait;
use Drupal\layout_builder\LayoutBuilderHighlightTrait;
use Drupal\layout_builder\LayoutTempstoreRepositoryInterface;
use Drupal\layout_builder\Section;
use Drupal\layout_builder\SectionStorageInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form for changing a section's layout.
 *
 * @internal
 *   Form classes are internal.
 */
class MapSectionRegionsForm extends FormBase {

  use AjaxFormHelperTrait;
  use LayoutBuilderHighlightTrait;
  use LayoutRebuildTrait;

  /**
   * The layout tempstore repository.
   *
   * @var \Drupal\layout_builder\LayoutTempstoreRepositoryInterface
   */
  protected $layoutTempstoreRepository;

  /**
   * The plugin being configured.
   *
   * @var \Drupal\Core\Layout\LayoutInterface|\Drupal\Core\Plugin\PluginFormInterface
   */
  protected $layout;

  /**
   * The plugin form manager.
   *
   * @var \Drupal\Core\Plugin\PluginFormFactoryInterface
   */
  protected $pluginFormFactory;

  /**
   * The layout plugin manager.
   *
   * @var \Drupal\Core\Layout\LayoutPluginManagerInterface
   */
  protected $layoutPluginManager;

  /**
   * The section storage.
   *
   * @var \Drupal\layout_builder\SectionStorageInterface
   */
  protected $sectionStorage;

  /**
   * The field delta.
   *
   * @var int
   */
  protected $delta;

  /**
   * Indicates whether the section is being added or updated.
   *
   * @var bool
   */
  protected $isUpdate;

  /**
   * Constructs a new MapSectionRegionsForm.
   *
   * @param \Drupal\layout_builder\LayoutTempstoreRepositoryInterface $layout_tempstore_repository
   *   The layout tempstore repository.
   * @param \Drupal\Core\Plugin\PluginFormFactoryInterface $plugin_form_manager
   *   The plugin form manager.
   * @param \Drupal\Core\Layout\LayoutPluginManagerInterface $layout_plugin_manager
   *   The layout plugin manager.
   */
  public function __construct(LayoutTempstoreRepositoryInterface $layout_tempstore_repository, PluginFormFactoryInterface $plugin_form_manager, LayoutPluginManagerInterface $layout_plugin_manager) {
    $this->layoutTempstoreRepository = $layout_tempstore_repository;
    $this->pluginFormFactory = $plugin_form_manager;
    $this->layoutPluginManager = $layout_plugin_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('layout_builder.tempstore_repository'),
      $container->get('plugin_form.factory'),
      $container->get('plugin.manager.core.layout')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'layout_builder_map_section_regions';
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state, SectionStorageInterface $section_storage = NULL, $delta = NULL, $plugin_id = NULL) {
    $this->sectionStorage = $section_storage;
    $this->delta = $delta;

    // @todo Do we need to pass in the layout_settings from the $form_state?
    $this->layout = $this->layoutPluginManager->createInstance($plugin_id);

    $form['#tree'] = TRUE;
    $form['layout_settings'] = [];
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $form['layout_settings'] = $this->getPluginForm($this->layout)->buildConfigurationForm($form['layout_settings'], $subform_state);

    $section = $this->sectionStorage->getSection($this->delta);
    $old_layout_region_labels = $section->getLayout()->getPluginDefinition()->getRegionLabels();
    $new_layout_region_labels = $this->layout->getPluginDefinition()->getRegionLabels();
    $new_layout_first_region = array_key_first($new_layout_region_labels);

    $form['region_mapping'] = [
      '#type' => 'fieldset',
      '#title' => $this->t('Region mapping'),
      '#attributes' => [
        'class' => ['js-layout-builder-region-mapping'],
      ],
    ];
    $form['region_mapping']['values'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['js-hide'],
      ],
    ];

    $visual_region_blocks = [];
    foreach ($new_layout_region_labels as $region => $region_label) {
      $visual_region_blocks[$region]['wrapper'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'layout-builder__region-mapping__region',
            'js-layout-builder-region-mapping-region',
          ],
          'data-new-region' => $region,
        ],
      ];
      $visual_region_blocks[$region]['wrapper']['label'] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => ['layout-builder__region-mapping__region-label'],
        ],
        '#markup' => $region_label,
      ];
    }

    foreach ($old_layout_region_labels as $region => $region_label) {
      $mapped_region = isset($new_layout_region_labels[$region]) ? $region : $new_layout_first_region;

      $form['region_mapping']['values'][$region] = [
        '#type' => 'select',
        '#title' => $region_label,
        '#options' => $new_layout_region_labels,
        '#default_value' => $mapped_region,
        '#attributes' => [
          'data-region' => $region,
        ],
      ];

      $visual_region_blocks[$mapped_region]['wrapper'][] = [
        '#type' => 'container',
        '#attributes' => [
          'class' => [
            'layout-builder__region-mapping__block',
            'js-layout-builder-region-mapping-block',
          ],
          'data-old-region' => $region,
        ],
        '#markup' => $region_label,
      ];
    }
    $form['region_mapping']['visual'] = [
      '#type' => 'container',
      '#attributes' => [
        'class' => ['layout-builder__region-mapping', 'js-show'],
      ],
    ];
    $form['region_mapping']['visual']['layout'] = $this->layout->build($visual_region_blocks);

    $form['actions']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Update'),
      '#button_type' => 'primary',
    ];
    if ($this->isAjax()) {
      $form['actions']['submit']['#ajax']['callback'] = '::ajaxSubmit';
      // @todo static::ajaxSubmit() requires data-drupal-selector to be the same
      //   between the various Ajax requests. A bug in
      //   \Drupal\Core\Form\FormBuilder prevents that from happening unless
      //   $form['#id'] is also the same. Normally, #id is set to a unique HTML
      //   ID via Html::getUniqueId(), but here we bypass that in order to work
      //   around the data-drupal-selector bug. This is okay so long as we
      //   assume that this form only ever occurs once on a page. Remove this
      //   workaround in https://www.drupal.org/node/2897377.
      $form['#id'] = Html::getId($form_state->getBuildInfo()['form_id']);
    }
    $target_highlight_id = $this->sectionUpdateHighlightId($delta);
    $form['#attributes']['data-layout-builder-target-highlight-id'] = $target_highlight_id;

    // Mark this as an administrative page for JavaScript ("Back to site" link).
    $form['#attached']['drupalSettings']['path']['currentPathIsAdmin'] = TRUE;
    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $this->getPluginForm($this->layout)->validateConfigurationForm($form['layout_settings'], $subform_state);
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    // Call the plugin submit handler.
    $subform_state = SubformState::createForSubform($form['layout_settings'], $form, $form_state);
    $this->getPluginForm($this->layout)->submitConfigurationForm($form['layout_settings'], $subform_state);

    $old_section = $this->sectionStorage->getSection($this->delta);
    $third_party_settings = [];
    foreach ($old_section->getThirdPartyProviders() as $provider) {
      $third_party_settings[$provider] = $old_section->getThirdPartySettings($provider);
    }

    $plugin_id = $this->layout->getPluginId();
    $configuration = $this->layout->getConfiguration();
    $new_section = new Section($plugin_id, $configuration, $old_section->getComponents(), $third_party_settings);

    $region_mapping = $form_state->getValue(['region_mapping', 'values']);
    foreach ($new_section->getComponents() as $component) {
      $component->setRegion($region_mapping[$component->getRegion()]);
    }

    $this->sectionStorage->removeSection($this->delta);
    $this->sectionStorage->insertSection($this->delta, $new_section);

    $this->layoutTempstoreRepository->set($this->sectionStorage);
    $form_state->setRedirectUrl($this->sectionStorage->getLayoutBuilderUrl());
  }

  /**
   * {@inheritdoc}
   */
  protected function successfulAjaxSubmit(array $form, FormStateInterface $form_state) {
    return $this->rebuildAndClose($this->sectionStorage);
  }

  /**
   * Retrieves the plugin form for a given layout.
   *
   * @param \Drupal\Core\Layout\LayoutInterface $layout
   *   The layout plugin.
   *
   * @return \Drupal\Core\Plugin\PluginFormInterface
   *   The plugin form for the layout.
   */
  protected function getPluginForm(LayoutInterface $layout) {
    if ($layout instanceof PluginWithFormsInterface) {
      return $this->pluginFormFactory->createInstance($layout, 'configure');
    }

    if ($layout instanceof PluginFormInterface) {
      return $layout;
    }

    throw new \InvalidArgumentException(sprintf('The "%s" layout does not provide a configuration form', $layout->getPluginId()));
  }

  /**
   * Retrieve the section storage property.
   *
   * @return \Drupal\layout_builder\SectionStorageInterface
   *   The section storage for the current form.
   */
  public function getSectionStorage() {
    return $this->sectionStorage;
  }

}
