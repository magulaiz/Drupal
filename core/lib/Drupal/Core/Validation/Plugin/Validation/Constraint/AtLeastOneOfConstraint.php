<?php

namespace Drupal\Core\Validation\Plugin\Validation\Constraint;

use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Validation\Attribute\Constraint;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\StringTranslation\TranslatableMarkup;
use Symfony\Component\Validator\Constraints\AtLeastOneOf;

#[Constraint(
  id: 'AtLeastOneOf',
  label: new TranslatableMarkup('At Least One Of', [], ['context' => 'Validation'])
)]
class AtLeastOneOfConstraint extends AtLeastOneOf implements ContainerFactoryPluginInterface
{

  /**
   * Constructs a AtLeastOfConstraint.
   */
  public function __construct(mixed $constraints = null) {
    parent::__construct($constraints);
  }

  /**
   * @inheritDoc
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition)
  {
    $constraint_manager = $container->get('validation.constraint');
    $constraints = $configuration['constraints'];
    foreach ($constraints as $constraint_id => $constraint) {
      foreach ($constraint as $constraint_name => $constraint_options) {
        unset($constraints[$constraint_id]);
        $constraints[] = $constraint_manager->create($constraint_name, $constraint_options);
      }
    }
    return new static(array_values($constraints));
  }

}
