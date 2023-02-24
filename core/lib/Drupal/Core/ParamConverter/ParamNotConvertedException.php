<?php

namespace Drupal\Core\ParamConverter;

/**
 * Provides an exception class for a request parameter that was not converted.
 */
class ParamNotConvertedException extends \Exception {

  /**
   * The raw parameters that were not converted.
   *
   * @var array
   */
  protected $rawParameters = [];

  /**
   * Constructs the ParamNotConvertedException.
   *
   * @param string $message
   *   The Exception message to throw.
   * @param int $code
   *   The Exception code.
   * @param \Exception $previous
   *   The previous exception used for the exception chaining.
   * @param string $routeName
   *   The route name that was not converted.
   * @param array $raw_parameters
   *   The raw parameters that were not converted.
   */
  public function __construct($message = "", $code = 0, \Exception $previous = NULL, protected $routeName = "", array $raw_parameters = []) {
    parent::__construct($message, $code, $previous);
    $this->rawParameters = $raw_parameters;
  }

  /**
   * Get the route name that was not converted.
   *
   * @return string
   *   The route name that was not converted.
   */
  public function getRouteName() {
    return $this->routeName;
  }

  /**
   * Get the raw parameters that were not converted.
   *
   * @return array
   *   The raw parameters that were not converted.
   */
  public function getRawParameters() {
    return $this->rawParameters;
  }

}
