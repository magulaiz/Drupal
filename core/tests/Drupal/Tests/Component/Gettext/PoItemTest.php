<?php

declare(strict_types=1);

namespace Drupal\Tests\Component\Gettext {

  use Drupal\Component\Gettext\PoItem;
  use PHPUnit\Framework\TestCase;

  /**
   * Unit tests for the Gettext PO items.
   *
   * @coversDefaultClass \Drupal\Component\Gettext\PoItem
   * @group gettext
   */
  class PoItemTest extends TestCase {

    /**
     * PO Item test variable.
     *
     * @var \Drupal\Component\Gettext\PoItem
     */
    protected $poItem;

    /**
     * {@inheritdoc}
     */
    public function setUp(): void {
      $this->poItem = new PoItem();
    }

    /**
     * @covers ::getLangcode
     * @covers ::setLangcode
     */
    public function testGetSetLangcode() {
      $langcode = 'es';
      $this->poItem->setLangcode($langcode);
      $this->assertEquals($langcode, $this->poItem->getLangcode());
    }

    /**
     * @covers ::getContext
     * @covers ::setContext
     */
    public function testGetSetContext() {
      $context = 'context';
      $this->poItem->setContext($context);
      $this->assertEquals($context, $this->poItem->getContext());
    }

    /**
     * @covers ::getSource
     * @covers ::setSource
     */
    public function testGetSetSource() {
      $source = 'Source string';
      $this->poItem->setSource($source);
      $this->assertEquals($source, $this->poItem->getSource());
    }

    /**
     * @covers ::getTranslation
     * @covers ::setTranslation
     */
    public function testGetSetTranslation() {
      $translation = 'Translated string';
      $this->poItem->setTranslation($translation);
      $this->assertEquals($translation, $this->poItem->getTranslation());
    }

    /**
     * @covers ::isPlural
     * @covers ::setPlural
     */
    public function testPlural() {
      $this->poItem->setPlural(FALSE);
      $this->assertFalse($this->poItem->isPlural());

      $this->poItem->setPlural(TRUE);
      $this->assertTrue($this->poItem->isPlural());
    }

    /**
     * @covers ::getComment
     * @covers ::setComment
     */
    public function testGetSetComment() {
      $comment = 'Translation comment';
      $this->poItem->setComment($comment);
      $this->assertEquals($comment, $this->poItem->getComment());
    }

    /**
     * @covers ::setFromArray
     * @covers ::getContext
     * @covers ::getSource
     * @covers ::getTranslation
     * @covers ::getComment
     * @covers ::isPlural
     * @covers ::__toString
     * @covers ::formatItem
     * @dataProvider setFromArrayDataProvider
     */
    public function testSetFromArray($values, $expectedOutput, $expectedIsPlural) {
      $this->poItem->setFromArray($values);

      $source = $expectedIsPlural ? explode(\DELIMITER, $values['source']) : $values['source'];
      $translation = $expectedIsPlural ? explode(\DELIMITER, $values['translation']) : $values['translation'];

      $this->assertEquals($values['context'], $this->poItem->getContext());
      $this->assertEquals($source, $this->poItem->getSource());
      $this->assertEquals($translation, $this->poItem->getTranslation());
      $this->assertEquals($values['comment'], $this->poItem->getComment());
      $this->assertEquals($expectedOutput, (string) $this->poItem);
    }

    /**
     * Data provider for testSetFromArray.
     */
    public function setFromArrayDataProvider() {
      return [
        [
          [
            'context' => 'context',
            'source' => 'Source string',
            'translation' => 'Translated string',
            'comment' => 'A comment',
          ],
          'msgctxt "context"' . "\n" . 'msgid "Source string"' . "\n" . 'msgstr "Translated string"' . "\n\n",
          FALSE,
        ],
        [
          [
            'context' => 'context',
            'source' => 'Source string' . \DELIMITER . 'Source strings',
            'translation' => 'Translated string' . \DELIMITER . 'Source translations',
            'comment' => 'A comment',
          ],
          'msgctxt "context"' . "\n" . 'msgid "Source string"' . "\n" . 'msgid_plural "Source strings"' . "\n" . 'msgstr[0] "Translated string"' . "\n" . 'msgstr[1] "Source translations"' . "\n\n",
          TRUE,
        ],
      ];
    }

  }
}

namespace {
  if (!defined('DELIMITER')) {
    define('DELIMITER', "\03");
  }
}
