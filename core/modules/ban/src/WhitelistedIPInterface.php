<?php

namespace Drupal\ban;

use Drupal\Core\Config\Entity\ConfigEntityInterface;

/**
* Provides an interface defining an Whitelisted IP entity.
*/
interface WhitelistedIPInterface extends ConfigEntityInterface {
    public function getWhitelistedIp(): string;
    public function setWhitelistedIp(string $ip): void;
}
