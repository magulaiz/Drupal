<?php

namespace Drupal\theme_suggestions_test\Hook;

use Drupal\Core\Hook\Attribute\Hook;
class ThemeSuggestionsTestHooks
{
    /**
     * Implements hook_theme_suggestions_alter().
     */
    #[Hook('theme_suggestions_alter')]
    public function themeSuggestionsAlter(array &$suggestions, array &$variables, $hook)
    {
        \Drupal::messenger()->addStatus(__FUNCTION__ . '() executed.');
        if ($hook == 'theme_test_general_suggestions') {
            $suggestions[] = $hook . '__module_override';
            $variables['module_hook'] = 'theme_suggestions_test_theme_suggestions_alter';
        }
    }
    /**
     * Implements hook_theme_suggestions_HOOK_alter().
     */
    #[Hook('theme_suggestions_theme_test_suggestions_alter')]
    public function themeSuggestionsThemeTestSuggestionsAlter(array &$suggestions, array $variables)
    {
        \Drupal::messenger()->addStatus(__FUNCTION__ . '() executed.');
        $suggestions[] = 'theme_test_suggestions__module_override';
    }
    /**
     * Implements hook_theme_suggestions_HOOK_alter().
     */
    #[Hook('theme_suggestions_theme_test_specific_suggestions_alter')]
    public function themeSuggestionsThemeTestSpecificSuggestionsAlter(array &$suggestions, array $variables)
    {
        $suggestions[] = 'theme_test_specific_suggestions__variant__foo';
    }
}
