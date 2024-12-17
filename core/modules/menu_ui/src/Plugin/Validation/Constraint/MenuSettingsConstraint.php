<?php

namespace Drupal\menu_ui\Plugin\Validation\Constraint;

use Drupal\Core\StringTranslation\TranslatableMarkup;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\Validator\Constraint as SymfonyConstraint;

/**
 * Validation constraint for changing the menu settings in pending revisions.
 */
#[Constraint(
  id: 'MenuSettings',
  label: new TranslatableMarkup('Menu settings.', [], ['context' => 'Validation'])
)]
class MenuSettingsConstraint extends SymfonyConstraint {

  public $message = 'You can only change the menu settings for the <em>published</em> version of this content.';
  public $weightMessage = 'You can only change the menu link weight for the <em>published</em> version of this content.';
  public $parentMessage = 'You can only change the parent menu link for the <em>published</em> version of this content.';
  public $removeMessage = 'You can only remove the menu link in the <em>published</em> version of this content.';

}
