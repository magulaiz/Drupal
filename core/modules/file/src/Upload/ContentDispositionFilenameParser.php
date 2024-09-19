<?php

namespace Drupal\file\Upload;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

/**
 * Parses the content-disposition header to extract the client filename.
 */
final class ContentDispositionFilenameParser {

  /**
   * The regex used to extract the filename from the content disposition header.
   */
  const REQUEST_HEADER_FILENAME_REGEX = '@\bfilename=\"(?<filename>.+)\"@';

  /**
   * The regex used to extract an extended filename from the content disposition header.
   *
   * @var string
   */
  const REQUEST_HEADER_EXTENDED_FILENAME_REGEX = "@\bfilename\*=\"(?<charset>[\w-]+)'(?<lang>\w*)'(?<filename>.+)\"@";

  /**
   * Private constructor to prevent instantiation.
   */
  private function __construct() {}

  /**
   * Parse the content disposition header and return the filename.
   *
   * @param \Symfony\Component\HttpFoundation\Request $request
   *   The request.
   *
   * @return string
   *   The filename.
   *
   * @throws \Symfony\Component\HttpKernel\Exception\BadRequestHttpException
   *   Thrown when the 'Content-Disposition' request header is invalid.
   */
  public static function parseFilename(Request $request): string {
    // Firstly, check the header exists.
    if (!$request->headers->has('content-disposition')) {
      throw new BadRequestHttpException('"Content-Disposition" header is required. A file name in the format "filename=FILENAME" must be provided.');
    }

    $content_disposition = $request->headers->get('content-disposition');

    // Parse the header value. This regex does not allow an empty filename.
    // Check for an extended filename first. i.e. 'filename*=""' or
    // 'filename=""'. This also matches on a word boundary so other keys like
    // 'not_a_filename' don't work.
    if (!preg_match(static::REQUEST_HEADER_EXTENDED_FILENAME_REGEX, $content_disposition, $matches)) {
      if (!preg_match(static::REQUEST_HEADER_FILENAME_REGEX, $content_disposition, $matches)) {
        throw new BadRequestHttpException('No filename found in "Content-Disposition" header. A file name in the format "filename=FILENAME" must be provided.');
      }
    }

    // Don't validate the actual filename here, that will be done by the upload
    // validators in validate().
    // @see \Drupal\file\Plugin\rest\resource\FileUploadResource::validate()
    $filename = $matches['filename'];

    // Decode the filename if a character set is provided by an extended
    // filename. Only UTF-8 is supported.
    if (!empty($matches['charset']) && strtolower($matches['charset']) === 'utf-8') {
      $filename = rawurldecode($filename);
    }

    // Make sure only the filename component is returned. Path information is
    // stripped as per https://tools.ietf.org/html/rfc6266#section-4.3.
    // We do not need to use Drupal's FileSystem service here as we are not
    // dealing with StreamWrappers.
    return \basename($filename);
  }

}
