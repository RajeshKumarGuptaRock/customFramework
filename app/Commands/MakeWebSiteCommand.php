<?php

namespace App\Commands;

use PDO;
use PDOException;
use config\EnvConfig;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class MakeWebSiteCommand extends Command
{
    protected static $defaultName = 'make:website';
    public function __construct()
    {
        parent::__construct(); // Always call parent constructor
    }
    
    protected function configure()
    {
        $this
            ->setName('make:website')
            ->setDescription('Make a basic website using PHP.')
            ->setHelp('This command make basic website, which contains Some pages like Home, contact,terms pages')
            ->addArgument('option', InputArgument::REQUIRED, 'option to create or remove website files.');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $option = $input->getArgument('option');
        $basePath = dirname(__DIR__, 2); // Go to project root
        if ($option === 'create') {
            $output->writeln("<info>Make website command execution start.</info>");
            $controllerName = 'WebsiteController';
            // Create routes file
            $this->createFile($basePath . '/routes/web.php', $this->getRoutesTemplate($controllerName),true);
            $output->writeln('<info>Routes file created successfully.</info>');

            // Create views directory if not exists
            $viewsPath = $basePath . '/app/Views/Website/';
            if (!is_dir($viewsPath)) {
                mkdir($viewsPath, 0777, true);
            }
            // Create view files
            $this->createFile($viewsPath . 'home.php', $this->getViewTemplate('Home Page'));
            $this->createFile($viewsPath . 'contact.php', $this->getViewTemplate('Contact Us'));
            $this->createFile($viewsPath . 'term.php', $this->getViewTemplate('Term and Condition'));
            $output->writeln('<info>Routes file created successfully.</info>');

            // Create Controller directory if not exists
            $controllerPath = $basePath . '/app/Controllers/';
            if (!is_dir($controllerPath)) {
                mkdir($controllerPath, 0777, true);
            }
            // Create view files
            $this->createFile($controllerPath . 'WebsiteController.php', $this->getControllerTemplate($controllerName));
            
            
            // Create core view directory if not exists
            $coreViewPath = $basePath . '/app/core/';
            if (!is_dir($coreViewPath)) {
                mkdir($coreViewPath, 0777, true);
            }
            // Create view files
            $this->createFile($coreViewPath . 'Website.php', $this->getCoreViewTemplate());
            
            $output->writeln('<info>Controller Core view file created successfully.</info>');

            return Command::SUCCESS;
        } elseif ($option === 'remove') {
            // Define file paths
            $controllerFile =$basePath. '/app/Controllers/WebsiteController.php';
            $coreFile = $basePath . '/app/core/Website.php';
            $viewsFolder = $basePath . '/app/Views/Website';
            $routesFile = $basePath. "/routes/web.php";

            if (file_exists($coreFile)) {
                unlink($coreFile);
                $output->writeln('<info>Core file deleted successfully.Path '. $coreFile.'</info>');
            }

            // Remove views folder
            if (is_dir($viewsFolder)) {
                $this->removeFolder($viewsFolder);
                $output->writeln('<info>View file deleted successfully.Path '.$viewsFolder.'</info>');
            }

            // Remove routes from web.php
            $this->removeRoutes($routesFile);
            $output->writeln('<info>Route file deleted successfully.Path '.$routesFile.'</info>');

            // Remove files
            if (file_exists($controllerFile)) {
                unlink($controllerFile);
                $output->writeln('<info>Controller file deleted successfully.Path '. $controllerFile.'</info>');
            }
            
            echo "All Website files removed successfully!";
            return Command::SUCCESS;
        } else {
            die("Invalid command. Use 'create' or 'remove'.");
        }
    }

    function removeFolder($folder) {
        $files = array_diff(scandir($folder), ['.', '..']);
        foreach ($files as $file) {
            $filePath = "$folder/$file";
            is_dir($filePath) ? $this->removeFolder($filePath) : unlink($filePath);
        }
        rmdir($folder);
    }
    
   
    function removeRoutes($routesFile) {
        $basePath = dirname(__DIR__, 2); // Go to project root
        $controllerFile = $basePath. '/app/Controllers/WebsiteController.php';
       
        if (!file_exists($controllerFile)) {
           echo "WebsiteController.php not found. Skipping route removal.\n";
            return;
        }
    
        // Read controller file content
        $controllerContent = file_get_contents($controllerFile);
    
        // Match all function names in the controller (excluding constructor)
        preg_match_all('/public function (\w+)/', $controllerContent, $matches);
        $methods = $matches[1]; // List of method names
    
        if (empty($methods)) {
            echo "No routes found in WebsiteController.php\n";
            return;
        }
    
        // Read routes file
        if (!file_exists($routesFile)) {
            echo "routes/web.php not found. Skipping route removal.\n";
            return;
        }
    
        $lines = file($routesFile);
        $filteredLines = array_filter($lines, function ($line) use ($methods) {
            foreach ($methods as $method) {
                if (str_contains($line, "WebsiteController@$method")) {
                    return false; // Remove this line
                }
            }
            return true; // Keep other lines
        });
    
        // Overwrite the routes file with cleaned-up content
        file_put_contents($routesFile, implode("", $filteredLines));
    }
    
    private function createFile($filePath, $content, $append = false)
    {
        if ($append && file_exists($filePath)) {
            file_put_contents($filePath, $content, FILE_APPEND); // Append mode
        } else {
            file_put_contents($filePath, $content);
        }
    }

    private function getRoutesTemplate($controllerName)
    {
        return <<<PHP
\$router->add('GET', '/home', '$controllerName@index');
\$router->add('GET', '/contact','$controllerName@contact');
\$router->add('GET', '/term','$controllerName@term');
PHP;
    }

    private function getControllerTemplate($controllerName)
    {
   return <<<PHP
<?php
namespace App\Controllers;
use Core\Website as View; 
class $controllerName {
    public function index() {
         View::render('home', ['title' => 'Home Page']);
    }
        public function contact() {
         View::render('contact', ['title' => 'Contact us']);
    }
        public function term() {
        View::render('term', ['title' => 'Term and Condition']);
    }
}
?>
PHP;
    }

    private function getCoreViewTemplate()
    {
       return <<<PHP
        <?php
        namespace Core;
        class Website {
        public static function render(\$view,\$data=[]){
        extract(\$data);
        include __DIR__ . "/../Views/Website/\$view.php";
        }
        }
        ?>
        PHP;
    }
    private function getViewTemplate($title)
    {
        return <<<HTML
        <!DOCTYPE html>
        <html>

        <head>
            <title>{$title}</title>
            <link rel="stylesheet" href="style.css">
        </head>
        <style>
            * {
            margin: 0;
            padding: 0;
        }

        .navbar {
            display: flex;
            align-items: center;
            justify-content: center;
            position: sticky;
            top: 0;
            padding: 15px;
            cursor: pointer;
        }

        .background {
            background: black;
            background-blend-mode: darken;
            background-size: cover;
        }

        .nav-list {
            width: 70%;
            display: flex;
            align-items: center;
            gap: 20px;
            list-style: none;
        }

        .logo {
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .logo img {
            width: 180px;
            border-radius: 50px;
        }

        .nav-list li {
            list-style: none;
            padding: 26px 30px;
            padding: 10px;
        }

        .nav-list li a {
            text-decoration: none;
            color: white;
        }

        .nav-list li a:hover {
            color: grey;
        }

        .rightnav {
            width: 30%;
            text-align: right;
        }

        #search {
            padding: 5px;
            font-size: 17px;
            border: 2px solid grey;
            border-radius: 9px;
        }

        .firstsection {
            background-color: green;
            height: 400px;
        }

        .secondsection {
            background-color: blue;
            height: 400px;
        }

        .box-main {
            display: flex;
            justify-content: center;
            align-items: center;
            color: black;
            max-width: 80%;
            margin: auto;
            height: 80%;
        }

        .firsthalf {
            width: 100%;
            display: flex;
            flex-direction: column;
            justify-content: center;
        }

        .secondhalf {
            width: 30%;
        }

        .secondhalf img {
            width: 70%;
            border: 4px solid white;
            border-radius: 150px;
            display: block;
            margin: auto;
        }

        .text-big {
            font-family: 'Piazzolla', serif;
            font-weight: bold;
            font-size: 35px;
        }

        .text-small {
            font-size: 18px;
        }

        .btn {
            padding: 8px 20px;
            margin: 7px 0;
            border: 2px solid white;
            border-radius: 8px;
            background: none;
            color: white;
            cursor: pointer;
        }

        .btn-sm {
            padding: 6px 10px;
            vertical-align: middle;
        }

        .section {
            height: 400px;
            display: flex;
            align-items: center;
            justify-content: center;
            max-width: 90%;
            margin: auto;
        }

        .section-Left {
            flex-direction: row-reverse;
        }

        .paras {
            padding: 0px 65px;
        }

        .thumbnail img {
            width: 250px;
            border: 2px solid black;
            border-radius: 26px;
            margin-top: 19px;
        }

        .center {
            text-align: center;
        }

        .text-footer {
            text-align: center;
            padding: 30px 0;
            font-family: 'Ubuntu', sans-serif;
            display: flex;
            justify-content: center;
            color: white;
        }

        footer {
            text-align: center;
            padding: 15px;
        }


        .rightnav {
            width: 100%;
            text-align: right;
            margin-top: 10px;
        }

        #search {
            box-sizing: border-box;
            width: 70%;
            padding: 8px;
            font-size: 17px;
            border: 2px solid grey;
            border-radius: 9px;
        }

        .btn-sm {
            padding: 8px 20px;
            margin: 7px 5px;
        }

        img {
            max-width: 100%;
            height: auto;
        }

        </style>

        <body>
        
            <nav class="navbar background">
                <div class="logo">
                    <img src="https://media.geeksforgeeks.org/gfg-gg-logo.svg" style="height: 30px;" alt="Logo">
                </div>
                <ul class="nav-list">
                    <li><a href="/home">Home</a></li>
                    <li><a href="/contact">Contact Us</a></li>
                    <li><a href="/term">Term & Conditions</a></li>
                </ul>
                <div class="rightnav">
                    <input type="text" name="search" id="search">
                    <button class="btn btn-sm">Search</button>
                </div>
            </nav>
            <section class="section">
                <div class="paras">
                    <h1 class="sectionTag text-big">{$title}</h1>
                    <p class="sectionSubTag text-small">
                        {$title}
                        
                    </p>
                </div>
            </section>
            <footer class="background">
                <p class="text-footer">
                    Copyright ©-All rights are reserved
                </p>
            </footer>
        </body>
        </html>
        HTML;
    }  
}
