<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__ . '/src')
;

return PhpCsFixer\Config::create()
    ->setUsingCache(false)
    ->setCacheFile(__DIR__.'/.php_cs.cache')
    ->setRules([
        '@Symfony' => true,
        'full_opening_tag' => false,
    ])
    ->setFinder($finder)
;