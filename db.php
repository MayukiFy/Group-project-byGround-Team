<?php
// ============================================================================
// Project: Ground Team - Volunteer & Social Impact Platform
// File: db.php - จัดการการเชื่อมต่อฐานข้อมูล MySQL และระบบจัดการข้อมูล 4 ตาราง
// Database: groundteam
// Tables:
//   1. user        : รับข้อมูลของผู้ใช้
//   2. job         : ข้อมูลกิจกรรมจิตอาสา (แอดมินเพิ่มและแก้ไขได้)
//   3. application : รับข้อมูลที่ผู้ใช้สมัครกิจกรรม (แอดมินตอบรับได้)
//   4. contact     : รับข้อมูลในหน้า Contact ที่ผู้ใช้ส่งให้เรา
// ============================================================================

$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'groundteam';

$pdo = null;

try {
    $dsn = "mysql:host={$db_host};dbname={$db_name};charset=utf8mb4";
    $options = [
        PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        PDO::ATTR_EMULATE_PREPARES   => false,
    ];
    $pdo = new PDO($dsn, $db_user, $db_pass, $options);

} catch (PDOException $e) {
    // กรณีฐานข้อมูลหรือตารางยังไม่สมบูรณ์ พยายามสร้างอัตโนมัติ
    try {
        $init_pdo = new PDO("mysql:host={$db_host};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $init_pdo->exec("CREATE DATABASE IF NOT EXISTS `{$db_name}` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;");

        $pdo = new PDO("mysql:host={$db_host};dbname={$db_name};charset=utf8mb4", $db_user, $db_pass, [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]);

        $sqlFile = __DIR__ . '/groundteam.sql';
        if (file_exists($sqlFile)) {
            $sqlContent = file_get_contents($sqlFile);
            $pdo->exec($sqlContent);
        }
    } catch (PDOException $ex) {
        $pdo = null;
    }
}

function get_db_connection() {
    global $pdo;
    return $pdo;
}

// ----------------------------------------------------------------------------
// Helper สำหรับ JSON Data Sync & Fallback
// ----------------------------------------------------------------------------
function _read_json_file($filename, $default = []) {
    $path = __DIR__ . '/data/' . $filename;
    if (!file_exists($path)) {
        return $default;
    }
    $content = file_get_contents($path);
    $data = json_decode($content, true);
    return is_array($data) ? $data : $default;
}

function _write_json_file($filename, $data) {
    $dir = __DIR__ . '/data';
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    $path = $dir . '/' . $filename;
    file_put_contents($path, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// ============================================================================
// 1. ตาราง USER (รับข้อมูลของผู้ใช้)
// ============================================================================
function db_get_all_users() {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM `user` ORDER BY id ASC");
            $rows = $stmt->fetchAll();
            if ($rows) {
                _write_json_file('users.json', $rows);
                return $rows;
            }
        } catch (Exception $e) {}
    }
    return _read_json_file('users.json', []);
}

function db_get_user_by_id($id) {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `user` WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $user = $stmt->fetch();
            if ($user) return $user;
        } catch (Exception $e) {}
    }
    $users = db_get_all_users();
    foreach ($users as $u) {
        if ((int)$u['id'] === (int)$id) return $u;
    }
    return null;
}

function db_get_user_by_username($username) {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `user` WHERE username = :u LIMIT 1");
            $stmt->execute([':u' => $username]);
            $user = $stmt->fetch();
            if ($user) return $user;
        } catch (Exception $e) {}
    }
    $users = db_get_all_users();
    foreach ($users as $u) {
        if ($u['username'] === $username) return $u;
    }
    return null;
}

