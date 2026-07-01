<?php

namespace App\Core\Controller;

use Smarty\Smarty;

class View
{
    /**
     * @var Smarty
     */
    private Smarty $smarty;

    /**
     * @var array
     */
    private array $data = [];

    /**
     * @var string
     */
    private string $layout = 'layouts/main.tpl';

    public function __construct()
    {
        $this->initSmarty();
        $this->data['base_url'] = $_ENV['BASE_URL'] ?? '/';
        $this->data['site_name'] = $_ENV['SITE_NAME'] ?? 'Blog';
    }

    /**
     * @return void
     */
    private function initSmarty(): void
    {
        $this->smarty = new Smarty();
        $this->smarty->setTemplateDir(__DIR__ . '/../../../templates/');
        $this->smarty->setCompileDir(__DIR__ . '/../../../var/templates_c/');
        $this->smarty->setCacheDir(__DIR__ . '/../../../var/cache/');
        $this->smarty->setConfigDir(__DIR__ . '/../../../config/');
    }

    /**
     * @param string $key
     * @param $value
     * @return $this
     */
    public function assign(string $key, $value): self
    {
        $this->data[$key] = $value;
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function assignMultiple(array $data): self
    {
        foreach ($data as $key => $value) {
            $this->assign($key, $value);
        }
        return $this;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function render(string $template, array $data = []): string
    {
        // Объединяем данные
        $allData = array_merge($this->data, $data);

        // Назначаем все переменные
        foreach ($allData as $key => $value) {
            $this->smarty->assign($key, $value);
        }

        // Рендерим
        return $this->smarty->fetch($template);
    }

    /**
     * @param string $template
     * @param array $data
     * @return void
     */
    public function display(string $template, array $data = []): void
    {
        echo $this->render($template, $data);
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function renderWithLayout(string $template, array $data = []): string
    {
        $content = $this->render($template, $data);
        return $this->render($this->layout, array_merge($data, ['content' => $content]));
    }

    /**
     * @param string $template
     * @param array $data
     * @return void
     */
    public function displayWithLayout(string $template, array $data = []): void
    {
        echo $this->renderWithLayout($template, $data);
    }

    /**
     * @param string $layout
     * @return $this
     */
    public function setLayout(string $layout): self
    {
        $this->layout = $layout;
        return $this;
    }

    /**
     * @return string
     */
    public function getLayout(): string
    {
        return $this->layout;
    }

    /**
     * @return $this
     */
    public function clear(): self
    {
        $this->data = [];
        return $this;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function addData(array $data): self
    {
        $this->data = array_merge($this->data, $data);
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function has(string $key): bool
    {
        return isset($this->data[$key]);
    }

    /**
     * @param string $key
     * @param $default
     * @return mixed|null
     */
    public function get(string $key, $default = null)
    {
        return $this->data[$key] ?? $default;
    }

    /**
     * @param string $template
     * @param array $data
     * @return string
     */
    public function partial(string $template, array $data = []): string
    {
        return $this->render('partials/' . $template . '.tpl', $data);
    }
}