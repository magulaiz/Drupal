<?php

namespace Drupal\Tests\user\Unit\Form;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Config\ConfigManagerInterface;
use Drupal\Core\Config\Entity\ConfigEntityDependency;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Tests\UnitTestCase;
use Drupal\user\Form\EntityPermissionsForm;
use Drupal\user\PermissionHandlerInterface;
use Drupal\user\RoleStorageInterface;
use Symfony\Component\Routing\Route;

/**
 * Tests the user permissions administration form.
 *
 * @coversDefaultClass \Drupal\user\Form\UserPermissionsForm
 * @group user
 */
class UserPermissionsFormTest extends UnitTestCase {

  /**
   *
   */

}
