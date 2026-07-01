<?php

namespace App\Core\Helper;

class PaginationHelper
{
    /**
     * @param int $currentPage
     * @param int $total
     * @param int $perPage
     * @param array $params
     * @return array
     */
    public static function build(int $currentPage, int $total, int $perPage = 10, array $params = []): array
    {
        $lastPage = max(1, ceil($total / $perPage));
        $currentPage = max(1, min($currentPage, $lastPage));

        // Базовый URL с параметрами
        $baseUrl = self::buildBaseUrl($params);

        return [
            'current_page' => $currentPage,
            'last_page' => $lastPage,
            'per_page' => $perPage,
            'total' => $total,
            'base_url' => $baseUrl,
            'show_edges' => true,
            'max_pages' => 5
        ];
    }

    /**
     * @param array $params
     * @return string
     */
    private static function buildBaseUrl(array $params): string
    {
        // Удаляем параметр page из массива
        unset($params['page']);

        // Если есть другие параметры
        if (!empty($params)) {
            return '?' . http_build_query($params) . '&';
        }

        return '?';
    }

    /**
     * @param int $page
     * @param int $perPage
     * @return int
     */
    public static function getOffset(int $page, int $perPage = 10): int
    {
        return max(0, ($page - 1) * $perPage);
    }

    /**
     * @param array $params
     * @return array
     */
    public static function buildWithSort(array $params): array
    {
        // Добавляем параметры сортировки в URL
        $queryParams = [];

        if (!empty($params['sort'])) {
            $queryParams['sort'] = $params['sort'];
        }

        if (!empty($params['dir'])) {
            $queryParams['dir'] = $params['dir'];
        }

        return self::build(
            $params['page'] ?? 1,
            $params['total'] ?? 0,
            $params['per_page'] ?? 10,
            $queryParams
        );
    }
}
