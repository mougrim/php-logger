<?php
return Symfony\CS\Config::create()
    // use default SYMFONY_LEVEL and extra fixers:
    ->fixers([
        'short_array_syntax',
        'ordered_use',
        'php_unit_construct',
        'php_unit_strict',
        'strict',
        '-phpdoc_to_comment',
        '-phpdoc_var_without_name',
        '-phpdoc_params',
    ])
    ->finder(
        \Symfony\CS\Finder::create()
            ->in(__DIR__)
    )
;
