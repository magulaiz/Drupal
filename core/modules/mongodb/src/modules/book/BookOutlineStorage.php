<?php

namespace Drupal\mongodb\modules\book;

use Drupal\book\BookOutlineStorage as CoreBookOutlineStorage;

/**
 * The MongoDB implementation of \Drupal\book\BookOutlineStorage.
 */
class BookOutlineStorage extends CoreBookOutlineStorage {

  /**
   * {@inheritdoc}
   */
  public function getBooks() {
    $result = $this->connection->select('book', 'b')
      ->fields('b', ['bid'])
      ->execute()
      ->fetchAll();

    $bids = [];
    foreach ($result as $row) {
      if (isset($row->bid) && !in_array($row->bid, $bids)) {
        $bids[] = $row->bid;
      }
    }

    return $bids;
  }

  /**
   * {@inheritdoc}
   */
  public function loadMultiple($nids, $access = TRUE) {
    foreach ($nids as &$nid) {
      $nid = (int) $nid;
    }

    return parent::loadMultiple($nids, $access);
  }

  /**
   * {@inheritdoc}
   */
  public function delete($nid) {
    $nid = (int) $nid;

    return parent::delete($nid);
  }


  /**
   * {@inheritdoc}
   */
  public function hasBooks() {
    return (bool) $this->connection->select('book', 'b')
      ->fields('b', ['bid'])
      ->range(0, 1)
      ->execute()
      ->fetchField();
  }

  /**
   * {@inheritdoc}
   */
  public function loadBookChildren($pid) {
    return $this->connection->select('book', 'b')
      ->fields('b')
      ->condition('pid', (int) $pid)
      ->execute()
      ->fetchAllAssoc('nid', \PDO::FETCH_ASSOC);
  }

  /**
   * {@inheritdoc}
   */
  public function getBookMenuTree($bid, $parameters, $min_depth, $max_depth) {
    $bid = (int) $bid;
    if (isset($parameters['expanded']) && is_array($parameters['expanded'])) {
      foreach ($parameters['expanded'] as &$expanded_parameter) {
        $expanded_parameter = (int) $expanded_parameter;
      }
    }
    if ($min_depth != 1) {
      $min_depth = (int) $min_depth;
    }
    if (isset($parameters['max_depth'])) {
      $parameters['max_depth'] = (int) $parameters['max_depth'];
    }

    return parent::getBookMenuTree($bid, $parameters, $min_depth, $max_depth);
  }

  /**
   * {@inheritdoc}
   */
  public function update($nid, $fields) {
    $nid = (int) $nid;

    return parent::update($nid, $fields);
  }

  /**
   * {@inheritdoc}
   */
  public function updateMovedChildren($bid, $original, $expressions, $shift) {
    $original['bid'] = (int) $original['bid'];
    for ($i = 1; !empty($original[$p]); $p = 'p' . ++$i) {
      $original[$p] = (int) $original[$p];
    }

    return parent::updateMovedChildren($bid, $original, $expressions, $shift);
  }

  /**
   * {@inheritdoc}
   */
  public function countOriginalLinkChildren($original) {
    $original['bid'] = (int) $original['bid'];
    $original['pid'] = (int) $original['pid'];
    $original['nid'] = (int) $original['nid'];

    return parent::countOriginalLinkChildren($original);
  }

  /**
   * {@inheritdoc}
   */
  public function getBookSubtree($link, $max_depth) {
    $link['bid'] = (int) $link['bid'];
    for ($i = 1; $i <= $max_depth && $link["p$i"]; ++$i) {
      $link["p$i"] = (int) $link["p$i"];
    }

    return parent::getBookSubtree($link, $max_depth);
  }

}
