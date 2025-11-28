<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
class RouteCacheCommand extends Command
{

    protected static $defaultName = 'route:cache';

    protected function configure()
    {
        $this
            ->setName('route:cache')
            ->setDescription('Clear cache files for routes.')
            ->setHelp('Use this command to delete all cache files in storage/cache folder.');
    }
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $basePath = dirname(__DIR__, 2); // Go to project root
       // echo $basePath;exit;
        // Get all routes
        $routes = $basePath . '/routes/web.php';
        

        // Serialize the routes array
        $compiledRoutes = var_export($routes, true);

        $saveFilePath = $basePath .'/app/cache/storage/routes.php';
        // Save to cache file
        file_put_contents($saveFilePath, "<?php return $compiledRoutes;");

        echo "Routes cached successfully!\n";
        return Command::SUCCESS;
    }
}
