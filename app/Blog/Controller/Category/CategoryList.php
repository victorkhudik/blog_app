<?php

namespace App\Blog\Controller\Category;

use App\Core\Controller\AbstractController;
use App\Blog\Model\List\Category;

class CategoryList extends AbstractController
{
    /**
     * @var Category
     */
    protected Category $categoryList;

    protected string $template = 'category/list.tpl';
    public function __construct()
    {
        parent::__construct();
        $this->categoryList = new Category();
    }

    public function execute(): void
    {
        $this->view->display($this->template, [
            'categories' => $this->categoryList->getCategoriesWithRecentPosts(),
        ]);
    }
}