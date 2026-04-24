-- ✅ Migration: เพิ่ม session_token สำหรับ Auth
ALTER TABLE `users` ADD COLUMN `session_token` VARCHAR(64) DEFAULT NULL AFTER `role`;
CREATE INDEX `idx_users_session_token` ON `users` (`session_token`);
