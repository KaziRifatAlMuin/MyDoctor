<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use App\Services\ActivityLogger;
use Illuminate\Database\Eloquent\Model;

class LogModelActivity
{
    public function handle(string $eventName, array $data): void
    {
        $model = $data[0] ?? null;

        if (!$model instanceof Model || $model instanceof ActivityLog) {
            return;
        }

        $event = $this->resolveEventName($eventName);
        if ($event === null) {
            return;
        }

        $changes = $this->buildChanges($model, $event);
        $subjectId = $model->getKey();
        $modelName = class_basename($model);

        $actorId = auth()->id();
        if ($actorId === null && $model instanceof User) {
            $actorId = $model->id;
        }

        ActivityLogger::log([
            'user_id' => $actorId,
            'category' => ActivityLogger::resolveModelCategory($model),
            'action' => 'model_' . $event,
            'description' => $this->descriptionFor($modelName, $event, $changes),
            'subject_type' => $model::class,
            'subject_id' => is_numeric($subjectId) ? (int) $subjectId : null,
            'context' => [
                'event' => $event,
                'model' => $modelName,
                'changed_fields' => array_values(array_keys($changes['new'] ?? [])),
                'changes' => $changes,
            ],
        ]);
    }

    private function descriptionFor(string $modelName, string $event, array $changes): string
    {
        return match ($event) {
            'created' => sprintf('Created a new %s record.', strtolower($modelName)),
            'updated' => sprintf('Updated %s fields: %s.', strtolower($modelName), $this->formatFields(array_keys($changes['new'] ?? []))),
            'deleted' => sprintf('Deleted a %s record.', strtolower($modelName)),
            default => sprintf('Changed %s.', strtolower($modelName)),
        };
    }

    private function formatFields(array $fields): string
    {
        if ($fields === []) {
            return 'no tracked fields';
        }

        return implode(', ', array_map(static fn ($field) => str_replace('_', ' ', (string) $field), $fields));
    }

    private function resolveEventName(string $eventName): ?string
    {
        if (str_starts_with($eventName, 'eloquent.created: ')) {
            return 'created';
        }

        if (str_starts_with($eventName, 'eloquent.updated: ')) {
            return 'updated';
        }

        if (str_starts_with($eventName, 'eloquent.deleted: ')) {
            return 'deleted';
        }

        return null;
    }

    private function buildChanges(Model $model, string $event): array
    {
        if ($event === 'created') {
            return [
                'new' => ActivityLogger::sanitizeData($model->attributesToArray()),
            ];
        }

        if ($event === 'deleted') {
            return [
                'old' => ActivityLogger::sanitizeData($model->attributesToArray()),
            ];
        }

        $newValues = [];
        $oldValues = [];

        foreach ($model->getChanges() as $key => $value) {
            if ($key === 'updated_at') {
                continue;
            }

            $oldValues[$key] = $model->getOriginal($key);
            $newValues[$key] = $model->getAttribute($key);
        }

        return [
            'old' => ActivityLogger::sanitizeData($oldValues),
            'new' => ActivityLogger::sanitizeData($newValues),
        ];
    }
}
