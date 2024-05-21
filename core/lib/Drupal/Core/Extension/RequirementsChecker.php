<?php

namespace Drupal\Core\Extension;

use Drupal\Core\Messenger\MessengerInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;

/**
 * Checks module requirements.
 */
class RequirementsChecker {

  use StringTranslationTrait;

  /**
   * Constructs a new RequirementsCheck object.
   */
  public function __construct(
    protected ModuleExtensionList $moduleExtensionList,
    protected ModuleHandlerInterface $moduleHandler,
    protected MessengerInterface $messenger,
  ) {}

  /**
   * Checks module install requirements are met.
   */
  public function checkInstallRequirements(string $module): bool {
    $file = DRUPAL_ROOT . '/' . $this->moduleExtensionList->getPath($module) . "/$module.install";
    if (is_file($file)) {
      require_once $file;
    }
    // Check requirements.
    $requirements = $this->moduleHandler->invoke($module, 'requirements', ['install']);
    if (!is_array($requirements) || RequirementsSeverity::getMaxSeverity($requirements) !== REQUIREMENT_ERROR) {
      return TRUE;
    }
    $this->printErrorMessages($requirements);
    return FALSE;
  }

  /**
   * Print error messages for a set of requirements.
   *
   * @phpstan-param array<string, mixed> $requirements
   */
  private function printErrorMessages(array $requirements): void {
    foreach ($requirements as $requirement) {
      if (isset($requirement['severity']) && $requirement['severity'] == REQUIREMENT_ERROR) {
        $message = $requirement['description'];
        if (isset($requirement['value']) && $requirement['value']) {
          $message = $this->t('@requirements_message (Currently using @item version @version)', [
            '@requirements_message' => $requirement['description'],
            '@item' => $requirement['title'],
            '@version' => $requirement['value'],
          ]);
        }
        $this->messenger->addError($message);
      }
    }
  }

}
