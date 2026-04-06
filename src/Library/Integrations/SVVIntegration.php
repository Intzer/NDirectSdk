<?php

namespace NDirectSdk\Library\Integrations;

class SVVIntegration extends AbstractIntegration
{
    private $settings;

    public function __construct($config, $integration)
    {
        parent::__construct($config);
        $this->settings = $config['integrations'][$integration] ?? [];
    }

    public function getLists($type)
    {
        $dbArray = parse_ini_file(str_replace('\\', '/',  $_SERVER['DOCUMENT_ROOT'] .'/').'/include/.conf');

        try 
        {
            $pdo = new \PDO('mysql:host='.$dbArray['server'].';dbname='.$dbArray['database'], $dbArray['username'], $dbArray['password']);
            $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            $pdo->query('SET NAMES utf8');
        } 
        catch (\PDOException $e) {
            exit ("Can't connect to mysql");
        }

        unset($dbArray);

        $data = [];

        if ($type == 'gamemenu')
        {
            $query = $pdo->prepare("SELECT address FROM servers WHERE services LIKE '%\"boost\"%'");
            $query->execute();
            while ($row = $query->fetch(\PDO::FETCH_OBJ)) {
                $data[] = $row->address;
            }
            shuffle($data);
        }
        else if ($type == 'favourites')
        {
            $query = $pdo->prepare("SELECT address FROM servers WHERE services LIKE '%\"gamemenu\"%'");
            $query->execute();
            while ($row = $query->fetch(\PDO::FETCH_OBJ)) {
                $data[] = $row->address;
            }
            shuffle($data);
        }
        else if ($type == 'search')
        {
            $sort = $this->settings['list_search_sort'] ?? 'random';
            if (!in_array($sort, ['random', 'connects'])) {
                $sort = 'random';
            }

            $duplicates = $this->settings['list_search_duplicates'] ?? false;
            $countGs = $this->settings['list_search_count_gs_in_connects_sort'] ?? false;

            $groups = [
                'befirst' => [],
                'top' => [],
                'boost' => [],
                'vip_color' => [],
                'vip' => [],
            ];

            $serversConnects = [];
            if ($sort == 'connects') {
                $serversConnects = $this->getServersConnects();
            }

            if ($countGs) 
            {
                try 
                {
                    $query = $pdo->prepare("SELECT address, services, uniq_to FROM servers WHERE services IS NOT null AND services != ''");
                    $query->execute();
                } 
                catch (\PDOException $e) 
                {
                    if ($e->getCode() == '42S22') {
                        $query = $pdo->prepare("SELECT address, services, 0 as uniq_to FROM servers WHERE services IS NOT null AND services != ''");
                        $query->execute();
                    } 
                    else 
                    {
                        throw $e;
                    }
                }
            } 
            else 
            {
                $query = $pdo->prepare("SELECT address, services, 0 as uniq_to FROM servers WHERE services IS NOT null AND services != ''");    
                $query->execute();
            }

            
            while ($row = $query->fetch(\PDO::FETCH_OBJ)) 
            {
                $js = json_decode($row->services, true);

                $connectsNext = isset($serversConnects[$row->address]) ? ((int) $serversConnects[$row->address]) : 0;
                $connectsGs = isset($row->uniq_to) ? $row->uniq_to : 0;

                $item = [
                    'address' => $row->address, 
                    'connects' => $connectsNext + $connectsGs,
                ];

                if (isset($js['befirst'])) {
                    $groups['befirst'][] = $item;
                }

                if (isset($js['top'])) {
                    $groups['top'][] = $item;
                }     

                if (isset($js['boost'])) {
                    $groups['boost'][]   = $item;
                }   

                if (isset($js['vip'])) 
                {
                    if (isset($js['color'])) {
                        $groups['vip_color'][]   = $item;
                    }

                    $groups['vip'][] = $item;
                }
            }

            $sortedData = [];
            $addedAddresses = [];

            foreach ($groups as $items) 
            {
                if (empty($items)) {
                    continue;
                }

                if ($this->settings['list_search_sort'] == 'connects')
                {
                    usort($items, function($a, $b) {
                        return $a['connects'] <=> $b['connects']; 
                    });
                }
                else
                {
                    shuffle($items);
                }
                
                foreach ($items as $val) 
                {
                    $addr = $val['address'];

                    if (!$duplicates && isset($addedAddresses[$addr])) {
                        continue;
                    }

                    $sortedData[] = $addr;
                    $addedAddresses[$addr] = true;
                }
            }

            $data = $sortedData;
        }

        return implode("\n", $data);
    }
}