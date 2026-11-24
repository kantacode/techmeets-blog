<?php

// どのファイルをチェックするか：src/ の中全部。ただし vendor などは除く
$finder = PhpCsFixer\Finder::create()
    ->in(__DIR__)
    ->exclude('vendor')
    ->exclude('storage')
    ->exclude('bootstrap/cache');

// どんなルールでそろえるか
return (new PhpCsFixer\Config())
    ->setRules([
        '@PSR12' => true,                                      // PHP の標準的な書き方のルール
        'array_syntax' => ['syntax' => 'short'],               // 配列は array() ではなく [] で書く
        'ordered_imports' => ['sort_algorithm' => 'alpha'],    // use をアルファベット順に並べる
        'no_unused_imports' => true,                           // 使っていない use を消す
    ])
    ->setFinder($finder);