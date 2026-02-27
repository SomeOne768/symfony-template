<?php

declare(strict_types=1);

$finder = (new PhpCsFixer\Finder())
    ->in(__DIR__.'/config')
    ->in(__DIR__.'/features')
    ->in(__DIR__.'/src')
    ->in(__DIR__.'/tests')
    ->in(__DIR__.'/public')
    ->exclude('secrets')
    ->notName('HalConfiguration.php')
    ->notName('reference.php')
    ->notName('NavConfiguration.php')
    ->notName('DepositActionsConfiguration.php')
    ->notName('DepositCitationConfiguration.php')
    ->notName('HalVcrConfiguration.php')
    ->notName('HalSpdxConfiguration.php')
    ->notName('RelationshipRepositoryConfiguration.php')
    ->notName('Kernel.php')
;

return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR1' => true,
        '@PSR2' => true,
        '@PSR12' => true,
        '@PSR12:risky' => true,
        '@PHP82Migration' => true,
        '@PhpCsFixer:risky' => true,
        '@Symfony:risky' => true,
        '@Symfony' => true,
        '@DoctrineAnnotation' => true,
        'array_indentation' => true,
        'array_syntax' => [
            'syntax' => 'short',
        ],
        'braces' => [
            'allow_single_line_closure' => true,
        ],
        'doctrine_annotation_array_assignment' => false,
        'doctrine_annotation_spaces' => false,
        'declare_strict_types' => true,
        'final_class' => true,
        'global_namespace_import' => [
            'import_classes' => true,
            'import_constants' => true,
            'import_functions' => true,
        ],
        'method_chaining_indentation' => true,
        'multiline_whitespace_before_semicolons' => [
            'strategy' => 'new_line_for_chained_calls',
        ],
        'native_function_invocation' => [
            'include' => ['@internal'],
            'scope' => 'namespaced',
        ],
        'no_extra_blank_lines' => [
            'tokens' => [
                'break',
                'continue',
                'curly_brace_block',
                'extra',
                'parenthesis_brace_block',
                'return',
                'square_brace_block',
                'throw',
                'use',
            ],
        ],
        'no_superfluous_phpdoc_tags' => true,
        'no_unneeded_control_parentheses' => true,
        'no_useless_else' => true,
        'no_useless_return' => true,
        'ordered_class_elements' => true,
        'ordered_imports' => [
            'imports_order' => [
                'class',
                'function',
                'const',
            ],
        ],
        'phpdoc_order' => true,
        'phpdoc_to_comment' => false,
        'self_static_accessor' => true,
        'semicolon_after_instruction' => true,
        'single_line_throw' => true,
        'static_lambda' => true,
        'ternary_to_null_coalescing' => true,
        'trailing_comma_in_multiline' => [
            'elements' => ['arguments', 'arrays', 'match', 'parameters'],
        ],
        'yoda_style' => true,
        'void_return' => true,
    ])
    ->setFinder($finder)
    ->setCacheFile(__DIR__.'/var/.php_cs.cache')
    ;

