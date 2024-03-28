<?php

use AmoCRM\Client\AmoCRMApiClient;
use Dotenv\Dotenv;

require_once __DIR__.'/../vendor/autoload.php';

$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$apiClient = new AmoCRMApiClient();
$longLivedToken = new \AmoCRM\Client\LongLivedAccessToken($_ENV['LONGLIVING_TOKEN']);

$apiClient->setAccessToken($longLivedToken)
    ->setAccountBaseDomain($_ENV['SUBDOMAIN']);

