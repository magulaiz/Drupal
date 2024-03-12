<?php

namespace Drupal\contextual;

use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\Site\Settings;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Returns responses for Contextual module routes.
 */
class ContextualController implements ContainerInjectionInterface {

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructors a new ContextualController.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer.
   */
  public function __construct(RendererInterface $renderer) {
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('renderer')
    );
  }

  /**
   * Returns the requested rendered contextual links.
   *
   * Given a list of contextual links IDs, render them. Hence this must be
   * robust to handle arbitrary input.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The Symfony request object.
   *
   * @return \Symfony\Component\HttpFoundation\JsonResponse
   *   The JSON response.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the request contains no ids.
   *
   * @internal
   *
   * @see contextual_preprocess()
   */
  public function render(Request $request) {
    if (!$request->request->has('ids')) {
      throw new BadRequestHttpException('No contextual ids specified.');
    }
    $ids = $request->request->all('ids');

    if (!$request->request->has('tokens')) {
      throw new BadRequestHttpException('No contextual ID tokens specified.');
    }
    $tokens = $request->request->all('tokens');

    $rendered = [];
    foreach ($ids as $key => $id) {
      if (!isset($tokens[$key]) || !hash_equals($tokens[$key], Crypt::hmacBase64($id, Settings::getHashSalt() . \Drupal::service('private_key')->get()))) {
        throw new BadRequestHttpException('Invalid contextual ID specified.');
      }
      $element = [
        '#type' => 'contextual_links',
        '#contextual_links' => static::_contextual_id_to_links($id),
      ];
      $rendered[$id] = $this->renderer->renderRoot($element);
    }

    return new JsonResponse($rendered);
  }

  /**
   * Unserializes the result of \Drupal\contextual\ContextualController::_contextual_links_to_id().
   *
   * Note that $id is user input. Before calling this method the ID should be
   * checked against the token stored in the 'data-contextual-token' attribute
   * which is passed via the 'tokens' request parameter to
   * \Drupal\contextual\ContextualController::render().
   *
   * @param string $id
   *   A serialized representation of a #contextual_links property value array.
   *
   * @return array
   *   The value for a #contextual_links property.
   *
   * @see \Drupal\contextual\ContextualController::_contextual_links_to_id()
   * @see \Drupal\contextual\ContextualController::render()
   */
  public static function _contextual_id_to_links($id) {
    $contextual_links = [];
    $contexts = explode('|', $id);
    foreach ($contexts as $context) {
      [$group, $route_parameters_raw, $metadata_raw] = explode(':', $context);
      parse_str($route_parameters_raw, $route_parameters);
      $metadata = [];
      parse_str($metadata_raw, $metadata);
      $contextual_links[$group] = [
        'route_parameters' => $route_parameters,
        'metadata' => $metadata,
      ];
    }
    return $contextual_links;
  }

  /**
   * Serializes #contextual_links property value array to a string.
   *
   * Examples:
   *  - node:node=1:langcode=en
   *  - views_ui_edit:view=frontpage:location=page&view_name=frontpage&view_display_id=page_1&langcode=en
   *  - menu:menu=tools:langcode=en|block:block=olivero.tools:langcode=en
   *
   * So, expressed in a pattern:
   *  <group>:<route parameters>:<metadata>
   *
   * The route parameters and options are encoded as query strings.
   *
   * @param array $contextual_links
   *   The $element['#contextual_links'] value for some render element.
   *
   * @return string
   *   A serialized representation of a #contextual_links property value array for
   *   use in a data- attribute.
   */
  public static function _contextual_links_to_id($contextual_links) {
    $ids = [];
    $langcode = \Drupal::languageManager()->getCurrentLanguage(LanguageInterface::TYPE_URL)->getId();
    foreach ($contextual_links as $group => $args) {
      $route_parameters = UrlHelper::buildQuery($args['route_parameters']);
      $args += ['metadata' => []];
      // Add the current URL language to metadata so a different ID will be
      // computed when URLs vary by language. This allows to store different
      // language-aware contextual links on the client side.
      $args['metadata'] += ['langcode' => $langcode];
      $metadata = UrlHelper::buildQuery($args['metadata']);
      $ids[] = "{$group}:{$route_parameters}:{$metadata}";
    }
    return implode('|', $ids);
  }

}
