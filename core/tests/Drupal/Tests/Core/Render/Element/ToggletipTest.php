<?php

declare(strict_types=1);

namespace Drupal\Tests\Core\Render\Element;

use Drupal\FunctionalJavascriptTests\WebDriverTestBase;

// cspell:ignore toggletip popovertarget

/**
 * Tests for the toggletip element.
 *
 * @group Render
 */
class ToggletipTest extends WebDriverTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['toggletip_test'];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $user = $this->createUser(['access content']);
    $this->drupalLogin($user);
    $this->drupalGet('/toggletip-test/toggletips');
  }

  /**
   * Tests toggletip functionality.
   *
   * @param string $element_selector
   *   Selector of element the toggle tip is for.
   * @param string $text
   *   Text inside the toggle tip.
   * @param string $at_description
   *   Text of atDescription property.
   *
   *  @dataProvider toggletipContent
   */
  public function testToggletip(string $element_selector, string $text, string $at_description = 'This tests the atDescription property') {
    $page = $this->getSession()->getPage();
    $toggle_button = $page->find('css', "$element_selector > button.toggletip__toggle");
    $at_description_span = $toggle_button->find('css', 'span');
    $this->assertEquals($at_description, $at_description_span->getText());
    // Get tip id from button.
    $tip_id = $toggle_button->getAttribute('popovertarget');
    $popover_element = $page->find('css', "#$tip_id");
    // Confirm popover is not visible.
    $this->assertFalse($popover_element->isVisible(), "Popover for $element_selector should not be visible before click");
    $toggle_button->click();
    // Confirm popover is visible.
    $this->assertTrue($popover_element->isVisible(), "Popover $element_selector should be visible after click");
    // Check for text.
    $this->assertEquals($text, $popover_element->getText());
    // Click to close popover.
    $toggle_button->click();
    // Confirm popover is not visible anymore.
    $this->assertFalse($popover_element->isVisible(), "Popover for $element_selector should not be visible after clicking off it");
  }

  /**
   * Data provider for toggletip test.
   *
   * @return array
   *   Array of selectors and text content.
   */
  public function toggletipContent() {
    return [
      ['div[data-drupal-selector="edit-a-div"]',
        "CONTAINER: A movie like this is a deep mystery. It asks the question: What went wrong? \"Clifford\" is not bad on the acting, directing or even writing levels. It fails on a deeper level still, the level of the underlying conception. Something about the material itself is profoundly not funny. Irredeemably not funny, so that it doesn't matter what the actors do, because they are in a movie that should never have been made. The story opens in the year 2050, when a kindly old priest is trying to reason with a rebellious kid in a home for troubled kids. The priest (Short) tells the kid that he was once a troubled kid, himself. That sets up three flashbacks that make up most of the movie. To deal with the 2050 scenes right up front: They are completely unnecessary. Their only apparent function is to show Martin Short made up as an old man.",
      ],
      ['h1[data-drupal-selector="edit-a-h1"]',
        "H1: Now. Back to the main story, which takes place in the present. Martin Short stars as little Clifford, a brat, about 10 years old, I guess. Short plays him with no makeup other than a wig and little boy's suits, and the camera angles are selected to make him look a foot shorter than the other actors. Clifford is a little boy from hell, a sneaky practical joker, spoiled, obnoxious. We meet him with his parents on a flight to Hawaii. He wants the plane to land in Los Angeles so he can visit the Dinosaur Park amusement park. This sets up the body of the movie, in which Clifford's uncle Martin agrees to take the lad for a week, partly to convince his girlfriend that he does, indeed, like children. But no one could like this child, who grows enraged when his uncle won't take him to Dinosaur Park, and plays a series of practical jokes, beginning with filling his uncle's drink with Tabasco sauce, and ending with the destruction of his uncle's plans for the Los Angeles transportation system.",
      ],
      ['div[data-drupal-selector="edit-a-long-div"]', 'Long div toggletip content'],
      ['summary[aria-controls="edit-a-details"]', "Details: Many of the jokes are of a cruel physical nature, involving a hairpiece worn by the uncle's boss, or face-lifts, or phony bomb threats. What they boil down to is, little Clifford is mean, vindictive, spiteful and cruel. So hateful that if a real little boy had played him, the movie would be like \"The Omen\" filtered through \"The Good Son\" and a particularly bad evening of \"Saturday Night Live.\" But Martin Short is clearly not a little boy. He is a curious adult pretending to be a little boy, with odd verbal mannerisms And then there is the \"climax,\" in which Uncle Martin finally does take little Clifford to the Dinosaur Park. The movie treats the sequence as a bravura set piece, but actually it's an embarrassing assembly of shabby special effects, resulting in absolutely no comic output. At one point the movie sets up an out-of-control thrill ride, and we in the audience think we know how the laughs will build, but we're wrong. They don't."],
      ['div[data-drupal-selector="edit-added-in-markup"] > span.toggletip', "What we have here is a suitable case for deep cinematic analysis. I would love to hear a symposium of veteran producers, marketing guys and exhibitors discuss this film. It is not bad in any usual way. It is bad in a new way all its own. There is something extraterrestrial about it, as if it is based on the sense of humor of an alien race with a completely different relationship to the physical universe. The movie is so odd, it is almost worth seeing just because we will never see anything like it again. I hope.", "More info about this"],
      ['.form-item-textfield',
        "TEXTFIELD: A movie like this is a deep mystery. It asks the question: What went wrong? \"Clifford\" is not bad on the acting, directing or even writing levels. It fails on a deeper level still, the level of the underlying conception. Something about the material itself is profoundly not funny. Irredeemably not funny, so that it doesn't matter what the actors do, because they are in a movie that should never have been made. The story opens in the year 2050, when a kindly old priest is trying to reason with a rebellious kid in a home for troubled kids. The priest (Short) tells the kid that he was once a troubled kid, himself. That sets up three flashbacks that make up most of the movie. To deal with the 2050 scenes right up front: They are completely unnecessary. Their only apparent function is to show Martin Short made up as an old man.",
      ],
      ['.form-item-textarea',
        "TEXTAREA: A movie like this is a deep mystery. It asks the question: What went wrong? \"Clifford\" is not bad on the acting, directing or even writing levels. It fails on a deeper level still, the level of the underlying conception. Something about the material itself is profoundly not funny. Irredeemably not funny, so that it doesn't matter what the actors do, because they are in a movie that should never have been made. The story opens in the year 2050, when a kindly old priest is trying to reason with a rebellious kid in a home for troubled kids. The priest (Short) tells the kid that he was once a troubled kid, himself. That sets up three flashbacks that make up most of the movie. To deal with the 2050 scenes right up front: They are completely unnecessary. Their only apparent function is to show Martin Short made up as an old man.",
      ],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-left-end"]', "Toggletip content for long div custom positioned button, left-end"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-left-start"]', "Toggletip content for long div custom positioned button, left-start"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-top-start"]', "Toggletip content for long div custom positioned button, top-start"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-bottom-start"]', "Toggletip content for long div custom positioned button, bottom-start"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-left"]', "Toggletip content for long div custom positioned button, left"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-top"]', "Toggletip content for long div custom positioned button, top"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-bottom"]', "Toggletip content for long div custom positioned button, bottom"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-bottom-end"]', "Toggletip content for long div custom positioned button, bottom-end"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-right-start"]', "Toggletip content for long div custom positioned button, right-start"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-right-end"]', "Toggletip content for long div custom positioned button, right-end"],
      ['div[data-drupal-selector="edit-a-long-div-custom-positioning-right"]', "Toggletip content for long div custom positioned button, right"],
    ];

  }

}
