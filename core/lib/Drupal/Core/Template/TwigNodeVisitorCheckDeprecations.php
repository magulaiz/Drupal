<?php

namespace Drupal\Core\Template;

use Twig\Environment;
use Twig\Node\Expression\NameExpression;
use Twig\Node\ModuleNode;
use Twig\Node\Node;
use Twig\NodeVisitor\AbstractNodeVisitor;

/**
 * Provides a Node Visitor to trigger errors if deprecated variables are used.
 *
 * Every use of a named variable is tracked, and the used variable names are passed
 * to TwigExtension::checkDeprecations at runtime for comparison against those in the
 * 'deprecated' array in the template context.
 *
 * @see \Drupal\Core\Template\TwigNodeCheckDeprecations
 */
class TwigNodeVisitorCheckDeprecations extends AbstractNodeVisitor {

  /**
   * The named variables used in the template.
   *
   * @var array
   */
  protected $usedNames = [];

  /**
   * {@inheritdoc}
   */
  protected function doEnterNode(Node $node, Environment $env) {
    if ($node instanceof ModuleNode) {
      $this->usedNames = [];
    }
    elseif ($node instanceof NameExpression) {
      $this->usedNames[$node->getAttribute('name')] = $node->getAttribute('name');
    }
    return $node;
  }

  /**
   * {@inheritdoc}
   */
  protected function doLeaveNode(Node $node, Environment $env) {
    if ($node instanceof ModuleNode) {
      if (!empty($this->usedNames)) {
        $checkNode = new Node([new TwigNodeCheckDeprecations($this->usedNames), $node->getNode('display_end')]);
        $node->setNode('display_end', $checkNode);
      }
    }
    return $node;
  }

  public function getPriority() {
    // Just above the Optimizer, which is the normal last one.
    return 256;
  }

}
