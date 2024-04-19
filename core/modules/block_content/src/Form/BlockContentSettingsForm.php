<?php

declare(strict_types=1);

namespace Drupal\block_content\Form;

use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Config\TypedConfigManagerInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Form\ConfigFormBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a form to configure Block Content settings.
 *
 * @internal
 */
final class BlockContentSettingsForm extends ConfigFormBase {

  /**
   * {@inheritdoc}
   */
  public function __construct(
    ConfigFactoryInterface $config_factory,
    TypedConfigManagerInterface $typedConfigManager,
    protected EntityTypeManagerInterface $entityTypeManager
  ) {
    parent::__construct($config_factory, $typedConfigManager);
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container): BlockContentSettingsForm|ConfigFormBase|static {
    return new static(
      $container->get('config.factory'),
      $container->get('config.typed'),
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId(): string {
    return 'block_content_settings_form';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames(): array {
    return ['block_content.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state): array {
    $form['standalone_url'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Standalone Block Content URL'),
      '#config_target' => 'block_content.settings:standalone_url',
      '#description' => $this->t("Allow users to access @block-content-entities at /admin/content/block/{id}.", ['@block-content-entities' => $this->entityTypeManager->getDefinition('block_content')->getPluralLabel()]),
    ];
    return parent::buildForm($form, $form_state);
  }

}
