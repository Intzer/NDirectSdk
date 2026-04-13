<?php
require_once __DIR__ . '/src/Autoloader.php';

use NDirectSdk\NDirectClient;

$integration = NDirectClient::getInstance()->get();

if (!$integration) 
{
    http_response_code(500);
    exit("SDK не инициализирован");
}

$type = $_GET['type'] ?? 'unique';
$days = max(1, min((int) ($_GET['days'] ?? 7), 90));

switch ($type) 
{
    case 'installs': $data = $integration->getInstalls($days); break;
    case 'connects': $data = $integration->getConnects($days); break;
    default: $data = $integration->getUnique($days); break;
}

if (!empty($data)) 
{
    $dataArray = json_decode($data, true);

    if (json_last_error() === JSON_ERROR_NONE && is_array($dataArray)) 
    {
        $dataArray = array_reverse($dataArray);
        $data = json_encode($dataArray);
    } 
    else 
    {
        http_response_code(500);
        exit("Ошибка отрисоки графика");
    }
}

echo $integration->renderChart($data);