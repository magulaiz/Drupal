<?php

namespace Drupal\system\Form;

use Drupal\Core\Form\ConfigFormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;


/**
 * Configure RSS settings for this site.
 *
 * @internal
 */
class RssFeedsForm extends ConfigFormBase {

  public function __construct(
    protected readonly AccountProxyInterface $currentUser,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('current_user')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'system_rss_feeds_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['system.rss'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $text_formats = $this->getTextFormats();

    $form['feed_view_mode'] = [
      '#type' => 'select',
      '#title' => $this->t('Feed content'),
      '#config_target' => 'system.rss:items.view_mode',
      '#options' => [
        'title' => $this->t('Titles only'),
        'teaser' => $this->t('Titles plus teaser'),
        'fulltext' => $this->t('Full text'),
      ],
      '#description' => $this->t('Global setting for the default display of content items in each feed.'),
    ];

    $form['text_format'] = [
      '#type' => 'select',
      '#title' => $this->t('Text Format'),
      '#config_target' => 'system.rss:items.text_format',
      '#description' => $this->t('Choose a text format to apply to RSS feeds.'),
      '#options' => $text_formats,
      '#required' => TRUE,
      '#default' => 'basic_html',
    ];

    return parent::buildForm($form, $form_state);
  }

  /**
   * Helper function to get available text formats.
   *
   * @return array
   *   An array of text formats.
   */
  protected function getTextFormats() {
    $available_formats = [];
    foreach (filter_formats() as $format) {
      // Check if the current user has access to use this format.
      if ($format->status() && $format->access('use', $this->currentUser)) {
        $available_formats[$format->id()] = $format->label();
      }
    }
    return $available_formats;
  }

}
