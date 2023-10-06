<?php

namespace Drupal\ban\Controller;

use Drupal\Core\Config\Entity\ConfigEntityListBuilder;
use Drupal\Core\Entity\EntityInterface;

/**
 * Provides a listing of Whitelisted IPs.
 */
class WhitelistedIPListBuilder extends ConfigEntityListBuilder {

    /**
     * {@inheritdoc}
     */
    public function buildHeader() {
        $header['whitelistedIp'] = $this->t('Whitelisted IP');
        return $header + parent::buildHeader();
    }

    /**
     * {@inheritdoc}
     */
    public function buildRow(EntityInterface $entity) {
        $row['whitelistedIp'] = $entity->getWhitelistedIp();

        return $row + parent::buildRow($entity);
    }

}