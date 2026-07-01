<?php

namespace App\Blog\Controller\Category;

use App\Core\Controller\AbstractController;
use App\Blog\Model\List\Category as CategoryListModel;
use App\Blog\Model\List\Post as PostListModel;
use App\Core\Helper\PaginationHelper;

class View extends AbstractController
{
    /**
     * @var string
     */
    protected string $template = 'category/view.tpl';

    /**
     * @var CategoryListModel
     */
    protected CategoryListModel $categoryList;

    /**
     * @var PostListModel
     */
    protected PostListModel $postList;

    public function __construct()
    {
        parent::__construct();
        $this->categoryList = new CategoryListModel();
        $this->postList = new PostListModel();
    }

    /**
     * @param ...$params
     * @return void
     */
    public function execute(...$params): void
    {
        if (isset($params['alias'])) {
            $category = $this->categoryList->findByAlias($params['alias']);

            if ($category->getPrimaryKey()) {

                $sortOptions = $this->getSortOptions();
                $page = $this->request->getRequest('page', 1);
                $selectedSortOptions = $this->getSelectedSortOptions($sortOptions);
                $posts = $this->postList->getPosts($category->getPrimaryKey(), $_ENV['PAGINATION_ITEMS_PER_PAGE'], $page, $selectedSortOptions['sort'], $selectedSortOptions['dir']);
                $paginationData = PaginationHelper::buildWithSort([
                    'page' => $page,
                    'total' => $this->postList->getPostsCount($category->getPrimaryKey()),
                    'per_page' => $_ENV['PAGINATION_ITEMS_PER_PAGE'],
                    'sort' => $selectedSortOptions['sort'],
                    'dir' => $selectedSortOptions['dir']
                ]);
                echo '<pre>';

                $this->view->display($this->template, [
                    'category' => $category->getData(),
                    'posts' => $posts,
                    'sortOptions' => $sortOptions,
                    'selectedSortOptions' => $selectedSortOptions,
                    'pagination' => $paginationData
                ]);
            }
        }

    }

    /**
     * @return array
     */
    private function getSortOptions(): array
    {
        $sortOptions = $_ENV['SORT_OPTIONS'] ?? '{}';

        return json_decode($sortOptions, true) ?? [];
    }

    /**
     * @param array $sortOptions
     * @return array
     */
    private function getSelectedSortOptions(array $sortOptions): array
    {
        $default = $this->findDefaultSortOption($sortOptions);

        $sortSort = $this->request->getParam('sort');
        $sortDir = $this->request->getParam('dir');

        if (!$sortSort || !isset($sortOptions[$sortSort])) {
            $sortSort = $default['sort'];
            $sortDir = $default['dir'];
        }

        $dirs = array_column($sortOptions[$sortSort], 'dir');
        if (!$sortDir || !in_array($sortDir, $dirs, true)) {
            $sortDir = $dirs[0] ?? $default['dir'];
        }

        return [
            'sort' => $sortSort,
            'dir' => $sortDir
        ];
    }

    /**
     * @param array $sortOptions
     * @return array
     */
    private function findDefaultSortOption(array $sortOptions): array
    {
        foreach ($sortOptions as $sort => $dirs) {
            foreach ($dirs as $option) {
                if (!empty($option['default'])) {
                    return [
                        'sort' => $sort,
                        'dir' => $option['dir']
                    ];
                }
            }
        }

        $firstSort = array_key_first($sortOptions);
        $firstDir = $sortOptions[$firstSort][0]['dir'] ?? 'asc';

        return [
            'sort' => $firstSort,
            'dir' => $firstDir
        ];
    }
}