-- ============================================================================
-- โครงสร้างฐานข้อมูลสำหรับโครงการ Ground Team
-- Database: groundteam
-- Table: tbground
-- ============================================================================

-- 1. สร้างฐานข้อมูล groundteam (หากยังไม่มี) และกำหนด Character Set เป็น utf8mb4
CREATE DATABASE IF NOT EXISTS `groundteam` 
DEFAULT CHARACTER SET utf8mb4 
COLLATE utf8mb4_unicode_ci;

-- 2. เลือกใช้งานฐานข้อมูล groundteam
USE `groundteam`;

-- 3. ลบตารางเดิมออกหากมีอยู่ (เพื่อสร้างใหม่แบบสมบูรณ์)
DROP TABLE IF EXISTS `tbground`;

-- 4. สร้างตาราง tbground สำหรับจัดเก็บข้อมูลสมาชิกที่สมัครรับข้อมูล
CREATE TABLE `tbground` (
  `id` INT(11) NOT NULL AUTO_INCREMENT COMMENT 'รหัสลำดับสมาชิก (Primary Key)',
  `username` VARCHAR(50) NOT NULL COMMENT 'ชื่อผู้ใช้งานสำหรับเข้าสู่ระบบ',
  `password` VARCHAR(255) NOT NULL COMMENT 'รหัสผ่านสำหรับเข้าสู่ระบบ',
  `fullname` VARCHAR(150) NOT NULL COMMENT 'ชื่อ - สกุล',
  `phone` VARCHAR(20) NOT NULL COMMENT 'หมายเลขโทรศัพท์',
  `email` VARCHAR(100) NOT NULL COMMENT 'อีเมล',
  `province` VARCHAR(100) NOT NULL COMMENT 'จังหวัดที่อยู่ (รายการตัวเลือก)',
  `age` INT(3) NOT NULL COMMENT 'อายุ',
  `gender` ENUM('ชาย', 'หญิง', 'lgbtq+') NOT NULL DEFAULT 'ชาย' COMMENT 'เพศ (ชาย / หญิง / lgbtq+)',
  `role` VARCHAR(20) NOT NULL DEFAULT 'user' COMMENT 'สิทธิ์การใช้งาน (admin หรือ user)',
  `created_at` DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP COMMENT 'วันและเวลาที่ลงทะเบียน',
  PRIMARY KEY (`id`),
  UNIQUE KEY `idx_username` (`username`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='ตารางจัดเก็บข้อมูลสมาชิกและจิตอาสา Ground Team';

-- ============================================================================
-- 5. ข้อมูลเริ่มต้นสำหรับการทดสอบระบบ (Seed Data)
-- ============================================================================

INSERT INTO `tbground` (`id`, `username`, `password`, `fullname`, `phone`, `email`, `province`, `age`, `gender`, `role`, `created_at`) VALUES
(1, 'admin', '0000', 'ผู้ดูแลระบบ Ground Team (Admin)', '02-123-4567', 'admin@groundteam.org', 'กรุงเทพมหานคร', 30, 'ชาย', 'admin', '2026-01-01 09:00:00'),
(2, 'somchai', '1234', 'สมชาย ใจดี', '0812345678', 'somchai@example.com', 'เชียงใหม่', 24, 'ชาย', 'user', '2026-10-10 14:30:00'),
(3, 'nicha', '1234', 'ณิชา วงศ์สวรรค์', '0898765432', 'nicha@example.com', 'ชลบุรี', 21, 'หญิง', 'user', '2026-10-11 11:20:00'),
(4, 'alex', '1234', 'อเล็กซ์ รัตนโชติ', '0865554321', 'alex@example.com', 'กรุงเทพมหานคร', 26, 'lgbtq+', 'user', '2026-10-11 16:45:00');

