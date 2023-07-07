<?php

namespace Drupal\Tests\Core\Form;

use Drupal\Core\Access\AccessManager;
use Drupal\Core\Access\AccessResult;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\GeneratedUrl;
use Drupal\Core\Session\AccountProxyInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Drupal\Core\Render\Element\FormElement;
use Drupal\Core\Render\MetadataBubblingUrlGenerator;

/**
 * @coversDefaultClass \Drupal\Core\Render\Element\FormElement
 * @group Form
 */
class FormElementTest extends FormTestBase {

  /**
   * The dependency injection container.
   *
   * @var \Symfony\Component\DependencyInjection\ContainerBuilder
   */
  protected $container;

  /**
   * The Access Manager.
   *
   * @var \Drupal\Core\Access\AccessManager&\PHPUnit\Framework\MockObject\MockObject
   */
  protected $accessManager;

  /**
   * The Current User.
   *
   * @var \Drupal\Core\Session\AccountProxyInterface&\PHPUnit\Framework\MockObject\MockObject
   */
  protected $currentUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->container = new ContainerBuilder();
    $this->urlGenerator = $this->createMock(MetadataBubblingUrlGenerator::class);
    $this->container->set('url_generator', $this->urlGenerator);
    $this->accessManager = $this->createMock(AccessManager::class);
    $this->container->set('access_manager', $this->accessManager);
    $this->currentUser = $this->createMock(AccountProxyInterface::class);
    $this->container->set('current_user', $this->currentUser);
    \Drupal::setContainer($this->container);
  }

  /**
   * Tests the testProcessAutocomplete() method with options and parameters.
   */
  public function testProcessAutocomplete() {
    $autocompleteRouteName = '/autocomplete';
    $autocompleteRouteParameters = ['term' => 'foo'];
    $autocompleteRouteOptions = ['filter' => 'bar'];
    $accessManagerCheckNamedRoute =
    $this->accessManager->expects($this->any())
      ->method('checkNamedRoute');
    $accessManagerCheckNamedRoute->willReturn(AccessResult::allowed());
    $this->urlGenerator->expects($this->any())
      ->method('generateFromRoute')
      ->willReturnCallback(function ($name, $parameters = [], $options = [], $collect_bubbleable_metadata = FALSE) use ($autocompleteRouteName, $autocompleteRouteParameters, $autocompleteRouteOptions) {
        $this->assertEquals($autocompleteRouteName, $name);
        $this->assertEquals($autocompleteRouteParameters, $parameters);
        $this->assertEquals($autocompleteRouteOptions, $options);
        $generatedUrl = new GeneratedUrl();
        $generatedUrl->setGeneratedUrl($this->buildAutocompleteUrlStub($name, $parameters, $options));
        return $generatedUrl;
      });

    $elementOriginal = [
      '#autocomplete_route_name' => $autocompleteRouteName,
      '#autocomplete_route_parameters' => $autocompleteRouteParameters,
      '#autocomplete_url_options' => $autocompleteRouteOptions,
    ];
    /** @var \Drupal\Core\Form\FormStateInterface|\PHPUnit\Framework\MockObject\MockObject $formState */
    $formState = $this->createMock(FormStateInterface::class);
    $completeForm = [];

    // Testing with access allowed.
    $element = $elementOriginal;
    FormElement::processAutocomplete($element, $formState, $completeForm);
    $this->assertSame(['form-autocomplete'], $element['#attributes']['class']);
    $this->assertSame(['core/drupal.autocomplete'], $element["#attached"]["library"]);
    $this->assertSame($element['#attributes']['data-autocomplete-path'], $this->buildAutocompleteUrlStub($autocompleteRouteName, $autocompleteRouteParameters, $autocompleteRouteOptions));

    // Testing with access denied.
    $accessManagerCheckNamedRoute->willReturn(AccessResult::forbidden());
    $element = $elementOriginal;
    FormElement::processAutocomplete($element, $formState, $completeForm);
    $this->assertArrayNotHasKey('#attributes', $element);
    $this->assertEmpty($element['#attached']);
  }

  private function buildAutocompleteUrlStub($name, $parameters, $options) {
    $url = "$name?" . http_build_query([
      'parameters' => json_encode($parameters),
      'options' => json_encode($options),
    ]);
    return $url;
  }

}
