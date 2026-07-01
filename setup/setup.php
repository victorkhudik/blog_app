#!/usr/bin/env php
<?php
require_once __DIR__ . '/../config/bootstrap.php';

try {
    ConfigLoader::loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    echo "\033[31m✗ Error loading .env: " . $e->getMessage() . "\033[0m\n";
    exit(1);
}

use App\Core\Model\Database;

class Setup
{
    private $db;
    private $lockFile = __DIR__ . '/.installed';
    private $migrations = [];

    public function __construct()
    {
        $this->db = Database::getInstance();
        $this->migrations = [
            $this->createCategoriesTable(),
            $this->createPostsTable(),
            $this->createPostToCategoryTable()
        ];
    }

    private function createCategoriesTable()
    {
        return "CREATE TABLE IF NOT EXISTS `blog_categories` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `alias` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_alias` (`alias`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
    }

    private function createPostsTable()
    {
        return "CREATE TABLE IF NOT EXISTS `blog_posts` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `title` VARCHAR(255) NOT NULL,
            `alias` VARCHAR(255) NOT NULL,
            `description` TEXT NULL,
            `content` LONGTEXT NOT NULL,
            `main_image` VARCHAR(255) NULL,
            `list_image` VARCHAR(255) NULL,
            `views` INT(11) NOT NULL DEFAULT 0,
            `published_date` DATETIME NULL DEFAULT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP NULL DEFAULT NULL ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_alias` (`alias`),
            INDEX `idx_views` (`views`)
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
    }

    private function createPostToCategoryTable()
    {
        return "CREATE TABLE IF NOT EXISTS `blog_post_to_category` (
            `id` INT(11) NOT NULL AUTO_INCREMENT,
            `post_id` INT(11) NOT NULL,
            `category_id` INT(11) NOT NULL,
            `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `uk_post_category` (`post_id`, `category_id`),
            CONSTRAINT `fk_pc_post` FOREIGN KEY (`post_id`) 
                REFERENCES `blog_posts` (`id`) ON DELETE CASCADE,
            CONSTRAINT `fk_pc_category` FOREIGN KEY (`category_id`) 
                REFERENCES `blog_categories` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;";
    }

    private function isLocked()
    {
        return file_exists($this->lockFile);
    }

    private function lock()
    {
        $data = [
            'installed_at' => date('Y-m-d H:i:s'),
            'db_name' => $_ENV['DB_NAME'],
            'php_version' => PHP_VERSION
        ];
        file_put_contents($this->lockFile, json_encode($data, JSON_PRETTY_PRINT));
    }
    public function run()
    {
        echo "\033[36m═══════════════════════════════════════════\033[0m\n";
        echo "\033[36m  Blog Application Setup\033[0m\n";
        echo "\033[36m═══════════════════════════════════════════\033[0m\n\n";

        if ($this->isLocked()) {
            $lockData = json_decode(file_get_contents($this->lockFile), true);
            echo "\033[33m⚠ System is already installed!\033[0m\n";
            echo "  Installed at: " . ($lockData['installed_at'] ?? 'unknown') . "\n";
            echo "  Database: " . ($lockData['db_name'] ?? 'unknown') . "\n";
            echo "  PHP version: " . ($lockData['php_version'] ?? 'unknown') . "\n";
            echo "\nDelete file " . $this->lockFile . " to reinstall.\n";
            exit(0);
        }

        try {
            echo "\nCreating tables...\n\n";

            foreach ($this->migrations as $sql) {
                $this->db->execute($sql);
                echo "\033[32m✓ Table created successfully\033[0m\n";
            }

            $tables = $this->db->fetchAll("SHOW TABLES");
            echo "\n\033[36mCreated tables:\033[0m\n";
            foreach ($tables as $row) {
                $tableName = reset($row);
                echo "  - {$tableName}\n";
            }

            $imageFolderPath = __DIR__ . '/../pub/media/images/posts';

            if (!is_dir($imageFolderPath) && mkdir($imageFolderPath, 0777, true)) {
                echo "\n\033[32m✔ Directory for media files created!\033[0m\n";
            }

            $this->lock();

            echo "\n\033[32m✔ Setup completed successfully!\033[0m\n";

        } catch (\PDOException $e) {
            echo "\033[31m✗ Error: " . $e->getMessage() . "\033[0m\n";
            exit(1);
        }
    }
}

if (php_sapi_name() === 'cli') {
    $setup = new Setup();
    $setup->run();
} else {
    die("This script can only be run from the command line.");
}