<?php

namespace Drupal\ban\Entity;

use Drupal\Core\Config\Entity\ConfigEntityBase;
use Drupal\ban\WhitelistedIPInterface;

/**
 * Defines the WhiteListIP entity.
 *
 * @ConfigEntityType(
 *   id = "ban_whitelisted_ip",
 *   label = @Translation("Whitelisted IP"),
 *   handlers = {
 *     "list_builder" = "Drupal\ban\Controller\WhitelistedIPListBuilder",
 *     "form" = {
 *       "add" = "Drupal\ban\Form\WhitelistedIPForm",
 *       "edit" = "Drupal\ban\Form\WhitelistedIPForm",
 *       "delete" = "Drupal\ban\Form\WhitelistedIPDeleteForm",
 *     }
 *   },
 *   config_prefix = "whitelist",
 *   admin_permission = "administer site configuration",
 *   entity_keys = {
 *     "id" = "id",
 *     "whitelistedIp" = "whitelistedIp",
 *   },
 *   config_export = {
 *     "id",
 *     "whitelistedIp",
 *   },
 *   links = {
 *     "edit-form" = "/admin/config/people/ban-whitelist/{ban_whitelisted_ip}",
 *     "delete-form" = "/admin/config/people/ban-whitelist/{ban_whitelisted_ip}/delete",
 *   },
 * )
 */
class WhitelistedIP extends ConfigEntityBase implements WhitelistedIPInterface {

  protected string $id;
  protected string $whitelistedIp = '';

  public function getId(): string
  {
    return $this->id;
  }

  public function setId(string $id): void
  {
    $this->id = $id;
  }

  public function getWhitelistedIp(): string
  {
      return $this->whitelistedIp;
  }

  public function setWhitelistedIp(string $ip): void
  {
      $this->whitelistedIp = $ip;
  }
}
