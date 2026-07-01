<?php

namespace App\Blog\Model;

use App\Core\Model\AbstractModel;

class PostToCategory extends AbstractModel
{
    const POST_ID = 'post_id';
    const CATEGORY_ID = 'category_id';

    protected string $_table = 'blog_post_to_category';

    protected array $_fillable = [self::POST_ID, self::CATEGORY_ID];
    protected string $updatedAt = '';

    /**
     * @param int $value
     * @return $this
     */
    public function setPostId(int $value): static
    {
        return $this->setData(self::POST_ID, $value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setCategoryId(int $value): static
    {
        return $this->setData(self::CATEGORY_ID, $value);
    }

    /**
     * @return int
     */
    public function getPostId(): int
    {
        return $this->getData(self::POST_ID);
    }

    /**
     * @return int
     */
    public function getCategoryId(): int
    {
        return $this->getData(self::CATEGORY_ID);
    }

    /**
     * @param int $postId
     * @param array $categoryId
     * @return array
     */
    public function assignPostToCategories(int $postId, array $categoryIds): array
    {
        $this->db->delete(
            $this->_table,
            self::POST_ID . ' = ?',
            [$postId]);
        $data = [];
        foreach ($categoryIds as $categoryId) {
            $data[] = [
                self::POST_ID => $postId,
                self::CATEGORY_ID => $categoryId
            ];
        }

        return $this->db->insert($this->_table, $data);
    }
}