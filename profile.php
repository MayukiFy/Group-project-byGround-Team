<?php
require_once __DIR__ . '/auth.php';

// บังคับว่าต้องเข้าสู่ระบบก่อน จึงจะดูข้อมูลตัวเองได้
if (!is_logged_in()) {
    header('Location: login.php?error=' . urlencode('กรุณาเข้าสู่ระบบก่อนเข้าดูข้อมูลส่วนตัว'));
    exit;
}

// ข้อกำหนดที่ 4: เมื่อแอดมิน login ไม่ต้องแสดงข้อมูลส่วนตัวของแอดมิน (เปลี่ยนเส้นทางไปหน้าจัดการข้อมูลสมาชิกแทน)
if (is_admin()) {
    header('Location: information.php');
    exit;
}

$user = get_current_user_data();
$is_just_registered = isset($_GET['registered']) && $_GET['registered'] == '1';

// ดึงประวัติการสมัครกิจกรรมจิตอาสาของผู้ใช้คนนี้จาก Table 3: application
$my_applications = db_get_applications_by_user($user['id']);
$total_applied = count($my_applications);
$approved_count = 0;
$total_hours = 0;

foreach ($my_applications as $app) {
    if (($app['status'] ?? '') === 'approved') {
        $approved_count++;
        $job_info = db_get_job_by_id($app['job_id']);
        $total_hours += (int)($job_info['hours'] ?? 3);
    }
}

// กำหนด Badge ตามเพศ
$gender_badge = 'bg-secondary';
$gender_icon = 'fa-user';
if ($user['gender'] === 'ชาย') {
    $gender_badge = 'bg-primary text-white';
    $gender_icon = 'fa-mars';
} elseif ($user['gender'] === 'หญิง') {
    $gender_badge = 'bg-danger text-white';
    $gender_icon = 'fa-venus';
} elseif (strtolower($user['gender']) === 'lgbtq+') {
    $gender_badge = 'bg-warning text-dark';
    $gender_icon = 'fa-rainbow';
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข้อมูลส่วนตัวของฉัน | Ground Team</title>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .profile-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-lg);
      box-shadow: var(--shadow-lg);
      overflow: hidden;
    }
    .profile-header-banner {
      background: var(--grad-nature);
      padding: 3rem 2rem 2.5rem 2rem;
      color: #fff;
      position: relative;
    }
    .profile-avatar-circle {
      width: 90px;
      height: 90px;
      border-radius: 50%;
      background: #ffffff;
      color: var(--color-green);
      display: flex;
      align-items: center;
      justify-content: center;
      font-size: 2.8rem;
      box-shadow: var(--shadow-md);
      border: 4px solid rgba(255, 255, 255, 0.4);
    }
    .profile-info-row {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 1rem 0;
      border-bottom: 1px solid var(--border-color);
    }
    .profile-info-row:last-child {
      border-bottom: none;
    }
    .profile-label {
      color: var(--text-muted);
      font-weight: 500;
      display: flex;
      align-items: center;
      gap: 10px;
    }
    .profile-val {
      font-weight: 600;
      color: var(--text-main);
    }
  </style>
