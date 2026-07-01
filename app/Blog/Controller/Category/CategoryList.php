<?php

namespace App\Blog\Controller\Category;

use App\Core\Controller\AbstractController;
use Smarty\Smarty;

class CategoryList extends AbstractController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function execute(): void
    {
        echo 'Test';
    }
}