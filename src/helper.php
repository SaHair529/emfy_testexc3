<?php

use AmoCRM\Client\AmoCRMApiClient;
use AmoCRM\Collections\NotesCollection;
use AmoCRM\Exceptions\AmoCRMApiException;
use AmoCRM\Models\NoteType\ServiceMessageNote;

require_once __DIR__.'/../vendor/autoload.php';

const ENTITIES_DIRPATH = __DIR__.'/../var/entities';

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
function prepareNoteText(string $action, array $entity, string $entityType): string {
    if ($action === 'add')
        return "{$entity['name']}\nId ответственного: {$entity['responsible_user_id']}\nВремя добавления: ".date('d.m.Y H:i:s', $entity['last_modified']);
    elseif ($action === 'update') {
        $result = "Время изменения: ".date('d.m.Y H:i:s', $entity['last_modified'])."Измененные поля:\n\n";
        $changedFields = getEntityChangedFields($entity, $entityType);
        foreach ($changedFields as $fieldName => $fieldValue)
            $result .= "$fieldName: $fieldValue\n";
        return $result;
    }

    return '';
}

/**
 * Сохранение состояния сущности в json файле для поиска измененных полей при создании примечания
 * @param string $entityType
 * @param array $entity
 * @return void
 */
function saveEntityState(string $entityType, array $entity): void
{
    $entityFilepath = ENTITIES_DIRPATH."/$entityType/{$entity['id']}.json";
    file_put_contents($entityFilepath, json_encode($entity, JSON_UNESCAPED_UNICODE));
}

/**
 * Получение последнего состояния сущности из файла
 * @param int $id
 * @param string $entityType
 * @return array
 */
function getEntityState(int $id, string $entityType): array
{
    $entityFilepath = ENTITIES_DIRPATH."/$entityType/$id.json";
    return json_decode(file_get_contents($entityFilepath), true);
}

/**
 * Получение измененных полей путем сравнения сохраненного в файле состояния с актуальным состоянием из запроса
 * @param array $updatedEntity
 * @param string $entityType
 * @return array
 */
function getEntityChangedFields(array $updatedEntity, string $entityType): array
{
    $changedFields = [];
    $oldEntity = getEntityState($updatedEntity['id'], $entityType);

    if ($oldEntity['name'] !== $updatedEntity['name'])
        $changedFields['Имя'] = $updatedEntity['name'];
    if ($entityType === 'leads' && $oldEntity['price'] !== $updatedEntity['price'])
        $changedFields['Бюджет'] = $updatedEntity['price'];

    $updatedCustomFields = array_column($updatedEntity['custom_fields'] ?? [], null, 'id');
    $oldCustomFields = array_column($oldEntity['custom_fields'] ?? [], null, 'id');

    foreach ($updatedCustomFields as $fieldId => $field) {
        if (!isset($oldCustomFields[$fieldId])) {
            $changedFields[$field['name']] = $field['values'][0]['value'];
            continue;
        }

        if ($oldCustomFields[$fieldId]['values'][0]['value'] !== $field['values'][0]['value'])
            $changedFields[$field['name']] = $field['values'][0]['value'];
    }

    foreach ($oldCustomFields as $fieldId => $field) {
        if (!isset($updatedCustomFields[$fieldId]))
            $changedFields[$field['name']] = 'Поле очищено';
    }

    return $changedFields;
}
