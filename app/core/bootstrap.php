<?php
declare(strict_types=1);

require_once APP_PATH . '/core/helpers.php';

spl_autoload_register(function (string $class): void {
    $directories = [
        APP_PATH . '/core/',
        APP_PATH . '/controllers/',
        APP_PATH . '/models/',
        APP_PATH . '/services/',
    ];
    foreach ($directories as $directory) {
        $file = $directory . $class . '.php';
        if (is_file($file)) {
            require_once $file;
            return;
        }
    }
});