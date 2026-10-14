<?php
// ============================================================================
// Project: Ground Team - Volunteer & Social Impact Platform
// File: db.php - จัดการการเชื่อมต่อฐานข้อมูล MySQL (Database: groundteam, Table: tbground)
// ============================================================================

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'groundteam';

$pdo = null;

try {
    // 1. เชื่อมต่อฐานข้อมูล groundteam ด้วย PDO UTF-8
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    // 2. กรณีที่ฐานข้อมูล groundteam ยังไม่ถูกสร้าง ให้สร้างฐานข้อมูลและตารางอัตโนมัติ
    try {
        $init_pdo = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $init_pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");
        
        $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        // รันคำสั่ง SQL สร้างตาราง tbground จากไฟล์ groundteam.sql
        $sqlFile = __DIR__ . '/groundteam.sql';
        if (file_exists($sqlFile)) {
            $sqlContent = file_get_contents($sqlFile);
            $pdo->exec($sqlContent);
        }
    } catch (PDOException $ex) {
        $pdo = null; // หากไม่สามารถเชื่อมต่อ MySQL ได้
    }
}

/**
 * ฟังก์ชันตรวจสอบสถานะการเชื่อมต่อฐานข้อมูล
 */
function get_db_connection() {
    global $pdo;
    return $pdo;
}

