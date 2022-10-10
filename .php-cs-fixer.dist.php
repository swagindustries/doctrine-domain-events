<?php

$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude(['Tests', 'vendor', 'docs', 'var'])
;

return (new PhpCsFixer\Config)
    ->setRiskyAllowed(true)
    ->setRules([
        '@Symfony' => true,
        'align_multiline_comment' => true,
        'binary_operator_spaces' => true,
        'blank_line_before_statement' => ['statements' => ['return', 'throw']],
        'concat_space' => ['spacing' => 'one'],
        'no_extra_blank_lines' => ['tokens' => ['break', 'continue', 'extra', 'return', 'throw', 'use', 'parenthesis_brace_block', 'square_brace_block', 'curly_brace_block']],
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_imports' => true,
        'phpdoc_order' => true,
    ])
    ->setFinder($finder)
;
