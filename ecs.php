<?php

declare(strict_types=1);

use Symplify\EasyCodingStandard\Config\ECSConfig;
use Symplify\EasyCodingStandard\ValueObject\Set\SetList;

return function (ECSConfig $ecsConfig): void {
    $ecsConfig->paths([
        __DIR__ . '/bin',
        __DIR__ . '/config',
        __DIR__ . '/migrations',
        __DIR__ . '/public',
        __DIR__ . '/src',
        __DIR__ . '/tests',
    ]);

    $ecsConfig->sets([
        SetList::CLEAN_CODE,
        SetList::PSR_12,
//        SetList::COMMENTS,
//        SetList::DOCBLOCK,
//        SetList::DOCTRINE_ANNOTATIONS,
//        SetList::NAMESPACES,
//        SetList::PHPUNIT,
        SetList::STRICT,
    ]);
};
