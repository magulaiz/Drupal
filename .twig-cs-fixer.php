<?php

$finder = new TwigCsFixer\File\Finder();
$finder->exclude('tests');

$config = new TwigCsFixer\Config\Config();
$config->setFinder($finder);
$config->setCacheFile(null);
$config->addTokenParser(new Drupal\Core\Template\TwigTransTokenParser());

return $config;
