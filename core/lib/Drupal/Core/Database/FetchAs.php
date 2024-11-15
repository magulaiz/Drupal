<?php

namespace Drupal\Core\Database;

/**
 * Enumeration of the fetch modes for result sets.
 */
enum FetchAs {

  // Returns an array indexed by column name as returned in the result set.
  case Associative;

  // Returns a new instance of a requested class, mapping the columns of the
  // result set to named properties in the class.
  case ClassObject;

  // Returns a single column from the next row of a result set.
  case Column;

  // Returns an array indexed by column number as returned in the result set,
  // starting at column 0.
  case Numbered;

  // Returns an anonymous object with property names that correspond to the
  // column names returned in the result set.
  case Object;

}
