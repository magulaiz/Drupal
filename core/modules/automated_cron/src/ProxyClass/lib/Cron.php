<?php
// phpcs:ignoreFile

/**
 * This file was generated via php core/scripts/generate-proxy-class.php 'Drupal\automated_cron\lib\Cron' "core/modules/automated_cron/src".
 */

namespace Drupal\automated_cron\ProxyClass\lib {

    /**
     * Provides a proxy class for \Drupal\automated_cron\lib\Cron.
     *
     * @see \Drupal\Component\ProxyBuilder
     */
    class Cron implements \Drupal\Core\CronInterface
    {

        use \Drupal\Core\DependencyInjection\DependencySerializationTrait;

        /**
         * The id of the original proxied service.
         *
         * @var string
         */
        protected $drupalProxyOriginalServiceId;

        /**
         * The real proxied service, after it was lazy loaded.
         *
         * @var \Drupal\automated_cron\lib\Cron
         */
        protected $service;

        /**
         * The service container.
         *
         * @var \Symfony\Component\DependencyInjection\ContainerInterface
         */
        protected $container;

        /**
         * Constructs a ProxyClass Drupal proxy object.
         *
         * @param \Symfony\Component\DependencyInjection\ContainerInterface $container
         *   The container.
         * @param string $drupal_proxy_original_service_id
         *   The service ID of the original service.
         */
        public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container, $drupal_proxy_original_service_id)
        {
            $this->container = $container;
            $this->drupalProxyOriginalServiceId = $drupal_proxy_original_service_id;
        }

        /**
         * Lazy loads the real service from the container.
         *
         * @return object
         *   Returns the constructed real service.
         */
        protected function lazyLoadItself()
        {
            if (!isset($this->service)) {
                $this->service = $this->container->get($this->drupalProxyOriginalServiceId);
            }

            return $this->service;
        }

        /**
         * {@inheritdoc}
         */
        public function instantProcessQueues(array $queues_to_process, int $max_items_to_process): void
        {
            $this->lazyLoadItself()->instantProcessQueues($queues_to_process, $max_items_to_process);
        }

        /**
         * {@inheritdoc}
         */
        public function run()
        {
            return $this->lazyLoadItself()->run();
        }

    }

}
