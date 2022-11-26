<?php

namespace Drupal\taxonomy\Controller;

use Drupal\Component\Utility\Xss;
use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Link;
use Drupal\Core\Url;
use Drupal\taxonomy\TermInterface;
use Drupal\taxonomy\TermStorageInterface;
use Drupal\taxonomy\VocabularyInterface;

/**
 * Provides route responses for taxonomy.module.
 */
class TaxonomyController extends ControllerBase {

  /**
   * Returns a form to add a new term to a vocabulary.
   *
   * @param \Drupal\taxonomy\VocabularyInterface $taxonomy_vocabulary
   *   The vocabulary this term will be added to.
   *
   * @return array
   *   The taxonomy term add form.
   */
  public function addForm(VocabularyInterface $taxonomy_vocabulary) {
    $term = $this->entityTypeManager()->getStorage('taxonomy_term')->create(['vid' => $taxonomy_vocabulary->id()]);
    return $this->entityFormBuilder()->getForm($term);
  }

  /**
   * Route title callback.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term
   *   The taxonomy term.
   *
   * @return array
   *   The term label as a render array.
   */
  public function termTitle(TermInterface $taxonomy_term) {
    return ['#markup' => $taxonomy_term->getName(), '#allowed_tags' => Xss::getHtmlTagList()];
  }

  /**
   * Displays a Taxonomy Term revision.
   *
   * @param int $taxonomy_term_revision
   *   The Taxonomy Term revision ID.
   *
   * @return array
   *   A renderable array.
   *
   * @throws \Drupal\Component\Plugin\Exception\InvalidPluginDefinitionException
   * @throws \Drupal\Component\Plugin\Exception\PluginNotFoundException
   */
  public function revisionShow($taxonomy_term_revision) {
    $taxonomy_term = $this->entityTypeManager()->getStorage('taxonomy_term')->loadRevision($taxonomy_term_revision);
    $view_builder = $this->entityTypeManager()->getViewBuilder('taxonomy_term');

    return $view_builder->view($taxonomy_term);
  }

  /**
   * Page title callback for a taxonomy term revision.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term_revision
   *   The taxonomy term revision.
   *
   * @return string
   *   The page title.
   */
  public function revisionPageTitle(TermInterface $taxonomy_term_revision) {
    return $this->t('Revision of %title from %date', [
      '%title' => $taxonomy_term_revision->label(),
      '%date' => $this->dateFormatter->format($taxonomy_term_revision->getRevisionCreationTime()),
    ]);
  }

