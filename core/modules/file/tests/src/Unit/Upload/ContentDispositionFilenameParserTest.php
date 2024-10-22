<?php

declare(strict_types=1);

namespace Drupal\Tests\file\Unit\Upload;

use Drupal\file\Upload\ContentDispositionFilenameParser;
use Drupal\Tests\UnitTestCase;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

// cspell:ignore résumé

/**
 * Tests the ContentDispositionFilenameParser class.
 *
 * @group file
 * @coversDefaultClass \Drupal\file\Upload\ContentDispositionFilenameParser
 */
class ContentDispositionFilenameParserTest extends UnitTestCase {

  /**
   * Tests the parseFilename() method.
   *
   * @covers ::parseFilename
   */
  public function testParseFilenameSuccess(): void {
    $request = $this->createRequest('filename="test.txt"');
    $filename = ContentDispositionFilenameParser::parseFilename($request);
    $this->assertEquals('test.txt', $filename);
  }

  /**
   * @covers ::parseFilename
   */
  public function testParseFilenameExtendedSuccess(): void {
    /* cspell:disable-next-line */
    $request = $this->createRequest('filename*="UTF-8\'\'r%C3%A9sum%C3%A9.txt"');
    $filename = ContentDispositionFilenameParser::parseFilename($request);
    $this->assertEquals('résumé.txt', $filename);
  }

  /**
   * @covers ::parseFilename
   * @dataProvider invalidHeaderProvider
   */
  public function testParseFilenameInvalid(string | bool $contentDisposition): void {
    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('No filename found in "Content-Disposition" header. A file name in the format "filename=FILENAME" must be provided.');
    $request = $this->createRequest($contentDisposition);
    ContentDispositionFilenameParser::parseFilename($request);
  }

  /**
   * @covers ::parseFilename
   */
  public function testParseFilenameMissing(): void {
    $this->expectException(BadRequestHttpException::class);
    $this->expectExceptionMessage('"Content-Disposition" header is required. A file name in the format "filename=FILENAME" must be provided.');
    $request = new Request();
    ContentDispositionFilenameParser::parseFilename($request);
  }

  /**
   * A data provider for invalid headers.
   */
  public static function invalidHeaderProvider(): array {
    return [
      'multiple' => ['file; filename=""'],
      'empty' => ['filename=""'],
      'bad key' => ['not_a_filename="example.txt"'],
    ];
  }

  /**
   * Creates a request with the given content-disposition header.
   */
  protected function createRequest(string $contentDisposition): Request {
    $request = new Request();
    $request->headers->set('Content-Disposition', $contentDisposition);
    return $request;
  }

}
