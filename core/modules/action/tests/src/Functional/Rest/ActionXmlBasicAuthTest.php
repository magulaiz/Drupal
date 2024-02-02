<?php

namespace Drupal\Tests\action\Functional\Rest;

use Drupal\Tests\rest\Functional\BasicAuthResourceTestTrait;
use Drupal\Tests\rest\Functional\EntityResource\XmlEntityNormalizationQuirksTrait;

/**
<<<<<<< HEAD:core/modules/aggregator/tests/src/Functional/Rest/ItemXmlBasicAuthTest.php
 * @group rest
 * @group legacy
=======
 * @group action
>>>>>>> upstream/11.x:core/modules/action/tests/src/Functional/Rest/ActionXmlBasicAuthTest.php
 */
class ActionXmlBasicAuthTest extends ActionResourceTestBase {

  use BasicAuthResourceTestTrait;
  use XmlEntityNormalizationQuirksTrait;

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
  protected static $format = 'xml';

  /**
   * {@inheritdoc}
   */
  protected static $mimeType = 'text/xml; charset=UTF-8';

  /**
   * {@inheritdoc}
   */
  protected static $auth = 'basic_auth';

}
