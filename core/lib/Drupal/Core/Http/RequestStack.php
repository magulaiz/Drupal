<?php

namespace Drupal\Core\Http;

@trigger_error('The ' . __NAMESPACE__ . '\RequestStack is deprecated in drupal:10.0.0 and is removed from drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/3265357', E_USER_DEPRECATED);

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack as SymfonyRequestStack;

/**
 * Forward-compatibility shim for Symfony's RequestStack.
 *
<<<<<<< HEAD
 * @todo https://www.drupal.org/node/3265121 Remove in Drupal 10.0.x.
=======
 * @deprecated in drupal:10.0.0 and is removed from drupal:11.0.0. There is no
 *   replacement.
 *
 * @see https://www.drupal.org/node/3265357
 *
 * @todo Remove this in Drupal 11 https://www.drupal.org/node/3265121
>>>>>>> upstream/11.x
 */
class RequestStack extends SymfonyRequestStack {

  /**
   * Gets the main request.
   *
   * @return \Symfony\Component\HttpFoundation\Request|null
   *   The main request.
   */
  public function getMainRequest(): ?Request {
<<<<<<< HEAD
    if (method_exists(SymfonyRequestStack::class, 'getMainRequest')) {
      return parent::getMainRequest();
    }
    else {
      return parent::getMasterRequest();
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getMasterRequest() {
    @trigger_error(__METHOD__ . '() is deprecated in drupal:9.3.0 and is removed from drupal:10.0.0. Use getMainRequest() instead. See https://www.drupal.org/node/3253744', E_USER_DEPRECATED);
    return $this->getMainRequest();
=======
    @trigger_error('The ' . __NAMESPACE__ . '\RequestStack is deprecated in drupal:10.0.0 and is removed from drupal:11.0.0. There is no replacement. See https://www.drupal.org/node/3265357', E_USER_DEPRECATED);
    return parent::getMainRequest();
>>>>>>> upstream/11.x
  }

}
