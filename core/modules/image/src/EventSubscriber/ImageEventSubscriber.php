<?php

namespace Drupal\image\EventSubscriber;

use Drupal\Component\Utility\Crypt;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\File\FileUrlGeneratorInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\PrivateKey;
use Drupal\Core\Routing\RequestHelper;
use Drupal\Core\Site\Settings;
use Drupal\Core\StreamWrapper\StreamWrapperInterface;
use Drupal\Core\StreamWrapper\StreamWrapperManagerInterface;
use Drupal\Core\Theme\Registry;
use Drupal\Core\Url;
use Drupal\image\Entity\ImageStyle;
use Drupal\image\Event\FlushSourceImageEvent;
use Drupal\image\Event\ImageDerivative\ApplyStyleEffectEvent;
use Drupal\image\Event\ImageDerivative\ApplyStyleEvent;
use Drupal\image\Event\ImageDerivative\BuildEvent;
use Drupal\image\Event\ImageDerivative\FindDimensionsEvent;
use Drupal\image\Event\ImageDerivative\FindFormatEvent;
use Drupal\image\Event\ImageDerivative\FindUriEvent;
use Drupal\image\Event\ImageDerivative\FindUrlEvent;
use Drupal\image\Event\ImageDerivative\FindUrlProtectionEvent;
use Drupal\image\Event\ImageDerivative\LoadSourceEvent;
use Drupal\image\Event\ImageDerivative\RemoveEvent;
use Drupal\image\Event\ImageDerivative\SaveEvent;
use Drupal\image\Event\ImageDerivative\ValidateSourceFormatEvent;
use Drupal\image\Event\ImageDerivative\ValidateSourceProcessableEvent;
use Drupal\image\Event\ImageStyle\FlushEvent;
use Drupal\image\ImageProcessorException;
use Drupal\image\ImageProcessor;
use Psr\Log\LoggerInterface;
use Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Defines a class for listening to events fired by the image module.
 */
class ImageEventSubscriber implements EventSubscriberInterface {

  /**
   * The Drupal private key.
   */
  protected string $privateKey;

  /**
   * The current request.
   */
  protected Request $currentRequest;

  /**
   * Constructs a new ImageDerivativeSubscriber.
   *
   * @param \Drupal\Core\Image\ImageFactory $imageFactory
   *   The image factory.
   * @param \Drupal\image\ImageProcessor $imageProcessor
   *   The image processor service.
   * @param \Drupal\Core\StreamWrapper\StreamWrapperManagerInterface $streamWrapperManager
   *   The stream wrapper manager service.
   * @param \Drupal\Core\PrivateKey $privateKey
   *   The Drupal private key service.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $moduleHandler
   *   The module handler service.
   * @param \Drupal\Core\Config\ConfigFactoryInterface $configFactory
   *   The config factory service.
   * @param \Symfony\Component\HttpFoundation\RequestStack $requestStack
   *   The request stack.
   * @param \Psr\Log\LoggerInterface $logger
   *   The logger service.
   * @param \Drupal\Core\File\FileSystemInterface $fileSystem
   *   The file system service.
   * @param \Drupal\Core\File\FileUrlGeneratorInterface $fileUrlGenerator
   *   The file URL generator.
   * @param \Drupal\Core\Theme\Registry $themeRegistry
   *   The theme registry.
   */
  public function __construct(
    protected readonly ImageFactory $imageFactory,
    protected readonly ImageProcessor $imageProcessor,
    protected readonly StreamWrapperManagerInterface $streamWrapperManager,
    PrivateKey $privateKey,
    protected readonly ModuleHandlerInterface $moduleHandler,
    protected readonly ConfigFactoryInterface $configFactory,
    RequestStack $requestStack,
    protected readonly LoggerInterface $logger,
    protected readonly FileSystemInterface $fileSystem,
    protected readonly FileUrlGeneratorInterface $fileUrlGenerator,
    protected readonly Registry $themeRegistry,
  ) {
    $this->privateKey = $privateKey->get();
    $this->currentRequest = $requestStack->getCurrentRequest();
  }

