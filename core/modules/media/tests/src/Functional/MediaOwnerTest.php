<?php

namespace Drupal\Tests\media\Functional;

/**
 * Tests media owner functionality.
 *
 * @group media
 */
class MediaOwnerTest extends MediaFunctionalTestBase {

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * Test resaving unchanged media entity through UI with deleted owner.
   *
   * The owning user will be deleted programmatically without deleting the media
   * entity nor asssigning it to the anonymous (uid = 0) user.
   */
  public function testProgrammaticallyDeletedMediaOwner() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $media_storage */
    $media_storage = $this->container->get('entity_type.manager')
      ->getStorage('media');
    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $user_storage */
    $user_storage = $this->container->get('entity_type.manager')
      ->getStorage('user');

    $this->drupalLogin($this->nonAdminUser);

    // Create a media type and media item owned by nonAdminUser.
    $media_type = $this->createMediaType('test');
    $media = $media_storage->create([
      'bundle' => $media_type->id(),
      'name' => 'Unnamed',
      'field_media_test' => 'Empty',
    ]);
    $media->save();

    $this->drupalLogout();

    // Delete nonAdminUser programmatically without deleting the created media
    // item or assigning it to anonymous.
    $user_storage->load($this->nonAdminUser->id())->delete();

    // Login as adminUser.
    $this->drupalLogin($this->adminUser);

    // Try to go to 'media/[mid]/edit'. This page should not exist anymore.
    $this->drupalGet('media/' . $media->id() . '/edit');
    $assert_session->statusCodeEquals(404);
  }

  /**
   * Test resaving unchanged media entity through UI with deleted owner.
   *
   * The owning user will be deleting its own account through the UI whilst its
   * content will be deleted.
   */
  public function testUserCancelDeleteMediaOwner() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $media_storage */
    $media_storage = $this->container->get('entity_type.manager')
      ->getStorage('media');
    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $user_storage */
    $user_storage = $this->container->get('entity_type.manager')
      ->getStorage('user');

    // Create a user.
    $account = $this->drupalCreateUser(['cancel account']);
    $this->drupalLogin($account);

    // Load a real user object.
    $user_storage->resetCache([$account->id()]);
    $account = $user_storage->load($account->id());

    // Create a media type and media item owned by the newly created user.
    $media_type = $this->createMediaType('test');
    $media = $media_storage->create([
      'bundle' => $media_type->id(),
      'name' => 'Unnamed',
      'field_media_test' => 'Empty',
    ]);
    $media->save();

    // Set the default option for deleting users to "Delete the account and its
    // content".
    $this->config('user.settings')
      ->set('cancel_method', 'user_cancel_delete')
      ->save();

    // Attempt to cancel account.
    $this->drupalGet('user/' . $account->id() . '/edit');
    $this->drupalPostForm(NULL, NULL, t('Cancel account'));

    // Confirm account cancellation.
    $timestamp = time();
    $this->drupalPostForm(NULL, NULL, t('Cancel account'));

    // Confirm account cancellation request.
    $this->drupalGet("user/" . $account->id() . "/cancel/confirm/$timestamp/" . user_pass_rehash($account, $timestamp));
    $this->loggedInUser = FALSE;

    // Login as adminUser.
    $this->drupalLogin($this->adminUser);

    // Try to go to 'media/[mid]/edit'. This page should not exist anymore.
    $this->drupalGet('media/' . $media->id() . '/edit');
    $assert_session->statusCodeEquals(404);
  }

  /**
   * Test resaving unchanged media entity through UI with deleted owner.
   *
   * The owning user will be deleting its own account through the UI whilst its
   * content will be reassigned to uid = 0.
   */
  public function testUserCancelReassignMediaOwner() {
    $session = $this->getSession();
    $page = $session->getPage();
    $assert_session = $this->assertSession();

    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $media_storage */
    $media_storage = $this->container->get('entity_type.manager')
      ->getStorage('media');
    /** @var \Drupal\Core\Entity\Sql\SqlContentEntityStorage $user_storage */
    $user_storage = $this->container->get('entity_type.manager')
      ->getStorage('user');

    // Create a user.
    $account = $this->drupalCreateUser(['cancel account']);
    $this->drupalLogin($account);

    // Load a real user object.
    $user_storage->resetCache([$account->id()]);
    $account = $user_storage->load($account->id());

    // Create a media type and media item owned by the newly created user.
    $media_type = $this->createMediaType('test');
    $media = $media_storage->create([
      'bundle' => $media_type->id(),
      'name' => 'Unnamed',
      'field_media_test' => 'Empty',
    ]);
    $media->save();

    // Set the default option for deleting users to "Delete the account and make
    // its content belong to the Anonymous user".
    $this->config('user.settings')
      ->set('cancel_method', 'user_cancel_reassign')
      ->save();

    // Attempt to cancel account.
    $this->drupalGet('user/' . $account->id() . '/edit');
    $this->drupalPostForm(NULL, NULL, t('Cancel account'));

    // Confirm account cancellation.
    $timestamp = time();
    $this->drupalPostForm(NULL, NULL, t('Cancel account'));

    // Confirm account cancellation request.
    $this->drupalGet("user/" . $account->id() . "/cancel/confirm/$timestamp/" . user_pass_rehash($account, $timestamp));
    $this->loggedInUser = FALSE;

    // Login as adminUser.
    $this->drupalLogin($this->adminUser);

    // Go to 'media/[mid]/edit' and save.
    $this->drupalGet('media/' . $media->id() . '/edit');
    $page->pressButton('Save');

    // Saving an unchanged Media item should be OK.
    $assert_session->statusCodeEquals(200);
  }

}
