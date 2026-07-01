<?php

namespace App\Blog\Model\List;

use App\Core\Model\AbstractModel;
use App\Blog\Model\Post as PostModel;

class Post extends AbstractModel
{
    /**
     * @var string
     */
    protected string $_table = 'blog_posts';
    protected array $_fillable = [PostModel::TITLE, PostModel::ALIAS, PostModel::LIST_IMAGE, PostModel::DESCRIPTION];


    /**
     * @param int $categoryId
     * @param int|null $limit
     * @param int $page
     * @param string $sort
     * @param string $dir
     * @return array
     */
    public function getPosts(int $categoryId, int $limit = null, int $page, string $sort = 'published_date', string $dir = 'desc'): array
    {
        $sql = 'SELECT p.' . implode(', p.', $this->_fillable) . ' FROM blog_posts p
                INNER JOIN blog_post_to_category pc ON p.id = pc.post_id
                WHERE pc.category_id = ?
                AND p.published_date IS NOT NULL 
                AND p.published_date <= NOW()';

        $sql .= ' ORDER BY p.' . $sort . ' ' . strtoupper($dir);

        if ($limit !== null) {
            $offset = ($page - 1) * $limit;
            $sql .= ' LIMIT ' . $limit . ' OFFSET ' . $offset;
        }

        return $this->db->fetchAll($sql, [$categoryId]);
    }

    /**
     * @param int $categoryId
     * @return int
     */
    public function getPostsCount(int $categoryId): int
    {
        $sql = "SELECT COUNT(*) as total FROM blog_posts p
                     INNER JOIN blog_post_to_category pc ON p.id = pc.post_id
                     WHERE pc.category_id = ?
                     AND p.published_date IS NOT NULL
    AND p.published_date <= NOW()";

        $countResult = $this->db->fetchOne($sql, [$categoryId]);

        return $countResult ? (int)$countResult['total'] : 0;
    }

    /**
     * @param string $alias
     * @return CategoryModel|null
     */
    public function findByAlias(string $alias): ?CategoryModel
    {
        $data = $this->db->selectOne(
            $this->_table,
            PostModel::ALIAS . ' = ?',
            [$alias]
        );

        $post = new PostModel();
        $post->setData($data);

        return $post;
    }
}