  /**
   * {@inheritdoc}
   */
  public static function getSubscribedEvents() {
    return [
      BuildEvent::class => 'buildDerivativeImage',
      FlushEvent::class => 'flushImageStyle',
      FlushSourceImageEvent::class => 'flushFromSourceImageUri',
      ApplyStyleEffectEvent::class => 'applyImageEffect',
      ApplyStyleEvent::class => 'applyImageStyle',
      LoadSourceEvent::class => 'loadSourceImage',
      RemoveEvent::class => 'removeDerivativeImage',
      FindUrlEvent::class => 'resolveDerivativeImageUrl',
      FindUrlProtectionEvent::class => 'resolveDerivativeImageUrlProtection',
      SaveEvent::class => 'saveDerivativeImage',
      FindDimensionsEvent::class => 'resolveDerivativeImageDimensions',
      FindFormatEvent::class => 'resolveDerivativeImageFormat',
      FindUriEvent::class => 'resolveDerivativeImageUri',
      ValidateSourceFormatEvent::class => 'resolveSourceImageFormat',
      ValidateSourceProcessableEvent::class => 'resolveSourceImageProcessable',
    ];
  }

  /**
   * Determines the format of a source image.
   *
   * @param \Drupal\image\Event\ImageDerivative\ValidateSourceFormatEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function resolveSourceImageFormat(ValidateSourceFormatEvent $event): void {
    $pipeline = $event->getPipeline();

    // Get the image file extension from the URI if not already specified.
    if (!$pipeline->hasVariable('sourceImageFileExtension')) {
      $pipeline->setSourceImageFileExtension(mb_strtolower(pathinfo($pipeline->getVariable('sourceImageUri'), PATHINFO_EXTENSION)));
    }
  }

  /**
   * Verifies that an image can be processed into a derivative.
   *
   * @param \Drupal\image\Event\ImageDerivative\ValidateSourceProcessableEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function resolveSourceImageProcessable(ValidateSourceProcessableEvent $event): void {
    $pipeline = $event->getPipeline();

    // Determine the image toolkit.
    if (!$pipeline->hasVariable('imageToolkitId')) {
      $pipeline->setImageToolkitId($this->imageFactory->getToolkitId());
    }

    // Ensure we know the format of the source image.
    try {
      $pipeline->dispatch(ValidateSourceFormatEvent::class);
    }
    catch (ImageProcessorException $e) {
      $pipeline->setVariable('isSourceImageProcessable', FALSE);
      return;
    }

    // The source image can be processed if the image toolkit supports its
    // format.
    $pipeline->setVariable(
      'isSourceImageProcessable',
      in_array(
        $pipeline->getVariable('sourceImageFileExtension'),
        $this->imageFactory->getSupportedExtensions($pipeline->getVariable('imageToolkitId'))
      )
    );
  }

  /**
   * Determines the format of a derivative image.
   *
   * @param \Drupal\image\Event\ImageDerivative\FindFormatEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function resolveDerivativeImageFormat(FindFormatEvent $event): void {
    $pipeline = $event->getPipeline();

    // Ensure we can process the source image.
    $pipeline->dispatch(ValidateSourceProcessableEvent::class);
    if (!$pipeline->getVariable('isSourceImageProcessable')) {
      throw new ImageProcessorException('Cannot determine derivative image format, source image not processable');
    }

    // Determine the derivative image file extension by looping through the
    // image effects' ::getDerivativeExtension methods.
    $extension = $pipeline->getVariable('sourceImageFileExtension');
    foreach ($pipeline->getVariable('imageStyle')->getEffects() as $effect) {
      $extension = $effect->getDerivativeExtension($extension);
    }
    $pipeline->setVariable('derivativeImageFileExtension', $extension);
  }

  /**
   * Determines the dimensions of a derivative image.
   *
   * Takes the source URI, the image style, and the starting dimensions to
   * determine the expected dimensions of the derivative image. The source URI
   * is used to allow effects to optionally use this information to retrieve
   * additional image metadata to determine output dimensions. The key
   * objective is to calculate derivative image dimensions without performing
   * actual image operations, so be aware that performing I/O on the URI may
   * lead to decrease in performance.
   *
   * @param \Drupal\image\Event\ImageDerivative\FindDimensionsEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function resolveDerivativeImageDimensions(FindDimensionsEvent $event): void {
    $pipeline = $event->getPipeline();

    // It's still possible to calculate dimensions even if the image at source
    // is not processable but we have input dimensions.
    $pipeline->dispatch(ValidateSourceProcessableEvent::class);
    if (!$pipeline->getVariable('isSourceImageProcessable') && (!$pipeline->hasVariable('sourceImageWidth') || !$pipeline->hasVariable('sourceImageHeight'))) {
      return;
    }

    // Determine the derivative image dimensions by looping through the image
    // style effects' ::transformDimensions methods.
    $dimensions = [
      'width' => $pipeline->getVariable('sourceImageWidth'),
      'height' => $pipeline->getVariable('sourceImageHeight'),
    ];
    foreach ($pipeline->getVariable('imageStyle')->getEffects() as $effect) {
      $effect->transformDimensions($dimensions, $pipeline->getVariable('sourceImageUri'));
    }
    $pipeline->setVariable('derivativeImageWidth', $dimensions['width']);
    $pipeline->setVariable('derivativeImageHeight', $dimensions['height']);
  }

  /**
   * Determines the URI of a derivative image.
   *
   * @param \Drupal\image\Event\ImageDerivative\FindUriEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function resolveDerivativeImageUri(FindUriEvent $event): void {
    $pipeline = $event->getPipeline();

    // Return if we already have the derivative URI.
    if ($pipeline->hasVariable('derivativeImageUri')) {
      return;
    }

    // Ensure we can process the source image.
    $pipeline->dispatch(FindFormatEvent::class);
    if (!$pipeline->getVariable('isSourceImageProcessable')) {
      throw new ImageProcessorException('Cannot determine derivative image URI, source image not processable');
    }

    // Determine derivative image URI.
    $source_scheme = $scheme = $this->streamWrapperManager->getScheme($pipeline->getVariable('sourceImageUri'));
    $default_scheme = $this->configFactory->get('system.file')->get('default_scheme');

    if ($source_scheme) {
      $path = $this->streamWrapperManager->getTarget($pipeline->getVariable('sourceImageUri'));
      // The scheme of derivative image files only needs to be computed for
      // source files not stored in the default scheme.
      if ($source_scheme != $default_scheme) {
        $class = $this->streamWrapperManager->getClass($source_scheme);
        $is_writable = NULL;
        if ($class) {
          $is_writable = $class::getType() & StreamWrapperInterface::WRITE;
        }

        // Compute the derivative URI scheme. Derivatives created from writable
        // source stream wrappers will inherit the scheme. Derivatives created
        // from read-only stream wrappers will fall-back to the default scheme.
        $scheme = $is_writable ? $source_scheme : $default_scheme;
      }
    }
    else {
      $path = $pipeline->getVariable('sourceImageUri');
      $source_scheme = $scheme = $default_scheme;
    }
    $path = $pipeline->getVariable('derivativeImageFileExtension') === $pipeline->getVariable('sourceImageFileExtension') ? $path : $path . '.' . $pipeline->getVariable('derivativeImageFileExtension');
    $pipeline->setVariable('derivativeImageUri', "$scheme://styles/{$pipeline->getVariable('imageStyle')->id()}/$source_scheme/$path");
  }

  /**
   * Determines the URL protection token of a derivative image.
   *
   * @param \Drupal\image\Event\ImageDerivative\FindUrlProtectionEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function resolveDerivativeImageUrlProtection(FindUrlProtectionEvent $event): void {
    $pipeline = $event->getPipeline();

    // Ensure we have the derivative image URI.
    $pipeline->dispatch(FindUriEvent::class);

    // The token query is added even if the
    // 'image.settings:allow_insecure_derivatives' configuration is TRUE, so
    // that the emitted links remain valid if it is changed back to the default
    // FALSE. However, sites which need to prevent the token query from being
    // emitted at all can additionally set the
    // 'image.settings:suppress_itok_output' configuration to TRUE to achieve
    // that (if both are set, the security token will neither be emitted in the
    // image derivative URL nor checked for in
    // \Drupal\image\ImageStyleInterface::deliver()).
    $token_query = [];
    $suppress_itok_output = $this->configFactory->get('image.settings')->get('suppress_itok_output');
    if (!$suppress_itok_output) {
      // The sourceUri property can be either a relative path or a full URI.
      $path = $pipeline->getVariable('sourceImageUri');
      if (!$this->streamWrapperManager->getScheme($path)) {
        $path = \Drupal::config('system.file')->get('default_scheme') . '://' . $path;
      }
      $original_uri_normalized = $this->streamWrapperManager->normalizeUri($path);
      $encryptable_uri = $pipeline->getVariable('derivativeImageFileExtension') === $pipeline->getVariable('sourceImageFileExtension') ? $original_uri_normalized : $original_uri_normalized . '.' . $pipeline->getVariable('derivativeImageFileExtension');
      // Return the first 8 characters.
      $token_query = [IMAGE_DERIVATIVE_TOKEN => substr(Crypt::hmacBase64($pipeline->getVariable('imageStyle')->id() . ':' . $encryptable_uri, $this->privateKey . Settings::getHashSalt()), 0, 8)];
      $pipeline->setVariable('derivativeImageUrlProtection', $token_query);
    }
  }

  /**
   * Determines the URL of a derivative image.
   *
   * Including the security token if specified.
   *
   * @param \Drupal\image\Event\ImageDerivative\FindUrlEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function resolveDerivativeImageUrl(FindUrlEvent $event): void {
    $pipeline = $event->getPipeline();

    // Ensure we have the derivative image URI and the URL protection token.
    $pipeline->dispatch(FindUriEvent::class);
    $pipeline->dispatch(FindUrlProtectionEvent::class);

    $clean_urls = $pipeline->hasVariable('setCleanUrl') ? $pipeline->getVariable('setCleanUrl') : NULL;
    $derivative_uri = $pipeline->getVariable('derivativeImageUri');
    $token_query = $pipeline->hasVariable('derivativeImageUrlProtection') ? $pipeline->getVariable('derivativeImageUrlProtection') : [];

    // Determine whether clean URLs can be used.
    if ($clean_urls === NULL) {
      // Assume clean URLs unless the request tells us otherwise.
      $clean_urls = TRUE;
      try {
        $clean_urls = RequestHelper::isCleanUrl($this->currentRequest);
      }
      catch (ServiceNotFoundException $e) {
      }
    }

    // If not using clean URLs, the image derivative callback is only available
    // with the script path. If the file does not exist, use Url::fromUri() to
    // ensure that it is included. Once the file exists it's fine to fall back
    // to the actual file path, this avoids bootstrapping PHP once the files are
    // built.
    if ($clean_urls === FALSE && $this->streamWrapperManager->getScheme($derivative_uri) == 'public' && !file_exists($derivative_uri)) {
      $validated_uri = 'base:' . $this->streamWrapperManager->getViaUri($derivative_uri)->getDirectoryPath() . '/' . $this->streamWrapperManager->getTarget($derivative_uri);
    }
    else {
      // Using clean URLs.
      $validated_uri = $this->fileUrlGenerator->generateAbsoluteString($derivative_uri);
    }

    $pipeline->setVariable('derivativeImageUrl', Url::fromUri($validated_uri, [
      'absolute' => TRUE,
      'query' => $token_query,
    ]));
  }

  /**
   * Loads an Image object for subsequent processing into a derivative.
   *
   * @param \Drupal\image\Event\ImageDerivative\LoadSourceEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function loadSourceImage(LoadSourceEvent $event): void {
    $pipeline = $event->getPipeline();

    if ($pipeline->hasVariable('sourceImageUri') && !$pipeline->hasImage()) {
      // If the source file doesn't exist or is invalid, throw an exception.
      $image = $this->imageFactory->get($pipeline->getVariable('sourceImageUri'), $pipeline->getVariable('imageToolkitId'));
      if (!$image->isValid()) {
        throw new ImageProcessorException('Missing or invalid source image file ' . $pipeline->getVariable('sourceImageUri'));
      }
      $pipeline->setImage($image);
    }
  }

  /**
   * Stores a transformed image at the derivative URI.
   *
   * @param \Drupal\image\Event\ImageDerivative\SaveEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function saveDerivativeImage(SaveEvent $event): void {
    $pipeline = $event->getPipeline();

    $pipeline->dispatch(FindUriEvent::class);

    // Get the folder for the final location of this style.
    $directory = $this->fileSystem->dirname($pipeline->getVariable('derivativeImageUri'));

    // Build the destination folder tree if it doesn't already exist.
    if (!$this->fileSystem->prepareDirectory($directory, FileSystemInterface::CREATE_DIRECTORY | FileSystemInterface::MODIFY_PERMISSIONS)) {
      $this->logger->error('Failed to create style directory: %directory', ['%directory' => $directory]);
      throw new ImageProcessorException('Failed to create style directory');
    }

    if (!$pipeline->getImage()->save($pipeline->getVariable('derivativeImageUri'))) {
      if (file_exists($pipeline->getVariable('derivativeImageUri'))) {
        $this->logger->error('Cached image file %destination already exists. There may be an issue with your rewrite configuration.', ['%destination' => $pipeline->getVariable('derivativeImageUri')]);
      }
      throw new ImageProcessorException('Cached image file already exists');
    }
  }

  /**
   * Produces an image derivative.
   *
   * @param \Drupal\image\Event\ImageDerivative\BuildEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function buildDerivativeImage(BuildEvent $event): void {
    $event->getPipeline()
      ->dispatch(ValidateSourceProcessableEvent::class)
      ->dispatch(LoadSourceEvent::class)
      ->dispatch(ApplyStyleEvent::class)
      ->dispatch(SaveEvent::class);
  }

  /**
   * Applies an image style to the image object.
   *
   * @param \Drupal\image\Event\ImageDerivative\ApplyStyleEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function applyImageStyle(ApplyStyleEvent $event): void {
    $pipeline = $event->getPipeline();

    // Apply the image effects to the image object.
    foreach ($pipeline->getVariable('imageStyle')->getEffects() as $effect) {
      $pipeline->dispatch(
        ApplyStyleEffectEvent::class, [
          'imageEffect' => $effect,
        ]);
    }
  }

  /**
   * Applies a single image style effect to the image object.
   *
   * @param \Drupal\image\Event\ImageDerivative\ApplyStyleEffectEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function applyImageEffect(ApplyStyleEffectEvent $event): void {
    $pipeline = $event->getPipeline();
    $effect = $event->getArgument('imageEffect');
    $effect->applyEffect($pipeline->getImage());
  }

  /**
   * Removes an image derivative file based on its source file URI.
   *
   * @param \Drupal\image\Event\ImageDerivative\RemoveEvent $event
   *   The image process event, carrying the process pipeline object.
   */
  public function removeDerivativeImage(RemoveEvent $event): void {
    $pipeline = $event->getPipeline();

    try {
      // Remove a single image derivative.
      $pipeline->dispatch(FindUriEvent::class);
      if ($pipeline->hasVariable('derivativeImageUri') && file_exists($pipeline->getVariable('derivativeImageUri'))) {
        $this->fileSystem->delete($pipeline->getVariable('derivativeImageUri'));
      }
    }
    catch (ImageProcessorException $e) {
      // Do nothing if derivative is non determinable.
    }
  }

