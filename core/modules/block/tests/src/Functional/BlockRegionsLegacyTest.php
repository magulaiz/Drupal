<?php

namespace Drupal\Tests\block\Functional;

use Drupal\block\BlockListBuilder;
use Drupal\block\Form\BlockDeleteForm;
use Drupal\Tests\BrowserTestBase;

/**
 * Block legacy tests.
 *
 * @group block
 * @group legacy
 */
class BlockRegionsLegacyTest extends BrowserTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['block', 'system'];

  /**
   * List builder.
   *
   * @var \Drupal\block\BlockListBuilder|\Drupal\Core\Entity\EntityListBuilder|\Drupal\Tests\block\Functional\BlockListBuilderWrapper
   */
  protected $listBuilder;

  /**
   * Theme handler.
   *
   * @var mixed
   */
  protected $themeHandler;

  /**
   * Block delete form.
   *
   * @var \Drupal\block\Form\BlockDeleteForm|\Drupal\Tests\block\Functional\BlockDeleteFormWrapper
   */
  protected $deleteForm;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->listBuilder = BlockListBuilderWrapper::createInstance(
      \Drupal::getContainer(),
      \Drupal::entityTypeManager()->getDefinition('block')
    );
    $this->themeHandler = \Drupal::service('theme_handler');
    $this->deleteForm = BlockDeleteFormWrapper::create(\Drupal::getContainer());
  }

  /**
   * Tests systemRegionList method deprecation.
   */
  public function testSystemRegionListListBuilder() {
    $this->expectDeprecation('Drupal\block\BlockListBuilder::systemRegionList() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use $this->themeHandler->getTheme()->listAllRegions() or $this->themeHandler->getTheme()->listVisibleRegions() instead. See https://www.drupal.org/node/3015925');
    $currentResult = $this->listBuilder->systemRegionList($this->defaultTheme);
    $expected = $this->themeHandler->getTheme($this->defaultTheme)->listAllRegions();
    $this->assertEquals($expected, $currentResult);
    $currentResult = $this->listBuilder->systemRegionList($this->defaultTheme, REGIONS_VISIBLE);
    $expected = $this->themeHandler->getTheme($this->defaultTheme)->listVisibleRegions();
    $this->assertEquals($expected, $currentResult);
  }

  /**
   * Tests systemRegionList method deprecation.
   */
  public function testSystemRegionListDeleteForm() {
    $this->expectDeprecation('Drupal\block\Form\BlockDeleteForm::systemRegionList() is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use $this->themeHandler->getTheme()->listAllRegions() or $this->themeHandler->getTheme()->listVisibleRegions() instead. See https://www.drupal.org/node/3015925');
    $currentResult = $this->deleteForm->systemRegionList($this->defaultTheme);
    $expected = $this->themeHandler->getTheme($this->defaultTheme)->listAllRegions();
    $this->assertEquals($expected, $currentResult);
    $currentResult = $this->deleteForm->systemRegionList($this->defaultTheme, REGIONS_VISIBLE);
    $expected = $this->themeHandler->getTheme($this->defaultTheme)->listVisibleRegions();
    $this->assertEquals($expected, $currentResult);
  }

}

/**
 * Block list builder.
 */
class BlockListBuilderWrapper extends BlockListBuilder {

  /**
   * {@inheritdoc}
   */
  public function systemRegionList($theme, $show = REGIONS_ALL) {
    return parent::systemRegionList($theme, $show);
  }

}

/**
 * Block delete form.
 */
class BlockDeleteFormWrapper extends BlockDeleteForm {

  /**
   * {@inheritdoc}
   */
  public function systemRegionList($theme, $show = REGIONS_ALL) {
    return parent::systemRegionList($theme, $show);
  }

}
