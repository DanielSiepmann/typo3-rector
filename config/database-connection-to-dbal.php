<?php

declare(strict_types=1);

use Ssch\TYPO3Rector\Helper\Typo3NodeResolver;
use Ssch\TYPO3Rector\Rector\v9\v0\DatabaseConnectionToDbalRector;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;
use function Symfony\Component\DependencyInjection\Loader\Configurator\tagged_iterator;

return static function (ContainerConfigurator $containerConfigurator): void {
    $containerConfigurator->import(__DIR__ . '/services.php');
    $services = $containerConfigurator->services();

    $services->set(DatabaseConnectionToDbalRector::class)->args(
        [service(Typo3NodeResolver::class), tagged_iterator('database.dbal.refactoring')]
    );
};
