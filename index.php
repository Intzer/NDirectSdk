<?php

require_once __DIR__ . '/src/Autoloader.php';

use NDirectSdk\Controllers\GetListsController;

$action = $_GET['action'] ?? '';

if ($action == 'get-lists') 
{
    $getListsController = new GetListsController();
    echo $getListsController->getList();
} 