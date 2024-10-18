<?php

namespace Drupal\user\Hook;

use Drupal\Component\Assertion\Inspector;
use Drupal\Component\Render\PlainTextOutput;
use Drupal\Component\Utility\Crypt;
use Drupal\Component\Utility\Unicode;
use Drupal\Core\Access\AccessibleInterface;
use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Batch\BatchBuilder;
use Drupal\Core\Datetime\Entity\DateFormat;
use Drupal\Core\Entity\Display\EntityViewDisplayInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Hook;
use Drupal\Core\Render\BubbleableMetadata;
use Drupal\Core\Render\Element;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Session\AnonymousUserSession;
use Drupal\Core\Site\Settings;
use Drupal\Core\Url;
use Drupal\filter\FilterFormatInterface;
use Drupal\image\Plugin\Field\FieldType\ImageItem;
use Drupal\system\Entity\Action;
use Drupal\user\Entity\Role;
use Drupal\user\Entity\User;
use Drupal\user\RoleInterface;
use Drupal\user\UserInterface;
use Drupal\views\ViewExecutable;

/**
 *
 */
class UserViewsExecutionHooks {

  /**
   * Implements hook_views_query_substitutions().
   *
   * Allow replacement of current user ID so we can cache these queries.
   */
  #[Hook('views_query_substitutions')]
    public function userViewsQuerySubstitutions(ViewExecutable $view) {
    return ['***CURRENT_USER***' => \Drupal::currentUser()->id()];
    }

}
