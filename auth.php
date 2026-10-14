<?php
// ==========================================================================
// Project: Ground Team - Volunteer & Social Impact Platform
// Page: auth.php - ระบบจัดการ Authentication, Session และสิทธิ์การเข้าถึง
// รองรับฐานข้อมูล MySQL: Database 'groundteam', Table 'tbground'
// ==========================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';

$dataFile = __DIR__ . '/data/users.json';

// --------------------------------------------------------------------------
// 1. ฟังก์ชันอ่านข้อมูลผู้ใช้งานทั้งหมดจากฐานข้อมูล MySQL (tbground)
// --------------------------------------------------------------------------
function get_all_users() {
    global $pdo, $dataFile;

    // ลองดึงจาก MySQL Database 'groundteam', Table 'tbground'
    if ($pdo) {
        try {
            $stmt = $pdo->query("SELECT * FROM tbground ORDER BY id ASC");
            $users = $stmt->fetchAll();
            if ($users && count($users) > 0) {
                return $users;
            }
        } catch (Exception $e) {
            // เกิดข้อผิดพลาดใน MySQL จะดึงจากไฟล์สำรองแทน
        }
    }

    // แหล่งข้อมูลสำรอง: data/users.json
    if (!file_exists($dataFile)) {
        return [];
    }
    $json = file_get_contents($dataFile);
    $users = json_decode($json, true);
    return is_array($users) ? $users : [];
}

