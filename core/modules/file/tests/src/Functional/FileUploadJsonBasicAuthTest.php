<?php

namespace Drupal\Tests\file\Functional;

use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Tests\rest\Functional\BasicAuthResourceTestTrait;
use Drupal\Tests\rest\Functional\FileUploadResourceTestBase;

/**
 * @group file
 */
class FileUploadJsonBasicAuthTest extends FileUploadResourceTestBase {

  use BasicAuthResourceTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['basic_auth'];

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
  protected static $auth = 'basic_auth';

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
