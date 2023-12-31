<?php

namespace Drupal\big_pipe_regression_test;

use Drupal\big_pipe\Render\BigPipeMarkup;
use Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException;
use Drupal\Component\Plugin\Exception\PluginNotFoundException;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Security\TrustedCallbackInterface;

class BigPipeRegressionTestController extends ControllerBase implements TrustedCallbackInterface {

  const MARKER_2678662 = '<script>var hitsTheFloor = "</body>";</script>';

  /**
   * @see \Drupal\Tests\big_pipe\FunctionalJavascript\BigPipeRegressionTest::testMultipleBodies_2678662()
   */
  public function regression2678662() {
    return [
      '#markup' => BigPipeMarkup::create(self::MARKER_2678662),
    ];
  }

  /**
   * @see \Drupal\Tests\big_pipe\FunctionalJavascript\BigPipeRegressionTest::testMultipleBodies_2678662()
   */
  public function regression2802923() {
    return [
      '#prefix' => BigPipeMarkup::create('<p>Hi, my train will arrive at '),
      'time' => [
        '#lazy_builder' => [static::class . '::currentTime', []],
        '#create_placeholder' => TRUE,
      ],
      '#suffix' => BigPipeMarkup::create(' — will I still be able to catch the connection to the center?</p>'),
    ];
  }

  /**
   * A page with large content.
   *
   * @see \Drupal\Tests\big_pipe\FunctionalJavascript\BigPipeRegressionTest::testBigPipeLargeContent
   */
  public function largeContent() {
    return [
      'item1' => [
        '#lazy_builder' => [static::class . '::largeContentBuilder', []],
        '#create_placeholder' => TRUE,
      ],
    ];
  }

  /**
   * A page with multiple nodes.
   *
   * @see \Drupal\Tests\big_pipe\FunctionalJavascript\BigPipeRegressionTest::testMultipleReplacements
   */
  public function multipleReplacements() {
    $build = [];
    try {
      $ids = $this->entityTypeManager()
        ->getStorage('node')
        ->getQuery()
        ->accessCheck(TRUE)
        ->execute();

      foreach ($ids as $id) {
        $build[] = [
          '#lazy_builder' => [static::class . '::renderNode', [$id]],
          '#create_placeholder' => TRUE,
        ];
      }
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      return [
        '#markup' => '<p>Node storage not available</p>',
      ];
    }

    return $build;
  }

  /**
   * Renders large content.
   *
   * @see \Drupal\Tests\big_pipe\FunctionalJavascript\BigPipeRegressionTest::testBigPipeLargeContent
   */
  public static function largeContentBuilder() {
    return [
      '#theme' => 'big_pipe_test_large_content',
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * #lazy_builder callback; builds <time> markup with current time.
   *
   * @return array
   */
  public static function currentTime() {
    return [
      '#markup' => '<time datetime="' . date('Y-m-d', time()) . '"></time>',
      '#cache' => ['max-age' => 0],
    ];
  }

  /**
   * Renders a node given a node id from #lazy_builder callback.
   *
   * @param int $id
   *   The node ID.
   *
   * @return array
   */
  public static function renderNode(int $id) {
    try {
      $node = \Drupal::entityTypeManager()->getStorage('node')->load($id);
      $build = \Drupal::entityTypeManager()->getViewBuilder('node')->view($node);
    }
    catch (InvalidPluginDefinitionException | PluginNotFoundException $e) {
      return [
        '#markup' => '<p>Node entity not available</p>',
      ];
    }

    return ['#cache' => ['max-age' => 0]] + $build;
  }

  /**
   * {@inheritdoc}
   */
  public static function trustedCallbacks() {
    return ['currentTime', 'largeContentBuilder', 'renderNode'];
  }

}
