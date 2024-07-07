<?php

namespace Drupal\Core\Ajax;

use Drupal\Component\Serialization\Json;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Render\AttachmentsInterface;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\HtmlResponseAttachmentsProcessor;

/**
 * Prepares attachment for HTMX powered responses.
 *
 * Extends the HTML response processor encode attachment data.
 *
 * @see \Drupal\Core\EventSubscriber\HtmxResponseSubscriber
 * @see \Drupal\Core\Render\HtmlResponseAttachmentsProcessor::processAttachments
 * @see core/misc/htmx.js
 */
class HtmxResponseAttachmentsProcessor extends HtmlResponseAttachmentsProcessor {

  /**
   * Json encoded string of event name and assets to be attached.
   */
  protected string $preparedAssetInfo;

  /**
   * {@inheritdoc}
   */
  public function processAttachments(AttachmentsInterface $response): HtmlResponse {
    $processed = parent::processAttachments($response);
    if (!($processed instanceof HtmlResponse)) {
      // Something has gone wrong. We sent an HtmlResponse that also
      // implemented AttachmentInterface and should have received the same back.
      throw new \TypeError("HtmlResponseAttachmentsProcessor::processAttachments should return an HtmlResponse.");
    }
    $processed->headers->set('HX-Trigger-After-Swap', $this->preparedAssetInfo);
    return $processed;
  }

  /**
   * {@inheritdoc}
   */
  protected function processAssetLibraries(AttachedAssetsInterface $assets, array $placeholders) {
    $settings = [];
    $variables = [];
    $maintenance_mode = defined('MAINTENANCE_MODE') || \Drupal::state()->get('system.maintenance_mode');

    // Print styles - if present.
    if (isset($placeholders['styles'])) {
      // Optimize CSS if necessary, but only during normal site operation.
      $optimize_css = !$maintenance_mode && $this->config->get('css.preprocess');
      $variables['styles'] = $this->cssCollectionRenderer->render($this->assetResolver->getCssAssets($assets, $optimize_css, $this->languageManager->getCurrentLanguage()));
    }

    // Copy and adjust parent::processAssetLibraries to adjust and
    // remove drupalSettings in line with
    // AjaxResponseAttachmentsProcessor::buildAttachmentsCommands
    // Print scripts - if any are present.
    if (isset($placeholders['scripts']) || isset($placeholders['scripts_bottom'])) {
      // Optimize JS if necessary, but only during normal site operation.
      $optimize_js = !$maintenance_mode && $this->config->get('js.preprocess');
      [$js_assets_header, $js_assets_footer] = $this->assetResolver
        ->getJsAssets($assets, $optimize_js, $this->languageManager->getCurrentLanguage());
      $settingsHeader = $js_assets_header['drupalSettings'] ?? NULL;
      $settingsFooter = $js_assets_footer['drupalSettings'] ?? NULL;
      if (is_array($settingsHeader)) {
        $settings = $js_assets_header['drupalSettings']['data'];
        unset($js_assets_header['drupalSettings']);
      }
      if (is_array($settingsFooter)) {
        $settings = $js_assets_footer['drupalSettings']['data'];
        unset($js_assets_footer['drupalSettings']);
      }
      if (empty($settings)) {
        $settings = $assets->getSettings();
      }
      $variables['scripts'] = $this->jsCollectionRenderer->render($js_assets_header);
      $variables['scripts_bottom'] = $this->jsCollectionRenderer->render($js_assets_footer);
      unset($settings['path']);
      $variables['settings'] = $settings;
    }
    // Store the prepared asset data so that it can be added to the response.
    $data = [
      'htmxDrupalAssetsAttached' => [
        'assets' => $variables,
      ],
    ];
    $this->preparedAssetInfo = Json::encode($data);
    // Restore assets for a standard page so that HTML state is preserved.
    if (is_array($settingsHeader)) {
      $js_assets_header['drupalSettings'] = $settingsHeader;
      $variables['scripts'] = $this->jsCollectionRenderer->render($js_assets_header);
    }
    if (is_array($settingsFooter)) {
      $js_assets_footer['drupalSettings'] = $settingsFooter;
      $variables['scripts_bottom'] = $this->jsCollectionRenderer->render($js_assets_footer);
    }
    return $variables;
  }

}
