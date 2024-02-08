<?php

namespace Drupal\language\Plugin\LanguageNegotiation;

use Drupal\Component\Utility\UserAgent;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\language\LanguageNegotiationMethodBase;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class for identifying language from the browser Accept-language HTTP header.
 *
 * @LanguageNegotiation(
 *   id = \Drupal\language\Plugin\LanguageNegotiation\LanguageNegotiationBrowser::METHOD_ID,
 *   weight = -2,
 *   name = @Translation("Browser"),
 *   description = @Translation("Language from the browser's language settings."),
 *   config_route_name = "language.negotiation_browser"
 * )
 */
class LanguageNegotiationBrowser extends LanguageNegotiationMethodBase implements ContainerFactoryPluginInterface {

  /**
   * The language negotiation method id.
   */
  const METHOD_ID = 'language-browser';

  /**
   * The page cache disabling policy.
   *
   * @var \Drupal\Core\PageCache\ResponsePolicy\KillSwitch
   */
  protected $pageCacheKillSwitch;

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    $instance = new static();
    $instance->pageCacheKillSwitch = $container->get('page_cache_kill_switch');
    return $instance;
  }

  /**
   * {@inheritdoc}
   */
  public function getLangcode(Request $request = NULL) {
    $langcode = NULL;

    \Drupal::service('page_cache_vary')->add('Accept-Language');
    if ($this->languageManager && $request && $request->server->get('HTTP_ACCEPT_LANGUAGE')) {
      $http_accept_language = $request->server->get('HTTP_ACCEPT_LANGUAGE');
      $langcodes = array_keys($this->languageManager->getLanguages());
      $mappings = $this->config->get('language.mappings')->get('map');
      $langcode = UserAgent::getBestMatchingLangcode($http_accept_language, $langcodes, $mappings);
    }

    return $langcode;
  }

}
