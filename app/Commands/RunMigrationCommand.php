<?php

namespace App\Commands;

use PDO;
use PDOException;
use config\EnvConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RunMigrationCommand extends Command
{
    protected static $defaultName = 'migrate:run';
    private string $dbHost;
    private string $dbName;
    private string $dbUser;
    private string $dbPass;
    public function __construct()
    {
        parent::__construct(); // Always call parent constructor

        // Load environment variables inside constructor
        $this->dbHost = env('DB_HOST', '127.0.0.1');
        $this->dbName = env('DB_NAME', 'roza');
        $this->dbUser = env('DB_USER', 'root');
        $this->dbPass = env('DB_PASS', '');
        //secho "DB_HOST: {$this->dbHost}, DB_NAME: {$this->dbName}, DB_USER: {$this->dbUser}, DB_PASS: {$this->dbPass}\n";
    }
    
    protected function configure()
    {
        $this
            ->setName('migrate:run')
            ->setDescription('Run all pending migrations.')
            ->setHelp('This command reads migration files and creates tables in the database.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $migratePath = __DIR__ . '/../migrate/';
        if (!is_dir($migratePath)) {
            $output->writeln("<error>No migration folder found.</error>");
            return Command::FAILURE;
        }

        // Get all migration files
        $files = glob($migratePath . '*.php');
        if (empty($files)) {
            $output->writeln("<comment>No migrations found.</comment>");
            return Command::SUCCESS;
        }

        // Connect to the database
        $pdo = $this->connectDatabase();
        if (!$pdo) {
            $output->writeln("<error>Failed to connect to the database.</error>");
            return Command::FAILURE;
        }

        foreach ($files as $file) {
            $output->writeln("<info>Processing migration:</info> " . basename($file));

            $migration = require $file;

            if (method_exists($migration, 'create')) {
                $query = $migration->create();
                if ($query) {
                    $this->executeQuery($pdo, $query, $output);
                }
            }
        }

        return Command::SUCCESS;
    }

    // protected function execute(InputInterface $input, OutputInterface $output): int
    // {
    //     $migratePath = __DIR__ . '/../migrate/';
    //     if (!is_dir($migratePath)) {
    //         $output->writeln("<error>No migration folder found.</error>");
    //         return Command::FAILURE;
    //     }

    //     // Get all migration files and sort them in reverse order (latest first)
    //     $files = glob($migratePath . '*.php');
    //     if (empty($files)) {
    //         $output->writeln("<comment>No migrations found.</comment>");
    //         return Command::SUCCESS;
    //     }

    //     // Sort files by modification time, latest first
    //     usort($files, function ($a, $b) {
    //         return filemtime($b) - filemtime($a);
    //     });

    //     // Get the latest migration file (first in the sorted array)
    //     $latestFile = $files[0];
    //     $output->writeln("<info>Processing latest migration:</info> " . basename($latestFile));

    //     // Connect to the database
    //     $pdo = $this->connectDatabase();
    //     if (!$pdo) {
    //         $output->writeln("<error>Failed to connect to the database.</error>");
    //         return Command::FAILURE;
    //     }

    //     // Include the latest migration and run its `create` method
    //     $migration = require $latestFile;

    //     if (method_exists($migration, 'create')) {
    //         $query = $migration->create();
    //         if ($query) {
    //             $this->executeQuery($pdo, $query, $output);
    //         }
    //     }

    //     return Command::SUCCESS;
    // }

    private function connectDatabase(): ?PDO
    {
        try {
            $pdo = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPass);
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            echo "Database Connection Failed: " . $e->getMessage() . "\n";
            return null;
        }
    }

    private function executeQuery(PDO $pdo, string $query, OutputInterface $output)
    {
        try {
            $pdo->exec($query);
            $output->writeln("<info>Migration executed successfully.</info>");
        } catch (PDOException $e) {
            $output->writeln("<error>Error executing migration:</error> " . $e->getMessage());
        }
    }
}
