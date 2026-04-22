<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $this->dropViews();
        $this->dropTriggers();

        $this->createViews();
        $this->createMedicineLogTriggers();
        $this->createActivityAuditTriggers();
    }

    public function down(): void
    {
        $this->dropTriggers();
        $this->dropViews();
    }

    private function createViews(): void
    {
        DB::unprepared(
            'CREATE VIEW v_user_dashboard_daily AS
            SELECT
                ml.user_id,
                ml.date,
                SUM(ml.total_scheduled) AS total_scheduled,
                SUM(ml.total_taken) AS total_taken,
                SUM(ml.total_missed) AS total_missed,
                CASE
                    WHEN SUM(ml.total_scheduled) > 0 THEN ROUND((SUM(ml.total_taken) * 100.0) / SUM(ml.total_scheduled), 2)
                    ELSE 0
                END AS adherence_rate
            FROM medicine_logs ml
            GROUP BY ml.user_id, ml.date'
        );

        DB::unprepared(
            'CREATE VIEW v_user_health_snapshot AS
            SELECT
                ud.user_id AS user_id,
                "disease" AS row_type,
                d.disease_name AS label,
                ud.status AS status,
                NULL AS severity_level,
                COALESCE(ud.updated_at, ud.created_at) AS recorded_at,
                ud.diagnosed_at AS detail_1,
                ud.notes AS detail_2,
                NULL AS detail_3
            FROM user_diseases ud
            LEFT JOIN diseases d ON d.id = ud.disease_id
            UNION ALL
            SELECT
                us.user_id AS user_id,
                "symptom" AS row_type,
                s.name AS label,
                NULL AS status,
                us.severity_level AS severity_level,
                us.recorded_at AS recorded_at,
                us.note AS detail_1,
                NULL AS detail_2,
                NULL AS detail_3
            FROM user_symptoms us
            LEFT JOIN symptoms s ON s.id = us.symptom_id
            UNION ALL
            SELECT
                uh.user_id AS user_id,
                "metric" AS row_type,
                hm.metric_name AS label,
                NULL AS status,
                NULL AS severity_level,
                uh.recorded_at AS recorded_at,
                uh.value AS detail_1,
                NULL AS detail_2,
                NULL AS detail_3
            FROM user_health uh
            LEFT JOIN health_metrics hm ON hm.id = uh.health_metric_id
            UNION ALL
            SELECT
                m.user_id AS user_id,
                "medicine" AS row_type,
                m.medicine_name AS label,
                NULL AS status,
                NULL AS severity_level,
                m.created_at AS recorded_at,
                m.type AS detail_1,
                m.rule AS detail_2,
                m.unit AS detail_3
            FROM medicines m'
        );

        DB::unprepared(
            'CREATE VIEW v_activity_feed_enriched AS
            SELECT
                al.id,
                al.user_id,
                COALESCE(u.name, "System") AS actor_name,
                al.category,
                al.action,
                al.description,
                al.subject_type,
                al.subject_id,
                al.context,
                al.created_at
            FROM activity_logs al
            LEFT JOIN users u ON u.id = al.user_id'
        );
    }

    private function createMedicineLogTriggers(): void
    {
        DB::unprepared(
            'CREATE TRIGGER trg_mr_after_insert
            AFTER INSERT ON medicine_reminders
            FOR EACH ROW
            BEGIN
                DELETE FROM medicine_logs
                WHERE medicine_id = (
                    SELECT ms.medicine_id FROM medicine_schedules ms WHERE ms.id = NEW.schedule_id
                )
                AND user_id = (
                    SELECT m.user_id
                    FROM medicines m
                    WHERE m.id = (
                        SELECT ms2.medicine_id FROM medicine_schedules ms2 WHERE ms2.id = NEW.schedule_id
                    )
                )
                AND date = DATE(NEW.reminder_at);

                REPLACE INTO medicine_logs (
                    medicine_id, user_id, date, total_scheduled, total_taken, total_missed, created_at, updated_at
                )
                SELECT
                    ms.medicine_id,
                    m.user_id,
                    DATE(mr.reminder_at) AS log_date,
                    COUNT(*) AS total_scheduled,
                    SUM(CASE WHEN mr.status = "taken" THEN 1 ELSE 0 END) AS total_taken,
                    SUM(CASE WHEN mr.status = "missed" THEN 1 ELSE 0 END) AS total_missed,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                FROM medicine_reminders mr
                INNER JOIN medicine_schedules ms ON ms.id = mr.schedule_id
                INNER JOIN medicines m ON m.id = ms.medicine_id
                INNER JOIN users u ON u.id = m.user_id
                WHERE ms.medicine_id = (
                    SELECT ms3.medicine_id FROM medicine_schedules ms3 WHERE ms3.id = NEW.schedule_id
                )
                AND DATE(mr.reminder_at) = DATE(NEW.reminder_at)
                GROUP BY ms.medicine_id, m.user_id, DATE(mr.reminder_at);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_mr_after_update
            AFTER UPDATE ON medicine_reminders
            FOR EACH ROW
            BEGIN
                DELETE FROM medicine_logs
                WHERE medicine_id = (
                    SELECT ms.medicine_id FROM medicine_schedules ms WHERE ms.id = OLD.schedule_id
                )
                AND user_id = (
                    SELECT m.user_id
                    FROM medicines m
                    WHERE m.id = (
                        SELECT ms2.medicine_id FROM medicine_schedules ms2 WHERE ms2.id = OLD.schedule_id
                    )
                )
                AND date = DATE(OLD.reminder_at);

                REPLACE INTO medicine_logs (
                    medicine_id, user_id, date, total_scheduled, total_taken, total_missed, created_at, updated_at
                )
                SELECT
                    ms.medicine_id,
                    m.user_id,
                    DATE(mr.reminder_at) AS log_date,
                    COUNT(*) AS total_scheduled,
                    SUM(CASE WHEN mr.status = "taken" THEN 1 ELSE 0 END) AS total_taken,
                    SUM(CASE WHEN mr.status = "missed" THEN 1 ELSE 0 END) AS total_missed,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                FROM medicine_reminders mr
                INNER JOIN medicine_schedules ms ON ms.id = mr.schedule_id
                INNER JOIN medicines m ON m.id = ms.medicine_id
                INNER JOIN users u ON u.id = m.user_id
                WHERE ms.medicine_id = (
                    SELECT ms3.medicine_id FROM medicine_schedules ms3 WHERE ms3.id = OLD.schedule_id
                )
                AND DATE(mr.reminder_at) = DATE(OLD.reminder_at)
                GROUP BY ms.medicine_id, m.user_id, DATE(mr.reminder_at);

                DELETE FROM medicine_logs
                WHERE medicine_id = (
                    SELECT ms.medicine_id FROM medicine_schedules ms WHERE ms.id = NEW.schedule_id
                )
                AND user_id = (
                    SELECT m.user_id
                    FROM medicines m
                    WHERE m.id = (
                        SELECT ms2.medicine_id FROM medicine_schedules ms2 WHERE ms2.id = NEW.schedule_id
                    )
                )
                AND date = DATE(NEW.reminder_at);

                REPLACE INTO medicine_logs (
                    medicine_id, user_id, date, total_scheduled, total_taken, total_missed, created_at, updated_at
                )
                SELECT
                    ms.medicine_id,
                    m.user_id,
                    DATE(mr.reminder_at) AS log_date,
                    COUNT(*) AS total_scheduled,
                    SUM(CASE WHEN mr.status = "taken" THEN 1 ELSE 0 END) AS total_taken,
                    SUM(CASE WHEN mr.status = "missed" THEN 1 ELSE 0 END) AS total_missed,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                FROM medicine_reminders mr
                INNER JOIN medicine_schedules ms ON ms.id = mr.schedule_id
                INNER JOIN medicines m ON m.id = ms.medicine_id
                INNER JOIN users u ON u.id = m.user_id
                WHERE ms.medicine_id = (
                    SELECT ms3.medicine_id FROM medicine_schedules ms3 WHERE ms3.id = NEW.schedule_id
                )
                AND DATE(mr.reminder_at) = DATE(NEW.reminder_at)
                GROUP BY ms.medicine_id, m.user_id, DATE(mr.reminder_at);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_mr_after_delete
            AFTER DELETE ON medicine_reminders
            FOR EACH ROW
            BEGIN
                DELETE FROM medicine_logs
                WHERE medicine_id = (
                    SELECT ms.medicine_id FROM medicine_schedules ms WHERE ms.id = OLD.schedule_id
                )
                AND user_id = (
                    SELECT m.user_id
                    FROM medicines m
                    WHERE m.id = (
                        SELECT ms2.medicine_id FROM medicine_schedules ms2 WHERE ms2.id = OLD.schedule_id
                    )
                )
                AND date = DATE(OLD.reminder_at);

                REPLACE INTO medicine_logs (
                    medicine_id, user_id, date, total_scheduled, total_taken, total_missed, created_at, updated_at
                )
                SELECT
                    ms.medicine_id,
                    m.user_id,
                    DATE(mr.reminder_at) AS log_date,
                    COUNT(*) AS total_scheduled,
                    SUM(CASE WHEN mr.status = "taken" THEN 1 ELSE 0 END) AS total_taken,
                    SUM(CASE WHEN mr.status = "missed" THEN 1 ELSE 0 END) AS total_missed,
                    CURRENT_TIMESTAMP,
                    CURRENT_TIMESTAMP
                FROM medicine_reminders mr
                INNER JOIN medicine_schedules ms ON ms.id = mr.schedule_id
                INNER JOIN medicines m ON m.id = ms.medicine_id
                INNER JOIN users u ON u.id = m.user_id
                WHERE ms.medicine_id = (
                    SELECT ms3.medicine_id FROM medicine_schedules ms3 WHERE ms3.id = OLD.schedule_id
                )
                AND DATE(mr.reminder_at) = DATE(OLD.reminder_at)
                GROUP BY ms.medicine_id, m.user_id, DATE(mr.reminder_at);
            END'
        );
    }

    private function createActivityAuditTriggers(): void
    {
        DB::unprepared(
            'CREATE TRIGGER trg_user_health_after_insert
            AFTER INSERT ON user_health
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NEW.user_id, "database", "db_user_health_insert", "user_health row inserted", "user_health", NEW.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_health_after_update
            AFTER UPDATE ON user_health
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NEW.user_id, "database", "db_user_health_update", "user_health row updated", "user_health", NEW.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_health_after_delete
            AFTER DELETE ON user_health
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NULL, "database", "db_user_health_delete", "user_health row deleted", "user_health", OLD.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_diseases_after_insert
            AFTER INSERT ON user_diseases
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NEW.user_id, "database", "db_user_diseases_insert", "user_diseases row inserted", "user_diseases", NEW.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_diseases_after_update
            AFTER UPDATE ON user_diseases
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NEW.user_id, "database", "db_user_diseases_update", "user_diseases row updated", "user_diseases", NEW.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_diseases_after_delete
            AFTER DELETE ON user_diseases
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NULL, "database", "db_user_diseases_delete", "user_diseases row deleted", "user_diseases", OLD.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_symptoms_after_insert
            AFTER INSERT ON user_symptoms
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NEW.user_id, "database", "db_user_symptoms_insert", "user_symptoms row inserted", "user_symptoms", NEW.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_symptoms_after_update
            AFTER UPDATE ON user_symptoms
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NEW.user_id, "database", "db_user_symptoms_update", "user_symptoms row updated", "user_symptoms", NEW.id, "{}", CURRENT_TIMESTAMP);
            END'
        );

        DB::unprepared(
            'CREATE TRIGGER trg_user_symptoms_after_delete
            AFTER DELETE ON user_symptoms
            FOR EACH ROW
            BEGIN
                INSERT INTO activity_logs (user_id, category, action, description, subject_type, subject_id, context, created_at)
                VALUES (NULL, "database", "db_user_symptoms_delete", "user_symptoms row deleted", "user_symptoms", OLD.id, "{}", CURRENT_TIMESTAMP);
            END'
        );
    }

    private function dropTriggers(): void
    {
        $triggers = [
            'trg_mr_after_insert',
            'trg_mr_after_update',
            'trg_mr_after_delete',
            'trg_user_health_after_insert',
            'trg_user_health_after_update',
            'trg_user_health_after_delete',
            'trg_user_diseases_after_insert',
            'trg_user_diseases_after_update',
            'trg_user_diseases_after_delete',
            'trg_user_symptoms_after_insert',
            'trg_user_symptoms_after_update',
            'trg_user_symptoms_after_delete',
        ];

        foreach ($triggers as $trigger) {
            DB::unprepared('DROP TRIGGER IF EXISTS ' . $trigger);
        }
    }

    private function dropViews(): void
    {
        DB::unprepared('DROP VIEW IF EXISTS v_activity_feed_enriched');
        DB::unprepared('DROP VIEW IF EXISTS v_user_health_snapshot');
        DB::unprepared('DROP VIEW IF EXISTS v_user_dashboard_daily');
    }
};
