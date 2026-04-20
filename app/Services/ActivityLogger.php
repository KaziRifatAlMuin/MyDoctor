<?php

namespace App\Services;

use App\Models\ActivityLog;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Throwable;

class ActivityLogger
{
    private const MAX_ROWS = 10000;
    private const RETRY_AFTER_FAILURE_SECONDS = 300;

    private const SECRET_KEYS = [
        'password',
        'remember_token',
        'token',
        'current_password',
        'password_confirmation',
    ];

    private static bool $isWriting = false;
    private static ?bool $canWrite = null;
    private static ?int $retryAt = null;

    public static function log(array $payload): void
    {
        if (!self::canLog() || self::$isWriting) {
            return;
        }

        self::$isWriting = true;

        try {
            ActivityLog::query()->create([
                'user_id' => $payload['user_id'] ?? null,
                'category' => (string) ($payload['category'] ?? 'system'),
                'action' => (string) ($payload['action'] ?? 'unknown_action'),
                'description' => $payload['description'] ?? null,
                'subject_type' => $payload['subject_type'] ?? null,
                'subject_id' => $payload['subject_id'] ?? null,
                'context' => isset($payload['context']) ? self::sanitizeData($payload['context']) : null,
                'created_at' => now(),
            ]);

            self::$canWrite = true;
            self::$retryAt = null;

            self::pruneIfNeeded();
        } catch (Throwable $e) {
            // Keep activity logging non-blocking for user requests.
            self::$canWrite = false;
            self::$retryAt = time() + self::RETRY_AFTER_FAILURE_SECONDS;
        } finally {
            self::$isWriting = false;
        }
    }

    public static function resolveRequestCategory(?Request $request): string
    {
        if ($request === null) {
            return 'system';
        }

        $routeName = (string) optional($request->route())->getName();

        if ($routeName !== '') {
            if (str_starts_with($routeName, 'admin.')) {
                return 'admin';
            }
            if (str_starts_with($routeName, 'community.')) {
                return 'community';
            }
            if (str_starts_with($routeName, 'medicine.')) {
                return 'medicine';
            }
            if (str_starts_with($routeName, 'health.')) {
                return 'health';
            }
            if (str_starts_with($routeName, 'profile.mailbox')) {
                return 'mailbox';
            }
            if (str_starts_with($routeName, 'notifications.')) {
                return 'notification';
            }
            if (str_starts_with($routeName, 'profile.')) {
                return 'profile';
            }
            if (str_starts_with($routeName, 'login') || str_starts_with($routeName, 'logout')) {
                return 'auth';
            }
        }

        $first = trim((string) $request->segment(1));
        if ($first !== '') {
            return match ($first) {
                'admin' => 'admin',
                'community' => 'community',
                'medicine' => 'medicine',
                'health' => 'health',
                'notifications' => 'notification',
                'profile' => 'profile',
                default => 'web',
            };
        }

        return 'web';
    }

    public static function resolveModelCategory(Model|string $model): string
    {
        $className = is_string($model) ? class_basename($model) : class_basename($model::class);

        return match (true) {
            in_array($className, ['Post', 'Comment', 'PostLike', 'CommentLike', 'UserStarredDisease'], true) => 'community',
            in_array($className, ['Mailing'], true) => 'mailbox',
            in_array($className, ['Notification', 'MedicineReminder'], true) => 'notification',
            in_array($className, ['Medicine', 'MedicineSchedule', 'MedicineLog'], true) => 'medicine',
            in_array($className, ['Disease', 'Symptom', 'UserDisease', 'UserSymptom', 'UserHealth', 'HealthMetric'], true) => 'health',
            in_array($className, ['User', 'UserAddress', 'UserSetting'], true) => 'account',
            default => 'database',
        };
    }

    public static function sanitizeData(mixed $value): mixed
    {
        if (is_array($value)) {
            $sanitized = [];
            foreach ($value as $key => $item) {
                if (is_string($key) && in_array(strtolower($key), self::SECRET_KEYS, true)) {
                    continue;
                }
                $sanitized[$key] = self::sanitizeData($item);
            }

            return $sanitized;
        }

        if (is_object($value)) {
            return self::sanitizeData((array) $value);
        }

        if (is_string($value) && strlen($value) > 4000) {
            return substr($value, 0, 4000) . '...';
        }

        return $value;
    }

    private static function canLog(): bool
    {
        if (app()->runningUnitTests()) {
            return true;
        }

        if (self::$canWrite === true) {
            return true;
        }

        if (self::$canWrite === false && self::$retryAt !== null && time() < self::$retryAt) {
            return false;
        }

        return true;
    }

    private static function pruneIfNeeded(): void
    {
        try {
            $count = ActivityLog::query()->count();
            if ($count <= self::MAX_ROWS) {
                return;
            }

            $toDelete = $count - self::MAX_ROWS;
            $ids = ActivityLog::query()
                ->orderBy('created_at')
                ->orderBy('id')
                ->limit($toDelete)
                ->pluck('id');

            if ($ids->isNotEmpty()) {
                ActivityLog::query()->whereIn('id', $ids)->delete();
            }
        } catch (Throwable $e) {
            // Never fail the request because pruning failed.
        }
    }
}
