<?php

namespace Drupal\Tests\action\Functional\Rest;

use Drupal\Tests\rest\Functional\AnonResourceTestTrait;
use Drupal\Tests\rest\Functional\EntityResource\XmlEntityNormalizationQuirksTrait;

/**
<<<<<<< HEAD:core/modules/aggregator/tests/src/Functional/Rest/FeedXmlAnonTest.php
 * @group rest
 * @group legacy
=======
 * @group action
>>>>>>> upstream/11.x:core/modules/action/tests/src/Functional/Rest/ActionXmlAnonTest.php
 */
class ActionXmlAnonTest extends ActionResourceTestBase {

  use AnonResourceTestTrait;
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
  protected $defaultTheme = 'stark';

}
