<?php
require_once __DIR__ . '/src/Autoloader.php';

use NDirectSdk\NDirectClient;

$integration = NDirectClient::getInstance()->get();

if (!$integration) {
    exit("SDK не инициализирован");
}

$type = $_GET['type'] ?? 'unique';
$days = (int) ($_GET['days'] ?? 7);

switch ($type) {
    case 'installs': $data = $integration->getInstalls($days); break;
    case 'connects': $data = $integration->getConnects($days); break;
    default: $data = $integration->getUnique($days); break;
}

echo $integration->renderChart($data);