<?php
namespace App\Core;

use App\Exceptions\AppException;

class View
{
    protected static string $viewsPath = '';

    public static function init(): void
    {
        self::$viewsPath = dirname(__DIR__, 2) . '/resources/views';
    }

    public static function render(string $view, array $data = [], ?string $layout = 'layouts/app'): void
    {
        if (empty(self::$viewsPath)) {
            self::init();
        }

        $viewFile = self::$viewsPath . '/' . str_replace('.', '/', $view) . '.php';

        if (!file_exists($viewFile)) {
            throw new AppException("View file not found: [{$viewFile}]");
        }

        // Extract data into local scope
        extract($data);

        // Capture view output
        ob_start();
        require $viewFile;
        $content = ob_get_clean();

        if ($layout === null) {
            echo $content;
            return;
        }

        $layoutFile = self::$viewsPath . '/' . str_replace('.', '/', $layout) . '.php';
        if (!file_exists($layoutFile)) {
            throw new AppException("Layout file not found: [{$layoutFile}]");
        }

        require $layoutFile;
    }

    public static function escape(mixed $value): string
    {
        return htmlspecialchars((string)$value, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');
    }
}