  /**
   * Generates an overview table of older revisions of taxonomy_term.
   *
   * @param \Drupal\taxonomy\TermInterface $taxonomy_term
   *   A taxonomy_term object.
   *
   * @return array
   *   An array expected by \Drupal\Core\Render\RendererInterface::render().
   */
  public function revisionOverview(TermInterface $taxonomy_term) {
    $account = $this->currentUser();
    $langcode = $taxonomy_term->language()->getId();
    $langname = $taxonomy_term->language()->getName();
    $languages = $taxonomy_term->getTranslationLanguages();
    $hasTranslations = (count($languages) > 1);

    /** @var \Drupal\taxonomy\TermStorageInterface $taxonomyStorage */
    $taxonomyStorage = $this->entityTypeManager()->getStorage('taxonomy_term');

    $title = $this->t('Revisions for %title', [
      '%title' => $taxonomy_term->label(),
    ]);
    if ($hasTranslations) {
      $title = $this->t('@langname revisions for %title', [
        '@langname' => $langname,
        '%title' => $taxonomy_term->label(),
      ]);
    }

    $build['#title'] = $title;
    $header = [
      $this->t('Revision'),
      $this->t('Operations'),
    ];

    $type = $taxonomy_term->bundle();
    $revert_permission = (($account->hasPermission('revert all revisions') || $account->hasPermission('revert taxonomy revision')) && $taxonomy_term->access('update'));
    $delete_permission = (($account->hasPermission('delete all revisions') || $account->hasPermission('delete taxonomy revision')) && $taxonomy_term->access('delete'));


    $rows = [];
    $defaultRevision = $taxonomy_term->getRevisionId();
    $currentRevisionDisplayed = FALSE;

    foreach ($this->getRevisionIds($taxonomy_term, $taxonomyStorage) as $vid) {
      /** @var \Drupal\taxonomy\TermInterface $revision */
      $revision = $taxonomyStorage->loadRevision($vid);
      if ($revision->hasTranslation($langcode) && $revision->getTranslation($langcode)->isRevisionTranslationAffected()) {
        $username = [
          '#theme' => 'username',
          '#account' => $revision->getRevisionUser(),
        ];

        $date = \Drupal::service('date.formatter')->format(
          $revision->getRevisionCreationTime(),
          'short'
        );

        $isCurrentRevision = $vid == $defaultRevision || (!$currentRevisionDisplayed && $revision->wasDefaultRevision());
        if (!$isCurrentRevision) {
          $link = Link::fromTextAndUrl($date, new Url(
            'entity.taxonomy_term.revision',
            [
              'taxonomy_term' => $taxonomy_term->id(),
              'taxonomy_term_revision' => $vid,
            ]
          ))->toString();
        }
        else {
          $link = $taxonomy_term->toLink($date)->toString();
          $currentRevisionDisplayed = TRUE;
        }

        $row = [];
        $renderer = \Drupal::service('renderer');
        $column = [
          'data' => [
            '#type' => 'inline_template',
            '#template' => '{% trans %}{{ date }} by {{ username }}{% endtrans %}{% if message %}<p class="revision-log">{{ message }}</p>{% endif %}',
            '#context' => [
              'date' => $link,
              'username' => $renderer->renderPlain($username),
              'message' => [
                '#markup' => $revision->getRevisionLogMessage(),
                '#allowed_tags' => Xss::getHtmlTagList(),
              ],
            ],
          ],
        ];

        $renderer->addCacheableDependency($column['data'], $username);
        $row[] = $column;

        if ($isCurrentRevision) {
          $row[] = [
            'data' => [
              '#prefix' => '<em>',
              '#markup' => $this->t('Current revision'),
              '#suffix' => '</em>',
            ],
          ];

          $rows[] = [
            'data' => $row,
            'class' => ['revision-current'],
          ];
        }
        else {
          $links = [];
          if ($revert_permission) {
            $revertLink = Url::fromRoute(
              'entity.taxonomy_term.revision_revert_confirm',
              [
                'taxonomy_term' => $taxonomy_term->id(),
                'taxonomy_term_revision' => $vid,
              ]
            );
            if ($hasTranslations) {
              $revertLink = Url::fromRoute(
                'entity.taxonomy_term.revision_revert_translation_confirm',
                [
                  'taxonomy_term' => $taxonomy_term->id(),
                  'taxonomy_term_revision' => $vid,
                  'langcode' => $langcode,
                ]
              );
            }
            $title = $this->t('Set as current revision');
            if ($vid < $taxonomy_term->getRevisionId()) {
              $title = $this->t('Revert');
            }
            $links['revert'] = [
              'title' => $title,
              'url' => $revertLink,
            ];
          }

          if ($delete_permission) {
            $links['delete'] = [
              'title' => $this->t('Delete'),
              'url' => Url::fromRoute(
                'entity.taxonomy_term.revision_delete_confirm',
                [
                  'taxonomy_term' => $taxonomy_term->id(),
                  'taxonomy_term_revision' => $vid,
                ]
              ),
            ];
          }

          $row[] = [
            'data' => [
              '#type' => 'operations',
              '#links' => $links,
            ],
          ];

          $rows[] = $row;
        }
      }
    }

    $build['taxonomy_term_revisions_table'] = [
      '#theme' => 'table',
      '#rows' => $rows,
      '#header' => $header,
      '#attached' => [
        'library' => [
          'taxonomy/taxonomy.admin',
        ],
      ],
      '#attributes' => [
        'class' => 'taxonomy-term-revision-table',
      ],
    ];
    $build['pager'] = [
      '#type' => 'pager',
    ];

    return $build;
  }

  /**
   * Gets a list of taxonomy term revision IDs for a specific taxonomy term.
   *
   * @param Drupal\taxonomy\TermInterface $taxonomy_term
   *   The node entity.
   * @param \Drupal\taxonomy\TermStorageInterface $taxonomyStorage
   *   The node storage handler.
   *
   * @return int[]
   *   Taxonomy term revision IDs (in descending order).
   */
  protected function getRevisionIds(TermInterface $taxonomy_term, TermStorageInterface $taxonomyStorage) {
    $result = $taxonomyStorage->getQuery()
      ->accessCheck(TRUE)
      ->allRevisions()
      ->condition($taxonomy_term->getEntityType()->getKey('id'), $taxonomy_term->id())
      ->sort($taxonomy_term->getEntityType()->getKey('revision'), 'DESC')
      ->pager(50)
      ->execute();
    return array_keys($result);
  }

}
