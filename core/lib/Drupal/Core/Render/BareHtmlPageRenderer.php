<?php

namespace Drupal\Core\Render;

/**
 * Default bare HTML page renderer.
 */
class BareHtmlPageRenderer implements BareHtmlPageRendererInterface {

  /**
   * Constructs a new BareHtmlPageRenderer.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Render\AttachmentsResponseProcessorInterface $htmlResponseAttachmentsProcessor
   *   The HTML response attachments processor service.
   */
  public function __construct(protected RendererInterface $renderer, protected AttachmentsResponseProcessorInterface $htmlResponseAttachmentsProcessor)
  {
  }

  /**
   * {@inheritdoc}
   */
  public function renderBarePage(array $content, $title, $page_theme_property, array $page_additions = []) {
    $attributes = [
      'class' => [
        str_replace('_', '-', $page_theme_property),
      ],
    ];
    $html = [
      '#type' => 'html',
      '#attributes' => $attributes,
      'page' => [
        '#type' => 'page',
        '#theme' => $page_theme_property,
        '#title' => $title,
        'content' => $content,
      ] + $page_additions,
    ];

    // For backwards compatibility.
    // @todo In Drupal 9, add a $show_messages function parameter.
    if (!isset($page_additions['#show_messages']) || $page_additions['#show_messages'] === TRUE) {
      $html['page']['highlighted'] = ['#type' => 'status_messages'];
    }

    // Add the bare minimum of attachments from the system module and the
    // current maintenance theme.
    system_page_attachments($html['page']);
    $this->renderer->renderRoot($html);

    $response = new HtmlResponse();
    $response->setContent($html);
    // Process attachments, because this does not go via the regular render
    // pipeline, but will be sent directly.
    $response = $this->htmlResponseAttachmentsProcessor->processAttachments($response);
    return $response;
  }

}