// --------------------------------------------------------------------------
// 2. ฟังก์ชันสำรองข้อมูลลงไฟล์ JSON
// --------------------------------------------------------------------------
function save_all_users($users) {
    global $dataFile;
    $dir = dirname($dataFile);
    if (!is_dir($dir)) {
        mkdir($dir, 0777, true);
    }
    file_put_contents($dataFile, json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// --------------------------------------------------------------------------
// 3. ฟังก์ชันเพิ่มข้อมูลสมาชิกใหม่ลงตาราง tbground
// --------------------------------------------------------------------------
function insert_new_user($userData) {
    global $pdo;

    $inserted_id = null;
    if ($pdo) {
        try {
            $sql = "INSERT INTO tbground (`username`, `password`, `fullname`, `phone`, `email`, `province`, `age`, `gender`, `role`, `created_at`) 
                    VALUES (:username, :password, :fullname, :phone, :email, :province, :age, :gender, :role, :created_at)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([
                ':username'   => $userData['username'],
                ':password'   => $userData['password'],
                ':fullname'   => $userData['fullname'],
                ':phone'      => $userData['phone'],
                ':email'      => $userData['email'],
                ':province'   => $userData['province'],
                ':age'        => $userData['age'],
                ':gender'     => $userData['gender'],
                ':role'       => $userData['role'] ?? 'user',
                ':created_at' => $userData['registered_at'] ?? date('Y-m-d H:i:s')
            ]);
            $inserted_id = (int)$pdo->lastInsertId();
            $userData['id'] = $inserted_id;
        } catch (Exception $e) {
            // หาก MySQL ไม่พร้อม ใช้วิธีคำนวณ ID สำรอง
        }
    }

    // อัปเดตสำรองลง data/users.json
    $allUsers = get_all_users();
    save_all_users($allUsers);

    return $userData;
}

// --------------------------------------------------------------------------
// 4. ฟังก์ชันลบสมาชิกจากตาราง tbground
// --------------------------------------------------------------------------
function delete_user_by_id($id) {
    global $pdo;
    if ($pdo) {
        try {
            $stmt = $pdo->prepare("DELETE FROM tbground WHERE id = :id AND username != 'admin'");
            $stmt->execute([':id' => $id]);
        } catch (Exception $e) {}
    }

    // อัปเดตสำรองในไฟล์ JSON
    $allUsers = get_all_users();
    save_all_users($allUsers);
}

// --------------------------------------------------------------------------
// 5. ตรวจสอบสถานะการเข้าสู่ระบบ
// --------------------------------------------------------------------------
function get_current_user_data() {
    return $_SESSION['current_user'] ?? null;
}

function is_logged_in() {
    return isset($_SESSION['current_user']) && !empty($_SESSION['current_user']);
}

function is_admin() {
    $user = get_current_user_data();
    return $user && isset($user['role']) && $user['role'] === 'admin';
}

function is_json_request() {
    return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
        || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
        || (isset($_GET['format']) && $_GET['format'] === 'json');
}

$action = $_REQUEST['action'] ?? '';

// --------------------------------------------------------------------------
// Action: STATUS (ตรวจสอบสถานะ Login ผ่าน JavaScript API)
// --------------------------------------------------------------------------
if ($action === 'status') {
    header('Content-Type: application/json; charset=utf-8');
    $user = get_current_user_data();
    echo json_encode([
        'logged_in' => is_logged_in(),
        'is_admin'  => is_admin(),
        'user'      => $user ? [
            'id'       => $user['id'],
            'username' => $user['username'],
            'fullname' => $user['fullname'],
            'email'    => $user['email'],
            'phone'    => $user['phone'],
            'province' => $user['province'],
            'age'      => $user['age'],
            'gender'   => $user['gender'],
            'role'     => $user['role']
        ] : null
    ]);
    exit;
}

// --------------------------------------------------------------------------
// Action: LOGOUT
// --------------------------------------------------------------------------
if ($action === 'logout') {
    unset($_SESSION['current_user']);
    session_destroy();

    if (is_json_request()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode(['success' => true, 'message' => 'ออกจากระบบเรียบร้อยแล้ว']);
        exit;
    }
    header('Location: login.php?msg=logout_success');
    exit;
}

// --------------------------------------------------------------------------
// Action: LOGIN (เข้าสู่ระบบ - แยกอย่างเด็ดขาดระหว่าง ผู้ใช้งานทั่วไป กับ แอดมิน)
// --------------------------------------------------------------------------
if ($action === 'login' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username   = trim($_POST['username'] ?? '');
    $password   = trim($_POST['password'] ?? '');
    $login_type = trim($_POST['login_type'] ?? 'user'); // 'user' หรือ 'admin'

    // ป้องกันกรณีที่แอดมินพยายามล็อกอินผ่านช่องทางของผู้ใช้ทั่วไป
    if ($login_type === 'user') {
        if (strcasecmp($username, 'admin') === 0) {
            $err = 'บัญชีผู้ดูแลระบบ (Admin) ไม่สามารถเข้าสู่ระบบผ่านช่องทางสมาชิกได้ กรุณาเข้าสู่ระบบผ่านแท็บ "เข้าสู่ระบบสำหรับแอดมิน"';
            if (is_json_request()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            header('Location: login.php?tab=user&error=' . urlencode($err));
            exit;
        }

        // ค้นหาเฉพาะผู้ใช้ทั่วไป
        $matched_user = null;
        if ($pdo) {
            try {
                $stmt = $pdo->prepare("SELECT * FROM tbground WHERE username = :username AND password = :password LIMIT 1");
                $stmt->execute([':username' => $username, ':password' => $password]);
                $user_found = $stmt->fetch();
                if ($user_found) {
                    if (isset($user_found['role']) && $user_found['role'] === 'admin') {
                        $err = 'บัญชีผู้ดูแลระบบ (Admin) ไม่สามารถเข้าสู่ระบบผ่านช่องทางสมาชิกได้ กรุณาเข้าสู่ระบบผ่านแท็บ "เข้าสู่ระบบสำหรับแอดมิน"';
                        header('Location: login.php?tab=user&error=' . urlencode($err));
                        exit;
                    }
                    $matched_user = $user_found;
                }
            } catch (Exception $e) {}
        }

        // ค้นหาสำรองใน data/users.json
        if (!$matched_user) {
            $users = get_all_users();
            foreach ($users as $u) {
                if (strcasecmp($u['username'], $username) === 0 && (string)$u['password'] === (string)$password) {
                    if (isset($u['role']) && $u['role'] === 'admin') {
                        $err = 'บัญชีผู้ดูแลระบบ (Admin) ไม่สามารถเข้าสู่ระบบผ่านช่องทางสมาชิกได้ กรุณาเข้าสู่ระบบผ่านแท็บ "เข้าสู่ระบบสำหรับแอดมิน"';
                        header('Location: login.php?tab=user&error=' . urlencode($err));
                        exit;
                    }
                    $matched_user = $u;
                    break;
                }
            }
        }

        if ($matched_user) {
            $_SESSION['current_user'] = $matched_user;
            if (is_json_request()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'  => true,
                    'message'  => 'เข้าสู่ระบบสมาชิกสำเร็จ',
                    'is_admin' => false,
                    'redirect' => 'profile.php'
                ]);
                exit;
            }
            header('Location: profile.php');
            exit;
        } else {
            $err = 'ชื่อผู้ใช้หรือรหัสผ่านสมาชิกไม่ถูกต้อง กรุณาลองใหม่อีกครั้ง';
            if (is_json_request()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            header('Location: login.php?tab=user&error=' . urlencode($err));
            exit;
        }

    } elseif ($login_type === 'admin') {
        // ตรวจสอบการเข้าสู่ระบบเฉพาะผู้ดูแลระบบ (Admin)
        $is_valid_admin = false;
        $admin_user = null;

        // บัญชี Admin พื้นฐานตามโจทย์: username: admin | password: 0000
        if ($username === 'admin' && $password === '0000') {
            $is_valid_admin = true;
            $admin_user = [
                'id'            => 1,
                'username'      => 'admin',
                'role'          => 'admin',
                'fullname'      => 'ผู้ดูแลระบบ Ground Team (Admin)',
                'phone'         => '062-831-3176',
                'email'         => '67011213047@msu.ac.th',
                'province'      => 'มหาสารคาม',
                'age'           => 22,
                'gender'        => 'ชาย',
                'registered_at' => '2026-01-01 09:00:00'
            ];
        } else {
            // ตรวจสอบใน MySQL กรณีมีบัญชี role = 'admin' อื่นๆ
            if ($pdo) {
                try {
                    $stmt = $pdo->prepare("SELECT * FROM tbground WHERE username = :username AND password = :password AND role = 'admin' LIMIT 1");
                    $stmt->execute([':username' => $username, ':password' => $password]);
                    $row = $stmt->fetch();
                    if ($row) {
                        $is_valid_admin = true;
                        $admin_user = $row;
                    }
                } catch (Exception $e) {}
            }

            // ตรวจสอบว่าผู้ใช้เผลอนำบัญชีสมาชิกทั่วไปมาล็อกอินที่แท็บแอดมินหรือไม่
            if (!$is_valid_admin) {
                $is_normal_user = false;
                if ($pdo) {
                    try {
                        $chk = $pdo->prepare("SELECT * FROM tbground WHERE username = :username AND password = :password LIMIT 1");
                        $chk->execute([':username' => $username, ':password' => $password]);
                        if ($chk->fetch()) $is_normal_user = true;
                    } catch (Exception $e) {}
                }
                if ($is_normal_user) {
                    $err = 'ช่องทางนี้สำหรับผู้ดูแลระบบ (Admin) เท่านั้น บัญชีสมาชิกทั่วไปกรุณาเข้าสู่ระบบผ่านแท็บ "เข้าสู่ระบบสมาชิก"';
                    if (is_json_request()) {
                        header('Content-Type: application/json; charset=utf-8');
                        echo json_encode(['success' => false, 'message' => $err]);
                        exit;
                    }
                    header('Location: login.php?tab=admin&error=' . urlencode($err));
                    exit;
                }
            }
        }

        if ($is_valid_admin && $admin_user) {
            $_SESSION['current_user'] = $admin_user;
            if (is_json_request()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode([
                    'success'  => true,
                    'message'  => 'เข้าสู่ระบบผู้ดูแลระบบสำเร็จ',
                    'is_admin' => true,
                    'redirect' => 'information.php'
                ]);
                exit;
            }
            header('Location: information.php');
            exit;
        } else {
            $err = 'ชื่อผู้ใช้หรือรหัสผ่านแอดมินไม่ถูกต้อง (Username: admin | Password: 0000)';
            if (is_json_request()) {
                header('Content-Type: application/json; charset=utf-8');
                echo json_encode(['success' => false, 'message' => $err]);
                exit;
            }
            header('Location: login.php?tab=admin&error=' . urlencode($err));
            exit;
        }
    } else {
        header('Location: login.php?error=' . urlencode('ประเภทการเข้าสู่ระบบไม่ถูกต้อง'));
        exit;
    }
}

// --------------------------------------------------------------------------
// Action: REGISTER (สมัครสมาชิกเพื่อรับข้อมูล บันทึกลงตาราง tbground)
// --------------------------------------------------------------------------
if ($action === 'register' && $_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = trim($_POST['password'] ?? '');
    $fullname = trim($_POST['fullname'] ?? '');
    $phone    = trim($_POST['phone'] ?? '');
    $email    = trim($_POST['email'] ?? '');
    $province = trim($_POST['province'] ?? '');
    $age      = (int)($_POST['age'] ?? 0);
    $gender   = trim($_POST['gender'] ?? 'ชาย');

    // ตรวจสอบความถูกต้องของข้อมูล
    if (empty($username) || empty($password) || empty($fullname) || empty($email) || empty($phone) || empty($province)) {
        $error_msg = 'กรุณากรอกข้อมูลให้ครบถ้วนทุกช่อง';
        if (is_json_request()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit;
        }
        header('Location: register.php?error=' . urlencode($error_msg));
        exit;
    }

    // ห้ามใช้ชื่อ admin ซ้ำ
    if (strtolower($username) === 'admin') {
        $error_msg = 'ชื่อผู้ใช้นี้ถูกสงวนไว้สำหรับผู้ดูแลระบบ';
        if (is_json_request()) {
            header('Content-Type: application/json; charset=utf-8');
            echo json_encode(['success' => false, 'message' => $error_msg]);
            exit;
        }
        header('Location: register.php?error=' . urlencode($error_msg));
        exit;
    }

    // ตรวจสอบ username ซ้ำในฐานข้อมูล tbground
    if ($pdo) {
        try {
            $chk = $pdo->prepare("SELECT id FROM tbground WHERE username = :username LIMIT 1");
            $chk->execute([':username' => $username]);
            if ($chk->fetch()) {
                $error_msg = 'ชื่อผู้ใช้ "' . htmlspecialchars($username) . '" มีผู้ใช้งานแล้ว กรุณาเลือกชื่ออื่น';
                if (is_json_request()) {
                    header('Content-Type: application/json; charset=utf-8');
                    echo json_encode(['success' => false, 'message' => $error_msg]);
                    exit;
                }
                header('Location: register.php?error=' . urlencode($error_msg));
                exit;
            }
        } catch (Exception $e) {}
    }

    // บันทึกสมาชิกใหม่ลง MySQL ตาราง tbground
    $new_user_data = [
        'username'      => $username,
        'password'      => $password,
        'role'          => 'user',
        'fullname'      => $fullname,
        'phone'         => $phone,
        'email'         => $email,
        'province'      => $province,
        'age'           => $age > 0 ? $age : 18,
        'gender'        => $gender,
        'registered_at' => date('Y-m-d H:i:s')
    ];

    $saved_user = insert_new_user($new_user_data);

    // เข้าสู่ระบบให้อัตโนมัติและนำทางไปยังหน้าข้อมูลส่วนตัว (profile.php)
    $_SESSION['current_user'] = $saved_user;

    if (is_json_request()) {
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode([
            'success'  => true,
            'message'  => 'สมัครสมาชิกสำเร็จ! ข้อมูลถูกบันทึกลงในตาราง tbground เรียบร้อยแล้ว',
            'redirect' => 'profile.php'
        ]);
        exit;
    }

    header('Location: profile.php?registered=1');
    exit;
}
