<?php

declare(strict_types=1);

namespace Drupal\Tests\contextual\FunctionalJavascript;

/**
 * Functions for testing contextual links.
 */
trait ContextualLinkClickTrait {

  /**
   * Clicks a contextual link.
   *
   * @param string $selector
   *   The selector for the element that contains the contextual link.
   * @param string $link_locator
   *   The link id, title, or text.
   * @param bool $force_visible
   *   If true then the button will be forced to visible so it can be clicked.
   */
  protected function clickContextualLink($selector, $link_locator, $force_visible = TRUE) {
    $link = $this->getContextualLink($selector, $link_locator);
    $this->assertNotNull($link);
    $link->click();

    if ($force_visible) {
      $this->toggleContextualTriggerVisibility($selector);
    }
  }

  /**
   * Get a contextual link with given CSS selector and operation.
   *
   * @param string $selector
   *   The selector for the element that contains the contextual link.
   * @param string $link_locator
   *   The link id, title, or text.
   * @param bool $force_visible
   *   If true then the button will be forced to visible so it can be clicked.
   *
   * @return \Behat\Mink\Element\NodeElement|null
   *   The link or NULL if not found.
   */
  protected function getContextualLink($selector, $link_locator, $force_visible = TRUE) {
    $page = $this->getSession()->getPage();
    $page->waitFor(10, function () use ($page, $selector) {
      return $page->find('css', "$selector .contextual-links");
    });

    if ($force_visible) {
      $this->toggleContextualTriggerVisibility($selector);
    }

    $element = $this->getSession()->getPage()->find('css', $selector);
    $element->find('css', '.contextual button')->press();
    return $element->findLink($link_locator);
  }

  /**
   * Toggles the visibility of a contextual trigger.
   *
   * @param string $selector
   *   The selector for the element that contains the contextual link.
   */
  protected function toggleContextualTriggerVisibility($selector) {
    // Hovering over the element itself with should be enough, but does not
    // work. Manually remove the visually-hidden class.
    $this->getSession()->executeScript("jQuery('{$selector} .contextual .trigger').toggleClass('visually-hidden');");
  }

}
