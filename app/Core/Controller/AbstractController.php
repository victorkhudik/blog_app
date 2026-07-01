<?php

namespace App\Core\Controller;

use App\Core\Controller\Request;
use App\Core\Controller\View;

abstract class AbstractController
{
    /**
     * @var Request
     */
    protected Request $request;

    /**
     * @var View
     */
    protected View $view;

    public function __construct()
    {
        $this->request = new Request();
        $this->view = new View();
    }

    abstract public function execute(...$params): void;

    /**
     * @param string $key
     * @param $default
     * @return mixed
     */
    protected function getParam(string $key, $default = null): mixed
    {
        return $this->request->getParam($key, $default);
    }
}