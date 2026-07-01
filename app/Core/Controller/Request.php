<?php

namespace App\Core\Controller;

class Request
{
    /**
     * @var array
     */
    private array $get;

    /**
     * @var array
     */
    private array $post;

    /**
     * @var array
     */
    private array $server;

    /**
     * @var array
     */
    private array $cookie;

    /**
     * @var array
     */
    private array $files;

    /**
     * @var array
     */
    private array $body;

    public function __construct()
    {
        $this->get = $_GET;
        $this->post = $_POST;
        $this->server = $_SERVER;
        $this->cookie = $_COOKIE;
        $this->files = $_FILES;
        $this->body = $this->parseBody();
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function getParam(string $key, $default = null)
    {
        return $this->get[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function getPost(string $key, $default = null)
    {
        return $this->post[$key] ?? $default;
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function getRequest(string $key, $default = null)
    {
        return $_REQUEST[$key] ?? $default;
    }

    /**
     * @return array
     */
    public function getQueryParams(): array
    {
        return $this->get;
    }

    /**
     * @return array
     */
    public function getPostParams(): array
    {
        return $this->post;
    }

    /**
     * @return array
     */
    public function getParams(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * @return array
     */
    public function getFiles(): array
    {
        return $this->files;
    }

    /**
     * @param string $key
     * @return mixed|null
     */
    public function getFile(string $key)
    {
        return $this->files[$key] ?? null;
    }

    /**
     * @param string $name
     * @return string|null
     */
    public function getHeader(string $name): ?string
    {
        $header = 'HTTP_' . strtoupper(str_replace('-', '_', $name));
        return $this->server[$header] ?? null;
    }

    /**
     * @return array
     */
    public function getHeaders(): array
    {
        $headers = [];
        foreach ($this->server as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('HTTP_', '', $key);
                $header = str_replace('_', '-', $header);
                $headers[$header] = $value;
            }
        }
        return $headers;
    }

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    /**
     * @param string $method
     * @return bool
     */
    public function isMethod(string $method): bool
    {
        return $this->getMethod() === strtoupper($method);
    }

    /**
     * @return bool
     */
    public function isGet(): bool
    {
        return $this->isMethod('GET');
    }

    /**
     * @return bool
     */
    public function isPost(): bool
    {
        return $this->isMethod('POST');
    }

    /**
     * @return bool
     */
    public function isPut(): bool
    {
        return $this->isMethod('PUT');
    }

    /**
     * @return bool
     */
    public function isDelete(): bool
    {
        return $this->isMethod('DELETE');
    }

    /**
     * @return bool
     */
    public function isAjax(): bool
    {
        return isset($this->server['HTTP_X_REQUESTED_WITH']) &&
            strtolower($this->server['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    }

    /**
     * @return string|null
     */
    public function getClientIp(): ?string
    {
        $ip = $this->server['HTTP_CLIENT_IP'] ??
            $this->server['HTTP_X_FORWARDED_FOR'] ??
            $this->server['REMOTE_ADDR'] ?? null;

        if ($ip && strpos($ip, ',') !== false) {
            $ips = explode(',', $ip);
            $ip = trim($ips[0]);
        }

        return $ip;
    }

    /**
     * @return string|null
     */
    public function getUserAgent(): ?string
    {
        return $this->server['HTTP_USER_AGENT'] ?? null;
    }

    /**
     * @return string|null
     */
    public function getReferer(): ?string
    {
        return $this->server['HTTP_REFERER'] ?? null;
    }

    /**
     * @return string
     */
    public function getUri(): string
    {
        return $this->server['REQUEST_URI'] ?? '/';
    }

    /**
     * @return string
     */
    public function getBaseUrl(): string
    {
        $protocol = isset($this->server['HTTPS']) && $this->server['HTTPS'] === 'on' ? 'https' : 'http';
        $host = $this->server['HTTP_HOST'] ?? 'localhost';
        return $protocol . '://' . $host;
    }

    /**
     * @return string
     */
    public function getFullUrl(): string
    {
        return $this->getBaseUrl() . $this->getUri();
    }

    /**
     * @return array
     */
    public function getBody(): array
    {
        return $this->body;
    }

    /**
     * @return string
     */
    public function getRawBody(): string
    {
        return file_get_contents('php://input');
    }

    /**
     * @return array
     */
    private function parseBody(): array
    {
        $method = $this->getMethod();

        if ($method === 'POST') {
            return $this->post;
        }

        if (in_array($method, ['PUT', 'DELETE', 'PATCH'])) {
            $rawBody = $this->getRawBody();

            $contentType = $this->getHeader('CONTENT_TYPE');

            if (strpos($contentType, 'application/json') !== false) {
                return json_decode($rawBody, true) ?? [];
            }

            if (strpos($contentType, 'application/x-www-form-urlencoded') !== false) {
                parse_str($rawBody, $data);
                return $data;
            }
        }

        return [];
    }

    /**
     * @return array
     */
    public function getPagination(): array
    {
        $page = max(1, (int) $this->getParam('page', 1));
        $perPage = min(100, (int) $this->getParam('per_page', 10));
        $offset = ($page - 1) * $perPage;

        return [
            'page' => $page,
            'per_page' => $perPage,
            'offset' => $offset
        ];
    }

    /**
     * @return array
     */
    public function getSorting(): array
    {
        $field = $this->getParam('sort_field', 'id');
        $direction = strtoupper($this->getParam('sort_direction', 'DESC'));

        if (!in_array($direction, ['ASC', 'DESC'])) {
            $direction = 'DESC';
        }

        return [
            'field' => $field,
            'direction' => $direction
        ];
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->get[$key]) || isset($this->post[$key]);
    }

    /**
     * @return array
     */
    public function all(): array
    {
        return array_merge($this->get, $this->post);
    }

    /**
     * @param $name
     * @return mixed|null
     */
    public function __get($name)
    {
        return $this->getParam($name);
    }
}