<?php

namespace Drupal\Tests\jsonapi\Traits;

use Drupal\Component\Serialization\Json;
use Drupal\Tests\jsonapi\Functional\ResourceTestBase;
use Psr\Http\Message\ResponseInterface;

trait GetDocumentFromRequestTrait {

  /**
   *  @param ReponseInterface $response Reponse to extract JSON:API document from.
   *  @param bool $dataRequired Validate the data property is available in the response.
   *
   *  @return array
   *    JSON:API document extracted from the response.
   **/
  protected function getDocumentFromResponse(ResponseInterface $response, bool $dataRequired = TRUE): array {
    assert($this instanceof ResourceTestBase);

    $doc = Json::decode((string) $response->getBody());

    if ($dataRequired === TRUE && !isset($document['data'])) {
      if (isset($document['errors'])) {
        $errors = [];
        foreach($document['errors'] as $error) {
          $errors[] = $error['title'] . ': ' . $error['detail'];
        }
        $this->fail('Missing expected data property in document. Errors: ' . implode(', ', $errors));
      }
      $this->fail('Missing expected data property in document but no errors found.');
    }
    return $doc;
  }

}
