<?php

namespace Drupal\Tests\link\Kernel;

use Drupal\Tests\field\Kernel\FieldKernelTestBase;

/**
 * Tests link field validation.
 *
 * @group link
 */
class LinkItemUrlValidationTest extends FieldKernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['link'];

  /**
   * Tests link validation.
   */
  public function testExternalLinkValidation() {
    $definition = \Drupal::typedDataManager()
      ->createDataDefinition('field_item:link');
    $link_item = \Drupal::typedDataManager()->create($definition);
    $test_links = $this->getTestLinks();

    foreach ($test_links as $data) {
      [$value, $expected_violations] = $data;
      $link_item->setValue($value);
      $violations = $link_item->validate();
      $expected_count = count($expected_violations);
      $this->assertCount($expected_count, $violations, sprintf('Violation message count error for %s', $value));
      if ($expected_count) {
        $i = 0;
        foreach ($expected_violations as $error_msg) {
          // If the expected message contains a '%' add the current link value.
          if (strpos($error_msg, '%')) {
            $error_msg = sprintf($error_msg, $value);
          }
          $this->assertEquals($error_msg, $violations[$i++]->getMessage());
        }
      }
    }
  }

  /**
   * Builds an array of links to test.
   *
   * @return array
   *   The first element of the array is the link value to test. The second
   *   value is an array of expected violation messages.
   */
  protected function getTestLinks() {
    $violation_0 = "The path '%s' is invalid.";
    $violation_1 = 'This value should be of the correct primitive type.';
    return [
      ['invalid://not-a-valid-protocol', [$violation_0]],
      ['https://www.example.com/', []],
      // Strings within parenthesis without leading space char.
      ['https://www.example.com/strings_(string_within_parenthesis)', []],
      // Numbers within parenthesis without leading space char.
      ['https://www.example.com/numbers_(9999)', []],
      ['https://www.example.com/?name=ferret&color=purple', []],
      ['https://www.example.com/page?name=ferret&color=purple', []],
      ['https://www.example.com?a=&b[]=c&d[]=e&d[]=f&h==', []],
      ['https://www.example.com#colors', []],
      // Use list of valid URLS from],
      // https://cran.r-project.org/web/packages/rex/vignettes/url_parsing.html.
      ["https://foo.com/blah_blah", []],
      ["https://foo.com/blah_blah/", []],
      ["https://foo.com/blah_blah_(wikipedia)", []],
      ["https://foo.com/blah_blah_(wikipedia)_(again)", []],
      ["https://www.example.com/wpstyle/?p=364", []],
      ["https://www.example.com/foo/?bar=baz&inga=42&quux", []],
      ["https://✪df.ws/123", []],
      ["https://userid:password@example.com:8080", []],
      ["https://userid:password@example.com:8080/", []],
      ["https://userid@example.com", []],
      ["https://userid@example.com/", []],
      ["https://userid@example.com:8080", []],
      ["https://userid@example.com:8080/", []],
      ["https://userid:password@example.com", []],
      ["https://userid:password@example.com/", []],
      ["https://➡.ws/䨹", []],
      ["https://⌘.ws", []],
      ["https://⌘.ws/", []],
      ["https://foo.com/blah_(wikipedia)#cite-1", []],
      ["https://foo.com/blah_(wikipedia)_blah#cite-1", []],
      // The following invalid URLs produce false positives.
      ["https://foo.com/unicode_(✪)_in_parens", []],
      ["https://foo.com/(something)?after=parens", []],
      ["https://☺.damowmow.com/", []],
      ["https://code.google.com/events/#&product=browser", []],
      ["https://j.mp", []],
      ["ftp://foo.bar/baz", []],
      ["https://foo.bar/?q=Test%20URL-encoded%20stuff", []],
      ["https://مثال.إختبار", []],
      ["https://例子.测试", []],
      ["https://-.~_!$&'()*+,;=:%40:80%2f::::::@example.com", []],
      ["https://1337.net", []],
      ["https://a.b-c.de", []],
      ["radar://1234", [$violation_0]],
      ["h://test", [$violation_0]],
      ["ftps://foo.bar/", [$violation_0]],
      // Use invalid URLS from
      // https://cran.r-project.org/web/packages/rex/vignettes/url_parsing.html.
      ['https://', [$violation_0, $violation_1]],
      ["https://?", [$violation_0, $violation_1]],
      ["https://??", [$violation_0, $violation_1]],
      ["https://??/", [$violation_0, $violation_1]],
      ["https://#", [$violation_0, $violation_1]],
      ["https://##", [$violation_0, $violation_1]],
      ["https://##/", [$violation_0, $violation_1]],
      ["//", [$violation_0, $violation_1]],
      ["///a", [$violation_0, $violation_1]],
      ["///", [$violation_0, $violation_1]],
      ["https:///a", [$violation_0, $violation_1]],
    ];
  }

}
