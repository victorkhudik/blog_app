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
     * @return array
     */
    public function getPosts(int $categoryId, int $limit = null): array
    {
        $sql = 'SELECT p.' . implode(', p.', $this->_fillable). ' FROM blog_posts p
                INNER JOIN blog_post_to_category pc ON p.id = pc.post_id
                WHERE pc.category_id = ?
                AND p.published_date IS NOT NULL 
                AND p.published_date <= NOW()
                ORDER BY p.published_date DESC';

        if ($limit !== null) {
            $sql .= ' LIMIT ' . $limit;
        }

        return $this->db->fetchAll($sql, [$categoryId]);
    }
}