-- Create job error table
CREATE TABLE IF NOT EXISTS `#__flexqueue_job_errors` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '錯誤 ID',
    `queue` VARCHAR(191) NOT NULL DEFAULT 'default' COMMENT '佇列名稱',
    `job_id` BIGINT UNSIGNED NOT NULL COMMENT '對應工作 ID',
    `payload` LONGTEXT NOT NULL COMMENT '失敗當下的工作內容',
    `error_message` TEXT NOT NULL COMMENT '錯誤訊息',
    `error_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '錯誤時間',
    PRIMARY KEY (`id`),
    KEY `idx_job_id` (`job_id`),
    KEY `idx_queue` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='工作錯誤紀錄';

-- Ensure jobs table exists before altering it
ALTER TABLE `#__flexqueue_jobs`
DROP COLUMN `attempts`;

ALTER TABLE `#__flexqueue_jobs`
ADD `worker_id` VARCHAR(191) NOT NULL COMMENT '寫入者 / 工作者識別',
ADD INDEX (`worker_id`);

ALTER TABLE `#__flexqueue_jobs`
    COMMENT='佇列工作主表',
    MODIFY `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT COMMENT '工作 ID',
    MODIFY `queue` VARCHAR(191) NOT NULL DEFAULT 'default' COMMENT '佇列名稱',
    MODIFY `payload` LONGTEXT NOT NULL COMMENT '序列化後的工作內容',
    MODIFY `available_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '可被取用時間',
    MODIFY `reserved_at` DATETIME DEFAULT NULL COMMENT '保留開始時間',
    MODIFY `reserved_by` VARCHAR(191) DEFAULT NULL COMMENT '保留者識別',
    MODIFY `reserved_until` DATETIME DEFAULT NULL COMMENT '保留到期時間',
    MODIFY `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT '建立時間';