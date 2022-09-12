<?php

namespace Drupal\dblog;

use Drupal\Component\Utility\UrlHelper;
use Drupal\Core\Datetime\DateFormatterInterface;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Entity\EntityStorageInterface;
use Drupal\Core\Entity\EntityTypeInterface;
use Drupal\Core\Link;
use Drupal\Core\Logger\RfcLogLevel;
use Drupal\Core\Render\RendererInterface;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\Core\Url;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\dblog\Entity\DblogEntryInterface;
use Drupal\Core\Entity\EntityViewBuilder;

/**
 * Provides a dblog entry view builder.
 */
class DblogEntryViewBuilder extends EntityViewBuilder {

  use StringTranslationTrait;

  /**
   * The renderer.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * The user storage.
   *
   * @var \Drupal\user\UserStorageInterface
   */
  protected $userStorage;

  /**
   * The date formatter service.
   *
   * @var \Drupal\Core\Datetime\DateFormatterInterface
   */
  protected $dateFormatter;

  /**
   * The dblog formatter service.
   *
   * @var \Drupal\dblog\DblogFormatterInterface
   */
  protected $dblogFormatter;

  /**
   * Constructs a new dblog entry view builder.
   *
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The renderer service.
   * @param \Drupal\Core\Entity\EntityStorageInterface $user_storage
   *   The user storage service.
   * @param \Drupal\Core\Datetime\DateFormatterInterface $date_formatter
   *   The date formatter service.
   * @param \Drupal\dblog\DblogFormatterInterface $dblog_formatter
   *   The dblog formatter service.
   */
  public function __construct(RendererInterface $renderer, EntityStorageInterface $user_storage, DateFormatterInterface $date_formatter, DblogFormatterInterface $dblog_formatter) {
    $this->renderer = $renderer;
    $this->userStorage = $user_storage;
    $this->dateFormatter = $date_formatter;
    $this->dblogFormatter = $dblog_formatter;
  }

  /**
   * {@inheritdoc}
   */
  public static function createInstance(ContainerInterface $container, EntityTypeInterface $entity_type) {
    return new static(
      $container->get('renderer'),
      $container->get('entity_type.manager')->getStorage('user'),
      $container->get('date.formatter'),
      $container->get('dblog.formatter'),
    );
  }

  /**
   * {@inheritdoc}
   */
  public function view(EntityInterface $entity, $view_mode = 'full', $langcode = NULL) {
    if (!$entity instanceof DblogEntryInterface) {
      throw new \InvalidArgumentException("The entity to render should implement DblogEntryInterface");
    }

    $build = [];
    $severity = RfcLogLevel::getLevels();
    $username = [
      '#theme' => 'username',
      '#account' => $this->userStorage->load($entity->getUid()),
    ];
    $rows = [
      [
        ['data' => $this->t('Type'), 'header' => TRUE],
        $this->t($entity->getType()),
      ],
      [
        ['data' => $this->t('Date'), 'header' => TRUE],
        $this->dateFormatter->format($entity->getTimestamp(), 'long'),
      ],
      [
        ['data' => $this->t('User'), 'header' => TRUE],
        ['data' => $username],
      ],
      [
        ['data' => $this->t('Location'), 'header' => TRUE],
        $this->createLink($entity->getLocation()),
      ],
      [
        ['data' => $this->t('Referrer'), 'header' => TRUE],
        $this->createLink($entity->getReferer()),
      ],
      [
        ['data' => $this->t('Message'), 'header' => TRUE],
        $entity->getFormattedMessage($this->dblogFormatter),
      ],
      [
        ['data' => $this->t('Severity'), 'header' => TRUE],
        $severity[$entity->getSeverity()],
      ],
      [
        ['data' => $this->t('Hostname'), 'header' => TRUE],
        $entity->getHostname(),
      ],
      [
        ['data' => $this->t('Operations'), 'header' => TRUE],
        ['data' => ['#markup' => $entity->getLink()]],
      ],
    ];
    $build['dblog_table'] = [
      '#type' => 'table',
      '#rows' => $rows,
      '#attributes' => ['class' => ['dblog-event']],
      '#attached' => [
        'library' => ['dblog/drupal.dblog'],
      ],
    ];

    $this->renderer->addCacheableDependency($build, $entity);
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function resetCache(array $entities = NULL) {
    // Intentionally empty.
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    // Intentionally empty.
    return [];
  }

  /**
   * Creates a Link object if the provided URI is valid.
   *
   * @param string|null $uri
   *   The uri string to convert into link if valid.
   *
   * @return \Drupal\Core\Link|string|null
   *   Return a Link object if the uri can be converted as a link. In case of
   *   empty uri or invalid, fallback to the provided $uri.
   */
  protected function createLink($uri) {
    if ($uri !== NULL && UrlHelper::isValid($uri, TRUE)) {
      return new Link($uri, Url::fromUri($uri));
    }
    return $uri;
  }

}
