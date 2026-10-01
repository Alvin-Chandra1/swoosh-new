<?php
declare(strict_types=1);

abstract class Controller
{
    protected function view(string $view, array $data = []): void
    {
        extract($data);
        require APP_PATH . '/views/' . $view . '.php';
    }

    protected function redirect(string $route = ''): void
    {
        header('Location: ' . appBasePath() . ($route !== '' ? '/?route=' . ltrim($route, '/') : '/'));
        exit;
    }

    protected function requirePost(): void
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            http_response_code(405);
            exit('Method Not Allowed');
        }
    }
}