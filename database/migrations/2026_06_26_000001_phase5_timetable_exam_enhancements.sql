-- =====================================================================
-- PUB CampusMate Migration: Phase 5 Timetable & Examination Enhancements
-- Version: 2026_06_26_000001
-- =====================================================================

-- 1. Add status column to class_schedules if not exists
SET @exist_cs_status := (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'class_schedules' 
      AND COLUMN_NAME = 'status'
);

SET @sql_cs := IF(@exist_cs_status = 0, 
    'ALTER TABLE `class_schedules` ADD COLUMN `status` ENUM(\'draft\', \'published\', \'revised\', \'cancelled\') NOT NULL DEFAULT \'published\' AFTER `class_type`, ADD INDEX `idx_cs_status` (`status`)', 
    'SELECT 1'
);
PREPARE stmt_cs FROM @sql_cs;
EXECUTE stmt_cs;
DEALLOCATE PREPARE stmt_cs;

-- 2. Add class_schedule_id to makeup_classes if not exists
SET @exist_mc_csid := (
    SELECT COUNT(*) FROM information_schema.COLUMNS 
    WHERE TABLE_SCHEMA = DATABASE() 
      AND TABLE_NAME = 'makeup_classes' 
      AND COLUMN_NAME = 'class_schedule_id'
);

SET @sql_mc := IF(@exist_mc_csid = 0, 
    'ALTER TABLE `makeup_classes` ADD COLUMN `class_schedule_id` INT NULL AFTER `section_id`, ADD CONSTRAINT `fk_mc_schedule` FOREIGN KEY (`class_schedule_id`) REFERENCES `class_schedules` (`id`) ON DELETE SET NULL ON UPDATE CASCADE', 
    'SELECT 1'
);
PREPARE stmt_mc FROM @sql_mc;
EXECUTE stmt_mc;
DEALLOCATE PREPARE stmt_mc;

-- 3. Update exams status enum to support draft & published
ALTER TABLE `exams` MODIFY COLUMN `status` ENUM('draft', 'published', 'scheduled', 'in_progress', 'completed', 'postponed', 'cancelled') NOT NULL DEFAULT 'scheduled';