  /**
   * Flushes all image derivatives for the specified image style.
   *
   * @param \Drupal\image\Event\ImageStyle\FlushEvent $event
   *   The image style event.
   */
  public function flushImageStyle(FlushEvent $event): void {
    $image_style = $event->getImageStyle();

    // Delete the style directory in each registered wrapper.
    $wrappers = $this->streamWrapperManager->getWrappers(StreamWrapperInterface::WRITE_VISIBLE);
    foreach ($wrappers as $wrapper => $wrapper_data) {
      if (file_exists($directory = $wrapper . '://styles/' . $image_style->id())) {
        $this->fileSystem->deleteRecursive($directory);
      }
    }

    // Let other modules update as necessary on flush.
    $this->moduleHandler->invokeAllDeprecated("is deprecated since version 9.x.x and will be removed in y.y.y.", 'image_style_flush', [$image_style]);

    // Clear caches so that formatters may be added for this style.
    $this->themeRegistry->reset();

    Cache::invalidateTags($image_style->getCacheTagsToInvalidate());
  }

  /**
   * Flushes all derivative versions of a specific file in all styles.
   *
   * @param \Drupal\image\Event\FlushSourceImageEvent $event
   *   The image style event.
   */
  public function flushFromSourceImageUri(FlushSourceImageEvent $event): void {
    foreach (ImageStyle::loadMultiple() as $style) {
      $this->imageProcessor->createInstance('derivative')
        ->setImageStyle($style)
        ->setSourceImageUri($event->uri)
        ->dispatch(RemoveEvent::class);
    }
  }

}