</head>
<body data-page="profile">

  <div id="preloader">
    <div class="preloader-spinner"></div>
    <div class="preloader-text">
      <i class="fa-solid fa-leaf text-success me-1"></i> กำลังโหลดข้อมูลส่วนตัว...
    </div>
  </div>

  <div id="navbar-placeholder"></div>

  <main class="py-5" style="background: var(--grad-soft-bg); min-height: 85vh;">
    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-lg-9 reveal">

          <?php if ($is_just_registered): ?>
            <!-- Alert ต้อนรับเมื่อเพิ่งสมัครสมาชิกใหม่ -->
            <div class="alert alert-success rounded-4 p-4 shadow-sm border-0 d-flex align-items-center gap-3 mb-4" 
                 style="background: var(--color-green-soft); color: var(--color-green-dark); border: 1px solid rgba(16, 185, 129, 0.3) !important;">
              <div class="fs-1 text-success"><i class="fa-solid fa-circle-check"></i></div>
              <div>
                <h4 class="fw-bold mb-1">ยินดีต้อนรับสมาชิกใหม่!</h4>
                <p class="mb-0 small">การสมัครสมาชิกเสร็จสมบูรณ์ ระบบได้บันทึกข้อมูลของคุณเรียบร้อยแล้ว และคุณสามารถสมัครเข้าร่วมโครงการจิตอาสาได้ทันที</p>
              </div>
            </div>
          <?php endif; ?>

          <!-- บัตรโปรไฟล์ของผู้ใช้ -->
          <div class="profile-card">
            <!-- ส่วนหัวแบนเนอร์ -->
            <div class="profile-header-banner">
              <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
                <div class="d-flex align-items-center gap-3">
                  <div class="profile-avatar-circle">
                    <i class="fa-solid fa-circle-user"></i>
                  </div>
                  <div>
                    <div class="d-flex align-items-center gap-2 mb-1">
                      <h2 class="fw-bold fs-3 mb-0 text-white"><?php echo htmlspecialchars($user['fullname']); ?></h2>
                      <?php if ($user['role'] === 'admin'): ?>
                        <span class="badge bg-warning text-dark"><i class="fa-solid fa-shield"></i> ผู้ดูแลระบบ</span>
                      <?php else: ?>
                        <span class="badge bg-white text-success"><i class="fa-solid fa-check"></i> สมาชิกจิตอาสา</span>
                      <?php endif; ?>
                    </div>
                    <p class="mb-0 text-white-50">
                      <i class="fa-solid fa-at me-1"></i> <?php echo htmlspecialchars($user['username']); ?> &bull; 
                      <i class="fa-solid fa-envelope ms-2 me-1"></i> <?php echo htmlspecialchars($user['email']); ?>
                    </p>
                  </div>
                </div>

                <div class="text-lg-end">
                  <a href="auth.php?action=logout" class="btn btn-outline-light rounded-pill px-4 btn-sm" onclick="return confirm('คุณต้องการออกจากระบบหรือไม่?')">
                    <i class="fa-solid fa-right-from-bracket me-1"></i> ออกจากระบบ
                  </a>
                </div>
              </div>
            </div>

            <!-- รายละเอียดข้อมูลส่วนตัวที่บันทึกไว้ -->
            <div class="p-4 p-md-5">
              
              <!-- Quick Stats สถิติการมีส่วนร่วมจริงจากระบบ -->
              <div class="row g-3 mb-5">
                <div class="col-md-4">
                  <div class="p-3 rounded-4 border text-center" style="background: var(--bg-subtle); border-color: var(--border-color) !important;">
                    <div class="text-success fs-3 mb-1"><i class="fa-solid fa-award"></i></div>
                    <div class="fw-bold fs-4 text-main"><?php echo $total_hours; ?> ชั่วโมง</div>
                    <div class="text-muted small">ชั่วโมงจิตอาสาที่ได้รับการรับรอง</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="p-3 rounded-4 border text-center" style="background: var(--bg-subtle); border-color: var(--border-color) !important;">
                    <div class="text-info fs-3 mb-1"><i class="fa-solid fa-hand-holding-heart"></i></div>
                    <div class="fw-bold fs-4 text-main"><?php echo $approved_count; ?> โครงการ</div>
                    <div class="text-muted small">ผ่านการตอบรับแล้ว (จากสมัคร <?php echo $total_applied; ?> งาน)</div>
                  </div>
                </div>
                <div class="col-md-4">
                  <div class="p-3 rounded-4 border text-center" style="background: var(--bg-subtle); border-color: var(--border-color) !important;">
                    <div class="text-warning fs-3 mb-1"><i class="fa-solid fa-certificate"></i></div>
                    <div class="fw-bold fs-4 text-main"><?php echo $approved_count; ?> ฉบับ</div>
                    <div class="text-muted small">สิทธิ์รับเกียรติบัตรสะสม</div>
                  </div>
                </div>
              </div>

              <!-- ประวัติการสมัครกิจกรรมจิตอาสาและสถานะการตอบรับ (การตอบโต้กับแอดมิน) -->
              <div class="mb-5">
                <div class="d-flex justify-content-between align-items-center mb-3 pb-2 border-bottom">
                  <h4 class="fw-bold fs-5 mb-0 text-main">
                    <i class="fa-solid fa-clipboard-check text-primary me-2"></i> สถานะการสมัครกิจกรรมจิตอาสาของฉัน (Table 3: application)
                  </h4>
                  <span class="badge bg-light text-dark border rounded-pill px-3 py-2 small">
                    สมัครทั้งหมด <?php echo $total_applied; ?> รายการ
                  </span>
                </div>

                <?php if (empty($my_applications)): ?>
                  <div class="p-4 rounded-4 text-center border" style="background: var(--bg-subtle); border-color: var(--border-color) !important;">
                    <div class="text-muted fs-2 mb-2"><i class="fa-solid fa-folder-open"></i></div>
                    <h6 class="fw-bold mb-1">ยังไม่มีประวัติการสมัครกิจกรรมจิตอาสา</h6>
                    <p class="text-muted small mb-3">คุณยังไม่ได้สมัครเข้าร่วมโครงการใด สามารถเลือกดูโครงการที่เปิดรับสมัครได้ทันที</p>
                    <a href="activities.html" class="btn-primary-gradient px-4 py-2 fs-6">
                      <i class="fa-solid fa-compass me-1"></i> เลือกดูกิจกรรมจิตอาสาตอนนี้
                    </a>
                  </div>
                <?php else: ?>
                  <div class="table-responsive rounded-4 border" style="background: var(--bg-surface); border-color: var(--border-color) !important;">
                    <table class="table align-middle mb-0">
                      <thead style="background: var(--bg-subtle);">
                        <tr>
                          <th style="width: 50px;" class="text-center">ลำดับ</th>
                          <th>โครงการจิตอาสา</th>
                          <th>วันที่สมัคร</th>
                          <th class="text-center">สถานะการตอบรับ</th>
                          <th>ข้อความจากแอดมิน</th>
                        </tr>
                      </thead>
                      <tbody>
                        <?php $n = 1; foreach ($my_applications as $app): ?>
                          <?php
                            $st = $app['status'] ?? 'pending';
                            $st_badge = 'bg-warning text-dark';
                            $st_icon = 'fa-clock';
                            $st_text = 'รอการตอบรับ';
                            if ($st === 'approved') {
                              $st_badge = 'bg-success text-white';
                              $st_icon = 'fa-check-circle';
                              $st_text = 'ตอบรับแล้ว (อนุมัติ)';
                            } elseif ($st === 'rejected') {
                              $st_badge = 'bg-danger text-white';
                              $st_icon = 'fa-circle-xmark';
                              $st_text = 'ปฏิเสธการสมัคร';
                            }
                          ?>
                          <tr>
                            <td class="text-center text-muted fw-bold"><?php echo $n++; ?></td>
                            <td>
                              <div class="fw-bold text-main"><?php echo htmlspecialchars($app['job_title']); ?></div>
                              <div class="small text-muted">ID กิจกรรม: #<?php echo $app['job_id']; ?></div>
                            </td>
                            <td class="small text-muted"><?php echo htmlspecialchars($app['applied_at'] ?? '-'); ?></td>
                            <td class="text-center">
                              <span class="badge <?php echo $st_badge; ?> rounded-pill px-3 py-2">
                                <i class="fa-solid <?php echo $st_icon; ?> me-1"></i> <?php echo $st_text; ?>
                              </span>
                            </td>
                            <td>
                              <?php if (!empty($app['admin_notes'])): ?>
                                <div class="p-2 rounded small border" style="background: var(--bg-subtle);">
                                  <i class="fa-solid fa-comment-dots text-primary me-1"></i>
                                  <strong>แอดมิน:</strong> <?php echo htmlspecialchars($app['admin_notes']); ?>
                                </div>
                              <?php else: ?>
                                <span class="text-muted small fst-italic">กำลังรอเจ้าหน้าที่ตรวจสอบ</span>
                              <?php endif; ?>
                            </td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  </div>
                <?php endif; ?>
              </div>

              <!-- ข้อมูลบัญชีและข้อมูลส่วนตัว -->
              <h4 class="fw-bold fs-5 mb-3 text-main pb-2 border-bottom">
                <i class="fa-solid fa-id-card text-success me-2"></i> ข้อมูลส่วนตัวของผู้ใช้งาน
              </h4>

              <div class="p-4 rounded-4 mb-4" style="background: var(--bg-subtle);">
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid fa-user text-success"></i> ชื่อผู้ใช้งาน (Username)</span>
                  <span class="profile-val"><?php echo htmlspecialchars($user['username']); ?></span>
                </div>
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid fa-signature text-info"></i> ชื่อ - สกุล</span>
                  <span class="profile-val"><?php echo htmlspecialchars($user['fullname']); ?></span>
                </div>
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid fa-phone text-warning"></i> เบอร์โทรศัพท์</span>
                  <span class="profile-val"><?php echo htmlspecialchars($user['phone']); ?></span>
                </div>
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid fa-envelope text-danger"></i> Email</span>
                  <span class="profile-val"><?php echo htmlspecialchars($user['email']); ?></span>
                </div>
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid fa-map-location-dot text-primary"></i> จังหวัดที่อยู่</span>
                  <span class="profile-val text-success"><?php echo htmlspecialchars($user['province']); ?></span>
                </div>
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid fa-cake-candles text-warning"></i> อายุ</span>
                  <span class="profile-val"><?php echo htmlspecialchars($user['age']); ?> ปี</span>
                </div>
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid <?php echo $gender_icon; ?> text-primary"></i> เพศ</span>
                  <span class="profile-val">
                    <span class="badge <?php echo $gender_badge; ?> px-3 py-2 rounded-pill">
                      <?php echo htmlspecialchars($user['gender']); ?>
                    </span>
                  </span>
                </div>
                <div class="profile-info-row">
                  <span class="profile-label"><i class="fa-solid fa-calendar-check text-muted"></i> วันที่ลงทะเบียน</span>
                  <span class="profile-val text-muted small"><?php echo htmlspecialchars($user['registered_at'] ?? '-'); ?></span>
                </div>
              </div>

              <!-- ปุ่มการจัดการและการนำทาง -->
              <div class="d-flex flex-wrap gap-3 justify-content-between align-items-center pt-3 border-top">
                <div class="d-flex flex-wrap gap-2">
                  <a href="activities.html" class="btn-primary-gradient px-4">
                    <i class="fa-solid fa-hand-holding-heart me-1"></i> สมัครเข้าร่วมโครงการจิตอาสา
                  </a>
                  <a href="index.html" class="btn btn-outline-secondary rounded-pill px-4">
                    <i class="fa-solid fa-house me-1"></i> กลับหน้าหลัก
                  </a>
                </div>

                <?php if ($user['role'] === 'admin'): ?>
                  <!-- ลิงก์พิเศษสำหรับแอดมิน ไปหน้า information.php -->
                  <a href="information.php" class="btn btn-warning rounded-pill px-4 fw-bold">
                    <i class="fa-solid fa-users-gear me-1"></i> ไปหน้าจัดการสมาชิก (information.php)
                  </a>
                <?php endif; ?>
              </div>

            </div>
          </div>

        </div>
      </div>
    </div>
  </main>

  <div id="footer-placeholder"></div>
  <button id="btn-scroll-top" aria-label="เลื่อนขึ้นด้านบน"><i class="fa-solid fa-arrow-up"></i></button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>

