<?php

declare(strict_types = 1);

namespace Drupal\path_alias\Form;

use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\DependencyInjection\DependencySerializationTrait;
use Drupal\Core\Form\FormStateInterface;
use Drupal\path_alias\AliasManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Alteration class for the site information settings form.
 */
class SiteInformationFormAlter implements ContainerInjectionInterface {

  use DependencySerializationTrait;

  /**
   * Constructs a SystemInformationFormAlter object.
   *
   * @param \Drupal\path_alias\AliasManagerInterface $aliasManager
   *   The path alias manager.
   */
  public function __construct(
    protected AliasManagerInterface $aliasManager,
  ) {}

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static($container->get('path_alias.manager'));
  }

  /**
   * Alters the specified form.
   *
   * @param array $form
   *   A form array.
   */
  public function alterForm(array &$form): void {
    $front_page = &$form['front_page']['site_frontpage']['#default_value'];
    if ($front_page !== '') {
      $front_page = $this->aliasManager->getAliasByPath($front_page);
    }
    array_unshift($form['#validate'], [$this, 'validateForm']);
  }

  /**
   * Validates the specified form.
   *
   * @param array $form
   *   A form array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function validateForm(array $form, FormStateInterface $form_state): void {
    // Check for empty front page path.
    if (!$form_state->isValueEmpty('site_frontpage')) {
      // Get the normal path of the front page.
      $form_state->setValueForElement(
        $form['front_page']['site_frontpage'],
        $this->aliasManager->getPathByAlias($form_state->getValue('site_frontpage')),
      );
    }

    // Get the normal paths of both error pages.
    if (!$form_state->isValueEmpty('site_403')) {
      $form_state->setValueForElement(
        $form['error_page']['site_403'],
        $this->aliasManager->getPathByAlias($form_state->getValue('site_403')),
      );
    }

    if (!$form_state->isValueEmpty('site_404')) {
      $form_state->setValueForElement(
        $form['error_page']['site_404'],
        $this->aliasManager->getPathByAlias($form_state->getValue('site_404')),
      );
    }
  }

}
