<?php

declare(strict_types=1);

namespace Drupal\Core\Theme\Plugin\IconExtractor;

use Drupal\Core\Template\Attribute;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Theme\Icon\Attribute\IconExtractor;
use Drupal\Core\Theme\Icon\IconExtractorWithFinder;
use Drupal\Core\Theme\Icon\IconPackExtractorForm;

/**
 * Plugin implementation of the icon_extractor.
 *
 * This extractor needs the file content only to extract ids, no SVG is parse or
 * printed.
 *
 * @internal
 *   This API is experimental.
 */
#[IconExtractor(
  id: 'svg_sprite',
  label: new TranslatableMarkup('SVG Sprite'),
  description: new TranslatableMarkup('Open an SVG XML file and get the icons.'),
  forms: [
    'settings' => IconPackExtractorForm::class,
  ]
)]
class SvgSpriteExtractor extends IconExtractorWithFinder {

  /**
   * {@inheritdoc}
   */
  public function discoverIcons(): array {
    $files = $this->getFilesFromSources();

    if (empty($files)) {
      return [];
    }

    $icons = [];
    foreach ($files as $file) {
      $extractedData = $this->extractIdsFromXml($file['absolute_path'] ?? '');
      if (!isset($extractedData['icon_ids'])) {
        continue;
      }
      foreach ($extractedData['icon_ids'] as $icon_id) {
        $icons[] = $this->createIcon((string) $icon_id, $file['source'], $file['group'] ?? NULL, ['attributes' => $extractedData['attributes']]);
      }
    }

    return $icons;
  }

  /**
   * Extract icon ID from XML.
   *
   * @param string $source
   *   Local path or url to the svg file.
   *
   * @return array
   *   A list of icons with keys:
   *   - icon_ids: array of icon Id found
   *   - attributes: Attribute object from the svg
   */
  private function extractIdsFromXml(string $source): array {
    if (!$content = $this->iconFinder->getFileContents($source)) {
      return [];
    }

    libxml_use_internal_errors(TRUE);

    if (!$svg = simplexml_load_string((string) $content)) {
      // @todo do we need to log a warning with the xml error?
      return [];
    }

    $return = [
      'icon_ids' => [],
      'attributes' => new Attribute(),
    ];

    // Add svg attributes to be available in the template.
    foreach ($svg->attributes() as $name => $value) {
      $return['attributes']->setAttribute($name, (string) $value);
    }

    $return['icon_ids'] = $this->extractIdsFromSymbols($svg->symbol) ?: $this->extractIdsFromSymbols($svg->defs->symbol ?? NULL);

    return $return;
  }

  /**
   * Extract icon ID from SVG symbols.
   *
   * @param \SimpleXMLElement|null $wrapper
   *   A SVG element.
   *
   * @return array
   *   A list of icons ID.
   */
  private function extractIdsFromSymbols(?\SimpleXMLElement $wrapper): array {
    if ($wrapper === NULL) {
      return [];
    }

    $ids = [];
    foreach ($wrapper as $symbol) {
      if (isset($symbol['id'])) {
        $ids[] = (string) $symbol['id'];
      }
    }

    return $ids;
  }

}