function db_create_user($data) {
    global $pdo;
    $inserted_id = null;
    $now = date('Y-m-d H:i:s');

    if ($pdo) {
        try {
            $sql = "INSERT INTO `user` (`username`, `password`, `fullname`, `phone`, `email`, `province`, `age`, `gender`, `role`, `created_at`)
                    VALUES (:username, :password, :fullname, :phone, :email, :province, :age, :gender, :role, :created_at)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username'   => $data['username'],
                ':password'   => $data['password'],
                ':fullname'   => $data['fullname'],
                ':phone'      => $data['phone'],
                ':email'      => $data['email'],
                ':province'   => $data['province'],
                ':age'        => (int)$data['age'],
                ':gender'     => $data['gender'],
                ':role'       => $data['role'] ?? 'user',
                ':created_at' => $now
            ]);
            $inserted_id = (int)$pdo->lastInsertId();
            // สำรอง tbground ด้วย
            try {
                $stmt2 = $pdo->prepare("INSERT IGNORE INTO `tbground` (`id`, `username`, `password`, `fullname`, `phone`, `email`, `province`, `age`, `gender`, `role`, `created_at`)
                                        VALUES (:id, :username, :password, :fullname, :phone, :email, :province, :age, :gender, :role, :created_at)");
                $stmt2->execute([
                    ':id'         => $inserted_id,
                    ':username'   => $data['username'],
                    ':password'   => $data['password'],
                    ':fullname'   => $data['fullname'],
                    ':phone'      => $data['phone'],
                    ':email'      => $data['email'],
                    ':province'   => $data['province'],
                    ':age'        => (int)$data['age'],
                    ':gender'     => $data['gender'],
                    ':role'       => $data['role'] ?? 'user',
                    ':created_at' => $now
                ]);
            } catch (Exception $ex) {}
        } catch (Exception $e) {}
    }

    $users = db_get_all_users();
    if (!$inserted_id) {
        $maxId = 0;
        foreach ($users as $u) {
            if ($u['id'] > $maxId) $maxId = (int)$u['id'];
        }
        $inserted_id = $maxId + 1;
    }
    $newUser = array_merge($data, [
        'id' => $inserted_id,
        'role' => $data['role'] ?? 'user',
        'created_at' => $now
    ]);
    $users[] = $newUser;
    _write_json_file('users.json', $users);

    return $newUser;
}

function db_delete_user($id) {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `user` WHERE id = :id AND username != 'admin'");
            $stmt->execute([':id' => $id]);
            $stmt2 = $pdo->prepare("DELETE FROM `tbground` WHERE id = :id AND username != 'admin'");
            $stmt2->execute([':id' => $id]);
        } catch (Exception $e) {}
    }
    $users = db_get_all_users();
    $filtered = array_values(array_filter($users, function($u) use ($id) {
        return (int)$u['id'] !== (int)$id || $u['username'] === 'admin';
    }));
    _write_json_file('users.json', $filtered);
    return true;
}

// ============================================================================
// 2. ตาราง JOB (กิจกรรมจิตอาสา - แอดมินเพิ่มและแก้ไขได้)
// ============================================================================
function db_get_all_jobs() {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM `job` ORDER BY id ASC");
            $rows = $stmt->fetchAll();
            if ($rows && count($rows) > 0) {
                _write_json_file('jobs.json', $rows);
                return $rows;
            }
        } catch (Exception $e) {}
    }
    return _read_json_file('jobs.json', []);
}

function db_get_job_by_id($id) {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `job` WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $job = $stmt->fetch();
            if ($job) return $job;
        } catch (Exception $e) {}
    }
    $jobs = db_get_all_jobs();
    foreach ($jobs as $j) {
        if ((int)$j['id'] === (int)$id) return $j;
    }
    return null;
}

