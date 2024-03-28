<?php

namespace Drupal\Core\Config\Entity;

/**
 * Interface for configuration entities to store third party information.
 *
 * A third party is a module that needs to store tightly coupled information to
 * the configuration entity. For example, a module that alters the node type
 * form can use this to store its configuration so that it will be deployed
 * with the node type.
 */
interface ThirdPartySettingsInterface {

  /**
   * Sets the value of a third-party setting.
   *
   * @param string $module
   *   The module providing the third-party setting.
   * @param string $key
   *   The setting name.
   * @param mixed $value
   *   The setting value.
   *
   * @return $this
   */
  public function setThirdPartySetting(string $module, string $key, $value);

  /**
   * Gets the value of a third-party setting.
   *
   * @param string $module
   *   The module providing the third-party setting.
   * @param string $key
   *   The setting name.
   * @param mixed $default
   *   The default value
   *
   * @return mixed
   *   The value.
   */
  public function getThirdPartySetting(string $module, string $key, $default = NULL);

  /**
   * Gets all third-party settings of a given module.
   *
   * @param string $module
   *   The module providing the third-party settings.
   *
   * @return array
   *   An array of key-value pairs.
   */
  public function getThirdPartySettings(string $module);

  /**
   * Unsets a third-party setting.
   *
   * @param string $module
   *   The module providing the third-party setting.
   * @param string $key
   *   The setting name.
   *
   * @return mixed
   *   The value.
   */
  public function unsetThirdPartySetting(string $module, string $key);

  /**
   * Gets the list of third parties that store information.
   *
   * @return array
   *   The list of third parties.
   */
  public function getThirdPartyProviders();

}
