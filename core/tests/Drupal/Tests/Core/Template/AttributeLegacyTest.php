<?php

namespace Drupal\Tests\Core\Template;

use Drupal\Component\HtmlAttribute\HtmlAttributeArray;
use Drupal\Component\HtmlAttribute\HtmlAttributeBoolean;
use Drupal\Component\HtmlAttribute\HtmlAttributeString;
use Drupal\Component\HtmlAttribute\HtmlAttributeValueBase;
use Drupal\Core\Template\AttributeArray as CoreAttributeArray;
use Drupal\Core\Template\AttributeBoolean as CoreAttributeBoolean;
use Drupal\Core\Template\AttributeString as CoreAttributeString;
use Drupal\Core\Template\AttributeValueBase as CoreAttributeValueBase;
use Drupal\Tests\UnitTestCase;

/**
 * Deprecation tests for the core Attribute* classes.
 *
 * @group Template
 * @group legacy
 */
class AttributeLegacyTest extends UnitTestCase {

  /**
   * Tests deprecation of Attribute* classes.
   */
  public function testCoreAttributeDeprecations(): void {
    $this->expectDeprecation('\Drupal\Core\Template\AttributeArray is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Component\HtmlAttribute\HtmlAttributeArray instead. See https://www.drupal.org/node/3070485');
    $this->expectDeprecation('\Drupal\Core\Template\AttributeBoolean is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Component\HtmlAttribute\HtmlAttributeBoolean instead. See https://www.drupal.org/node/3070485');
    $this->expectDeprecation('\Drupal\Core\Template\AttributeString is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Component\HtmlAttribute\HtmlAttributeString instead. See https://www.drupal.org/node/3070485');
    $this->assertInstanceOf(HtmlAttributeArray::class, new CoreAttributeArray('a', ['test']));
    $this->assertInstanceOf(HtmlAttributeBoolean::class, new CoreAttributeBoolean('b', FALSE));
    $this->assertInstanceOf(HtmlAttributeString::class, new CoreAttributeString('c', 'test'));

  }

  /**
   * Tests deprecation of AttributeValueBase.
   */
  public function testCoreAttributeValueBaseDeprecation(): void {
    $this->expectDeprecation('\Drupal\Core\Template\AttributeValueBase is deprecated in drupal:10.1.0 and is removed from drupal:11.0.0. Use \Drupal\Component\HtmlAttribute\HtmlAttributeValueBase instead. See https://www.drupal.org/node/3070485');
    $this->assertInstanceOf(HtmlAttributeValueBase::class, new class('a', ['test']) extends CoreAttributeValueBase {

      /**
       * Implements the magic __toString() method.
       */
      public function __toString(): string {
        return '';
      }

    });
  }

}
