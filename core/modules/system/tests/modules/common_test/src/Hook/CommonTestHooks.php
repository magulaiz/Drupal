<?php

namespace Drupal\common_test\Hook;

use Drupal\Core\Asset\AttachedAssetsInterface;
use Drupal\Core\Language\LanguageInterface;
use Drupal\Core\Hook\Attribute\Hook;
class CommonTestHooks
{
    /**
     * Implements hook_TYPE_alter().
     */
    #[Hook('drupal_alter_alter')]
    public function drupalAlterAlter(&$data, &$arg2 = \NULL, &$arg3 = \NULL)
    {
        // Alter first argument.
        if (\is_array($data)) {
            $data['foo'] = 'Drupal';
        } elseif (\is_object($data)) {
            $data->foo = 'Drupal';
        }
        // Alter second argument, if present.
        if (isset($arg2)) {
            if (\is_array($arg2)) {
                $arg2['foo'] = 'Drupal';
            } elseif (\is_object($arg2)) {
                $arg2->foo = 'Drupal';
            }
        }
        // Try to alter third argument, if present.
        if (isset($arg3)) {
            if (\is_array($arg3)) {
                $arg3['foo'] = 'Drupal';
            } elseif (\is_object($arg3)) {
                $arg3->foo = 'Drupal';
            }
        }
    }
    /**
     * Implements hook_TYPE_alter().
     *
     * Same as common_test_drupal_alter_alter(), but here, we verify that themes
     * can also alter and come last.
     */
    #[Hook('drupal_alter_alter', module: 'olivero')]
    public function oliveroDrupalAlterAlter(&$data, &$arg2 = \NULL, &$arg3 = \NULL)
    {
        // Alter first argument.
        if (\is_array($data)) {
            $data['foo'] .= ' theme';
        } elseif (\is_object($data)) {
            $data->foo .= ' theme';
        }
        // Alter second argument, if present.
        if (isset($arg2)) {
            if (\is_array($arg2)) {
                $arg2['foo'] .= ' theme';
            } elseif (\is_object($arg2)) {
                $arg2->foo .= ' theme';
            }
        }
        // Try to alter third argument, if present.
        if (isset($arg3)) {
            if (\is_array($arg3)) {
                $arg3['foo'] .= ' theme';
            } elseif (\is_object($arg3)) {
                $arg3->foo .= ' theme';
            }
        }
    }
    /**
     * Implements hook_TYPE_alter().
     *
     * This is to verify that
     * \Drupal::moduleHandler()->alter(array(TYPE1, TYPE2), ...) allows
     * hook_module_implements_alter() to affect the order in which module
     * implementations are executed.
     */
    #[Hook('drupal_alter_foo_alter', module: 'block')]
    public function blockDrupalAlterFooAlter(&$data, &$arg2 = \NULL, &$arg3 = \NULL)
    {
        $data['foo'] .= ' block';
    }
    /**
     * Implements hook_module_implements_alter().
     *
     * @see block_drupal_alter_foo_alter()
     */
    #[Hook('module_implements_alter')]
    public function moduleImplementsAlter(&$implementations, $hook)
    {
        // For
        // \Drupal::moduleHandler()->alter(array('drupal_alter', 'drupal_alter_foo'), ...),
        // make the block module implementations run after all the other modules. Note
        // that when \Drupal::moduleHandler->alter() is called with an array of types,
        // the first type is considered primary and controls the module order.
        if ($hook == 'drupal_alter_alter' && isset($implementations['block'])) {
            $group = $implementations['block'];
            unset($implementations['block']);
            $implementations['block'] = $group;
        }
    }
    /**
     * Implements hook_theme().
     */
    #[Hook('theme')]
    public function theme()
    {
        return ['common_test_foo' => ['variables' => ['foo' => 'foo', 'bar' => 'bar']], 'common_test_render_element' => ['render element' => 'foo']];
    }
    /**
     * Implements hook_library_info_build().
     */
    #[Hook('library_info_build')]
    public function libraryInfoBuild()
    {
        $libraries = [];
        if (\Drupal::state()->get('common_test.library_info_build_test')) {
            $libraries['dynamic_library'] = ['version' => '1.0', 'css' => ['base' => ['common_test.css' => []]]];
        }
        return $libraries;
    }
    /**
     * Implements hook_library_info_alter().
     */
    #[Hook('library_info_alter')]
    public function libraryInfoAlter(&$libraries, $module)
    {
        if ($module === 'core' && isset($libraries['loadjs'])) {
            // Change the version of loadjs to 0.0.
            $libraries['loadjs']['version'] = '0.0';
            // Make loadjs depend on jQuery Form to test library dependencies.
            $libraries['loadjs']['dependencies'][] = 'core/internal.jquery.form';
        }
        // Alter the dynamically registered library definition.
        if ($module === 'common_test' && isset($libraries['dynamic_library'])) {
            $libraries['dynamic_library']['dependencies'] = ['core/jquery'];
        }
    }
    /**
     * Implements hook_cron().
     *
     * System module should handle if a module does not catch an exception and keep
     * cron going.
     *
     * @see common_test_cron_helper()
     */
    #[Hook('cron')]
    public function cron()
    {
        throw new \Exception('Uncaught exception');
    }
    /**
     * Implements hook_page_attachments().
     *
     * @see \Drupal\system\Tests\Common\PageRenderTest::assertPageRenderHookExceptions()
     */
    #[Hook('page_attachments')]
    public function pageAttachments(array &$page)
    {
        $page['#attached']['library'][] = 'core/foo';
        $page['#attached']['library'][] = 'core/bar';
        $page['#cache']['tags'] = ['example'];
        $page['#cache']['contexts'] = ['user.permissions'];
        if (\Drupal::state()->get('common_test.hook_page_attachments.descendant_attached', \FALSE)) {
            $page['content']['#attached']['library'][] = 'core/jquery';
        }
        if (\Drupal::state()->get('common_test.hook_page_attachments.render_array', \FALSE)) {
            $page['something'] = ['#markup' => 'test'];
        }
        if (\Drupal::state()->get('common_test.hook_page_attachments.early_rendering', \FALSE)) {
            // Do some early rendering.
            $element = ['#markup' => '123'];
            \Drupal::service('renderer')->render($element);
        }
    }
    /**
     * Implements hook_page_attachments_alter().
     *
     * @see \Drupal\system\Tests\Common\PageRenderTest::assertPageRenderHookExceptions()
     */
    #[Hook('page_attachments_alter')]
    public function pageAttachmentsAlter(array &$page)
    {
        // Remove a library that was added in common_test_page_attachments(), to test
        // that this hook can do what it claims to do.
        if (isset($page['#attached']['library']) && ($index = \array_search('core/bar', $page['#attached']['library'])) && $index !== \FALSE) {
            unset($page['#attached']['library'][$index]);
        }
        $page['#attached']['library'][] = 'core/baz';
        $page['#cache']['tags'] = ['example'];
        $page['#cache']['contexts'] = ['user.permissions'];
        if (\Drupal::state()->get('common_test.hook_page_attachments_alter.descendant_attached', \FALSE)) {
            $page['content']['#attached']['library'][] = 'core/jquery';
        }
        if (\Drupal::state()->get('common_test.hook_page_attachments_alter.render_array', \FALSE)) {
            $page['something'] = ['#markup' => 'test'];
        }
    }
    /**
     * Implements hook_js_alter().
     *
     * @see \Drupal\KernelTests\Core\Asset\AttachedAssetsTest::testAlter()
     */
    #[Hook('js_alter')]
    public function jsAlter(&$javascript, \Drupal\Core\Asset\AttachedAssetsInterface $assets, \Drupal\Core\Language\LanguageInterface $language)
    {
        // Attach alter.js above tableselect.js.
        $alter_js = \Drupal::service('extension.list.module')->getPath('common_test') . '/alter.js';
        if (\array_key_exists($alter_js, $javascript) && \array_key_exists('core/misc/tableselect.js', $javascript)) {
            $javascript[$alter_js]['weight'] = $javascript['core/misc/tableselect.js']['weight'] - 1;
        }
    }
    /**
     * Implements hook_js_settings_alter().
     *
     * @see \Drupal\system\Tests\Common\JavaScriptTest::testHeaderSetting()
     */
    #[Hook('js_settings_alter')]
    public function jsSettingsAlter(&$settings, \Drupal\Core\Asset\AttachedAssetsInterface $assets)
    {
        // Modify an existing setting.
        if (\array_key_exists('pluralDelimiter', $settings)) {
            $settings['pluralDelimiter'] = '☃';
        }
        // Add a setting.
        $settings['foo'] = 'bar';
    }
}
