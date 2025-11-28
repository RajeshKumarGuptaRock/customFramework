<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RouteClearCommand extends Command
{
    protected static $defaultName = 'route:clear';

    protected function configure()
    {
        $this
            ->setName('route:clear')
            ->setDescription('Clear the application cache.')
            ->setHelp('This command clears the cache directory by deleting all cached files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $cacheDirectory = __DIR__ . '/../cache/storage/'; // Set your cache directory path here

        // Check if the cache directory exists
        if (!is_dir($cacheDirectory)) {
            $output->writeln("<error>Cache directory does not exist: $cacheDirectory</error>");
            return Command::FAILURE;
        }

        // Get all files in the cache directory
        $files = glob($cacheDirectory . '*'); // Get all files and subdirectories

        if (empty($files)) {
            $output->writeln("<comment>No cache files found to delete.</comment>");
            return Command::SUCCESS;
        }

        // Iterate over the files and delete them
        foreach ($files as $file) {
            try {
                if (is_dir($file)) {
                    // Recursively delete directories
                    $this->deleteDirectory($file);
                } else {
                    // Delete files
                    unlink($file);
                }
                $output->writeln("<info>Deleted: $file</info>");
            } catch (\Exception $e) {
                $output->writeln("<error>Failed to delete $file: " . $e->getMessage() . "</error>");
            }
        }

        $output->writeln("<info>Cache cleared successfully.</info>");
        return Command::SUCCESS;
    }

    // Recursive function to delete directories
    private function deleteDirectory($dir)
    {
        $files = glob($dir . '/*');
        foreach ($files as $file) {
            if (is_dir($file)) {
                $this->deleteDirectory($file); // Recursively delete subdirectories
            } else {
                unlink($file); // Delete file
            }
        }
        rmdir($dir); // Finally, remove the empty directory
    }
}
