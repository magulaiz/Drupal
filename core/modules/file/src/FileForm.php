<?php

namespace Drupal\file;

use Drupal\Component\Datetime\TimeInterface;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\ContentEntityForm;
use Drupal\Core\Entity\EntityRepositoryInterface;
use Drupal\Core\Entity\EntityTypeBundleInfoInterface;
use Drupal\Core\File\FileExists;
use Drupal\Core\File\FileSystemInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Image\ImageFactory;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Form handler for the file edit forms.
 *
 * @internal
 */
class FileForm extends ContentEntityForm {

  public function __construct(
    EntityRepositoryInterface $entity_repository,
    EntityTypeBundleInfoInterface $entity_type_bundle_info,
    TimeInterface $time,
    protected AccountInterface $current_user,
    protected DateFormatterInterface $date_formatter,
    protected FileSystemInterface $fileSystem,
    protected ImageFactory $imageFactory,
  ) {
    parent::__construct($entity_repository, $entity_type_bundle_info, $time);

  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity.repository'),
      $container->get('entity_type.bundle.info'),
      $container->get('datetime.time'),
      $container->get('current_user'),
      $container->get('date.formatter'),
      $container->get('file_system'),
      $container->get('image.factory'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function form(array $form, FormStateInterface $form_state) {
    /** @var \Drupal\file\FileInterface $file */
    $file = $this->entity;

    if ($this->operation == 'edit') {
      $form['#title'] = $this->t('<em>Edit</em> @title', [
        '@title' => $file->label(),
      ]);
    }

    // Changed must be sent to the client, for later overwrite error checking.
    $form['changed'] = [
      '#type' => 'hidden',
      '#default_value' => $file->getChangedTime(),
    ];

    $form['replacement_file'] = [
      '#type' => 'file',
      '#title' => $this->t('Replace file'),
      '#required' => TRUE,
    ];

    $form = parent::form($form, $form_state);

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function validateForm(array &$form, FormStateInterface $form_state) {
    /** @var \Drupal\file\FileInterface $originalFile */
    $originalFile = parent::validateForm($form, $form_state);
    $replacementFile = file_save_upload('replacement_file');
    if (is_array($replacementFile) && $replacementFile[0] !== FALSE && count($replacementFile) > 0) {
      $replacementFile = reset($replacementFile);

      // If file mime type do not match the existing file, set an error.
      if ($originalFile->getMimeType() !== $replacementFile->getMimeType()) {
        $form_state->setErrorByName('replacement_file', $this->t('The uploaded file is not the same type as the existing file.'));
        $replacementFile->delete();
      }
      else {
        $form_state->set('replacement_file', $replacementFile);
      }
    }
    else {
      $form_state->set('replacement_file', NULL);
    }
    return $originalFile;
  }

  /**
   * {@inheritdoc}
   */
  public function save(array $form, FormStateInterface $form_state) {
    assert($this->entity instanceof FileInterface, '\Drupal\file\FileInterface instance expected.');

    $replacementFile = $form_state->get('replacement_file');

    if (!$replacementFile) {
      return parent::save($form, $form_state);
    }

    $this->fileSystem->copy($replacementFile->getFileUri(), $this->entity->getFileUri(), FileExists::Replace);
    $this->entity->setMimeType($replacementFile->getMimeType());
    $this->entity->setSize($replacementFile->getSize());

    $replacementFile->delete();

    if ($this->moduleHandler->moduleExists('image')) {
      $image = $this->imageFactory->get($this->entity->getFileUri());
      if ($image->isValid()) {
        image_path_flush($this->entity->getFileUri());
      }
    }
    return parent::save($form, $form_state);
  }

}
