<?php
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, POST, OPTIONS");
header("Access-Control-Allow-Headers: Content-Type, Authorization");

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Client\LongLivedAccessToken;
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
    ->setAccountBaseDomain($_ENV['AMO_DOMAIN']);

// Создание примечаний
$entityType = isset($requestData['leads']) ? 'leads' : 'contacts';
$action = isset($requestData[$entityType]['add']) ? 'add' : 'update';
$entity = $requestData[$entityType][$action][0];

$text = prepareNoteText($action, $entity, $entityType);
addNote($entity['id'], $text, $entityType, $apiClient);
saveEntityState($entityType, $entity);

echo 'ok';
