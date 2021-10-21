<?php

namespace Drupal\media_library\Form;

use Drupal\Core\Form\FormBuilderInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;
use Drupal\media\OEmbed\ResourceException;
use Drupal\media\OEmbed\ResourceFetcherInterface;
use Drupal\media\OEmbed\UrlResolverInterface;
use Drupal\media\Plugin\media\Source\OEmbedInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Creates a form to create media entities from oEmbed URLs.
 *
 * @internal
 *   Form classes are internal.
 */
class OEmbedForm extends AddFormBase {

  /**
   * The oEmbed URL resolver service.
   *
   * @var \Drupal\media\OEmbed\UrlResolverInterface
   */
  protected $urlResolver;

  /**
   * The oEmbed resource fetcher service.
   *
   * @var \Drupal\media\OEmbed\ResourceFetcherInterface
   */
  protected $resourceFetcher;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    $form = parent::create($container);
    $form->setUrlResolver($container->get('media.oembed.url_resolver'));
    $form->setResourceFetcher($container->get('media.oembed.resource_fetcher'));
    return $form;
  }

  /**
   * Set URL resolver service.
   *
   * @param \Drupal\media\OEmbed\UrlResolverInterface $urlResolver
   *   The URL resolver service.
   */
  protected function setUrlResolver(UrlResolverInterface $urlResolver) {
    $this->urlResolver = $urlResolver;
  }

  /**
   * Set resource fetcher service.
   *
   * @param \Drupal\media\OEmbed\ResourceFetcherInterface $resourceFetcher
   *   The resource fetcher service.
   */
  protected function setResourceFetcher(ResourceFetcherInterface $resourceFetcher) {
    $this->resourceFetcher = $resourceFetcher;
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return $this->getBaseFormId() . '_oembed';
  }

  /**
   * {@inheritdoc}
   */
  protected function getMediaType(FormStateInterface $form_state) {
    if ($this->mediaType) {
      return $this->mediaType;
    }

    $media_type = parent::getMediaType($form_state);
    if (!$media_type->getSource() instanceof OEmbedInterface) {
      throw new \InvalidArgumentException('Can only add media types which use an oEmbed source plugin.');
    }
    return $media_type;
  }

  /**
   * {@inheritdoc}
   */
  protected function buildInputElement(array $form, FormStateInterface $form_state) {
    $media_type = $this->getMediaType($form_state);
    $providers = $media_type->getSource()->getProviders();

    // Add a container to group the input elements for styling purposes.
    $form['container'] = [
      '#type' => 'container',
    ];

    $form['container']['url'] = [
      '#type' => 'url',
      '#title' => $this->t('Add @type via URL', [
        '@type' => $this->getMediaType($form_state)->label(),
      ]),
      '#description' => $this->t('Allowed providers: @providers.', [
        '@providers' => implode(', ', $providers),
      ]),
      '#required' => TRUE,
      '#attributes' => [
        'placeholder' => 'https://',
      ],
    ];

    $form['container']['submit'] = [
      '#type' => 'submit',
      '#value' => $this->t('Add'),
      '#button_type' => 'primary',
      '#validate' => ['::validateUrl'],
      '#submit' => ['::addButtonSubmit'],
      // @todo Move validation in https://www.drupal.org/node/2988215
      '#ajax' => [
        'callback' => '::updateFormCallback',
        'wrapper' => 'media-library-wrapper',
        // Add a fixed URL to post the form since AJAX forms are automatically
        // posted to <current> instead of $form['#action'].
        // @todo Remove when https://www.drupal.org/project/drupal/issues/2504115
        //   is fixed.
        'url' => Url::fromRoute('media_library.ui'),
        'options' => [
          'query' => $this->getMediaLibraryState($form_state)->all() + [
            FormBuilderInterface::AJAX_FORM_REQUEST => TRUE,
          ],
        ],
      ],
    ];
    return $form;
  }

  /**
   * Validates the oEmbed URL.
   *
   * @param array $form
   *   The complete form.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The current form state.
   */
  public function validateUrl(array &$form, FormStateInterface $form_state) {
    $url = $form_state->getValue('url');
    if ($url) {
      try {
        $resource_url = $this->urlResolver->getResourceUrl($url);
        $this->resourceFetcher->fetchResource($resource_url);
      }
      catch (ResourceException $e) {
        $form_state->setErrorByName('url', $e->getMessage());
      }
    }
  }

  /**
   * Submit handler for the add button.
   *
   * @param array $form
   *   The form render array.
   * @param \Drupal\Core\Form\FormStateInterface $form_state
   *   The form state.
   */
  public function addButtonSubmit(array $form, FormStateInterface $form_state) {
    $this->processInputValues([$form_state->getValue('url')], $form, $form_state);
  }

}
