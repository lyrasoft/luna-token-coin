<?php

declare(strict_types=1);

namespace Lyrasoft\TokenCoin;

use Windwalker\Core\Package\AbstractPackage;
use Windwalker\Core\Package\PackageInstaller;
use Windwalker\DI\Container;
use Windwalker\DI\ServiceProviderInterface;

class TokenCoinPackage extends AbstractPackage implements ServiceProviderInterface
{
    public function install(PackageInstaller $installer): void
    {
        $installer->installConfig(static::path('etc/*.php'), 'config');
        $installer->installMigrations(static::path('resources/migrations/**/*'), 'migrations');
        $installer->installRoutes(static::path('routes/**/*.php'), 'routes');
        $installer->installFiles(static::path('components/**/*.php'), 'components');
        $installer->installModules(
            [
                static::path('src/Module/Admin/TokenCoinHistory/**/*') => '@source/Module/Admin/TokenCoinHistory',
            ],
            ['Lyrasoft\\TokenCoin\\Module\\Admin', 'App\\Module\\Admin'],
            ['modules', 'token_coin_history_admin']
        );

        $installer->installModules(
            [
                static::path("src/Entity/TokenCoinHistory.php") => '@source/Entity',
                static::path("src/Repository/TokenCoinHistoryRepository.php") => '@source/Repository',
            ],
            [
                'Lyrasoft\\TokenCoin\\Entity', 'App\\Module\\Entity',
                'Lyrasoft\\TokenCoin\\Repository', 'App\\Module\\Repository',
            ],
            ['modules', 'token_coin_history_model']
        );
    }

    public function register(Container $container): void
    {
        $container->bind(static::class, $this);

        $container->mergeParameters(
            'renderer.edge.component_scans',
            [
                'Lyrasoft\\TokenCoin\\Component'
            ],
            Container::MERGE_OVERRIDE
        );

        $container->mergeParameters(
            'renderer.paths',
            [
                static::path('views'),
            ],
            Container::MERGE_OVERRIDE
        );
    }
}
