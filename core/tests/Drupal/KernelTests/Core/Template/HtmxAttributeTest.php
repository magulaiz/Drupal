<?php

declare(strict_types=1);

namespace Drupal\KernelTests\Core\Template;

use Drupal\Core\Url;
use Drupal\Core\Template\HtmxAttribute;
use Drupal\KernelTests\KernelTestBase;

/**
 * Test description.
 *
 * @group htmx
 *
 * @coversDefaultClass \Drupal\Core\Template\HtmxAttribute
 */
final class HtmxAttributeTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['common_test'];

  /**
   * Class under test.
   *
   * @var \Drupal\Core\Template\HtmxAttribute
   */
  protected HtmxAttribute $htmxAttribute;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->htmxAttribute = new HtmxAttribute();
  }

  /**
   * Test get method.
   *
   * @covers ::get
   */
  public function testHxGet(): void {
    $url = Url::fromRoute('common_test.destination');
    $this->htmxAttribute->get($url);
    $rendered = (string) $this->htmxAttribute;
    // The paths in GitLabCI include a subfolder.
    $this->assertStringStartsWith(' data-hx-get="', $rendered);
    $this->assertStringEndsWith('/common-test/destination"', $rendered);
  }

  /**
   * Test get method.
   *
   * @covers ::post
   */
  public function testHxPost(): void {
    $url = Url::fromRoute('common_test.destination');
    $this->htmxAttribute->post($url);
    $rendered = (string) $this->htmxAttribute;
    // The paths in GitLabCI include a subfolder.
    $this->assertStringStartsWith(' data-hx-post="', $rendered);
    $this->assertStringEndsWith('/common-test/destination"', $rendered);
  }

  /**
   * Test get method.
   *
   * @covers ::put
   */
  public function testHxPut(): void {
    $url = Url::fromRoute('common_test.destination');
    $this->htmxAttribute->put($url);
    $rendered = (string) $this->htmxAttribute;
    // The paths in GitLabCI include a subfolder.
    $this->assertStringStartsWith(' data-hx-put="', $rendered);
    $this->assertStringEndsWith('/common-test/destination"', $rendered);
  }

  /**
   * Test get method.
   *
   * @covers ::patch
   */
  public function testHxPatch(): void {
    $url = Url::fromRoute('common_test.destination');
    $this->htmxAttribute->patch($url);
    $rendered = (string) $this->htmxAttribute;
    // The paths in GitLabCI include a subfolder.
    $this->assertStringStartsWith(' data-hx-patch="', $rendered);
    $this->assertStringEndsWith('/common-test/destination"', $rendered);
  }

  /**
   * Test get method.
   *
   * @covers ::delete
   */
  public function testHxDelete(): void {
    $url = Url::fromRoute('common_test.destination');
    $this->htmxAttribute->delete($url);
    $rendered = (string) $this->htmxAttribute;
    // The paths in GitLabCI include a subfolder.
    $this->assertStringStartsWith(' data-hx-delete="', $rendered);
    $this->assertStringEndsWith('/common-test/destination"', $rendered);
  }

  /**
   * Test on method.
   *
   * @covers ::on
   * @dataProvider hxOnDataProvider
   */
  public function testHxOn(string $event, string $expected): void {
    $this->htmxAttribute->on($event, 'someAction');
    $rendered = (string) $this->htmxAttribute;
    $expected .= 'someAction"';
    // The paths in GitLabCI include a subfolder.
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Provides data to ::testHxOn.
   *
   * @return array<int, string[]>
   *   Array of event, expected.
   */
  public static function hxOnDataProvider(): array {
    return [
      ['alllowercase', ' data-hx-on-alllowercase="'],
      ['already-kebab-case', ' data-hx-on-already-kebab-case="'],
      ['snake_case', ' data-hx-on-snake-case="'],
      ['camelCaseEvent', ' data-hx-on-camel-case-event="'],
      ['htmx:beforeRequest', ' data-hx-on-htmx-before-request="'],
      ['::beforeRequest', ' data-hx-on--before-request="'],
    ];
  }

  /**
   * Test pushUrl method.
   *
   * @covers ::pushUrl
   * @dataProvider hxPushUrlDataProvider
   */
  public function testHxPushUrl(bool|Url $value, string $endsWith): void {
    $startsWith = ' data-hx-push-url="';
    $this->htmxAttribute->pushUrl($value);
    $rendered = (string) $this->htmxAttribute;
    // The paths in GitLabCI include a subfolder.
    // So treat all the results as paths.
    $this->assertStringStartsWith($startsWith, $rendered);
    $this->assertStringEndsWith($endsWith, $rendered);
  }

  /**
   * Test pushUrl method.
   *
   * @covers ::pushUrl
   * @dataProvider hxPushUrlDataProvider
   */
  public function testHxReplaceUrl(bool|Url $value, string $endsWith): void {
    $startsWith = ' data-hx-replace-url="';
    $this->htmxAttribute->replaceUrl($value);
    $rendered = (string) $this->htmxAttribute;
    // The paths in GitLabCI include a subfolder.
    // So treat all the results as paths.
    $this->assertStringStartsWith($startsWith, $rendered);
    $this->assertStringEndsWith($endsWith, $rendered);
  }

  /**
   * Provides data to ::testHxPushUrl and :testHxReplaceUrl.
   *
   * @return array{bool|Url, string}[]
   *   Array of bool|Url, expected.
   */
  public static function hxPushUrlDataProvider(): array {
    $url = Url::fromRoute('common_test.destination');
    return [
      [TRUE, 'true"'],
      [FALSE, 'false"'],
      [$url, '/common-test/destination"'],
    ];
  }

  /**
   * Test swapOob method.
   *
   * @covers ::swapOob
   * @dataProvider hxSwapOobDataProvider
   */
  public function testHxSwapOob(true|string $value, string $expected): void {
    $this->htmxAttribute->swapOob($value);
    $rendered = (string) $this->htmxAttribute;
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Provides data to ::testHxSwapOob.
   *
   * @return array{true|string, string}[]
   *   Array of true|string, expected.
   */
  public static function hxSwapOobDataProvider(): array {
    return [
      [TRUE, ' data-hx-swap-oob="true"'],
      ['body:beforeend', ' data-hx-swap-oob="body:beforeend"'],
    ];
  }

  /**
   * Test vals method.
   *
   * @covers ::vals
   */
  public function testHxVals(): void {
    $values = ['myValue' => 'My Value'];
    $this->htmxAttribute->vals($values);
    $rendered = (string) $this->htmxAttribute;
    $expected = " data-hx-vals='" . '{"myValue":"My Value"}' . "'";
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Test swapOob method.
   *
   * @covers ::swapOob
   * @dataProvider hxBoostDataProvider
   */
  public function testHxBoost(bool $value, string $expected): void {
    $this->htmxAttribute->boost($value);
    $rendered = (string) $this->htmxAttribute;
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Provides data to ::testHxBoost.
   *
   * @return array{bool, string}[]
   *   Array of bool|string, expected.
   */
  public static function hxBoostDataProvider(): array {
    return [
      [TRUE, ' data-hx-boost="true"'],
      [FALSE, ' data-hx-boost="false"'],
    ];
  }

  /**
   * Test headers method.
   *
   * @covers ::headers
   */
  public function testHxHeaders(): void {
    $values = ['myValue' => 'My Value'];
    $this->htmxAttribute->headers($values);
    $rendered = (string) $this->htmxAttribute;
    $expected = " data-hx-headers='" . '{"myValue":"My Value"}' . "'";
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Test history method.
   *
   * @covers ::history
   * @dataProvider hxHistoryDataProvider
   */
  public function testHxHistory(?bool $value, string $expected): void {
    if (is_null($value)) {
      $this->htmxAttribute->history();
    }
    else {
      $this->htmxAttribute->history($value);
    }
    $rendered = (string) $this->htmxAttribute;
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Provides data to ::testHxHistory.
   *
   * @return array{?bool, string}[]
   *   Array of null|bool, string, expected.
   */
  public static function hxHistoryDataProvider(): array {
    return [
      [TRUE, ' data-hx-history="true"'],
      [FALSE, ' data-hx-history="false"'],
      [NULL, ' data-hx-history="false"'],
    ];
  }

  /**
   * Test request method.
   *
   * @covers ::request
   */
  public function testHxRequest(): void {
    $values = ['timeout' => 100, 'credentials' => FALSE];
    $this->htmxAttribute->request($values);
    $rendered = (string) $this->htmxAttribute;
    $expected = " data-hx-request='" . '{"timeout":100,"credentials":false}' . "'";
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Test swapOob method.
   *
   * @covers ::swapOob
   * @dataProvider hxValidateDataProvider
   */
  public function testHxValidate(?bool $value, string $expected): void {
    if (is_null($value)) {
      $this->htmxAttribute->validate();
    }
    else {
      $this->htmxAttribute->validate($value);
    }
    $rendered = (string) $this->htmxAttribute;
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Provides data to ::testHxHistory.
   *
   * @return array{?bool, string}[]
   *   Array of null|bool, string, expected.
   */
  public static function hxValidateDataProvider(): array {
    return [
      [TRUE, ' data-hx-validate="true"'],
      [FALSE, ' data-hx-validate="false"'],
      [NULL, ' data-hx-validate="true"'],
    ];
  }

  /**
   * Test pushUrl method.
   *
   * @covers ::select
   * @covers ::selectOob
   * @covers ::swap
   * @covers ::target
   * @covers ::trigger
   * @covers ::confirm
   * @covers ::disable
   * @covers ::disabledElt
   * @covers ::disinherit
   * @covers ::encoding
   * @covers ::history
   * @covers ::historyElement
   * @covers ::include
   * @covers ::indicator
   * @covers ::inherit
   * @covers ::params
   * @covers ::preserve
   * @covers ::prompt
   * @covers ::sync
   * @covers \Drupal\Core\Template\AttributeValueBase::render
   * @dataProvider hxSimpleStringAttributesDataProvider
   */
  public function testHxSimpleStringAttributes(string $method, ?string $value, string $expected): void {
    if (is_null($value)) {
      $this->htmxAttribute->$method();
    }
    else {
      $this->htmxAttribute->$method($value);
    }
    $rendered = (string) $this->htmxAttribute;
    $this->assertEquals($expected, $rendered);
  }

  /**
   * Provides data to ::testHxSimpleStringAttributes.
   *
   * @return array{string, string, string}[]
   *   Array of method, value, expected.
   */
  public static function hxSimpleStringAttributesDataProvider(): array {
    $singleQuotedSyntax = 'info[data-drupal-selector="edit-select"]';
    return [
      ['select', '#info-details', ' data-hx-select="#info-details"'],
      ['select', 'info[data-drupal-selector="edit-select"]', " data-hx-select='$singleQuotedSyntax'"],
      ['selectOob', '#info-details', ' data-hx-select-oob="#info-details"'],
      ['selectOob', '#info-details:afterbegin, #alert', ' data-hx-select-oob="#info-details:afterbegin, #alert"'],
      ['swap', 'afterbegin', ' data-hx-swap="afterbegin  ignoreTitle:true"'],
      ['target', 'descriptor', ' data-hx-target="descriptor"'],
      ['trigger', 'event', ' data-hx-trigger="event"'],
      ['confirm', 'A confirmation message', ' data-hx-confirm="A confirmation message"'],
      ['disable', NULL, ' data-hx-disable'],
      ['disabledElements', 'descriptor', ' data-hx-disabled-elt="descriptor"'],
      ['disinherit', 'descriptor', ' data-hx-disinherit="descriptor"'],
      ['encoding', NULL, ' data-hx-encoding="multipart/form-data"'],
      ['encoding', 'application/x-www-form-urlencoded', ' data-hx-encoding="application/x-www-form-urlencoded"'],
      ['ext', 'name, name', ' data-hx-ext="name, name"'],
      ['historyElement', NULL, ' data-hx-history-elt'],
      ['include', 'descriptor', ' data-hx-include="descriptor"'],
      ['indicator', 'descriptor', ' data-hx-indicator="descriptor"'],
      ['inherit', 'descriptor', ' data-hx-inherit="descriptor"'],
      ['params', 'filter string', ' data-hx-params="filter string"'],
      ['preserve', 'id', ' data-hx-preserve="id"'],
      ['prompt', 'A prompt message', ' data-hx-prompt="A prompt message"'],
      ['sync', 'closest form:abort', ' data-hx-sync="closest form:abort"'],
    ];
  }

}
