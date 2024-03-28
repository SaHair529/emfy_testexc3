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