function db_create_job($data) {
    global $pdo;
    $now = date('Y-m-d H:i:s');
    $inserted_id = null;

    if ($pdo) {
        try {
            $sql = "INSERT INTO `job` (`title`, `description`, `category`, `event_date`, `event_time`, `location`, `hours`, `max_quota`, `current_quota`, `image_url`, `status`, `created_at`, `updated_at`)
                    VALUES (:title, :description, :category, :event_date, :event_time, :location, :hours, :max_quota, :current_quota, :image_url, :status, :created_at, :updated_at)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':title'         => $data['title'],
                ':description'   => $data['description'],
                ':category'      => $data['category'] ?? 'recruiting',
                ':event_date'    => $data['event_date'],
                ':event_time'    => $data['event_time'] ?? '09.00-16.00 น.',
                ':location'      => $data['location'],
                ':hours'         => (int)($data['hours'] ?? 3),
                ':max_quota'     => (int)($data['max_quota'] ?? 30),
                ':current_quota' => (int)($data['current_quota'] ?? 0),
                ':image_url'     => !empty($data['image_url']) ? $data['image_url'] : 'assets/images/กระดาษทำมือ.jpeg',
                ':status'        => $data['status'] ?? 'open',
                ':created_at'    => $now,
                ':updated_at'    => $now
            ]);
            $inserted_id = (int)$pdo->lastInsertId();
        } catch (Exception $e) {}
    }

    if ($inserted_id) {
        $jobs = db_get_all_jobs();
        foreach ($jobs as $j) {
            if ((int)$j['id'] === $inserted_id) return $j;
        }
    }

    $jobs = db_get_all_jobs();
    $maxId = 0;
    foreach ($jobs as $j) {
        if ($j['id'] > $maxId) $maxId = (int)$j['id'];
    }
    $newJob = array_merge($data, [
        'id'            => $maxId + 1,
        'hours'         => (int)($data['hours'] ?? 3),
        'max_quota'     => (int)($data['max_quota'] ?? 30),
        'current_quota' => (int)($data['current_quota'] ?? 0),
        'image_url'     => !empty($data['image_url']) ? $data['image_url'] : 'assets/images/กระดาษทำมือ.jpeg',
        'status'        => $data['status'] ?? 'open',
        'created_at'    => $now,
        'updated_at'    => $now
    ]);
    $jobs[] = $newJob;
    _write_json_file('jobs.json', $jobs);
    return $newJob;
}

function db_update_job($id, $data) {
    global $pdo;
    $now = date('Y-m-d H:i:s');
    $id = (int)$id;

    if ($pdo) {
        try {
            $sql = "UPDATE `job` SET
                    `title` = :title,
                    `description` = :description,
                    `category` = :category,
                    `event_date` = :event_date,
                    `event_time` = :event_time,
                    `location` = :location,
                    `hours` = :hours,
                    `max_quota` = :max_quota,
                    `current_quota` = :current_quota,
                    `image_url` = :image_url,
                    `status` = :status,
                    `updated_at` = :updated_at
                    WHERE id = :id";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':id'            => $id,
                ':title'         => $data['title'],
                ':description'   => $data['description'],
                ':category'      => $data['category'],
                ':event_date'    => $data['event_date'],
                ':event_time'    => $data['event_time'] ?? '09.00-16.00 น.',
                ':location'      => $data['location'],
                ':hours'         => (int)$data['hours'],
                ':max_quota'     => (int)$data['max_quota'],
                ':current_quota' => (int)($data['current_quota'] ?? 0),
                ':image_url'     => $data['image_url'],
                ':status'        => $data['status'] ?? 'open',
                ':updated_at'    => $now
            ]);
        } catch (Exception $e) {}
    }

    $jobs = db_get_all_jobs();
    foreach ($jobs as &$j) {
        if ((int)$j['id'] === $id) {
            $j = array_merge($j, $data, ['updated_at' => $now]);
            break;
        }
    }
    _write_json_file('jobs.json', $jobs);
    return true;
}

function db_delete_job($id) {
    global $pdo;
    $id = (int)$id;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `job` WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } catch (Exception $e) {}
    }
    $jobs = _read_json_file('jobs.json', []);
    $filtered = array_values(array_filter($jobs, function($j) use ($id) {
        return (int)$j['id'] !== $id;
    }));
    _write_json_file('jobs.json', $filtered);
    return true;
}

function db_toggle_job_status($id) {
    $job = db_get_job_by_id($id);
    if (!$job) return false;
    $newStatus = ($job['status'] === 'open') ? 'closed' : 'open';
    $job['status'] = $newStatus;
    return db_update_job($id, $job);
}

// ============================================================================
// 3. ตาราง APPLICATION (รับข้อมูลการสมัครกิจกรรม - แอดมินตอบรับได้)
// ============================================================================
function db_get_all_applications($statusFilter = null) {
    global $pdo;
    if ($pdo) {
        try {
            if ($statusFilter) {
                $stmt = $pdo->prepare("SELECT * FROM `application` WHERE status = :st ORDER BY id DESC");
                $stmt->execute([':st' => $statusFilter]);
            } else {
                $stmt = $pdo->query("SELECT * FROM `application` ORDER BY id DESC");
            }
            $rows = $stmt->fetchAll();
            if ($rows !== false && count($rows) > 0) {
                if (!$statusFilter) _write_json_file('applications.json', $rows);
                return $rows;
            }
        } catch (Exception $e) {}
    }
    $apps = _read_json_file('applications.json', []);
    if ($statusFilter) {
        return array_values(array_filter($apps, function($a) use ($statusFilter) {
            return ($a['status'] ?? '') === $statusFilter;
        }));
    }
    return $apps;
}

