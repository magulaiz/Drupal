<?php

namespace Drupal\Tests\media\Kernel;

use Drupal\Core\Queue\QueueInterface;
use Drupal\language\Entity\ConfigurableLanguage;
use Drupal\media\Entity\Media;
use Drupal\media\Plugin\QueueWorker\ThumbnailDownloader;

/**
 * Tests media translation thumbnail downloader logic.
 *
 * @group media
 */
class MediaThumbnailDownloaderTest extends MediaKernelTestBase {

  /**
   * Modules to install.
   *
   * @var array
   */
  protected static $modules = ['language' , 'content_translation'];

  /**
   * The test media translation type.
   *
   * @var \Drupal\media\MediaTypeInterface
   */
  protected $testTranslationMediaType;

  /**
   * The system under test.
   *
   * @var \Drupal\media\Plugin\QueueWorker\ThumbnailDownloader
   */
  private ThumbnailDownloader $sut;

  /**
   * The Queue where we assert items are created/stored.
   *
   * @var \Drupal\Core\Queue\QueueInterface
   */
  private QueueInterface $queue;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $this->installConfig(['language']);

    // Create a test media type for translations,
    // which uses the thumbnail queue downloader.
    $this->testTranslationMediaType = $this->createMediaType('test_translation', ['queue_thumbnail_downloads' => TRUE]);

    for ($i = 0; $i < 3; ++$i) {
      $language_id = 'l' . $i;
      ConfigurableLanguage::create([
        'id' => $language_id,
        'label' => $this->randomString(),
      ])->save();
      file_put_contents('public://' . $language_id . '.png', '');
    }

    $this->sut = new ThumbnailDownloader([], '', [], \Drupal::entityTypeManager());
    $this->queue = \Drupal::queue('media_entity_thumbnail');
  }

  /**
   * Tests thumbnail downloader queue and media translations interaction.
   */
  public function testThumbnailDownloaderQueueWithMediaTranslations() {
    // Prepare the field translations.
    $source_field_definition = $this->testTranslationMediaType->getSource()->getSourceFieldDefinition($this->testTranslationMediaType);
    $source_field_storage = $source_field_definition->getFieldStorageDefinition();
    /** @var \Drupal\media\Entity\Media $media */
    $media = Media::create([
      'bundle' => $this->testTranslationMediaType->id(),
      'name' => $this->randomString(),
    ]);

    $field_translations = [];
    $available_langcodes = array_keys($this->container->get('language_manager')->getLanguages());
    $media->set('langcode', reset($available_langcodes));
    foreach ($available_langcodes as $langcode) {
      $values = [];
      for ($i = 0; $i < $source_field_storage->getCardinality(); $i++) {
        $values[$i]['value'] = $this->randomString();
      }
      $field_translations[$langcode] = $values;
      $translation = $media->hasTranslation($langcode) ? $media->getTranslation($langcode) : $media->addTranslation($langcode);
      $translation->{$source_field_definition->getName()}->setValue($field_translations[$langcode]);
    }
    $media->save();

    // Creating media with translations would result in queue items for each language the first time around.
    $this->assertEquals(4, $this->queue->numberOfItems());
    // This is generally what happens during cron, albeit somewhat simplified.
    $item = $this->queue->claimItem();
    $this->sut->processItem($item->data);
    $this->queue->deleteItem($item);

    // So, after processing/deleting it would make sense to only have 3 items left in the queue.
    // But, because the queue also saves the media, this results in more items being added.
    // One item for each translation is added.
    $this->assertEquals(3, $this->queue->numberOfItems());
  }

}
