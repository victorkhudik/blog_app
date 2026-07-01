<?php

namespace App\Blog\Model;

use App\Core\Model\AbstractModel;
use App\Blog\Model\PostToCategory;
use App\Core\Services\AliasGeneratorService;
use HTMLPurifier;
use HTMLPurifier_Config;

class Post extends AbstractModel
{
    const TITLE = 'title';
    const ALIAS = 'alias';
    const DESCRIPTION = 'description';
    const CONTENT = 'content';
    const MAIN_IMAGE = 'main_image';
    const LIST_IMAGE = 'list_image';
    const VIEWS = 'views';
    const PUBLISHED_DATE = 'published_date';
    const CATEGORY_IDS = 'category_ids';

    protected string $_table = 'blog_posts';
    protected array $_fillable = [self::TITLE, self::ALIAS, self::DESCRIPTION, self::CONTENT, self::MAIN_IMAGE, self::LIST_IMAGE, self::VIEWS, self::PUBLISHED_DATE];

    /**
     * @var AliasGeneratorService
     */
    private AliasGeneratorService $aliasGenerator;

    /**
     * @var \App\Blog\Model\PostToCategory
     */
    private PostToCategory $postToCategory;

    public function __construct()
    {
        parent::__construct();
        $this->aliasGenerator = new AliasGeneratorService();
        $this->postToCategory = new PostToCategory();
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setTitle(string $value): Post
    {
        return $this->setData(self::TITLE, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setAlias(string $value): Post
    {
        return $this->setData(self::ALIAS, $value);
    }

    public function setDescription(string $value): Post
    {
        return $this->setData(self::DESCRIPTION, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setContent(string $value): Post
    {
        return $this->setData(self::CONTENT, $this->sanitizeText($value));
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setMainImage(string $value): Post
    {
        return $this->setData(self::MAIN_IMAGE, $value);
    }

    /**
     * @param string $value
     * @return Post
     */
    public function setListImage(string $value): Post
    {
        return $this->setData(self::LIST_IMAGE, $value);
    }

    /**
     * @param int $value
     * @return $this
     */
    public function setViews(int $value): Post
    {
        return $this->setData(self::VIEWS, $value);
    }

    /**
     * @param string $date
     * @return $this
     */
    public function setPublishedDate(string $date): Post
    {
        return $this->setData(self::PUBLISHED_DATE, $date);
    }

    /**
     * @param array $value
     * @return $this
     */
    public function setCategoryIds(array $value): Post
    {
        return $this->setData(self::CATEGORY_IDS, $value);
    }

    /**
     * @return string
     */
    public function getTitle(): string
    {
        return $this->getData(self::TITLE);
    }

    /**
     * @return ?string
     */
    public function getAlias(): ?string
    {
        return $this->getData(self::ALIAS);
    }

    /**
     * @return string
     */
    public function getDescription(): string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @return string
     */
    public function getContent(): string
    {
        return $this->getData(self::CONTENT);
    }

    /**
     * @return string
     */
    public function getMainImage(): string {
        return $this->getData(self::MAIN_IMAGE);
    }

    /**
     * @return string
     */
    public function getListImage(): string {
        return $this->getData(self::LIST_IMAGE);
    }

    /**
     * @return string
     */
    public function getPublishedDate(): string
    {
        return $this->getData(self::PUBLISHED_DATE);
    }

    /**
     * @return array
     */
    public function getCategoryIds(): array
    {
        return $this->getData(self::CATEGORY_IDS);
    }

    public function save(): bool
    {
        try {
            $alias = $this->getAlias();
            if (!$alias) {
                $this->setAlias($this->aliasGenerator->sanitizeAlias($this->getTitle()));
            }

            $this->setAlias($this->aliasGenerator->generateUniqueAlias(
                $this->_table,
                $this->getAlias()
            ));

            if (parent::save() && count($this->getCategoryIds())) {
                $this->postToCategory->assignPostToCategories($this->getPrimaryKey(), $this->getCategoryIds());
            }
            return true;

        } catch (\Exception $e) {
            var_dump($this->getPrimaryKey(), $this->getCategoryIds());
            throw new \Exception("Failed assign post to categories: " . $e->getMessage());
        }

    }

    /**
     * @param string $value
     * @return string
     */
    private function sanitizeText(string $value): string
    {

        $config = HTMLPurifier_Config::createDefault();
        $purifier = new HTMLPurifier($config);

        return $purifier->purify($value);
    }
}