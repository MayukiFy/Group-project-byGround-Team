<?php
// ============================================================================
// Project: Ground Team - Volunteer & Social Impact Platform
// File: api.php - API Controller สำหรับการตอบโต้ระหว่างผู้ใช้และแอดมิน
// จัดการข้อมูลทั้ง 4 ตาราง: user, job, application, contact
// ============================================================================

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

header('Content-Type: application/json; charset=utf-8');

$action = $_REQUEST['action'] ?? '';
$user = get_current_user_data();
$is_admin = is_admin();


// ----------------------------------------------------------------------------
// ฟังก์ชันช่วยจัดการอัปโหลดรูปภาพกิจกรรมจิตอาสา (Upload Image Helper)
// ----------------------------------------------------------------------------
function handle_job_image_upload($input_name = 'job_image', $required = false) {
    if (!isset($_FILES[$input_name]) || $_FILES[$input_name]['error'] === UPLOAD_ERR_NO_FILE) {
        if ($required) {
            return ['success' => false, 'message' => 'กรุณาเลือกไฟล์รูปภาพเพื่ออัปโหลดใหม่ (ระบบกำหนดให้อัปโหลดรูปภาพใหม่เท่านั้น)'];
        }
        return ['success' => true, 'path' => null];
    }

    $file = $_FILES[$input_name];
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'เกิดข้อผิดพลาดในการอัปโหลดไฟล์ (Error code: ' . $file['error'] . ')'];
    }

    // จำกัดขนาดไฟล์ไม่เกิน 10MB
    if ($file['size'] > 10 * 1024 * 1024) {
        return ['success' => false, 'message' => 'ขนาดไฟล์ใหญ่เกินไป กรุณาใช้ไฟล์รูปภาพขนาดไม่เกิน 10MB'];
    }

    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $allowed = ['jpg', 'jpeg', 'png', 'webp', 'gif'];
    if (!in_array($ext, $allowed)) {
        return ['success' => false, 'message' => 'รูปแบบไฟล์ไม่ถูกต้อง รองรับเฉพาะไฟล์รูปภาพ (JPG, PNG, WEBP, GIF) เท่านั้น'];
    }

    // ตรวจสอบ MIME type
    if (function_exists('finfo_open')) {
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mime = finfo_file($finfo, $file['tmp_name']);
        finfo_close($finfo);
        if (!in_array($mime, ['image/jpeg', 'image/png', 'image/webp', 'image/gif'])) {
            return ['success' => false, 'message' => 'ไฟล์ที่อัปโหลดไม่ใช่รูปภาพที่ถูกต้อง'];
        }
    }

    $uploadDir = __DIR__ . '/assets/images/';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0777, true);
    }

    // ตั้งชื่อไฟล์ใหม่แบบสุ่ม ป้องกันการซ้ำ
    $filename = 'job_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
    $targetPath = $uploadDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        return ['success' => true, 'path' => 'assets/images/' . $filename];
    }

    return ['success' => false, 'message' => 'ไม่สามารถบันทึกไฟล์รูปภาพไปยังโฟลเดอร์เซิร์ฟเวอร์ได้'];
}

// ----------------------------------------------------------------------------
// 1. ตาราง JOB: กิจกรรมจิตอาสา (ผู้ใช้ดูได้ / แอดมินเพิ่มและแก้ไขได้)
// ----------------------------------------------------------------------------
if ($action === 'get_jobs') {
    $jobs = db_get_all_jobs();
    echo json_encode(['success' => true, 'data' => $jobs], JSON_UNESCAPED_UNICODE);
    exit;
}

if ($action === 'get_job') {
    $id = (int)($_GET['id'] ?? 0);
    $job = db_get_job_by_id($id);
    if ($job) {
        echo json_encode(['success' => true, 'data' => $job], JSON_UNESCAPED_UNICODE);
    } else {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อมูลกิจกรรม'], JSON_UNESCAPED_UNICODE);
    }
    exit;
}

