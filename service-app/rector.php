<?php

declare(strict_types=1);

use Rector\Caching\ValueObject\Storage\FileCacheStorage;
use Rector\Config\RectorConfig;
use Rector\DeadCode\Rector\ClassMethod\RemoveParentDelegatingConstructorRector;
use Rector\Doctrine\Set\DoctrineSetList;
use Rector\EarlyReturn\Rector\Foreach_\ChangeNestedForeachIfsToEarlyContinueRector;
use Rector\EarlyReturn\Rector\If_\ChangeIfElseValueAssignToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeNestedIfsToEarlyReturnRector;
use Rector\EarlyReturn\Rector\If_\ChangeOrIfContinueToMultiContinueRector;
use Rector\EarlyReturn\Rector\If_\RemoveAlwaysElseRector;
use Rector\EarlyReturn\Rector\Return_\PreparedValueToEarlyReturnRector;
use Rector\EarlyReturn\Rector\StmtsAwareInterface\ReturnEarlyIfVariableRector;
use Rector\PHPUnit\Set\PHPUnitLevelSetList;
use Rector\Set\ValueObject\LevelSetList;
use Rector\Set\ValueObject\SetList;
use Rector\Symfony\Set\SymfonyLevelSetList;
use Rector\Symfony\Symfony43\Rector\MethodCall\WebTestCaseAssertResponseCodeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\AddMethodCallBasedStrictParamTypeRector;
use Rector\TypeDeclaration\Rector\ClassMethod\NarrowObjectReturnTypeRector;

return static function (RectorConfig $rectorConfig): void {
    $rectorConfig->paths([
        __DIR__.'/config',
        __DIR__.'/features',
        __DIR__.'/migrations',
        __DIR__.'/public',
        __DIR__.'/src',
        __DIR__.'/tests',
    ]);

    $rectorConfig->skip([
        __DIR__.'/config/secrets',
        __DIR__.'/tests/Behat',
        __DIR__.'/tests/View/Metadata/TranslatedValuesTest.php',
        __DIR__.'/src/Common/Entity/Hal/Person.php',
        AddMethodCallBasedStrictParamTypeRector::class,
        NarrowObjectReturnTypeRector::class,
    ]);

    $rectorConfig->sets([
        LevelSetList::UP_TO_PHP_82,
        SetList::DEAD_CODE,
        SetList::CODE_QUALITY,
        SetList::TYPE_DECLARATION,
        #DoctrineSetList::DOCTRINE_ORM_213, Those sets are useful only during upgrade process, seems to be too heavy
        #PHPUnitLevelSetList::UP_TO_PHPUNIT_100,
        #SymfonyLevelSetList::UP_TO_SYMFONY_62,
    ]);

    // customizing rules from SetList::EARLY_RETURN removing ReturnBinaryAndToEarlyReturnRector rule
    $rectorConfig->rules([
        ChangeNestedForeachIfsToEarlyContinueRector::class,
        ChangeIfElseValueAssignToEarlyReturnRector::class,
        ChangeNestedIfsToEarlyReturnRector::class,
        RemoveAlwaysElseRector::class,
        ChangeOrIfContinueToMultiContinueRector::class,
        PreparedValueToEarlyReturnRector::class,
        ReturnEarlyIfVariableRector::class,
    ]);


    $rectorConfig->phpstanConfig('./phpstan.dist.neon');

    $rectorConfig->cacheClass(FileCacheStorage::class);
    $rectorConfig->cacheDirectory('./var/cache/rector');
};