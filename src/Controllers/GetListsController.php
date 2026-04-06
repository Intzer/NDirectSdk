<?php

namespace NDirectSdk\Controllers;

use NDirectSdk\NDirectClient;

class GetListsController
{
    public function getList()
    {
        $integration = NDirectClient::getInstance()->get();

        if (!$integration) {
            return json_encode(['error' => 'Integration not found']);
        }

        $type = $_GET['type'] ?? 'search';
        if (!in_array($type, ['search', 'gamemenu', 'favourites'])) {
            return json_encode(['error' => 'Bad param: type']);
        }

        $list = $integration->getLists($type);

        return $list;
    }
}