<?php

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__ . '/src/')
    ->exclude('var')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,
    ])
    ->setFinder($finder)
;
