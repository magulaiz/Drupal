<?php

namespace Drupal\ban\Event;

use Drupal\Component\EventDispatcher\Event;
use Drupal\user\UserInterface;

class BanEvent extends Event {

    const EVENT_NAME = 'ban_ip_event';

    public string $ip;
    public bool $isBanned;

    public function __construct(string $ip, bool $isBanned=true) {
        $this->ip = $ip;
        $this->isBanned = $isBanned;
    }

}
