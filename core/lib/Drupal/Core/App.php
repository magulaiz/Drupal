<?php

namespace Drupal\Core;

use Symfony\Component\HttpFoundation\Request;

/**
 * Provides the applications base URL in a front controller independent way.
 */
class App {


  /**
   * The current request.
   *
   * @var \Symfony\Component\HttpFoundation\Request
   */
  protected Request $request;

  /**
   * The base URL of the application.
   *
   * @var string
   */
  protected ?string $baseUrl;

  /**
   * The path part of the base URL.
   *
   * @var string
   */
  protected ?string $basePath;

  /**
   * Constructs a new app object.
   *
   * @param string $root
   *   The absolute path to the Drupal root directory.
   */
  public function __construct(
    protected string $root,
  ) {}

  /**
   * Sets the current request.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The current request.
   */
  public function setRequest(Request $request): void {
    $this->request = $request;
    $this->reset();
  }

  /**
   * Returns the base URL of the application.
   *
   * @return string
   *   The base URL of the application.
   */
  public function getBaseUrl(): string {
    if (!isset($this->baseUrl)) {
      $this->prepareBaseUrl();
    }

    return $this->baseUrl;
  }

  /**
   * Returns the path part of the base URL.
   *
   * @return string
   *   The path part of the base URL.
   */
  public function getBasePath(): string {
    if (!isset($this->basePath)) {
      $this->prepareBasePath();
    }

    return $this->basePath;
  }

  /**
   * Resets internal state.
   */
  protected function reset(): void {
    $this->baseUrl = NULL;
    $this->basePath = NULL;
  }

  /**
   * Computes the base URL.
   */
  protected function prepareBaseUrl(): void {
    $this->baseUrl = $this->request->getSchemeAndHttpHost() . $this->getBasePath();
  }

  /**
   * Computes the base path.
   */
  protected function prepareBasePath(): void {
    $this->basePath = $this->request->getBasePath();

    if ($this->basePath) {
      $script_path = $this->realpath($this->request->server->get('SCRIPT_FILENAME'));
      // Remove trailing filename from path to front controller.
      $script_base = substr($script_path, 0, -strlen(basename($script_path)) - 1);
      // Remove leading document root from path to front controller.
      $script_subdir = substr($script_base, strlen($this->realpath($this->root)));
      if ($script_subdir) {
        // Base path is request basePath - script subdir
        $this->basePath = substr($this->request->getBasePath(), 0, -strlen($script_subdir));
      }
    }
  }

  /**
   * Wraps the PHP realpath for testing purposes.
   *
   * @param string $path
   *   The path to be converted.
   *
   * @return string
   *   The true path with symlinks, and relative portions, resolved.
   */
  protected function realpath(string $path): string {
    return realpath($path);
  }

}
