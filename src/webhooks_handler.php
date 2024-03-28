<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
use AmoCRM\Helpers\EntityTypesInterface;
use Dotenv\Dotenv;

require_once __DIR__.'/helper.php';

parse_str(file_get_contents('php://input'), $requestData);

// Проверка наличия обязательных полей в запросе
if (!isset($requestData['leads']['add']) &&
    !isset($requestData['leads']['update']) &&
    !isset($requestData['contacts']['add']) &&
    !isset($requestData['contacts']['update'])) {
    http_response_code(400);
    die('Invalid request data');
}

// Подготовка окружения
$dotenv = Dotenv::createImmutable(__DIR__.'/../');
$dotenv->load();

$apiClient = new AmoCRMApiClient();
$longLivedToken = new LongLivedAccessToken($_ENV['LONGLIVING_TOKEN']);

$apiClient->setAccessToken($longLivedToken)
    ->setAccountBaseDomain($_ENV['SUBDOMAIN']);

// Создание примечаний
if (isset($requestData['leads']['add'])) {
    $lead = $requestData['leads']['add'][0];
    addNote($lead['id'], $text, EntityTypesInterface::LEADS, $apiClient);
}
elseif (isset($requestData['contacts']['add'])) {
    $contact = $requestData['contacts']['add'][0];
    addNote($contact['id'], $text, EntityTypesInterface::CONTACTS, $apiClient);
}
elseif (isset($requestData['leads']['update'])) {
    $lead = $requestData['leads']['update'][0];
    addNote($lead['id'], $text, EntityTypesInterface::LEADS, $apiClient);
}
elseif (isset($requestData['contacts']['update'])) {
    $contact = $requestData['contacts']['update'][0];
    addNote($contact['id'], $text, EntityTypesInterface::CONTACTS, $apiClient);
}
