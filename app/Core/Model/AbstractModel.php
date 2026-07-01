<?php

namespace App\Core\Model;
use App\Core\Model\Database;
class AbstractModel
{

    /**
     * @var Database|null
     */
    protected ?Database $db;

    /**
     * @var string
     */
    protected string $_table = '';

    /**
     * @var string
     */
    protected string $_primaryKey = 'id';

    /**
     * @var array
     */
    protected array $_fillable = [];

    /**
     * @var array
     */
    protected array $_data = [];

    /**
     * @var bool
     */
    protected bool $_hasDataChanges = false;

    /**
     * @var bool
     */
    protected bool $timestamps = true;

    /**
     * @var string
     */
    protected string $_createdAt = 'created_at';

    /**
     * @var string
     */
    protected string $updatedAt = 'updated_at';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }



    /**
     * @param array|string $key
     * @param $value
     * @return $this
     */
    public function setData($key, $value = null): AbstractModel
    {
        if ($key === (array)$key) {
            if ($this->_data !== $key) {
                $this->_hasDataChanges = true;
            }
            $this->_data = $key;
        } else {
            if (!array_key_exists($key, $this->_data) || $this->_data[$key] !== $value) {
                $this->_hasDataChanges = true;
            }
            $this->_data[$key] = $value;
        }
        return $this;
    }

    /**
     * @param $value
     * @return $this
     */
    public function setPrimaryKey($value): AbstractModel
    {
        return $this->setData($this->_primaryKey, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setCreatedAt(string $value): AbstractModel
    {
        return $this->setData($this->_createdAt, $value);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setUpdatedAt(string $value): AbstractModel
    {
        return $this->setData($this->updatedAt, $value);
    }

    /**
     * @param string $key
     * @return array|mixed|null
     */
    public function getData($key = '', $default = null)
    {
        if ('' === $key) {
            return $this->_data;
        }

        if (isset($this->_data[$key])) {
            return $this->_data[$key];
        }

        return $this->data[$key] ?? $default;
    }

    /**
     * @return ?int
     */
    public function getPrimaryKey(): ?int
    {
        return $this->getData($this->_primaryKey)?:0;
    }

    /**
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->getData($this->_createdAt);
    }

    /**
     * @return string
     */
    public function getUpdatedAt(): string
    {
        return $this->getData($this->updatedAt);
    }

    /**
     * @return bool
     * @throws \Exception
     */
    public function save(): bool
    {
        if (!$this->_hasDataChanges) {
            return false;
        }

        try {
            $primaryKey = $this->getPrimaryKey();
            if ($this->timestamps) {
                $now = date('Y-m-d H:i:s');

                if (!$primaryKey) {
                    $this->setCreatedAt($now);
                }

                if (!empty($this->updatedAt)) {
                    $this->setUpdatedAt($now);
                }
            }

            $filteredData = array_intersect_key($this->getData(), array_flip($this->_fillable));

            if (!$primaryKey) {
                $id = $this->db->insert($this->_table, $filteredData);
                $this->setPrimaryKey($id);
            } else {
                $this->db->update(
                    $this->_table,
                    $filteredData,
                    "{$this->_primaryKey} = ?",
                    [$this->getPrimaryKey()]
                );
            }

            return true;

        } catch (\PDOException $e) {
            throw new \Exception("Failed to save {$this->table}: " . $e->getMessage());
        }
    }

    /**
     * @return bool
     */
    public function delete(): bool
    {
        try {
            $this->db->delete(
                $this->_table,
                "{$this->_primaryKey} = ?",
                [$this->getPrimaryKey()]);
        } catch (\PDOException $e) {
            throw new \Exception("Failed to save {$this->table}: " . $e->getMessage());
        }

        return true;
    }
}