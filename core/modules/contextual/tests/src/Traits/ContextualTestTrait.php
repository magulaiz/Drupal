<?php

namespace Drupal\Tests\contextual\Traits;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;

/**
 * Provides helper methods for testing contextual links.
 *
 * This trait is meant to be used only by test classes.
 */
trait ContextualTestTrait {

  /**
   * Asserts that a contextual link placeholder with the given id exists.
   *
   * @param string $id
   *   A contextual link id.
   *
   * @internal
   */
  protected function assertContextualLinkPlaceHolder(string $id): void {
    $this->assertSession()->elementAttributeContains(
      'css',
      'div[data-contextual-id="' . $id . '"]',
      'data-contextual-token',
      $this->createContextualIdToken($id)
    );
  }

  /**
   * Asserts that a contextual link placeholder with the given id does not exist.
   *
   * @param string $id
   *   A contextual link id.
   *
   * @internal
   */
  protected function assertNoContextualLinkPlaceHolder(string $id): void {
    $this->assertSession()->elementNotExists('css', 'div[data-contextual-id="' . $id . '"]');
  }

  /**
   * Get server-rendered contextual links for the given contextual link ids.
   *
   * @param array $ids
   *   An array of contextual link ids.
   * @param string $current_path
   *   The Drupal path for the page for which the contextual links are rendered.
   *
   * @return \Psr\Http\Message\ResponseInterface
   *   The response object.
   */
  protected function renderContextualLinks($ids, $current_path) {
    $tokens = array_map([$this, 'createContextualIdToken'], $ids);
    $http_client = $this->getHttpClient();
    $url = Url::fromRoute('contextual.render', [], [
      'query' => [
        '_format' => 'json',
        'destination' => $current_path,
      ],
    ]);

    return $http_client->request('POST', $this->buildUrl($url), [
      'cookies' => $this->getSessionCookies(),
      'form_params' => ['ids' => $ids, 'tokens' => $tokens],
      'http_errors' => FALSE,
    ]);
  }

  /**
   * Creates a contextual ID token.
   *
   * @param string $id
   *   The contextual ID to create a token for.
   *
   * @return string
   *   The contextual ID token.
   */
  protected function createContextualIdToken($id) {
    return Crypt::hmacBase64($id, Settings::getHashSalt() . $this->container->get('private_key')->get());
  }

}
