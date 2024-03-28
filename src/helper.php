<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\NoteType\ServiceMessageNote;

require_once __DIR__.'/../vendor/autoload.php';

/**
 * Добавление примечания в сущность типа $entityType
 * @param int $entityId - id сущности
 * @param string $text - текст примечания
 * @param string $entityType - тип сущности
 * @param AmoCRMApiClient $apiClient - клиент амо, с помощью которого делаются запросы в амо по апи
 * @return void
 */
function addNote(int $entityId, string $text, string $entityType, AmoCRMApiClient $apiClient): void
{
    $notesCollection = new NotesCollection();
    $serviceMessageNote = new ServiceMessageNote();
    $serviceMessageNote->setEntityId($entityId)
        ->setText($text)
        ->setService('Api Library');

    $notesCollection->add($serviceMessageNote);

    try {
        $entityNotesService = $apiClient->notes($entityType);
        $entityNotesService->add($notesCollection);
    } catch (AmoCRMApiException $ex) {
        printError($ex);
        die;
    }
}

/**
 * Подготовка текста примечания, исходя из $action
 * @param string $action - ключевой параметр, от которого зависит алгоритм построения текста
 * @param array $entity - сущность amocrm, из которой будут извлекаться данные для текста
 * @return string - текст примечания
 */
function prepareNoteText(string $action, array $entity): string {
    if ($action === 'add')
        return "{$entity['name']}\nId ответственного: {$entity['responsible_user_id']}\nВремя добавления: ".date('d.m.Y H:i:s', $entity['last_modified']);
    elseif ($action === 'update') {
        // TODO Добавить измененные поля сущности в строку
        return "Время изменения: ".date('d.m.Y H:i:s', $entity['last_modified']);
    }

    return '';
}
