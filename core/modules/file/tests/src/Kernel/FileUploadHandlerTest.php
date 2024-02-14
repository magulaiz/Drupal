<?php

namespace Drupal\Tests\file\Kernel;

use Drupal\file\Upload\FileUploadHandler;
use Drupal\file\Upload\UploadedFileInterface;
use Drupal\KernelTests\KernelTestBase;
use Symfony\Component\Mime\MimeTypeGuesserInterface;

/**
 * Tests the file upload handler.
 *
 * @group file
 */
class FileUploadHandlerTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['file', 'file_validator_test'];

  /**
   * The file upload handler under test.
   */
  protected FileUploadHandler $fileUploadHandler;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->fileUploadHandler = $this->container->get('file.upload_handler');
  }

  /**
   * Tests the legacy extension support.
   *
   * @group legacy
   */
  public function testLegacyExtensions(): void {
    $filename = $this->randomMachineName() . '.txt';
    $uploadedFile = $this->createMock(UploadedFileInterface::class);
    $uploadedFile->expects($this->once())
      ->method('getClientOriginalName')
      ->willReturn($filename);
    $uploadedFile->expects($this->once())->method('isValid')->willReturn(TRUE);

    // Throw an exception in mimeTypeGuesser to return early from the method.
    $mimeTypeGuesser = $this->createMock(MimeTypeGuesserInterface::class);
    $mimeTypeGuesser->expects($this->once())->method('guessMimeType')
      ->willThrowException(new \RuntimeException('Expected exception'));

    $fileUploadHandler = new FileUploadHandler(
      fileSystem: $this->container->get('file_system'),
      entityTypeManager: $this->container->get('entity_type.manager'),
      streamWrapperManager: $this->container->get('stream_wrapper_manager'),
      eventDispatcher: $this->container->get('event_dispatcher'),
      mimeTypeGuesser: $mimeTypeGuesser,
      currentUser: $this->container->get('current_user'),
      requestStack: $this->container->get('request_stack'),
      fileRepository: $this->container->get('file.repository'),
      file_validator: $this->container->get('file.validator'),
    );

    $this->expectException(\Exception::class);
    $this->expectDeprecation('\'file_validate_extensions\' is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use the \'FileExtension\' constraint instead. See https://www.drupal.org/node/3363700');
    $fileUploadHandler->handleFileUpload($uploadedFile, ['file_validate_extensions' => ['txt']]);

    $subscriber = $this->container->get('file_validation_sanitization_subscriber');
    $this->assertEquals(['txt'], $subscriber->getAllowedExtensions());
  }

  /**
   * Tests handleExtensionValidation() with different validators.
   *
   * @dataProvider validatorsProvider
   * @group legacy
   */
  public function testHandleExtensionValidation(array $validators, array $expectedValidators, bool $deprecation): void {
    $method = new \ReflectionMethod(
      FileUploadHandler::class,
      'handleExtensionValidation'
    );
    $method->setAccessible(TRUE);

    if ($deprecation) {
      $this->expectDeprecation(
        '\'file_validate_extensions\' is deprecated in drupal:10.2.0 and is removed from drupal:11.0.0. Use the \'FileExtension\' constraint instead. See https://www.drupal.org/node/3363700'
      );
    }
    $method->invokeArgs($this->fileUploadHandler, [&$validators]);

    $this->assertEquals($expectedValidators, $validators);
  }

  /**
   * Provides data for testHandleExtensionValidation().
   */
  public static function validatorsProvider(): array {
    return [
      // Legacy extension validator.
      'valid_legacy' => [
        ['file_validate_extensions' => ['txt']],
        ['FileExtension' => ['extensions' => 'txt']],
        TRUE,
      ],
      'non_default_legacy' => [
        ['file_validate_extensions' => ['foo']],
        ['FileExtension' => ['extensions' => 'foo']],
        TRUE,
      ],
      'empty_legacy' => [
        ['file_validate_extensions' => ''],
        [],
        TRUE,
      ],
      // Plugin extension validator.
      'valid_extension' => [
        ['FileExtension' => ['extensions' => 'txt']],
        ['FileExtension' => ['extensions' => 'txt']],
        FALSE,
      ],
      'non_default_extension' => [
        ['FileExtension' => ['extensions' => 'foo']],
        ['FileExtension' => ['extensions' => 'foo']],
        FALSE,
      ],
      'empty_extensions' => [
        ['FileExtension' => []],
        [],
        FALSE,
      ],
      'undefined' => [
        [],
        ['FileExtension' => ['extensions' => FileUploadHandler::DEFAULT_EXTENSIONS]],
        FALSE,
      ],
    ];
  }

}