function db_get_applications_by_user($user_id) {
    global $pdo;
    $user_id = (int)$user_id;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `application` WHERE user_id = :uid ORDER BY id DESC");
            $stmt->execute([':uid' => $user_id]);
            $rows = $stmt->fetchAll();
            if ($rows !== false && count($rows) > 0) return $rows;
        } catch (Exception $e) {}
    }
    $apps = db_get_all_applications();
    return array_values(array_filter($apps, function($a) use ($user_id) {
        return (int)($a['user_id'] ?? 0) === $user_id;
    }));
}

function db_get_application_by_id($id) {
    global $pdo;
    $id = (int)$id;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `application` WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $app = $stmt->fetch();
            if ($app) return $app;
        } catch (Exception $e) {}
    }
    $apps = db_get_all_applications();
    foreach ($apps as $a) {
        if ((int)$a['id'] === $id) return $a;
    }
    return null;
}

function db_create_application($data) {
    global $pdo;
    $now = date('Y-m-d H:i:s');
    $inserted_id = null;

    if ($pdo) {
        try {
            $sql = "INSERT INTO `application` (`user_id`, `job_id`, `job_title`, `fullname`, `phone`, `email`, `age`, `skills`, `status`, `admin_notes`, `applied_at`, `updated_at`)
                    VALUES (:user_id, :job_id, :job_title, :fullname, :phone, :email, :age, :skills, :status, :admin_notes, :applied_at, :updated_at)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':user_id'     => !empty($data['user_id']) ? (int)$data['user_id'] : null,
                ':job_id'      => (int)$data['job_id'],
                ':job_title'   => $data['job_title'],
                ':fullname'    => $data['fullname'],
                ':phone'       => $data['phone'],
                ':email'       => $data['email'],
                ':age'         => (int)$data['age'],
                ':skills'      => $data['skills'] ?? '',
                ':status'      => 'pending',
                ':admin_notes' => null,
                ':applied_at'  => $now,
                ':updated_at'  => $now
            ]);
            $inserted_id = (int)$pdo->lastInsertId();

            // เพิ่มจำนวน current_quota ในตาราง job อัตโนมัติ
            $pdo->prepare("UPDATE `job` SET current_quota = current_quota + 1 WHERE id = :jid")->execute([':jid' => (int)$data['job_id']]);
        } catch (Exception $e) {}
    }

    if ($inserted_id) {
        $apps = db_get_all_applications();
        foreach ($apps as $a) {
            if ((int)$a['id'] === $inserted_id) return $a;
        }
    }

    $apps = db_get_all_applications();
    $maxId = 0;
    foreach ($apps as $a) {
        if ($a['id'] > $maxId) $maxId = (int)$a['id'];
    }
    $newApp = array_merge($data, [
        'id'          => $maxId + 1,
        'user_id'     => !empty($data['user_id']) ? (int)$data['user_id'] : null,
        'job_id'      => (int)$data['job_id'],
        'age'         => (int)$data['age'],
        'status'      => 'pending',
        'admin_notes' => null,
        'applied_at'  => $now,
        'updated_at'  => $now
    ]);
    array_unshift($apps, $newApp);
    _write_json_file('applications.json', $apps);

    return $newApp;
}

function db_update_application_status($id, $status, $admin_notes = null) {
    global $pdo;
    $id = (int)$id;
    $now = date('Y-m-d H:i:s');

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE `application` SET `status` = :st, `admin_notes` = :notes, `updated_at` = :up WHERE id = :id");
            $stmt->execute([
                ':st'    => $status,
                ':notes' => $admin_notes,
                ':up'    => $now,
                ':id'    => $id
            ]);
        } catch (Exception $e) {}
    }

    $apps = db_get_all_applications();
    foreach ($apps as &$a) {
        if ((int)$a['id'] === $id) {
            $a['status'] = $status;
            if ($admin_notes !== null) $a['admin_notes'] = $admin_notes;
            $a['updated_at'] = $now;
            break;
        }
    }
    _write_json_file('applications.json', $apps);
    return true;
}

