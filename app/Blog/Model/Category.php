<?php

namespace App\Blog\Model;

use App\Core\Model\AbstractModel;
use App\Core\Services\AliasGeneratorService;

class Category extends AbstractModel
{
    const TITLE = 'title';
    const ALIAS = 'alias';
    const DESCRIPTION = 'description';

    protected string $_table = 'blog_categories';
    protected array $_fillable = [self::TITLE, self::ALIAS, self::DESCRIPTION];

    /**
     * @var AliasGeneratorService
     */
    private AliasGeneratorService $aliasGenerator;

    public function __construct()
    {
        parent::__construct();
        $this->aliasGenerator = new AliasGeneratorService();
    }

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle(string $title): Category
    {
        return $this->setData(self::TITLE, $title);
    }

    /**
     * @param string $alias
     * @return $this
     */
    public function setAlias(string $alias): Category
    {
        return $this->setData(self::ALIAS, $alias);
    }

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription(string $description): Category
    {
        return $this->setData(self::DESCRIPTION, $description);
    }

    /**
     * @return ?string
     */
    public function getTitle(): ?string
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
     * @return ?string
     */
    public function getDescription(): ?string
    {
        return $this->getData(self::DESCRIPTION);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save(): bool
    {
        $alias = $this->getAlias();
        if (!$alias) {
            $this->setAlias($this->aliasGenerator->sanitizeAlias($this->getTitle()));
        }

        $this->setAlias($this->aliasGenerator->generateUniqueAlias(
            $this->_table,
            $this->getAlias()
        ));

        return parent::save();
    }
}