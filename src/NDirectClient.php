<?php

namespace NDirectSdk;

use NDirectSdk\Library\Integrations\IntegrationFactory;

class NDirectClient
{
    private static $instance = null;
    private $integration = null;

    private function __construct()
    {
        $config = require __DIR__ . '/../config.php';
        
        $this->integration = IntegrationFactory::create($config, $config['integration'] ?? '');
    }

    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function get()
    {
        return $this->integration;
    }
}