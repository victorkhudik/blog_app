<?php
require_once __DIR__ . '/../config/bootstrap.php';

try {
    ConfigLoader::loadEnv(__DIR__ . '/../.env');
} catch (Exception $e) {
    echo "\033[31m✗ Error loading .env: " . $e->getMessage() . "\033[0m\n";
    exit(1);
}

use App\Core\Model\Database;
use App\Blog\Model\Post;
use App\Blog\Model\Category;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver as GdDriver;

class Seeder
{
    public function run() {
        echo "\033[36m═══════════════════════════════════════════\033[0m\n";
        echo "\033[36m  Blog Sample Data Setup\033[0m\n";
        echo "\033[36m═══════════════════════════════════════════\033[0m\n\n";
        $faker = Faker\Factory::create();
        $faker->addProvider(new \Hatchet\FakerLoremFlickrKittens\FakerLoremFlickrKittensProvider($faker));

        echo "\nCreating categories...\n\n";
        for ($categoryIndex = 0; $categoryIndex < $_ENV['SAMPLE_CATEGORIES_LENGTHS']; $categoryIndex++) {
            $category = new Category();
            $category->setTitle($faker->sentence(20));
            $category->setDescription($faker->text());
            $category->save();
        }

        $db = Database::getInstance();
        $categoryIds = $db->fetchAll('select id from blog_categories');

        echo "\nCreating posts...\n\n";
        for ($postIndex = 0; $postIndex < $_ENV['SAMPLE_POSTS_LENGTHS']; $postIndex++) {
            $post = new Post();
            $post->setTitle($faker->sentence(20));
            $post->setDescription($faker->text());
            $html = "<h1>" . $faker->sentence(5) . "</h1>\n";
            $html .= "<p>" . $faker->paragraph(3) . "</p>\n";
            $html .= "<p>" . $faker->paragraph(5) . "</p>\n";
            $html .= "<p>" . $faker->paragraph(4) . "</p>\n";
            $post->setContent($html);
            $post->setMainImage($faker->image('pub/media/images/posts', $_ENV['POST_IMAGE_WIDTH'], $_ENV['POST_IMAGE_HEIGHT']));
            $post->setPublishedDate($faker->date());
            $postCategoryIds = [];
            $randomCategoriesCount = rand(0, count($categoryIds));
            if ($randomCategoriesCount > 0) {
                for ($i = 0; $i < $randomCategoriesCount; $i++) {
                    $randomCategoryId = array_values($categoryIds[array_rand($categoryIds)])[0];
                    if (!in_array($randomCategoryId, $postCategoryIds)) {
                        $postCategoryIds[] = $randomCategoryId;
                    }
                }
            }
            $imagePath = $post->getMainImage();
            $imageType = pathinfo($imagePath, PATHINFO_EXTENSION);
            $smallImagePath = str_replace('.'.$imageType, '', $imagePath) . '-small.' . $imageType;
            $manager = new ImageManager(GdDriver::class);
            $image = $manager->read($imagePath);
            $image->resize($_ENV['POST_IMAGE_WIDTH_SMALL'], $_ENV['POST_IMAGE_HEIGHT_SMALL']);
            $image->save($smallImagePath);

            $post->setListImage($smallImagePath);
            $post->setCategoryIds($postCategoryIds);

            $post->save();
        }

        echo "\n\033[32m✔ Setup completed successfully!\033[0m\n";
    }
}

if (php_sapi_name() === 'cli') {
    $seeder = new Seeder();
    $seeder->run();
} else {
    die("This script can only be run from the command line.");
}