// แอดมิน: เพิ่มกิจกรรมใหม่ (Create Job) - ข้อกำหนด: ให้อัปโหลดรูปใหม่เข้าไปเท่านั้น
if ($action === 'create_job') {
    if (!$is_admin && !isset($_SESSION['current_user'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ กรุณาเข้าสู่ระบบด้วยบัญชีแอดมิน']);
        exit;
    }

    $title       = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $category    = trim($_POST['category'] ?? 'recruiting');
    $event_date  = trim($_POST['event_date'] ?? '');
    $event_time  = trim($_POST['event_time'] ?? '09.00-16.00 น.');
    $location    = trim($_POST['location'] ?? '');
    $hours       = (int)($_POST['hours'] ?? 3);
    $max_quota   = (int)($_POST['max_quota'] ?? 30);
    $status      = trim($_POST['status'] ?? 'open');

    if (empty($title) || empty($description) || empty($event_date) || empty($location)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลกิจกรรมที่จำเป็นให้ครบถ้วน']);
        exit;
    }

    // บังคับให้อัปโหลดรูปภาพใหม่เท่านั้น (Required Image Upload)
    $uploadResult = handle_job_image_upload('job_image', true);
    if (!$uploadResult['success']) {
        echo json_encode(['success' => false, 'message' => $uploadResult['message']]);
        exit;
    }
    $image_url = $uploadResult['path'];

    $newJob = db_create_job([
        'title'         => $title,
        'description'   => $description,
        'category'      => $category,
        'event_date'    => $event_date,
        'event_time'    => $event_time,
        'location'      => $location,
        'hours'         => $hours,
        'max_quota'     => $max_quota,
        'current_quota' => 0,
        'image_url'     => $image_url,
        'status'        => $status
    ]);

    echo json_encode(['success' => true, 'message' => 'เพิ่มกิจกรรมจิตอาสาและอัปโหลดรูปภาพเรียบร้อยแล้ว!', 'data' => $newJob], JSON_UNESCAPED_UNICODE);
    exit;
}

// แอดมิน: แก้ไขข้อมูลกิจกรรม (Update Job)
if ($action === 'update_job') {
    if (!$is_admin && !isset($_SESSION['current_user'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ กรุณาเข้าสู่ระบบด้วยบัญชีแอดมิน']);
        exit;
    }
    $id = (int)($_POST['id'] ?? 0);
    $currentJob = db_get_job_by_id($id);
    if (!$id || !$currentJob) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบกิจกรรมที่ต้องการแก้ไข']);
        exit;
    }

    // ตรวจสอบว่ามีการอัปโหลดรูปภาพใหม่เพื่อเปลี่ยนรูปหรือไม่
    $image_url = $currentJob['image_url'] ?? '';
    if (isset($_FILES['job_image']) && $_FILES['job_image']['error'] === UPLOAD_ERR_OK) {
        $uploadResult = handle_job_image_upload('job_image', false);
        if ($uploadResult['success'] && !empty($uploadResult['path'])) {
            $image_url = $uploadResult['path'];
        }
    } elseif (!empty($_POST['image_url'])) {
        $image_url = trim($_POST['image_url']);
    }

    $updateData = [
        'title'         => trim($_POST['title'] ?? $currentJob['title']),
        'description'   => trim($_POST['description'] ?? $currentJob['description']),
        'category'      => trim($_POST['category'] ?? $currentJob['category']),
        'event_date'    => trim($_POST['event_date'] ?? $currentJob['event_date']),
        'event_time'    => trim($_POST['event_time'] ?? ($currentJob['event_time'] ?? '09.00-16.00 น.')),
        'location'      => trim($_POST['location'] ?? $currentJob['location']),
        'hours'         => (int)($_POST['hours'] ?? $currentJob['hours']),
        'max_quota'     => (int)($_POST['max_quota'] ?? $currentJob['max_quota']),
        'current_quota' => (int)($_POST['current_quota'] ?? $currentJob['current_quota']),
        'image_url'     => $image_url,
        'status'        => trim($_POST['status'] ?? ($currentJob['status'] ?? 'open'))
    ];

    db_update_job($id, $updateData);
    echo json_encode(['success' => true, 'message' => 'แก้ไขข้อมูลกิจกรรมจิตอาสาเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
    exit;
}

// แอดมิน: ลบกิจกรรม
if ($action === 'delete_job') {
    if (!$is_admin && !isset($_SESSION['current_user'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ กรุณาเข้าสู่ระบบด้วยบัญชีแอดมิน']);
        exit;
    }
    $id = (int)($_REQUEST['id'] ?? 0);
    if ($id <= 0) {
        echo json_encode(['success' => false, 'message' => 'รหัสกิจกรรมไม่ถูกต้อง']);
        exit;
    }
    db_delete_job($id);
    echo json_encode(['success' => true, 'message' => 'ลบกิจกรรมจิตอาสาเรียบร้อยแล้ว'], JSON_UNESCAPED_UNICODE);
    exit;
}

// แอดมิน: สลับสถานะเปิด/ปิดรับสมัคร (Toggle Status)
if ($action === 'toggle_job_status') {
    if (!$is_admin && !isset($_SESSION['current_user'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ']);
        exit;
    }
    $id = (int)($_REQUEST['id'] ?? 0);
    db_toggle_job_status($id);
    $job = db_get_job_by_id($id);
    echo json_encode(['success' => true, 'message' => 'เปลี่ยนสถานะกิจกรรมสำเร็จ', 'status' => $job['status'] ?? 'open'], JSON_UNESCAPED_UNICODE);
    exit;
}

// ----------------------------------------------------------------------------
// 2. ตาราง APPLICATION: ผู้ใช้สมัครกิจกรรม / แอดมินตอบรับ
// ----------------------------------------------------------------------------

// ผู้ใช้: สมัครเข้าร่วมกิจกรรมจิตอาสา (อนุญาตทั้งผู้ใช้ที่ล็อกอินและบุคคลทั่วไป โดยบันทึกลง Table: application เสมอ)
if ($action === 'apply_job') {
    $job_id    = (int)($_POST['activity_id'] ?? $_POST['job_id'] ?? 0);
    $job_title = trim($_POST['job_title'] ?? '');
    $fullname  = trim($_POST['fullname'] ?? ($user['fullname'] ?? ''));
    $phone     = trim($_POST['phone'] ?? ($user['phone'] ?? ''));
    $email     = trim($_POST['email'] ?? ($user['email'] ?? ''));
    $age       = (int)($_POST['age'] ?? ($user['age'] ?? 20));
    $skills    = trim($_POST['skills'] ?? '');

    // หากไม่ระบุชื่อกิจกรรม ให้ดึงจากฐานข้อมูล
    if (empty($job_title) && $job_id) {
        $job = db_get_job_by_id($job_id);
        if ($job) {
            $job_title = $job['title'];
        }
    }
    if (empty($job_title)) {
        $job_title = 'กิจกรรมจิตอาสา Ground Team';
    }

    if (empty($fullname) || empty($phone) || empty($email)) {
        echo json_encode(['success' => false, 'message' => 'กรุณากรอกชื่อ-นามสกุล เบอร์โทรศัพท์ และอีเมลให้ครบถ้วน']);
        exit;
    }

    // ตรวจสอบ user_id
    $user_id = $user['id'] ?? null;
    if (!$user_id) {
        $foundUser = db_get_user_by_username($email);
        if ($foundUser) {
            $user_id = $foundUser['id'];
        }
    }

    $application = db_create_application([
        'user_id'   => $user_id,
        'job_id'    => $job_id,
        'job_title' => $job_title,
        'fullname'  => $fullname,
        'phone'     => $phone,
        'email'     => $email,
        'age'       => $age,
        'skills'    => $skills
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'ส่งใบสมัครเข้าร่วมกิจกรรมจิตอาสาสำเร็จ! ข้อมูลถูกส่งถึงแอดมินเรียบร้อยแล้ว และรอการตอบรับ',
        'data'    => $application
    ], JSON_UNESCAPED_UNICODE);
    exit;
}

// แอดมิน: ดึงรายการผู้สมัครทั้งหมด
if ($action === 'get_applications') {
    if (!$is_admin && !isset($_SESSION['current_user'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ']);
        exit;
    }
    $status = $_GET['status'] ?? null;
    $apps = db_get_all_applications($status);
    echo json_encode(['success' => true, 'data' => $apps], JSON_UNESCAPED_UNICODE);
    exit;
}

// แอดมิน: ตอบรับการสมัคร (Approve / Reject พร้อมบันทึกข้อความ)
if ($action === 'respond_application') {
    if (!$is_admin && !isset($_SESSION['current_user'])) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ (Admin Only)']);
        exit;
    }
    $app_id      = (int)($_POST['application_id'] ?? 0);
    $status      = trim($_POST['status'] ?? 'approved'); // 'approved' หรือ 'rejected'
    $admin_notes = trim($_POST['admin_notes'] ?? '');

    if (!$app_id) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบรหัสการสมัคร']);
        exit;
    }

    if (!in_array($status, ['approved', 'rejected', 'pending'])) {
        echo json_encode(['success' => false, 'message' => 'สถานะไม่ถูกต้อง']);
        exit;
    }

    db_update_application_status($app_id, $status, $admin_notes);
    $statusText = ($status === 'approved') ? 'ตอบรับเข้าร่วมกิจกรรมเรียบร้อยแล้ว' : 'ปฏิเสธการสมัครเรียบร้อยแล้ว';
    echo json_encode(['success' => true, 'message' => $statusText], JSON_UNESCAPED_UNICODE);
    exit;
}

// ผู้ใช้: ดูประวัติการสมัครของตัวเอง
if ($action === 'get_my_applications') {
    if (!is_logged_in()) {
        http_response_code(401);
        echo json_encode(['success' => false, 'message' => 'กรุณาเข้าสู่ระบบ']);
        exit;
    }
    $myApps = db_get_applications_by_user($user['id']);
    echo json_encode(['success' => true, 'data' => $myApps], JSON_UNESCAPED_UNICODE);
    exit;
}

// ----------------------------------------------------------------------------
// 3. ตาราง CONTACT: ผู้ใช้ส่งข้อความ / แอดมินดูและตอบกลับ
// ----------------------------------------------------------------------------

// ผู้ใช้: ส่งข้อความจากหน้า contact.html (บันทึกลง Table: contact)
if ($action === 'send_contact') {
    $name    = trim($_POST['name'] ?? '');
    $email   = trim($_POST['email'] ?? '');
    $phone   = trim($_POST['phone'] ?? '');
    $subject = trim($_POST['subject'] ?? 'สอบถามทั่วไป');
    $message = trim($_POST['message'] ?? '');

    if (empty($name) || empty($email) || empty($phone) || empty($message)) {
        if (is_json_request()) {
            echo json_encode(['success' => false, 'message' => 'กรุณากรอกข้อมูลและข้อความให้ครบถ้วน'], JSON_UNESCAPED_UNICODE);
            exit;
        }
        header('Location: contact.html?error=' . urlencode('กรุณากรอกข้อมูลและข้อความให้ครบถ้วน'));
        exit;
    }

    $contact = db_create_contact([
        'name'    => $name,
        'email'   => $email,
        'phone'   => $phone,
        'subject' => $subject,
        'message' => $message
    ]);

    if (is_json_request()) {
        echo json_encode([
            'success' => true,
            'message' => 'ส่งข้อความถึงทีมงาน Ground Team เรียบร้อยแล้ว! ข้อมูลส่งถึงแอดมินแล้ว',
            'data'    => $contact
        ], JSON_UNESCAPED_UNICODE);
        exit;
    }

    header('Location: contact.html?msg=' . urlencode('ส่งข้อความถึงทีมงานเรียบร้อยแล้ว'));
    exit;
}

// แอดมิน: ดึงข้อความติดต่อทั้งหมด
if ($action === 'get_contacts') {
    if (!$is_admin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ']);
        exit;
    }
    $status = $_GET['status'] ?? null;
    $contacts = db_get_all_contacts($status);
    echo json_encode(['success' => true, 'data' => $contacts], JSON_UNESCAPED_UNICODE);
    exit;
}

// แอดมิน: ทำเครื่องหมายอ่านแล้ว หรือตอบกลับข้อความติดต่อ
if ($action === 'respond_contact') {
    if (!$is_admin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ (Admin Only)']);
        exit;
    }
    $contact_id    = (int)($_POST['contact_id'] ?? 0);
    $status        = trim($_POST['status'] ?? 'read'); // 'read' หรือ 'replied'
    $reply_message = trim($_POST['reply_message'] ?? '');

    if (!$contact_id) {
        echo json_encode(['success' => false, 'message' => 'ไม่พบข้อความติดต่อ']);
        exit;
    }

    db_update_contact_status($contact_id, $status, $reply_message);
    echo json_encode(['success' => true, 'message' => 'อัปเดตสถานะข้อความติดต่อเรียบร้อยแล้ว']);
    exit;
}

// แอดมิน: ลบข้อความติดต่อ
if ($action === 'delete_contact') {
    if (!$is_admin) {
        http_response_code(403);
        echo json_encode(['success' => false, 'message' => 'สิทธิ์ไม่เพียงพอ']);
        exit;
    }
    $id = (int)($_REQUEST['id'] ?? 0);
    db_delete_contact($id);
    echo json_encode(['success' => true, 'message' => 'ลบข้อความติดต่อเรียบร้อยแล้ว']);
    exit;
}

// ----------------------------------------------------------------------------
// Action อื่นๆ
// ----------------------------------------------------------------------------
echo json_encode(['success' => false, 'message' => 'Unknown Action: ' . htmlspecialchars($action)]);
exit;

