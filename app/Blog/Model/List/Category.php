<?php

namespace App\Blog\Model\List;

use App\Core\Model\AbstractModel;
use App\Blog\Model\Category as CategoryModel;
use App\Blog\Model\List\Post as PostList;

class Category extends AbstractModel
{
    /**
     * @var string
     */
    protected string $_table = 'blog_categories';

    /**
     * @var array
     */
    protected array $_fillable = [CategoryModel::PRIMARY_KEY, CategoryModel::TITLE, CategoryModel::ALIAS];

    protected PostList $_postList;

    public function __construct()
    {
        parent::__construct();
        $this->_postList = new PostList();
    }

    /**
     * @return array
     */
    public function getWithPostCount(): array
    {
        $sql = 'SELECT c.' . implode(', c.', $this->_fillable) .', 
                       COUNT(pc.post_id) as posts_count 
                FROM ' . $this->_table .' c
                LEFT JOIN blog_post_to_category pc ON c.id = pc.category_id
                LEFT JOIN blog_posts p ON pc.post_id = p.id
                GROUP BY c.id
                HAVING posts_count > 0
                ORDER BY posts_count DESC';

        return $this->db->fetchAll($sql);
    }

    /**
     * @param int $postsPerCategory
     * @return array
     */
    public function getCategoriesWithRecentPosts(int $postsPerCategory = 3): array
    {
        $categories = $this->getWithPostCount();
        $result = [];

        foreach ($categories as $categoryData) {
            $posts = $this->_postList->getPosts($categoryData[self::PRIMARY_KEY], $postsPerCategory);
            if (count($posts)) {
                $categoryData['posts'] = $posts;
                $result[] = $categoryData;
            }
        }

        return $result;
    }

    /**
     * @param string $alias
     * @return CategoryModel|null
     */
    public function findByAlias(string $alias): ?CategoryModel
    {
        $data = $this->db->selectOne(
            $this->_table,
            CategoryModel::ALIAS . ' = ?',
            [$alias]
        );

        $category = new CategoryModel();
        $category->setData($data);

        return $category;
    }
}