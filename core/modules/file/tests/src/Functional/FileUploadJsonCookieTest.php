<?php

namespace Drupal\Tests\file\Functional;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Tests\rest\Functional\CookieResourceTestTrait;
use Drupal\Tests\rest\Functional\FileUploadResourceTestBase;

/**
 * @group file
 */
class FileUploadJsonCookieTest extends FileUploadResourceTestBase {

  use CookieResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected static $format = 'json';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'application/json';

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'cookie';

  /**
   * Entity type ID for this storage.
   *
   * @var string
   */
  protected static string $entityTypeId;

  /**
   * The cacheability of unauthorized 'view' entity access.
   *
   * @param bool $is_authenticated
   *   Whether the current request is authenticated or not. This matters for
   *   some entity access control handlers, but not for most.
   *
   * @return \Drupal\Core\Cache\CacheableMetadata
   *   The expected cacheability.
   */
  protected function getExpectedUnauthorizedEntityAccessCacheability($is_authenticated) {
    return new CacheableMetadata();
  }

}
