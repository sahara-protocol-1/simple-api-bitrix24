<?php

declare(strict_types=1);

namespace SimpleApiBitrix24\Brokers;

use SimpleApiBitrix24\Exception\HttpBrokerException;
use SimpleApiBitrix24\ApiBitrix24;

class HttpBroker
{
    private static HttpBroker $instance;

    private function __construct()
    {
    }

    public static function getInstance()
    {
        if (empty(self::$instance)) {
            self::$instance = new HttpBroker;
        }
        return self::$instance;
    }

    public function request($url, $data, $getCurlInfo = false)
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTREDIR, 10);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_USERAGENT, ApiBitrix24::APP_INFO);

        $response = curl_exec($ch);
        curl_close($ch);

        $response = json_decode($response, true);

        if ($response === false) {
            throw new HttpBrokerException("curl error: " . curl_error($ch));
        }

        if ($getCurlInfo) {
            $curlInfo = curl_getinfo($ch);
            $response['curl_info'] = $curlInfo;
        }
        
        return $response;
    }
}