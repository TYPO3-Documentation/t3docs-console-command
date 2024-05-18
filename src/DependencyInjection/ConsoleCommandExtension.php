<?php

declare(strict_types=1);

namespace T3Docs\ConsoleCommand\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Extension\PrependExtensionInterface;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

final class ConsoleCommandExtension extends Extension implements PrependExtensionInterface
{
    /** @param mixed[] $configs */
    public function load(array $configs, ContainerBuilder $container): void
    {
        $loader = new PhpFileLoader(
            $container,
            new FileLocator(\dirname(__DIR__, 2) . '/resources/config'),
        );
        $loader->load('console-command.php');
    }

    public function prepend(ContainerBuilder $container): void
    {
        $container->prependExtensionConfig(
            'guides',
            [
                'base_template_paths' => [\dirname(__DIR__, 2) . '/resources/template/html'],
                'templates' => [
                ],
            ],
        );
    }
}
