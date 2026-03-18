<?php

declare(strict_types=1);

error_reporting(E_ALL);
ini_set('display_errors', '1');

define('_JEXEC', 1);

$autoload = dirname(__DIR__, 2) . '/vendor/autoload.php';
if (is_file($autoload)) {
    require_once $autoload;
}

if (!class_exists(\Joomla\Registry\Registry::class)) {
    require_once __DIR__ . '/stubs/Registry.php';
}

require_once dirname(__DIR__, 2) . '/lib_flexqueue/autoload.php';
