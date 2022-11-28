<?php

declare(strict_types = 1);

namespace Drupal\Tests\taxonomy\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\taxonomy\Entity\Term;
use Drupal\Tests\taxonomy\Traits\TaxonomyTestTrait;

/**
 * Taxonomy term revision form test.
 *
 * @group taxonomy
 * @coversDefaultClass \Drupal\Core\Entity\Form\RevisionRevertForm
 */
class TaxonomyContentRevisionRevertTest extends BrowserTestBase {

  use TaxonomyTestTrait;

  /**
   * {@inheritdoc}
   */
  protected static $modules = [
    'system',
    'taxonomy',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * {@inheritdoc}
   */
  protected $permissions = [
    'view terms revisions in test',
    'revert all taxonomy revisions',
  ];

  /**
   * Vocabulary for testing.
   *
   * @var \Drupal\taxonomy\VocabularyInterface
   */
  protected $vocabulary;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->vocabulary = $this->createVocabulary('test');
  }


  /**
   * Tests revision revert.
   */
  public function testRevertForm(): void {
    $entity = Term::create([
      'vid' => $this->vocabulary->id(),
      'name' => 'Test taxonomy term',
    ]);

    $entity->setRevisionCreationTime((new \DateTimeImmutable('11 January 2009 4pm'))->getTimestamp())
      ->setRevisionTranslationAffected(TRUE);
    $entity->setNewRevision();
    $entity->save();
    $revisionId = $entity->getRevisionId();

    // Cannot revert latest revision.
    $this->drupalGet($entity->toUrl('revision-revert-form'));
    $this->assertSession()->statusCodeEquals(403);

    // Create a new latest revision.
    $entity
      ->setRevisionCreationTime((new \DateTimeImmutable('11 January 2009 5pm'))->getTimestamp())
      ->setRevisionTranslationAffected(TRUE)
      ->setNewRevision();
    $entity->save();

    // Reload the entity.
    $revision = \Drupal::entityTypeManager()->getStorage('taxonomy_term')
      ->loadRevision($revisionId);
    $this->drupalGet($revision->toUrl('revision-revert-form'));
    $this->assertSession()->pageTextContains('Are you sure you want to revert to the revision from Sun, 01/11/2009 - 16:00?');
    $this->assertSession()->buttonExists('Revert');
    $this->assertSession()->linkExists('Cancel');

    $countRevisions = static function (): int {
      return (int) \Drupal::entityTypeManager()->getStorage('taxonomy_term')
        ->getQuery()
        ->accessCheck(FALSE)
        ->allRevisions()
        ->count()
        ->execute();
    };

    $count = $countRevisions();
    $this->submitForm([], 'Revert');
    $this->assertEquals($count + 1, $countRevisions());
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->addressEquals(sprintf('taxonomy/term/%s/revisions', $entity->id()));
    $this->assertSession()->pageTextContains(sprintf('term \'%s\' has been reverted to the revision from Sun, 01/11/2009 - 16:00.', $entity->label()));
    $this->assertSession()->elementsCount('css', 'table tbody tr', 3);
  }

}
