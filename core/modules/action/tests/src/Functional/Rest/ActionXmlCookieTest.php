<?php

namespace Drupal\Tests\action\Functional\Rest;

use Drupal\Tests\rest\Functional\CookieResourceTestTrait;
use Drupal\Tests\rest\Functional\EntityResource\XmlEntityNormalizationQuirksTrait;

/**
<<<<<<< HEAD:core/modules/aggregator/tests/src/Functional/Rest/FeedXmlCookieTest.php
 * @group rest
 * @group legacy
=======
 * @group action
>>>>>>> upstream/11.x:core/modules/action/tests/src/Functional/Rest/ActionXmlCookieTest.php
 */
class ActionXmlCookieTest extends ActionResourceTestBase {

  use CookieResourceTestTrait;
  use XmlEntityNormalizationQuirksTrait;

  /**
   * {@inheritdoc}
   */
  protected static $format = 'xml';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'text/xml; charset=UTF-8';

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'cookie';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

}
