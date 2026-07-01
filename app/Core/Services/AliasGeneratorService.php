<?php

namespace App\Core\Services;

use App\Core\Model\Database;

class AliasGeneratorService
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * @param string $table
     * @param string $alias
     * @param int|null $excludeId
     * @return string
     */
    public function generateUniqueAlias(string $table, string $alias, ?int $excludeId = null): string
    {
        $cleanAlias = $this->sanitizeAlias($alias);

        // Если алиас пустой, генерируем из времени
        if (empty($cleanAlias)) {
            $cleanAlias = 'item-' . time();
        }

        $originalAlias = $cleanAlias;
        $counter = 1;

        while ($this->isAliasExists($table, $cleanAlias, $excludeId)) {
            $cleanAlias = $originalAlias . '-' . $counter;
            $counter++;
        }

        return $cleanAlias;
    }

    /**
     * @param string $alias
     * @return string
     */
    public function sanitizeAlias(string $alias): string
    {
        $alias = mb_strtolower(trim($alias));
        $alias = preg_replace('/[^a-zа-я0-9-\s]/u', '', $alias);
        $alias = preg_replace('/[\s]+/', '-', $alias);
        $alias = preg_replace('/-+/', '-', $alias);
        return trim($alias, '-');
    }

    /**
     * @param string $table
     * @param string $alias
     * @param int|null $excludeId
     * @return bool
     */
    private function isAliasExists(string $table, string $alias, ?int $excludeId = null): bool
    {
        $sql = "SELECT COUNT(*) as count FROM {$table} WHERE alias = ?";
        $params = [$alias];

        if ($excludeId !== null) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        $result = $this->db->fetchOne($sql, $params);
        return $result && $result['count'] > 0;
    }

    /**
     * @param string $table
     * @param string $baseAlias
     * @return int
     */
    public function getNextAliasNumber(string $table, string $baseAlias): int
    {
        $sql = "SELECT alias FROM {$table} WHERE alias LIKE ? ORDER BY alias DESC LIMIT 1";
        $pattern = $this->sanitizeAlias($baseAlias) . '-%';

        $result = $this->db->fetchOne($sql, [$pattern]);

        if (!$result) {
            return 1;
        }

        // Извлекаем номер из алиаса
        preg_match('/-(\d+)$/', $result['alias'], $matches);
        return isset($matches[1]) ? (int) $matches[1] + 1 : 1;
    }
}