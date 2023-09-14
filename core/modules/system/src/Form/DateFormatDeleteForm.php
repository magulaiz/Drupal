<?php

namespace Drupal\system\Form;

use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityDeleteForm;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Builds a form to delete a date format.
 *
 * @internal
 */
class DateFormatDeleteForm extends EntityDeleteForm {

  /**
   * Constructs a DateFormatDeleteForm object.
   *
   * @param \Drupal\Core\Datetime\DateFormatterInterface $dateFormatter
   *   The date formatter service.
   */
  public function __construct(protected DateFormatterInterface $dateFormatter)
  {
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('date.formatter')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function getQuestion() {
    return $this->t('Are you sure you want to delete the format %name : %format?', [
      '%name' => $this->entity->label(),
      '%format' => $this->dateFormatter->format(REQUEST_TIME, $this->entity->id()),
    ]);
  }

}
