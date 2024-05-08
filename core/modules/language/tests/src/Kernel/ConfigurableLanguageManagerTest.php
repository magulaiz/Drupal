<?php

declare(strict_types=1);

namespace Drupal\Tests\language\Kernel;

use PHPUnit\Framework\Attributes\CoversClass;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Url;

/**
 * Tests the ConfigurableLanguage entity.
 *
 * @group language
 */
#[CoversClass(\Drupal\language\ConfigurableLanguageManager::class)]
class ConfigurableLanguageManagerTest extends LanguageTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['user'];

  /**
   * The language negotiator.
   *
   * @var \Drupal\language\LanguageNegotiatorInterface
   */
  protected $languageNegotiator;

  /**
   * The language manager.
   *
   * @var \Drupal\language\ConfigurableLanguageManagerInterface
   */
  protected $languageManager;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installEntitySchema('user');

    $this->languageNegotiator = $this->container->get('language_negotiator');
    $this->languageManager = $this->container->get('language_manager');
  }

  public function testLanguageSwitchLinks() {
    $this->languageNegotiator->setCurrentUser($this->prophesize('Drupal\Core\Session\AccountInterface')->reveal());
    $this->languageManager->getLanguageSwitchLinks(LanguageInterface::TYPE_INTERFACE, new Url('<current>'));
  }

}
