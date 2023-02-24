<?php
// phpcs:ignoreFile

/**
 * This file was generated via php core/scripts/generate-proxy-class.php 'Drupal\book\BookUninstallValidator' "core/modules/book/src".
 */

namespace Drupal\book\ProxyClass {

    /**
     * Provides a proxy class for \Drupal\book\BookUninstallValidator.
     *
     * @see \Drupal\Component\ProxyBuilder
     */
    class BookUninstallValidator implements \Drupal\Core\Extension\ModuleUninstallValidatorInterface
    {

        use \Drupal\Core\DependencyInjection\DependencySerializationTrait;

        /**
         * The real proxied service, after it was lazy loaded.
         *
         * @var \Drupal\book\BookUninstallValidator
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
         * @param string $drupalProxyOriginalServiceId
         *   The service ID of the original service.
         */
        public function __construct(\Symfony\Component\DependencyInjection\ContainerInterface $container, protected $drupalProxyOriginalServiceId)
        {
            $this->container = $container;
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
        public function validate($module)
        {
            return $this->lazyLoadItself()->validate($module);
        }

        /**
         * {@inheritdoc}
         */
        public function setStringTranslation(\Drupal\Core\StringTranslation\TranslationInterface $translation)
        {
            return $this->lazyLoadItself()->setStringTranslation($translation);
        }

    }

}
