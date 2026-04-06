<?php

namespace NDirectSdk\Library\Integrations;

class IntegrationFactory
{
    public static function create($config, $integration)
    {
        switch ($integration) 
        {
            case 'svv':
                return new SVVIntegration($config, $integration);
            default:
                return null;
        }
    }
}