// ============================================================================
// 4. ตาราง CONTACT (รับข้อมูลในหน้า Contact ที่ผู้ใช้ส่งให้เรา)
// ============================================================================
function db_get_all_contacts($statusFilter = null) {
    global $pdo;
    if ($pdo) {
        try {
            if ($statusFilter) {
                $stmt = $pdo->prepare("SELECT * FROM `contact` WHERE status = :st ORDER BY id DESC");
                $stmt->execute([':st' => $statusFilter]);
            } else {
                $stmt = $pdo->query("SELECT * FROM `contact` ORDER BY id DESC");
            }
            $rows = $stmt->fetchAll();
            if ($rows !== false && count($rows) > 0) {
                if (!$statusFilter) _write_json_file('contacts.json', $rows);
                return $rows;
            }
        } catch (Exception $e) {}
    }
    $msgs = _read_json_file('contacts.json', []);
    if ($statusFilter) {
        return array_values(array_filter($msgs, function($m) use ($statusFilter) {
            return ($m['status'] ?? '') === $statusFilter;
        }));
    }
    return $msgs;
}

function db_get_contact_by_id($id) {
    global $pdo;
    $id = (int)$id;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("SELECT * FROM `contact` WHERE id = :id LIMIT 1");
            $stmt->execute([':id' => $id]);
            $row = $stmt->fetch();
            if ($row) return $row;
        } catch (Exception $e) {}
    }
    $msgs = db_get_all_contacts();
    foreach ($msgs as $m) {
        if ((int)$m['id'] === $id) return $m;
    }
    return null;
}

function db_create_contact($data) {
    global $pdo;
    $now = date('Y-m-d H:i:s');
    $inserted_id = null;

    if ($pdo) {
        try {
            $sql = "INSERT INTO `contact` (`name`, `email`, `phone`, `subject`, `message`, `status`, `reply_message`, `created_at`)
                    VALUES (:name, :email, :phone, :subject, :message, :status, :reply_message, :created_at)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':name'          => $data['name'],
                ':email'         => $data['email'],
                ':phone'         => $data['phone'],
                ':subject'       => $data['subject'],
                ':message'       => $data['message'],
                ':status'        => 'unread',
                ':reply_message' => null,
                ':created_at'    => $now
            ]);
            $inserted_id = (int)$pdo->lastInsertId();
        } catch (Exception $e) {}
    }

    if ($inserted_id) {
        $msgs = db_get_all_contacts();
        foreach ($msgs as $m) {
            if ((int)$m['id'] === $inserted_id) return $m;
        }
    }

    $msgs = db_get_all_contacts();
    $maxId = 0;
    foreach ($msgs as $m) {
        if ($m['id'] > $maxId) $maxId = (int)$m['id'];
    }
    $newMsg = array_merge($data, [
        'id'            => $maxId + 1,
        'status'        => 'unread',
        'reply_message' => null,
        'created_at'    => $now
    ]);
    array_unshift($msgs, $newMsg);
    _write_json_file('contacts.json', $msgs);

    return $newMsg;
}

function db_update_contact_status($id, $status, $reply_message = null) {
    global $pdo;
    $id = (int)$id;

    if ($pdo) {
        try {
            $stmt = $pdo->prepare("UPDATE `contact` SET `status` = :st, `reply_message` = :rep WHERE id = :id");
            $stmt->execute([
                ':st'  => $status,
                ':rep' => $reply_message,
                ':id'  => $id
            ]);
        } catch (Exception $e) {}
    }

    $msgs = db_get_all_contacts();
    foreach ($msgs as &$m) {
        if ((int)$m['id'] === $id) {
            $m['status'] = $status;
            if ($reply_message !== null) $m['reply_message'] = $reply_message;
            break;
        }
    }
    _write_json_file('contacts.json', $msgs);
    return true;
}

function db_delete_contact($id) {
    global $pdo;
    $id = (int)$id;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM `contact` WHERE id = :id");
            $stmt->execute([':id' => $id]);
        } catch (Exception $e) {}
    }
    $msgs = db_get_all_contacts();
    $filtered = array_values(array_filter($msgs, function($m) use ($id) {
        return (int)$m['id'] !== $id;
    }));
    _write_json_file('contacts.json', $filtered);
    return true;
}
