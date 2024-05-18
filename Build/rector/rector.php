<?php

use Rector\Config\RectorConfig;

return RectorConfig::configure()
    ->withPaths([
        __DIR__ . '/..',
        __DIR__ . '/../../src',
    ])
    ->withPhpSets(php81: true)
    ->withImportNames(importShortClasses: false, removeUnusedImports: true)
    ->withPreparedSets(deadCode: true)
;
