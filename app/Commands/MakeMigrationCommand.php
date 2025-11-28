<?php

namespace App\Commands;

use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeMigrationCommand extends Command
{
    protected static $defaultName = 'make:migration';

    protected function configure()
    {
        $this
            ->setName('make:migration')
            ->setDescription('Creates a new migration file inside app/migrate/')
            ->setHelp('Use this command to generate a migration file.')
            ->addArgument('table', InputArgument::REQUIRED, 'The name of the table.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $query = '';
        $tableName = $input->getArgument('table');
        $folderPath = __DIR__ . '/../migrate/';
        $timestamp = date('Ymd_His');
        $fileName = "{$timestamp}_create_{$tableName}_table.php";
        $filePath = $folderPath . $fileName;

        // Ensure the migrate folder exists
        if (!is_dir($folderPath)) {
            mkdir($folderPath, 0777, true);
        }

        // Migration template
        $migrationTemplate = <<<PHP
<?php

return new class {
    public function create()
    {
        // Add or Modify your table structure and query logic here
        return "CREATE TABLE {$tableName} (
                    id INT(6) UNSIGNED AUTO_INCREMENT PRIMARY KEY,
                    firstname VARCHAR(30) NOT NULL,
                    lastname VARCHAR(30) NOT NULL,
                    email VARCHAR(50),
                    reg_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
                )";
    
    }

    public function drop()
    {
       return "DROP TABLE {$tableName}";
        // Add rollback logic here
    }
};
PHP;

        // Create the migration file
        file_put_contents($filePath, $migrationTemplate);

        $output->writeln("<info>Migration created:</info> app/migrate/{$fileName}");
        return Command::SUCCESS;
    }
}
