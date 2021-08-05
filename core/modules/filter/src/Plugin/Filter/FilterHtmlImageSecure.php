<?php

namespace Drupal\filter\Plugin\Filter;

use Drupal\Component\Utility\Html;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\filter\FilterProcessResult;
use Drupal\filter\Plugin\FilterBase;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a filter to restrict images to site.
 *
 * @Filter(
 *   id = "filter_html_image_secure",
 *   title = @Translation("Restrict images to this site"),
 *   description = @Translation("Disallows usage of &lt;img&gt; tag sources that are not hosted on this site by replacing them with a placeholder image."),
 *   type = Drupal\filter\Plugin\FilterInterface::TYPE_TRANSFORM_IRREVERSIBLE,
 *   weight = 9
 * )
 */
class FilterHtmlImageSecure extends FilterBase implements ContainerFactoryPluginInterface {

  /**
   * The file URL generator.
   *
   * @var \Drupal\Core\File\FileUrlGeneratorInterface
   */
  protected $fileUrlGenerator;

  /**
   * The module handler.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * Constructs a new FilterHtmlImageSecure plugin.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin ID.
   * @param mixed $plugin_definition
   *   The plugin definition.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $file_url_generator
   *   The file URL generator.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, FileUrlGeneratorInterface $file_url_generator, ModuleHandlerInterface $module_handler) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->fileUrlGenerator = $file_url_generator;
    $this->moduleHandler = $module_handler;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('file_url_generator'),
      $container->get('module_handler')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function process($text, $langcode) {
    // Find the path (e.g. '/') to Drupal root.
    $base_path = base_path();
    $base_path_length = mb_strlen($base_path);

    // Find the directory on the server where index.php resides.
    $local_dir = \Drupal::root() . '/';

    $html_dom = Html::load($text);
    $images = $html_dom->getElementsByTagName('img');

    foreach ($images as $image) {
      $src = $image->getAttribute('src');
      // Transform absolute image URLs to relative image URLs: prevent problems on
      // multisite set-ups and prevent mixed content errors.
      $image->setAttribute('src', $this->fileUrlGenerator->transformRelative($src));

      // Verify that $src starts with $base_path.
      // This also ensures that external images cannot be referenced.
      $src = $image->getAttribute('src');
      if (mb_substr($src, 0, $base_path_length) === $base_path) {
        // Remove the $base_path to get the path relative to the Drupal root.
        // Ensure the path refers to an actual image by prefixing the image source
        // with the Drupal root and running getimagesize() on it.
        $local_image_path = $local_dir . mb_substr($src, $base_path_length);
        $local_image_path = rawurldecode($local_image_path);
        if (@getimagesize($local_image_path)) {
          // The image has the right path. Erroneous images are dealt with below.
          continue;
        }
      }
      // Allow modules and themes to replace an invalid image with an error
      // indicator. See filter_filter_secure_image_alter().
      $this->moduleHandler->alter('filter_secure_image', $image);
    }

    return new FilterProcessResult(Html::serialize($html_dom));
  }

  /**
   * {@inheritdoc}
   */
  public function tips($long = FALSE) {
    return $this->t('Only images hosted on this site may be used in &lt;img&gt; tags.');
  }

}
