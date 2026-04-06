<?php

namespace NDirectSdk\Library\Integrations;

abstract class AbstractIntegration implements IntegrationInterface
{
    protected $sqlite;
    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
        date_default_timezone_set($config['timezone'] ?? 'UTC');
        $this->initSqlite();
    }

    private function initSqlite()
    {
        try 
        {
            $this->sqlite = new \PDO("sqlite:" . ($this->config['api']['cache_file'] ?? __DIR__ . '/db_.sqlite'));
            $this->sqlite->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            
            $this->sqlite->exec("CREATE TABLE IF NOT EXISTS cache_store (
                cache_key TEXT PRIMARY KEY,
                cache_data MEDIUMTEXT,
                last_updated INTEGER
            )");
        } 
        catch (\PDOException $e) 
        {
            exit("Can't connect to sqlite");
        }
    }

    private function getApiData($endpoint, $from, $to)
    {
        $data = [
            'from' => $from,
            'to' => $to,
            'tz' => $this->config['timezone'] ?? 'UTC',
        ];

        $url = $this->config['api']['endpoint'].$endpoint.'?'.http_build_query($data); 

        $ch = curl_init();
        curl_setopt_array($ch, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => true,
            CURLOPT_TIMEOUT        => 10,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER => [
                'Content-Type: application/json',
                'X-API-Key: '.$this->config['api']['token'],
                'User-Agent: nDirect-Integration/2.0',
            ],
        ]);
        $response = curl_exec($ch);
        $error = curl_errno($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($error) {
            return json_encode([]);
        } 

        if ($httpCode != 200) {
            return json_encode([]);
        }

        json_decode($response);
        if (json_last_error() != JSON_ERROR_NONE) {
            return json_encode([]);
        }
        
        return $response;
    }

    public function getUnique($days = 6) 
    { 
        return $this->getCachedData('unique', $days); 
    }

    public function getInstalls($days = 6) 
    { 
        return $this->getCachedData('installs', $days); 
    }

    public function getConnects($days = 1) 
    { 
        return $this->getCachedData('connects', $days); 
    }

    public function getUninstalls($days = 6) 
    { 
        return $this->getCachedData('uninstalls', $days); 
    }

    private function getCachedData($type, $days)
    {
        $now = time();
        $cacheKey = "{$type}_{$days}"; 

        $query = $this->sqlite->prepare("SELECT cache_data, last_updated FROM cache_store WHERE cache_key = :cache_key");
        $query->execute([':cache_key' => $cacheKey]);
        $row = $query->fetch(\PDO::FETCH_OBJ);

        if ($row && ($now - $row->last_updated < $this->config['api']['cache_alive_time'] ?? 120)) {
            return $row->cache_data;
        }

        $from = date("Y-m-d", $now - 86400 * $days);
        $to = date("Y-m-d");
        $data = $this->getApiData($type, $from, $to);

        $query = $this->sqlite->prepare("INSERT OR REPLACE INTO cache_store (cache_key, cache_data, last_updated) VALUES (:cache_key, :cache_data, :last_updated)");
        $query->execute([
            ':cache_key' => $cacheKey, 
            ':cache_data' => $data, 
            ':last_updated' =>$now,
        ]);

        return $data;
    }

    public function getServersConnects($days = 0)
    {
        $serversConnects = [];
        $connectsRaw = $this->getConnects($days);

        if (!empty($connectsRaw))
        {
            $connects = json_decode($connectsRaw, true);

            if (is_array($connects)) 
            {
                foreach ($connects as $date => $events) 
                {
                    if (is_array($events))
                    {
                        foreach ($events as $event) 
                        {
                            if (isset($event['server'])) 
                            {
                                $serverIp = $event['server'];
                                
                                if (isset($serversConnects[$serverIp])) {
                                    $serversConnects[$serverIp] += 1;
                                } else {
                                    $serversConnects[$serverIp] = 1;
                                }
                            }
                        }
                    }
                }
            }
        }

        return $serversConnects;
    }

    public function getServerConnectsTodayYesterday($address)
    {
        $serversConnectsToday = [];
		$serversConnectsYesterday = [];

		$connectsRaw = $this->getConnects(1);

		if (!empty($connectsRaw))
		{
			$connects = json_decode($connectsRaw, true);

			if (is_array($connects)) 
			{
                $today = date("Y-m-d");
                
				foreach ($connects as $date => $events) 
				{
					if (is_array($events))
					{
						foreach ($events as $event) 
						{
							if (isset($event['server']) && $event['server'] == $address) 
							{
								if ($today == $date) {
									$serversConnectsToday[] = $event;
								} else {
									$serversConnectsYesterday[] = $event;
								}
							}
						}
					}
				}
			}
		}

        return ['today' => $serversConnectsToday, 'yesterday' => $serversConnectsYesterday];
    }

    public function renderChart($data)
    {
        $unique = is_array($data) ? $data : json_decode($data, true);
        
        if (!is_array($unique)) {
            return "nDirect API error";
        }

        $labels = [];
        $data = [];

        foreach($unique as $uniqueData)
        {
            $labels[] = $uniqueData['date'];
            $data[] = $uniqueData['count'];
        }

        $labels = json_encode($labels);
        $data = json_encode($data);

        $chart = file_get_contents(__DIR__.'/../../../templates/chart.tpl');

        $chart = str_replace('{uid}', uniqid(), $chart);
        $chart = str_replace('{labels}', $labels, $chart);
        $chart = str_replace('{data}', $data, $chart);

        return $chart;
    }
}