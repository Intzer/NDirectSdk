<?php

spl_autoload_register(function ($class) {
    $prefix = 'NDirectSdk\\';
    $base_dir = __DIR__ . '/';
    $prefixLen = strlen($prefix);

    if (strncmp($prefix, $class, $prefixLen) !== 0) {
        return;
    }

    $relative_class = substr($class, $prefixLen);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});