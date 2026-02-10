CREATE TABLE IF NOT EXISTS `#__flexqueue_jobs` (
    `id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `queue` VARCHAR(191) NOT NULL DEFAULT 'default',
    `payload` LONGTEXT NOT NULL,
    `attempts` TINYINT UNSIGNED NOT NULL DEFAULT 0,
    `available_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `reserved_at` DATETIME DEFAULT NULL,
    `reserved_by` VARCHAR(191) DEFAULT NULL,
    `reserved_until` DATETIME DEFAULT NULL,
    `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    `last_error` TEXT NULL,
    PRIMARY KEY (`id`),
    KEY `idx_queue_available` (`queue`, `available_at`),
    KEY `idx_reserved_until` (`reserved_until`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;


