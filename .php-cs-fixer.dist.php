<?php

$finder = (new PhpCsFixer\Finder())
    ->in([
        __DIR__ . '/src',
        __DIR__ . '/kernel',
        __DIR__ . '/public',
        __DIR__ . '/tests',
    ])
    ->append([
        __DIR__ . '/routes.php',
    ])
;

return (new PhpCsFixer\Config())
    ->setRiskyAllowed(true)
    ->setRules([
        '@PSR12'                      => true,
        '@PHP80Migration:risky'       => true,
        'array_syntax'                => ['syntax' => 'short'],
        'list_syntax'                 => ['syntax' => 'short'],
        'declare_strict_types'        => true,
        'use_arrow_functions'         => true,
        'ordered_imports'             => ['sort_algorithm' => 'alpha'],
        'no_unused_imports'           => true,
        'global_namespace_import'     => [
            'import_classes'    => true,
            'import_functions'  => true,
            'import_constants'  => true,
        ],
        'strict_comparison'           => true,
        'strict_param'                => true,
        'modernize_types_casting'     => true,
        'no_superfluous_phpdoc_tags'  => true,
        'no_useless_else'             => true,
        'no_useless_return'           => true,
        'simplified_if_return'        => true,
        'concat_space'                => ['spacing' => 'one'],
        'blank_line_before_statement' => ['statements' => ['return', 'throw', 'try']],
        'operator_linebreak'          => ['position' => 'beginning'],
        'trailing_comma_in_multiline' => ['elements' => ['arrays', 'arguments', 'parameters']],
        'phpdoc_summary'              => false,
        'phpdoc_to_comment'           => false,
        'phpdoc_order'                => true,
        'lowercase_cast'              => true,
        'short_scalar_cast'           => true,
    ])
    ->setFinder($finder);