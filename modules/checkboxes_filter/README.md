## Checkboxes Filter

Module allows you to filter a large amount of the checkboxes quickly.

This module is a port of the Drupal 7 module:
https://www.drupal.org/project/filter_checkboxes

### Requirements

No special requirements.

### Usage

Use the "Checkboxes Filter" field widget on checkbox fields. Or you can
apply via Form API, setting the '#filter' key like below:

```php
$form['options'] = [
  '#type' => 'checkboxes',
  '#title' => 'Title',
  '#options' => $options,
  '#filter' => TRUE,
];
```

Just add the '#filter' key and set it to TRUE on the checkboxes element
and the module will do the rest.

### Maintainers

George Anderson (geoanders)
https://www.drupal.org/u/geoanders
