<?php

namespace Drupal\toggletip_test\Form;

// cspell:ignore Beale
use Drupal\Component\Serialization\Json;
use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Url;

/**
 * Provides a page for testing toggletips.
 */
class ToggletipForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    return [
      '#attached' => [
        'library' => [
          'core/drupal.toggletip',
          'core/drupal.dialog.off_canvas',
        ],
      ],
      'off_canvas_link_1' => [
        '#title' => 'Open side panel 1',
        '#type' => 'link',
        '#url' => Url::fromRoute('off_canvas_test.thing1'),
        '#attributes' => [
          'class' => ['use-ajax'],
          'data-dialog-type' => 'dialog',
          'data-dialog-renderer' => 'off_canvas',
          'data-dialog-options' => Json::encode([
            'classes' => [
              "ui-dialog" => "ui-corner-all side-1",
            ],
          ]),
        ],
      ],
      'a_div' => [
        '#type' => 'container',
        '#markup' => 'A container render array',
        '#toggletip' => [
          'content' => '<p>CONTAINER: A movie like this is a deep <a href="https://drupal.org">mystery</a>. It asks the question: What went wrong? "Clifford" is not bad on the acting, directing or even writing levels. It fails on a deeper level still, the level of the underlying conception. Something about the material itself is profoundly not funny. Irredeemably not funny, so that it doesn\'t matter what the actors do, because they are in a movie that should never have been made.</p>
            <p>The story opens in the year 2050, when a kindly old priest is trying to reason with a rebellious kid in a home for troubled kids. The priest (Short) tells the kid that he was once a troubled kid, himself. That sets up three flashbacks that make up most of the movie. To deal with the 2050 scenes right up front: They are completely unnecessary. Their only apparent function is to show Martin Short made up as an old man.</p>',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_long_div' => [
        '#type' => 'container',
        '#markup' => '<p>Long, default. A container render array with longer content, default positioning.</p><p>You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear?</p><p>You think you\'ve merely stopped a business deal. That is not the case!</p>',
        '#toggletip' => [
          'content' => 'Long div toggletip content',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_h1' => [
        '#type' => 'html_tag',
        '#tag' => 'h1',
        '#value' => 'Toggletip on html tag h1',
        '#attributes' => [
          'style' => 'font-size: 4rem',
        ],
        '#toggletip' => [
          'content' => '<p>H1: Now. Back to the main story, which takes place in the present. Martin Short stars as little Clifford, a brat, about 10 years old, I guess. Short plays him with no makeup other than a wig and little boy\'s suits, and the camera angles are selected to make him look a foot shorter than the other actors. Clifford is a little boy from hell, a sneaky practical joker, spoiled, obnoxious. We meet him with his parents on a flight to Hawaii. He wants the plane to land in Los Angeles so he can visit the Dinosaur Park amusement park.</p>
            <p>This sets up the body of the movie, in which Clifford\'s uncle Martin agrees to take the lad for a week, partly to convince his girlfriend that he does, indeed, like children. But no one could like this child, who grows enraged when his uncle won\'t take him to Dinosaur Park, and plays a series of practical jokes, beginning with filling his uncle\'s drink with Tabasco sauce, and ending with the destruction of his uncle\'s plans for the Los Angeles transportation system.</p>',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_h2' => [
        '#type' => 'html_tag',
        '#tag' => 'h2',
        '#value' => 'Toggletip on html tag h2',
        '#toggletip' => [
          'content' => '<p>H2: Now. Back to the main story, which takes place in the present. Martin Short stars as little Clifford, a brat, about 10 years old, I guess. Short plays him with no makeup other than a wig and little boy\'s suits, and the camera angles are selected to make him look a foot shorter than the other actors. Clifford is a little boy from hell, a sneaky practical joker, spoiled, obnoxious. We meet him with his parents on a flight to Hawaii. He wants the plane to land in Los Angeles so he can visit the Dinosaur Park amusement park.</p>
            <p>This sets up the body of the movie, in which Clifford\'s uncle Martin agrees to take the lad for a week, partly to convince his girlfriend that he does, indeed, like children. But no one could like this child, who grows enraged when his uncle won\'t take him to Dinosaur Park, and plays a series of practical jokes, beginning with filling his uncle\'s drink with Tabasco sauce, and ending with the destruction of his uncle\'s plans for the Los Angeles transportation system.</p>',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_h3' => [
        '#type' => 'html_tag',
        '#tag' => 'h3',
        '#value' => 'Toggletip on html tag h3',
        '#toggletip' => [
          'content' => '<p>H3: Now. Back to the main story, which takes place in the present. Martin Short stars as little Clifford, a brat, about 10 years old, I guess. Short plays him with no makeup other than a wig and little boy\'s suits, and the camera angles are selected to make him look a foot shorter than the other actors. Clifford is a little boy from hell, a sneaky practical joker, spoiled, obnoxious. We meet him with his parents on a flight to Hawaii. He wants the plane to land in Los Angeles so he can visit the Dinosaur Park amusement park.</p>
            <p>This sets up the body of the movie, in which Clifford\'s uncle Martin agrees to take the lad for a week, partly to convince his girlfriend that he does, indeed, like children. But no one could like this child, who grows enraged when his uncle won\'t take him to Dinosaur Park, and plays a series of practical jokes, beginning with filling his uncle\'s drink with Tabasco sauce, and ending with the destruction of his uncle\'s plans for the Los Angeles transportation system.</p>',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_details' => [
        '#type' => 'details',
        '#title' => 'Toggletip on details',
        '#toggletip' => [
          'content' => '<p>Details: Many of the jokes are of a cruel physical nature, involving a hairpiece worn by the uncle\'s boss, or face-lifts, or phony bomb threats. What they boil down to is, little Clifford is mean, vindictive, spiteful and cruel. So hateful that if a real little boy had played him, the movie would be like "The Omen" filtered through "The Good Son" and a particularly bad evening of "Saturday Night Live." But Martin Short is clearly not a little boy. He is a curious adult pretending to be a little boy, with odd verbal mannerisms</p>
            <p>And then there is the "climax," in which Uncle Martin finally does take little Clifford to the Dinosaur Park. The movie treats the sequence as a bravura set piece, but actually it\'s an embarrassing assembly of shabby special effects, resulting in absolutely no comic output. At one point the movie sets up an out-of-control thrill ride, and we in the audience think we know how the laughs will build, but we\'re wrong. They don\'t.</p>',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'added_in_markup' => [
        '#type' => 'container',
        'text' => [
          '#markup' => 'JS attribute: Sometimes for placeholder text we use <span data-drupal-toggletip="{&quot;content&quot;:&quot;\u003Cp\u003EWhat we have here is a suitable case for deep cinematic analysis. I would love to hear a symposium of veteran producers, marketing guys and exhibitors discuss this film. It is not bad in any usual way. It is bad in a new way all its own. There is something extraterrestrial about it, as if it is based on the sense of humor of an alien race with a completely different relationship to the physical universe. The movie is so odd, it is almost worth seeing just because we will never see anything like it again. I hope.\u003C\/p\u003E&quot;,&quot;rendered&quot;:true}">IPSUM!</span>, and that is nice.',
        ],
      ],
      'textfield' => [
        '#type' => 'textfield',
        '#title' => 'A Textfield',
        '#maxlength' => '254',
        '#toggletip' => [
          'content' => '<p>TEXTFIELD: A movie like this is a deep mystery. It asks the question: What went wrong? "Clifford" is not bad on the acting, directing or even writing levels. It fails on a deeper level still, the level of the underlying conception. Something about the material itself is profoundly not funny. Irredeemably not funny, so that it doesn\'t matter what the actors do, because they are in a movie that should never have been made.</p>
            <p>The story opens in the year 2050, when a kindly old priest is trying to reason with a rebellious kid in a home for troubled kids. The priest (Short) tells the kid that he was once a troubled kid, himself. That sets up three flashbacks that make up most of the movie. To deal with the 2050 scenes right up front: They are completely unnecessary. Their only apparent function is to show Martin Short made up as an old man.</p>',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'textarea' => [
        '#type' => 'textarea',
        '#title' => 'A Textarea',
        '#cols' => '80',
        '#rows' => '20',
        '#toggletip' => [
          'content' => '<p>TEXTAREA: A movie like this is a deep mystery. It asks the question: What went wrong? "Clifford" is not bad on the acting, directing or even writing levels. It fails on a deeper level still, the level of the underlying conception. Something about the material itself is profoundly not funny. Irredeemably not funny, so that it doesn\'t matter what the actors do, because they are in a movie that should never have been made.</p>
            <p>The story opens in the year 2050, when a kindly old priest is trying to reason with a rebellious kid in a home for troubled kids. The priest (Short) tells the kid that he was once a troubled kid, himself. That sets up three flashbacks that make up most of the movie. To deal with the 2050 scenes right up front: They are completely unnecessary. Their only apparent function is to show Martin Short made up as an old man.</p>',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_long_div_custom_positioning_right_end' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned right-end. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, right-end',
          'place' => 'right-end',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_long_div_custom_positioning_left_end' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned left-end. A container render array with longer content.You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, left-end',
          'place' => 'left-end',
          'atDescription' => 'This tests the atDescription property',
        ],
        '#attributes' => [
          'style' => 'margin-left: 1.5rem',
        ],
      ],
      'a_long_div_custom_positioning_right_start' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned right-start. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, right-start',
          'place' => 'right-start',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_long_div_custom_positioning_left_start' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned left-start. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, left-start',
          'place' => 'left-start',
          'atDescription' => 'This tests the atDescription property',
        ],
        '#attributes' => [
          'style' => 'margin-left: 1.5rem',
        ],
      ],
      'a_long_div_custom_positioning_top_end' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned top-end. A container render array with longer content.You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, top-end',
          'place' => 'top-end',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_long_div_custom_positioning_top_start' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned top-start. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, top-start',
          'place' => 'top-start',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_long_div_custom_positioning_bottom_end' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned bottom-end. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, bottom-end',
          'place' => 'bottom-end',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],

      'a_long_div_custom_positioning_bottom_start' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned bottom-start. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, bottom-start',
          'place' => 'bottom-start',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],

      'a_long_div_custom_positioning_top' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned top. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, top',
          'place' => 'top',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],

      'a_long_div_custom_positioning_bottom' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned bottom. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, bottom',
          'place' => 'bottom',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],

      'a_long_div_custom_positioning_left' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned left. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, left',
          'place' => 'left',
          'atDescription' => 'This tests the atDescription property',
        ],
        '#attributes' => [
          'style' => 'margin-left: 1.5rem',
        ],
      ],

      'a_long_div_custom_positioning_right' => [
        '#type' => 'container',
        '#markup' => '<p>Long, positioned left. A container render array with longer content. You have meddled with the primal forces of nature, Mr. Beale, and I won\'t have it! Is that clear? You think you\'ve merely stopped a business deal. That is not the case! They get out their linear programming charts, statistical decision theories, minimax solutions, and compute the price-cost probabilities of their transactions and investments, just like we do. We no longer live in a world of nations and ideologies, Mr. Beale. The world is a college of corporations, inexorably determined by the immutable by-laws of business. The world is a business, Mr. Beale. It has been since man crawled out of the slime. And our children will live, Mr. Beale, to see that perfect world in which there\'s no war or famine, oppression or brutality. One vast and ecumenical holding company, for whom all men will work to serve a common profit, in which all men will hold a share of stock. All necessities provided, all anxieties tranquilized, all boredom amused. And I have chosen you, Mr. Beale, to preach this.</p>',
        '#toggletip' => [
          'content' => 'Toggletip content for long div custom positioned button, right',
          'place' => 'right',
          'atDescription' => 'This tests the atDescription property',
        ],
      ],
      'a_trigger' => [
        '#type' => 'container',
        '#allowed_tags' => ['button'],
        '#markup' => '<button data-drupal-tooltip-toggle-button="Tooltip Text" type="button" popovertarget="very_unical_id">This is tooltip trigger(can be any trigger)</button>',
      ],
      'a_trigger_with_placement' => [
        '#type' => 'container',
        '#allowed_tags' => ['button'],
        '#markup' => '<button data-drupal-tooltip-toggle-button="Tooltip Text" data-drupal-tooltip-placement="bottom" type="button" popovertarget="very_very_unical_id">And this places tooltip on bottom</button>',
      ],
      'a_trigger_with_hover' => [
        '#type' => 'container',
        '#allowed_tags' => ['button'],
        '#markup' => '<button data-drupal-tooltip-toggle-button="Tooltip Text" data-drupal-tooltip-on-hover="true" type="button" popovertarget="very_very_very_unical_id">And this one works on hover</button>',
      ],
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'toggletip_test_form';
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
  }

}
