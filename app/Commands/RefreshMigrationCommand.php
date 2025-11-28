<?php

namespace App\Commands;

use PDO;
use PDOException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class RefreshMigrationCommand extends Command
{
    protected static $defaultName = 'migrate:fresh';
    private PDO $pdo;

    public function __construct()
    {
        parent::__construct();

        // Load environment variables
        $this->pdo = $this->connectDatabase();
    }

    protected function configure()
    {
        $this
            ->setName('migrate:fresh')
            ->setDescription('Drop all tables and re-run migrations')
            ->setHelp('This command will drop existing tables and recreate them.');
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

        // Drop all tables first
        foreach ($files as $file) {
            $migration = require $file;
            if (method_exists($migration, 'drop')) {
                $query = $migration->drop();
                if ($query) {
                    $this->executeQuery($query, $output, "Dropping table");
                }
            }
        }

        // Recreate all tables
        foreach ($files as $file) {
            $migration = require $file;
            if (method_exists($migration, 'create')) {
                $query = $migration->create();
                if ($query) {
                    $this->executeQuery($query, $output, "Creating table");
                }
            }
        }

        $output->writeln("<info>Database migration refreshed successfully.</info>");
        return Command::SUCCESS;
    }

    private function connectDatabase(): ?PDO
    {
        try {
            $pdo = new PDO("mysql:host=" . env('DB_HOST', '127.0.0.1') . ";dbname=" . env('DB_NAME', 'roza'),
                env('DB_USER', 'root'), env('DB_PASS', ''));
            $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return $pdo;
        } catch (PDOException $e) {
            echo "Database Connection Failed: " . $e->getMessage() . "\n";
            return null;
        }
    }

    private function executeQuery(string $query, OutputInterface $output, string $action)
    {
        try {
            $this->pdo->exec($query);
            $output->writeln("<info>{$action} executed successfully.</info>");
        } catch (PDOException $e) {
            $output->writeln("<error>Error during {$action}: " . $e->getMessage() . "</error>");
        }
    }
}
