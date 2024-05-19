<?php

declare(strict_types=1);

use phpDocumentor\Guides\RestructuredText\Directives\BaseDirective;
use phpDocumentor\Guides\RestructuredText\Directives\SubDirective;
use phpDocumentor\Guides\RestructuredText\Parser\Productions\DirectiveContentRule;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;

use T3Docs\ConsoleCommand\Directives\CommandDirective;
use T3Docs\ConsoleCommand\Directives\CommandListDirective;
use T3Docs\ConsoleCommand\Service\CommandNodeService;
use T3Docs\ConsoleCommand\Service\DirectiveParameterService;
use T3Docs\ConsoleCommand\Service\FileLoadingService;

use T3Docs\ConsoleCommand\Service\JsonLoadingService;
use function Symfony\Component\DependencyInjection\Loader\Configurator\service;

return static function (ContainerConfigurator $container): void {
    $container->services()
        ->defaults()
        ->autowire()
        ->autoconfigure()
        ->instanceof(SubDirective::class)
        ->bind('$startingRule', service(DirectiveContentRule::class))
        ->instanceof(BaseDirective::class)
        ->tag('phpdoc.guides.directive')

        ->set(CommandNodeService::class)
        ->bind('$startingRule', service(DirectiveContentRule::class))
        ->set(DirectiveParameterService::class)
        ->set(FileLoadingService::class)
        ->set(JsonLoadingService::class)
        ->set(CommandDirective::class)
        ->set(CommandListDirective::class)
    ;
};
