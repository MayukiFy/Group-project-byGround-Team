<?php
// ==========================================================================
// Project: Ground Team - Volunteer & Social Impact Platform
// Page: information.php - ศูนย์ควบคุมและจัดการระบบ (Admin Dashboard)
// ครอบคลุมทั้ง 4 ตาราง:
//   1. user        : รับข้อมูลของผู้ใช้
//   2. job         : กิจกรรมจิตอาสา (แอดมินเพิ่มและแก้ไขได้)
//   3. application : รับข้อมูลที่ผู้ใช้สมัครกิจกรรม (แอดมินตอบรับได้)
//   4. contact     : รับข้อมูลในหน้า Contact ที่ผู้ใช้ส่งให้เรา
// ==========================================================================

require_once __DIR__ . '/db.php';
require_once __DIR__ . '/auth.php';

// ตรวจสอบสิทธิ์ว่าผู้ใช้เป็น Admin หรือไม่
$admin_logged_in = is_admin();
$current_user = get_current_user_data();

// ดึงข้อมูลทั้ง 4 ตาราง
$users = db_get_all_users();
$jobs = db_get_all_jobs();
$applications = db_get_all_applications();
$contacts = db_get_all_contacts();

// คำนวณสถิติภาพรวม
$total_users = count($users);
$count_male = 0;
$count_female = 0;
$count_lgbt = 0;
foreach ($users as $u) {
    $g = $u['gender'] ?? '';
    if ($g === 'ชาย') $count_male++;
    elseif ($g === 'หญิง') $count_female++;
    elseif (strtolower($g) === 'lgbtq+') $count_lgbt++;
}

$total_jobs = count($jobs);
$open_jobs = 0;
foreach ($jobs as $j) {
    if (($j['status'] ?? 'open') === 'open') $open_jobs++;
}

$total_apps = count($applications);
$pending_apps = 0;
$approved_apps = 0;
foreach ($applications as $a) {
    $st = $a['status'] ?? 'pending';
    if ($st === 'pending') $pending_apps++;
    elseif ($st === 'approved') $approved_apps++;
}

$total_contacts = count($contacts);
$unread_contacts = 0;
foreach ($contacts as $c) {
    if (($c['status'] ?? 'unread') === 'unread') $unread_contacts++;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ระบบจัดการและตอบโต้แอดมิน | Ground Team (Admin Portal)</title>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    .admin-badge {
      background: linear-gradient(135deg, #f59e0b 0%, #ef4444 100%);
      color: #fff;
      font-weight: 700;
      padding: 6px 16px;
      border-radius: var(--radius-pill);
      display: inline-flex;
      align-items: center;
      gap: 6px;
      font-size: 0.85rem;
    }
    .admin-tab-nav {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-pill);
      padding: 6px;
      display: flex;
      flex-wrap: wrap;
      gap: 6px;
    }
    .admin-tab-btn {
      flex: 1;
      min-width: 160px;
      border: none;
      background: transparent;
      padding: 10px 18px;
      border-radius: var(--radius-pill);
      color: var(--text-muted);
      font-weight: 600;
      font-size: 0.95rem;
      transition: all var(--transition-fast);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
      cursor: pointer;
    }
    .admin-tab-btn:hover {
      color: var(--text-main);
      background-color: var(--bg-subtle);
    }
    .admin-tab-btn.active {
      background: var(--grad-nature);
      color: #ffffff;
      box-shadow: 0 4px 14px rgba(16, 185, 129, 0.35);
    }
    .admin-stat-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 1.25rem;
      box-shadow: var(--shadow-sm);
      transition: all var(--transition-smooth);
    }
    .admin-stat-card:hover {
      transform: translateY(-3px);
      box-shadow: var(--shadow-md);
      border-color: var(--color-green);
    }
    .table-custom {
      background-color: var(--bg-surface);
      border-radius: var(--radius-md);
      overflow: hidden;
      border: 1px solid var(--border-color);
    }
    .table-custom th {
      background: var(--bg-subtle);
      color: var(--text-main);
      font-weight: 600;
      padding: 1rem 0.75rem;
      border-bottom: 2px solid var(--border-color);
      white-space: nowrap;
    }
    .table-custom td {
      padding: 0.9rem 0.75rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--border-color);
      color: var(--text-main);
    }
    .job-thumb {
      width: 55px;
      height: 42px;
      object-fit: cover;
      border-radius: 8px;
      border: 1px solid var(--border-color);
    }
    .badge-counter {
      font-size: 0.75rem;
      padding: 3px 8px;
      border-radius: 10px;
    }
    @media print {
      #navbar-placeholder, #footer-placeholder, #btn-scroll-top, .no-print {
        display: none !important;
      }
      body {
        background: #ffffff !important;
        color: #000000 !important;
      }
      .table-custom {
        box-shadow: none !important;
        border: 1px solid #000 !important;
      }
    }
  </style>
</head>
<body data-page="information">

  <div id="preloader">
    <div class="preloader-spinner"></div>
    <div class="preloader-text">
      <i class="fa-solid fa-leaf text-success me-1"></i> กำลังโหลดระบบจัดการข้อมูลแอดมิน...
    </div>
  </div>

  <div id="navbar-placeholder"></div>

  <main class="py-5" style="background: var(--grad-soft-bg); min-height: 85vh;">
    <div class="container py-3">

      <?php if ($admin_logged_in): ?>
        <!-- ===================================================================
             เข้าสู่ระบบในฐานะ Admin (แสดง Dashboard จัดการ 4 ตาราง)
             =================================================================== -->
        <div class="reveal">
          <!-- Header Dashboard -->
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
              <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <div class="admin-badge">
                  <i class="fa-solid fa-shield-halved"></i> พื้นที่ผู้ดูแลระบบ (Admin Control Center)
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 shadow-sm">
                  <i class="fa-solid fa-database text-success me-1"></i> Database: <strong class="text-success">groundteam</strong> &bull; 4 Tables
                </span>
              </div>
              <h1 class="section-title mb-1">
                ศูนย์จัดการและตอบโต้ข้อมูล <span class="gradient-text-nature">Ground Team</span>
              </h1>
              <p class="text-muted small mb-0">
                ระบบจัดการผู้ใช้ (user), จัดการและเพิ่มแก้ไขกิจกรรม (job), ตอบรับการสมัคร (application), และกล่องข้อความ (contact)
              </p>
            </div>

            <div class="d-flex gap-2 no-print">
              <button class="btn btn-outline-custom rounded-pill px-3" onclick="window.print()">
                <i class="fa-solid fa-print me-1"></i> พิมพ์รายงาน
              </button>
              <a href="auth.php?action=logout" class="btn btn-outline-danger rounded-pill px-3" onclick="return confirm('ต้องการออกจากระบบแอดมินหรือไม่?')">
                <i class="fa-solid fa-right-from-bracket me-1"></i> ออกจากระบบแอดมิน
              </a>
            </div>
          </div>

          <!-- Quick Stats 4 Cards -->
          <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center" role="button" onclick="switchAdminTab('user')">
                <div class="text-success fs-3 mb-1"><i class="fa-solid fa-users"></i></div>
                <div class="fw-bold fs-3 text-main"><?php echo $total_users; ?></div>
                <div class="text-muted small">สมาชิกทั้งหมด (user)</div>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center" role="button" onclick="switchAdminTab('job')">
                <div class="text-info fs-3 mb-1"><i class="fa-solid fa-list-check"></i></div>
                <div class="fw-bold fs-3 text-main"><?php echo $total_jobs; ?></div>
                <div class="text-muted small">กิจกรรมจิตอาสา (job)</div>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center" role="button" onclick="switchAdminTab('application')">
                <div class="text-warning fs-3 mb-1 position-relative d-inline-block">
                  <i class="fa-solid fa-envelope-open-text"></i>
                  <?php if ($pending_apps > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                  <?php endif; ?>
                </div>
                <div class="fw-bold fs-3 text-main"><?php echo $total_apps; ?></div>
                <div class="text-muted small">ใบสมัครจิตอาสา (application)</div>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center" role="button" onclick="switchAdminTab('contact')">
                <div class="text-danger fs-3 mb-1 position-relative d-inline-block">
                  <i class="fa-solid fa-comments"></i>
                  <?php if ($unread_contacts > 0): ?>
                    <span class="position-absolute top-0 start-100 translate-middle p-1 bg-danger border border-light rounded-circle"></span>
                  <?php endif; ?>
                </div>
                <div class="fw-bold fs-3 text-main"><?php echo $total_contacts; ?></div>
                <div class="text-muted small">ข้อความติดต่อ (contact)</div>
              </div>
            </div>
          </div>

          <!-- Tab Navigation Controls (4 ตารางหลัก) -->
          <div class="mb-4 no-print">
            <div class="admin-tab-nav">
              <button class="admin-tab-btn active" id="tabBtn-user" onclick="switchAdminTab('user')">
                <i class="fa-solid fa-users"></i> 1. ผู้ใช้งาน (user)
                <span class="badge bg-white text-dark ms-1"><?php echo $total_users; ?></span>
              </button>
              <button class="admin-tab-btn" id="tabBtn-job" onclick="switchAdminTab('job')">
                <i class="fa-solid fa-hand-holding-heart"></i> 2. จัดการกิจกรรม (job)
                <span class="badge bg-white text-dark ms-1"><?php echo $total_jobs; ?></span>
              </button>
              <button class="admin-tab-btn" id="tabBtn-application" onclick="switchAdminTab('application')">
                <i class="fa-solid fa-clipboard-check"></i> 3. ตอบรับการสมัคร (application)
                <?php if ($pending_apps > 0): ?>
                  <span class="badge bg-danger ms-1"><?php echo $pending_apps; ?> รอตอบรับ</span>
                <?php else: ?>
                  <span class="badge bg-white text-dark ms-1"><?php echo $total_apps; ?></span>
                <?php endif; ?>
              </button>
              <button class="admin-tab-btn" id="tabBtn-contact" onclick="switchAdminTab('contact')">
                <i class="fa-solid fa-envelope"></i> 4. ข้อความติดต่อ (contact)
                <?php if ($unread_contacts > 0): ?>
                  <span class="badge bg-danger ms-1"><?php echo $unread_contacts; ?> ใหม่</span>
                <?php else: ?>
                  <span class="badge bg-white text-dark ms-1"><?php echo $total_contacts; ?></span>
                <?php endif; ?>
              </button>
            </div>
          </div>

          <!-- ===================================================================
               TAB 1: USER (รับข้อมูลของผู้ใช้)
               =================================================================== -->
          <div class="admin-tab-pane" id="pane-user">
            <!-- Filter & Search Users -->
            <div class="card border-0 rounded-4 p-3 shadow-sm mb-4 no-print" style="background-color: var(--bg-surface); border: 1px solid var(--border-color) !important;">
              <div class="row g-2 align-items-center">
                <div class="col-md-6">
                  <div class="input-group">
                    <span class="input-group-text bg-transparent border-0 text-muted ps-2"><i class="fa-solid fa-magnifying-glass"></i></span>
                    <input type="text" id="adminSearchInput" class="form-control-custom border-start-0" placeholder="ค้นหาตามชื่อ, username, เบอร์โทร, email หรือจังหวัด...">
                  </div>
                </div>
                <div class="col-md-3 col-6">
                  <select id="genderFilter" class="form-select-custom">
                    <option value="">เพศทั้งหมด</option>
                    <option value="ชาย">ชาย (<?php echo $count_male; ?>)</option>
                    <option value="หญิง">หญิง (<?php echo $count_female; ?>)</option>
                    <option value="lgbtq+">lgbtq+ (<?php echo $count_lgbt; ?>)</option>
                  </select>
                </div>
                <div class="col-md-3 col-6 text-end">
                  <span class="text-muted small">แสดง: <strong id="memberCountDisplay"><?php echo $total_users; ?></strong> คน</span>
                </div>
              </div>
            </div>

            <!-- Users Table -->
            <div class="table-responsive table-custom shadow-sm mb-4">
              <table class="table table-hover mb-0" id="membersTable">
                <thead>
                  <tr>
                    <th class="text-center" style="width: 50px;">ลำดับ</th>
                    <th>Username</th>
                    <th>ชื่อ - สกุล</th>
                    <th>เบอร์โทรศัพท์</th>
                    <th>Email</th>
                    <th>จังหวัดที่อยู่</th>
                    <th class="text-center">อายุ</th>
                    <th class="text-center">เพศ</th>
                    <th>วันที่สมัคร</th>
                    <th class="text-center no-print">จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php $idx = 1; foreach ($users as $u): ?>
                    <?php 
                      $g = $u['gender'] ?? 'ไม่ระบุ';
                      $badge = 'bg-secondary';
                      if ($g === 'ชาย') $badge = 'bg-primary';
                      elseif ($g === 'หญิง') $badge = 'bg-danger';
                      elseif (strtolower($g) === 'lgbtq+') $badge = 'bg-warning text-dark';
                      $is_admin_user = ($u['username'] === 'admin');
                    ?>
                    <tr class="member-row" 
                        data-fullname="<?php echo htmlspecialchars($u['fullname']); ?>" 
                        data-username="<?php echo htmlspecialchars($u['username']); ?>"
                        data-phone="<?php echo htmlspecialchars($u['phone']); ?>"
                        data-email="<?php echo htmlspecialchars($u['email']); ?>"
                        data-province="<?php echo htmlspecialchars($u['province']); ?>"
                        data-gender="<?php echo htmlspecialchars($g); ?>">
                      <td class="text-center text-muted fw-bold"><?php echo $idx++; ?></td>
                      <td>
                        <span class="fw-bold text-main font-monospace"><?php echo htmlspecialchars($u['username']); ?></span>
                        <?php if ($is_admin_user): ?>
                          <span class="badge bg-warning text-dark ms-1">Admin</span>
                        <?php endif; ?>
                      </td>
                      <td class="fw-semibold text-main"><?php echo htmlspecialchars($u['fullname']); ?></td>
                      <td><a href="tel:<?php echo htmlspecialchars($u['phone']); ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-phone text-success small me-1"></i><?php echo htmlspecialchars($u['phone']); ?></a></td>
                      <td><a href="mailto:<?php echo htmlspecialchars($u['email']); ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-envelope text-info small me-1"></i><?php echo htmlspecialchars($u['email']); ?></a></td>
                      <td><span class="badge rounded-pill bg-light text-dark border px-2 py-1"><i class="fa-solid fa-location-dot text-danger me-1"></i><?php echo htmlspecialchars($u['province']); ?></span></td>
                      <td class="text-center fw-bold"><?php echo htmlspecialchars($u['age'] ?? '-'); ?> ปี</td>
                      <td class="text-center"><span class="badge <?php echo $badge; ?> rounded-pill px-2 py-1"><?php echo htmlspecialchars($g); ?></span></td>
                      <td class="text-muted small"><?php echo htmlspecialchars($u['created_at'] ?? $u['registered_at'] ?? '-'); ?></td>
                      <td class="text-center no-print">
                        <?php if (!$is_admin_user): ?>
                          <button type="button" class="btn btn-sm btn-outline-danger rounded-circle" onclick="deleteUser(<?php echo $u['id']; ?>, '<?php echo htmlspecialchars($u['fullname']); ?>')" title="ลบข้อมูล">
                            <i class="fa-solid fa-trash-can"></i>
                          </button>
                        <?php else: ?>
                          <span class="text-muted small fst-italic">แอดมินหลัก</span>
                        <?php endif; ?>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ===================================================================
               TAB 2: JOB (กิจกรรมจิตอาสา - แอดมินเพิ่มและแก้ไขได้)
               =================================================================== -->
          <div class="admin-tab-pane" id="pane-job" style="display: none;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
              <div>
                <h3 class="fw-bold fs-4 mb-1 text-main">จัดการกิจกรรมจิตอาสา</h3>
                <p class="text-muted small mb-0">แอดมินสามารถเพิ่มกิจกรรมใหม่ แก้ไขรายละเอียด วันเวลา สถานที่ และสลับสถานะเปิด-ปิดรับสมัครได้</p>
              </div>
              <button class="btn btn-primary-gradient px-4 py-2" data-bs-toggle="modal" data-bs-target="#addJobModal">
                <i class="fa-solid fa-plus-circle me-1"></i> เพิ่มกิจกรรมจิตอาสาใหม่
              </button>
            </div>

            <!-- Table of Jobs -->
            <div class="table-responsive table-custom shadow-sm mb-4">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th style="width: 50px;" class="text-center">ID</th>
                    <th>ภาพ</th>
                    <th>ชื่อโครงการจิตอาสา</th>
                    <th>หมวดหมู่</th>
                    <th>วันที่ / เวลา</th>
                    <th>สถานที่</th>
                    <th class="text-center">ชั่วโมง</th>
                    <th class="text-center">รับแล้ว/เป้าหมาย</th>
                    <th class="text-center">สถานะ</th>
                    <th class="text-center no-print" style="width: 170px;">จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php foreach ($jobs as $j): ?>
                    <tr>
                      <td class="text-center fw-bold text-muted"><?php echo $j['id']; ?></td>
                      <td>
                        <img src="<?php echo htmlspecialchars($j['image_url'] ?? 'assets/images/กระดาษทำมือ.jpeg'); ?>" class="job-thumb" alt="thumb" onerror="this.src='assets/images/กระดาษทำมือ.jpeg'">
                      </td>
                      <td>
                        <div class="fw-bold text-main"><?php echo htmlspecialchars($j['title']); ?></div>
                        <div class="small text-muted text-truncate" style="max-width: 280px;"><?php echo htmlspecialchars($j['description']); ?></div>
                      </td>
                      <td>
                        <span class="badge bg-light text-dark border px-2 py-1 small"><?php echo htmlspecialchars($j['category']); ?></span>
                      </td>
                      <td class="small">
                        <div><i class="fa-regular fa-calendar text-success me-1"></i><?php echo htmlspecialchars($j['event_date']); ?></div>
                        <div class="text-muted"><i class="fa-regular fa-clock me-1"></i><?php echo htmlspecialchars($j['event_time'] ?? '-'); ?></div>
                      </td>
                      <td class="small"><i class="fa-solid fa-location-dot text-danger me-1"></i><?php echo htmlspecialchars($j['location']); ?></td>
                      <td class="text-center fw-bold text-warning"><?php echo $j['hours']; ?> ชม.</td>
                      <td class="text-center small">
                        <strong class="text-success"><?php echo $j['current_quota']; ?></strong> / <?php echo $j['max_quota']; ?> คน
                      </td>
                      <td class="text-center">
                        <?php if (($j['status'] ?? 'open') === 'open'): ?>
                          <span class="badge bg-success rounded-pill px-3 py-1">เปิดรับ</span>
                        <?php else: ?>
                          <span class="badge bg-secondary rounded-pill px-3 py-1">ปิดรับ</span>
                        <?php endif; ?>
                      </td>
                      <td class="text-center no-print">
                        <div class="d-flex justify-content-center gap-1">
                          <!-- ปุ่มแก้ไข -->
                          <button class="btn btn-sm btn-outline-primary rounded-circle" title="แก้ไขข้อมูลกิจกรรม" data-job="<?php echo htmlspecialchars(json_encode($j, JSON_UNESCAPED_UNICODE), ENT_QUOTES, 'UTF-8'); ?>" onclick="openEditJobModalFromBtn(this)">
                            <i class="fa-solid fa-pen-to-square"></i>
                          </button>
                          <!-- สลับสถานะเปิดปิด -->
                          <button class="btn btn-sm btn-outline-warning rounded-circle" title="สลับสถานะเปิด/ปิดรับสมัคร" onclick="toggleJobStatus(<?php echo (int)$j['id']; ?>)">
                            <i class="fa-solid fa-power-off"></i>
                          </button>
                          <!-- ลบกิจกรรม -->
                          <button class="btn btn-sm btn-outline-danger rounded-circle" title="ลบกิจกรรม" data-job-id="<?php echo (int)$j['id']; ?>" data-job-title="<?php echo htmlspecialchars($j['title'], ENT_QUOTES, 'UTF-8'); ?>" onclick="deleteJobSafe(this)">
                            <i class="fa-solid fa-trash-can"></i>
                          </button>
                        </div>
                      </td>
                    </tr>
                  <?php endforeach; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ===================================================================
               TAB 3: APPLICATION (ตอบรับการสมัครจิตอาสา)
               =================================================================== -->
          <div class="admin-tab-pane" id="pane-application" style="display: none;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
              <div>
                <h3 class="fw-bold fs-4 mb-1 text-main">รายการสมัครเข้าร่วมกิจกรรมจิตอาสา</h3>
                <p class="text-muted small mb-0">แอดมินสามารถตรวจสอบคุณสมบัติของผู้สมัคร และกด "ตอบรับ (Approve)" หรือ "ปฏิเสธ (Reject)" พร้อมระบุข้อความตอบกลับได้</p>
              </div>
              <div class="d-flex gap-2">
                <button class="btn btn-sm btn-outline-secondary rounded-pill px-3 active" onclick="filterApplicationTable('all', this)">ทั้งหมด (<?php echo $total_apps; ?>)</button>
                <button class="btn btn-sm btn-outline-warning rounded-pill px-3" onclick="filterApplicationTable('pending', this)">รอการตอบรับ (<?php echo $pending_apps; ?>)</button>
                <button class="btn btn-sm btn-outline-success rounded-pill px-3" onclick="filterApplicationTable('approved', this)">ตอบรับแล้ว (<?php echo $approved_apps; ?>)</button>
              </div>
            </div>

            <!-- Table of Applications -->
            <div class="table-responsive table-custom shadow-sm mb-4">
              <table class="table table-hover mb-0" id="applicationsTable">
                <thead>
                  <tr>
                    <th style="width: 50px;" class="text-center">ID</th>
                    <th>ผู้สมัคร (User)</th>
                    <th>เบอร์โทร & อีเมล</th>
                    <th>โครงการจิตอาสาที่สมัคร</th>
                    <th>ทักษะ/ความสามารถ</th>
                    <th>วันที่สมัคร</th>
                    <th class="text-center">สถานะ</th>
                    <th>บันทึกจากแอดมิน</th>
                    <th class="text-center no-print" style="width: 140px;">ตอบรับ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($applications)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">ยังไม่มีรายการสมัครกิจกรรม</td></tr>
                  <?php else: ?>
                    <?php foreach ($applications as $app): ?>
                      <?php
                        $st = $app['status'] ?? 'pending';
                        $st_badge = 'bg-warning text-dark';
                        $st_text = 'รอการตอบรับ';
                        if ($st === 'approved') {
                          $st_badge = 'bg-success text-white';
                          $st_text = 'ตอบรับแล้ว';
                        } elseif ($st === 'rejected') {
                          $st_badge = 'bg-danger text-white';
                          $st_text = 'ปฏิเสธ';
                        }
                      ?>
                      <tr class="app-row" data-status="<?php echo $st; ?>">
                        <td class="text-center fw-bold text-muted">#<?php echo $app['id']; ?></td>
                        <td>
                          <div class="fw-bold text-main"><?php echo htmlspecialchars($app['fullname']); ?></div>
                          <div class="small text-muted">อายุ: <?php echo htmlspecialchars($app['age']); ?> ปี (User ID: <?php echo $app['user_id'] ?? '-'; ?>)</div>
                        </td>
                        <td class="small">
                          <div><a href="tel:<?php echo htmlspecialchars($app['phone']); ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-phone text-success me-1"></i><?php echo htmlspecialchars($app['phone']); ?></a></div>
                          <div><a href="mailto:<?php echo htmlspecialchars($app['email']); ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-envelope text-info me-1"></i><?php echo htmlspecialchars($app['email']); ?></a></div>
                        </td>
                        <td>
                          <div class="fw-bold text-main"><?php echo htmlspecialchars($app['job_title']); ?></div>
                          <div class="small text-muted">Job ID: #<?php echo $app['job_id']; ?></div>
                        </td>
                        <td class="small" style="max-width: 200px;">
                          <?php echo !empty($app['skills']) ? htmlspecialchars($app['skills']) : '<span class="text-muted fst-italic">- ไม่ระบุ -</span>'; ?>
                        </td>
                        <td class="small text-muted"><?php echo htmlspecialchars($app['applied_at'] ?? '-'); ?></td>
                        <td class="text-center">
                          <span class="badge <?php echo $st_badge; ?> rounded-pill px-3 py-1"><?php echo $st_text; ?></span>
                        </td>
                        <td class="small" style="max-width: 220px;">
                          <?php if (!empty($app['admin_notes'])): ?>
                            <span class="text-primary fw-medium"><?php echo htmlspecialchars($app['admin_notes']); ?></span>
                          <?php else: ?>
                            <span class="text-muted fst-italic">ยังไม่มีบันทึก</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center no-print">
                          <button class="btn btn-sm btn-primary-gradient px-3 py-1" onclick="openRespondAppModal(<?php echo $app['id']; ?>, '<?php echo htmlspecialchars($app['fullname']); ?>', '<?php echo htmlspecialchars($app['job_title']); ?>', '<?php echo $st; ?>', '<?php echo htmlspecialchars($app['admin_notes'] ?? ''); ?>')">
                            <i class="fa-solid fa-reply me-1"></i> ตอบรับ
                          </button>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <!-- ===================================================================
               TAB 4: CONTACT (รับข้อมูลในหน้า Contact ที่ผู้ใช้ส่งให้เรา)
               =================================================================== -->
          <div class="admin-tab-pane" id="pane-contact" style="display: none;">
            <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
              <div>
                <h3 class="fw-bold fs-4 mb-1 text-main">กล่องข้อความติดต่อจากผู้ใช้</h3>
                <p class="text-muted small mb-0">ข้อความทั้งหมดที่ส่งมาจากแบบฟอร์มหน้า Contact ทีมงานสามารถอ่าน ทำเครื่องหมาย และบันทึกข้อความตอบกลับได้</p>
              </div>
              <span class="badge bg-light text-dark border rounded-pill px-3 py-2">
                ข้อความทั้งหมด <?php echo $total_contacts; ?> รายการ (ยังไม่อ่าน <?php echo $unread_contacts; ?>)
              </span>
            </div>

            <!-- Table of Contact Messages -->
            <div class="table-responsive table-custom shadow-sm mb-4">
              <table class="table table-hover mb-0">
                <thead>
                  <tr>
                    <th style="width: 50px;" class="text-center">ID</th>
                    <th>ผู้ส่งข้อความ</th>
                    <th>เบอร์โทร & Email</th>
                    <th>หัวข้อที่ติดต่อ</th>
                    <th>ข้อความรายละเอียด</th>
                    <th>วันที่ส่ง</th>
                    <th class="text-center">สถานะ</th>
                    <th>บันทึกการตอบกลับ</th>
                    <th class="text-center no-print" style="width: 140px;">จัดการ</th>
                  </tr>
                </thead>
                <tbody>
                  <?php if (empty($contacts)): ?>
                    <tr><td colspan="9" class="text-center py-4 text-muted">ยังไม่มีข้อความติดต่อเข้ามา</td></tr>
                  <?php else: ?>
                    <?php foreach ($contacts as $msg): ?>
                      <?php
                        $c_st = $msg['status'] ?? 'unread';
                        $c_badge = 'bg-danger text-white';
                        $c_text = 'ยังไม่อ่าน';
                        if ($c_st === 'read') {
                          $c_badge = 'bg-info text-dark';
                          $c_text = 'อ่านแล้ว';
                        } elseif ($c_st === 'replied') {
                          $c_badge = 'bg-success text-white';
                          $c_text = 'ตอบกลับแล้ว';
                        }
                      ?>
                      <tr>
                        <td class="text-center fw-bold text-muted">#<?php echo $msg['id']; ?></td>
                        <td class="fw-semibold text-main"><?php echo htmlspecialchars($msg['name']); ?></td>
                        <td class="small">
                          <div><a href="tel:<?php echo htmlspecialchars($msg['phone']); ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-phone text-success me-1"></i><?php echo htmlspecialchars($msg['phone']); ?></a></div>
                          <div><a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" class="text-decoration-none text-muted"><i class="fa-solid fa-envelope text-info me-1"></i><?php echo htmlspecialchars($msg['email']); ?></a></div>
                        </td>
                        <td><span class="badge bg-light text-dark border px-2 py-1"><?php echo htmlspecialchars($msg['subject']); ?></span></td>
                        <td class="small" style="max-width: 250px;">
                          <?php echo nl2br(htmlspecialchars($msg['message'])); ?>
                        </td>
                        <td class="small text-muted"><?php echo htmlspecialchars($msg['created_at'] ?? '-'); ?></td>
                        <td class="text-center">
                          <span class="badge <?php echo $c_badge; ?> rounded-pill px-3 py-1"><?php echo $c_text; ?></span>
                        </td>
                        <td class="small" style="max-width: 200px;">
                          <?php if (!empty($msg['reply_message'])): ?>
                            <span class="text-success fw-medium"><i class="fa-solid fa-check me-1"></i><?php echo htmlspecialchars($msg['reply_message']); ?></span>
                          <?php else: ?>
                            <span class="text-muted fst-italic">ยังไม่ตอบกลับ</span>
                          <?php endif; ?>
                        </td>
                        <td class="text-center no-print">
                          <div class="d-flex justify-content-center gap-1">
                            <button class="btn btn-sm btn-outline-primary rounded-circle" title="บันทึกการตอบกลับ" onclick="openReplyContactModal(<?php echo $msg['id']; ?>, '<?php echo htmlspecialchars($msg['name']); ?>', '<?php echo $c_st; ?>', '<?php echo htmlspecialchars($msg['reply_message'] ?? ''); ?>')">
                              <i class="fa-solid fa-reply"></i>
                            </button>
                            <button class="btn btn-sm btn-outline-danger rounded-circle" title="ลบข้อความ" onclick="deleteContactMessage(<?php echo $msg['id']; ?>)">
                              <i class="fa-solid fa-trash-can"></i>
                            </button>
                          </div>
                        </td>
                      </tr>
                    <?php endforeach; ?>
                  <?php endif; ?>
                </tbody>
              </table>
            </div>
          </div>

          <div class="text-end no-print mt-4">
            <a href="index.html" class="btn btn-outline-secondary rounded-pill px-4">
              <i class="fa-solid fa-house me-1"></i> กลับหน้าหลัก
            </a>
          </div>
        </div>

        <!-- ===================================================================
             MODALS: ฟอร์มจัดการระบบสำหรับแอดมิน
             =================================================================== -->

        <!-- Modal 1: เพิ่มกิจกรรมจิตอาสาใหม่ (Add Job) -->
        <div class="modal fade" id="addJobModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-custom">
              <div class="modal-header modal-header-gradient">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-plus-circle me-2"></i> เพิ่มกิจกรรมจิตอาสาใหม่</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body p-4">
                <form id="addJobForm" onsubmit="submitCreateJob(event)">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label-custom">ชื่อโครงการจิตอาสา <span class="text-danger">*</span></label>
                      <input type="text" name="title" class="form-control-custom" placeholder="เช่น จิตอาสาปลูกป่าชายเลนเพื่อระบบนิเวศ" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">หมวดหมู่ <span class="text-danger">*</span></label>
                      <select name="category" class="form-select-custom" required>
                        <option value="recruiting,nature">ธรรมชาติ & สิ่งแวดล้อม (nature)</option>
                        <option value="recruiting,edu">การศึกษา & เยาวชน (edu)</option>
                        <option value="recruiting,food">อาหาร & โภชนาการ (food)</option>
                        <option value="recruiting,donate">บริจาค & ช่วยเหลือ (donate)</option>
                        <option value="recruiting,artificial">งานประดิษฐ์ & แฮนด์เมด (artificial)</option>
                      </select>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">สถานที่จัดกิจกรรม / จังหวัด <span class="text-danger">*</span></label>
                      <input type="text" name="location" class="form-control-custom" placeholder="เช่น จ.มหาสารคาม" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">วันที่จัดกิจกรรม <span class="text-danger">*</span></label>
                      <input type="text" name="event_date" class="form-control-custom" placeholder="เช่น 25 พ.ย. พ.ศ.2569" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">เวลาจัดกิจกรรม</label>
                      <input type="text" name="event_time" class="form-control-custom" placeholder="เช่น 09.00-16.00 น." value="09.00-16.00 น.">
                    </div>
                    <div class="col-md-4">
                      <label class="form-label-custom">ชั่วโมงจิตอาสาที่ได้รับ (ชม.) <span class="text-danger">*</span></label>
                      <input type="number" name="hours" class="form-control-custom" min="1" max="50" value="4" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label-custom">จำนวนรับสมัคร (คน) <span class="text-danger">*</span></label>
                      <input type="number" name="max_quota" class="form-control-custom" min="1" max="500" value="30" required>
                    </div>
                    <div class="col-md-4">
                      <label class="form-label-custom">สถานะเปิดรับสมัคร <span class="text-danger">*</span></label>
                      <select name="status" class="form-select-custom" required>
                        <option value="open" selected>เปิดรับสมัคร (open)</option>
                        <option value="closed">ปิดรับสมัคร (closed)</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom">
                        <i class="fa-solid fa-cloud-arrow-up text-success me-1"></i> อัปโหลดรูปภาพกิจกรรมใหม่ 
                        <span class="text-danger fw-bold">* (บังคับอัปโหลดรูปภาพใหม่เท่านั้น)</span>
                      </label>
                      <div class="p-3 border rounded-3" style="background-color: var(--bg-subtle); border-color: var(--border-color) !important;">
                        <input type="file" name="job_image" id="addJobImageInput" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="form-control-custom mb-2" required onchange="previewAddJobImage(this)">
                        <div class="d-flex align-items-center gap-2 text-muted small">
                          <i class="fa-solid fa-circle-info text-primary"></i>
                          <span>รองรับไฟล์ JPG, JPEG, PNG, WEBP ขนาดไม่เกิน 10MB</span>
                        </div>
                        <!-- Live Image Preview Container -->
                        <div id="addJobImagePreviewWrap" class="mt-2 p-2 border rounded-3 text-center" style="display: none; background: var(--bg-surface);">
                          <p class="small text-muted mb-2"><i class="fa-solid fa-eye me-1"></i> ตัวอย่างรูปภาพที่จะอัปโหลด:</p>
                          <img id="addJobImagePreview" src="" alt="preview" style="max-height: 200px; max-width: 100%; border-radius: 8px; object-fit: cover; box-shadow: var(--shadow-sm);">
                          <div id="addJobImageFileName" class="small fw-semibold text-success mt-2"></div>
                        </div>
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom">รายละเอียดกิจกรรม <span class="text-danger">*</span></label>
                      <textarea name="description" class="form-control-custom" rows="3" placeholder="ระบุรายละเอียดของกิจกรรม สิ่งที่ต้องเตรียม และประโยชน์ที่ได้รับ..." required></textarea>
                    </div>
                  </div>
                  <div class="text-end mt-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary-gradient px-4">
                      <i class="fa-solid fa-save me-1"></i> บันทึกกิจกรรมใหม่
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal 2: แก้ไขกิจกรรมจิตอาสา (Edit Job) -->
        <div class="modal fade" id="editJobModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-lg modal-dialog-centered">
            <div class="modal-content modal-content-custom">
              <div class="modal-header modal-header-gradient">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-pen-to-square me-2"></i> แก้ไขข้อมูลกิจกรรมจิตอาสา</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body p-4">
                <form id="editJobForm" onsubmit="submitUpdateJob(event)">
                  <input type="hidden" name="id" id="editJobId">
                  <div class="row g-3">
                    <div class="col-12">
                      <label class="form-label-custom">ชื่อโครงการจิตอาสา <span class="text-danger">*</span></label>
                      <input type="text" name="title" id="editJobTitle" class="form-control-custom" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">หมวดหมู่ <span class="text-danger">*</span></label>
                      <input type="text" name="category" id="editJobCategory" class="form-control-custom" placeholder="เช่น recruiting,nature" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">สถานที่จัดกิจกรรม / จังหวัด <span class="text-danger">*</span></label>
                      <input type="text" name="location" id="editJobLocation" class="form-control-custom" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">วันที่จัดกิจกรรม <span class="text-danger">*</span></label>
                      <input type="text" name="event_date" id="editJobEventDate" class="form-control-custom" required>
                    </div>
                    <div class="col-md-6">
                      <label class="form-label-custom">เวลาจัดกิจกรรม</label>
                      <input type="text" name="event_time" id="editJobEventTime" class="form-control-custom">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label-custom">ชั่วโมงจิตอาสา (ชม.)</label>
                      <input type="number" name="hours" id="editJobHours" class="form-control-custom" min="1" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label-custom">จำนวนรับทั้งหมด (คน)</label>
                      <input type="number" name="max_quota" id="editJobMaxQuota" class="form-control-custom" min="1" required>
                    </div>
                    <div class="col-md-3">
                      <label class="form-label-custom">สมัครแล้ว (คน)</label>
                      <input type="number" name="current_quota" id="editJobCurrentQuota" class="form-control-custom" min="0">
                    </div>
                    <div class="col-md-3">
                      <label class="form-label-custom">สถานะเปิดรับสมัคร</label>
                      <select name="status" id="editJobStatus" class="form-select-custom">
                        <option value="open">เปิดรับ (open)</option>
                        <option value="closed">ปิดรับ (closed)</option>
                      </select>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom">รูปภาพประกอบกิจกรรม</label>
                      <div class="p-3 border rounded-3" style="background-color: var(--bg-subtle); border-color: var(--border-color) !important;">
                        <div class="d-flex align-items-center gap-3 mb-2">
                          <img id="editJobImagePreviewCurrent" src="assets/images/กระดาษทำมือ.jpeg" alt="current image" style="width: 80px; height: 60px; object-fit: cover; border-radius: 8px; border: 1px solid var(--border-color);">
                          <div>
                            <div class="small fw-bold text-main">รูปภาพปัจจุบัน</div>
                            <input type="text" name="image_url" id="editJobImageUrl" class="form-control-custom form-control-sm" readonly style="font-size: 0.8rem; background: var(--bg-surface);">
                          </div>
                        </div>
                        <label class="form-label-custom small mb-1"><i class="fa-solid fa-upload text-info me-1"></i> อัปโหลดรูปภาพใหม่เพื่อเปลี่ยนรูปเดิม (เว้นว่างไว้หากใช้รูปเดิม):</label>
                        <input type="file" name="job_image" id="editJobImageInput" accept="image/png, image/jpeg, image/jpg, image/webp, image/gif" class="form-control-custom form-control-sm" onchange="previewEditJobImage(this)">
                      </div>
                    </div>
                    <div class="col-12">
                      <label class="form-label-custom">รายละเอียดกิจกรรม <span class="text-danger">*</span></label>
                      <textarea name="description" id="editJobDesc" class="form-control-custom" rows="3" required></textarea>
                    </div>
                  </div>
                  <div class="text-end mt-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary-gradient px-4">
                      <i class="fa-solid fa-save me-1"></i> บันทึกการแก้ไข
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal 3: ตอบรับการสมัครจิตอาสา (Respond Application) -->
        <div class="modal fade" id="respondAppModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
              <div class="modal-header modal-header-gradient">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-reply me-2"></i> ตอบรับการสมัครจิตอาสา</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body p-4">
                <form id="respondAppForm" onsubmit="submitRespondApp(event)">
                  <input type="hidden" name="application_id" id="respondAppId">
                  <div class="mb-3">
                    <label class="form-label-custom">ผู้สมัคร</label>
                    <input type="text" id="respondAppUserName" class="form-control-custom" readonly style="background: var(--bg-subtle);">
                  </div>
                  <div class="mb-3">
                    <label class="form-label-custom">โครงการที่สมัคร</label>
                    <input type="text" id="respondAppJobTitle" class="form-control-custom" readonly style="background: var(--bg-subtle);">
                  </div>
                  <div class="mb-3">
                    <label class="form-label-custom">สถานะการตอบรับ <span class="text-danger">*</span></label>
                    <select name="status" id="respondAppStatus" class="form-select-custom" required>
                      <option value="approved">🟢 ตอบรับแล้ว / อนุมัติการเข้าร่วม (Approved)</option>
                      <option value="rejected">🔴 ปฏิเสธการสมัคร (Rejected)</option>
                      <option value="pending">🟡 รอการตอบรับ (Pending)</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label-custom">ข้อความตอบกลับหรือคำแนะนำถึงผู้สมัคร (Admin Notes)</label>
                    <textarea name="admin_notes" id="respondAppNotes" class="form-control-custom" rows="3" placeholder="เช่น ยินดีต้อนรับครับ กรุณาเตรียมตัวมาร่วมกิจกรรมตามวันเวลาที่กำหนด หรือแจ้งข้อมูลจุดนัดพบ..."></textarea>
                  </div>
                  <div class="text-end mt-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary-gradient px-4">
                      <i class="fa-solid fa-check me-1"></i> ยืนยันการตอบรับ
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Modal 4: ตอบกลับข้อความติดต่อ (Reply Contact) -->
        <div class="modal fade" id="replyContactModal" tabindex="-1" aria-hidden="true">
          <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content modal-content-custom">
              <div class="modal-header modal-header-gradient">
                <h5 class="modal-title fw-bold text-white"><i class="fa-solid fa-comment-dots me-2"></i> บันทึกการตอบกลับข้อความติดต่อ</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
              </div>
              <div class="modal-body p-4">
                <form id="replyContactForm" onsubmit="submitReplyContact(event)">
                  <input type="hidden" name="contact_id" id="replyContactId">
                  <div class="mb-3">
                    <label class="form-label-custom">ผู้ส่งข้อความ</label>
                    <input type="text" id="replyContactName" class="form-control-custom" readonly style="background: var(--bg-subtle);">
                  </div>
                  <div class="mb-3">
                    <label class="form-label-custom">สถานะข้อความ</label>
                    <select name="status" id="replyContactStatus" class="form-select-custom" required>
                      <option value="read">อ่านแล้ว (Read)</option>
                      <option value="replied">ตอบกลับแล้ว (Replied)</option>
                      <option value="unread">ยังไม่อ่าน (Unread)</option>
                    </select>
                  </div>
                  <div class="mb-3">
                    <label class="form-label-custom">บันทึกข้อความการตอบกลับ</label>
                    <textarea name="reply_message" id="replyContactMessage" class="form-control-custom" rows="3" placeholder="ระบุผลการติดต่อกลับ หรือข้อความตอบกลับทางอีเมล/โทรศัพท์..."></textarea>
                  </div>
                  <div class="text-end mt-4">
                    <button type="button" class="btn btn-outline-secondary rounded-pill px-4 me-2" data-bs-dismiss="modal">ยกเลิก</button>
                    <button type="submit" class="btn btn-primary-gradient px-4">
                      <i class="fa-solid fa-check me-1"></i> บันทึกสถานะ
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>

        <!-- Script จัดการ Admin Interactions (Tab, Modals & API Calls) -->
        <script>
          // สลับ Tab ใน Admin Dashboard
          function switchAdminTab(tabName) {
            document.querySelectorAll('.admin-tab-btn').forEach(btn => btn.classList.remove('active'));
            document.querySelectorAll('.admin-tab-pane').forEach(pane => pane.style.display = 'none');

            const btn = document.getElementById('tabBtn-' + tabName);
            const pane = document.getElementById('pane-' + tabName);
            if (btn) btn.classList.add('active');
            if (pane) pane.style.display = 'block';

            // จำลองการเปลี่ยน URL state ให้จดจำแท็บปัจจุบัน
            if (window.history && window.history.replaceState) {
              window.history.replaceState(null, '', 'information.php?tab=' + encodeURIComponent(tabName));
            }
          }

          // แสดงตัวอย่างรูปภาพเมื่อเลือกไฟล์ใน Modal เพิ่มกิจกรรม (Live Preview)
          function previewAddJobImage(input) {
            const previewWrap = document.getElementById('addJobImagePreviewWrap');
            const previewImg = document.getElementById('addJobImagePreview');
            const fileNameEl = document.getElementById('addJobImageFileName');
            if (input.files && input.files[0]) {
              const file = input.files[0];
              const reader = new FileReader();
              reader.onload = function(e) {
                if (previewImg) previewImg.src = e.target.result;
                if (fileNameEl) fileNameEl.textContent = file.name + ' (' + (file.size / 1024).toFixed(1) + ' KB)';
                if (previewWrap) previewWrap.style.display = 'block';
              };
              reader.readAsDataURL(file);
            } else {
              if (previewWrap) previewWrap.style.display = 'none';
            }
          }

          // แสดงตัวอย่างรูปภาพเมื่อเลือกไฟล์ใหม่ใน Modal แก้ไขกิจกรรม
          function previewEditJobImage(input) {
            const previewCurrent = document.getElementById('editJobImagePreviewCurrent');
            if (input.files && input.files[0]) {
              const reader = new FileReader();
              reader.onload = function(e) {
                if (previewCurrent) previewCurrent.src = e.target.result;
              };
              reader.readAsDataURL(input.files[0]);
            }
          }

          // กรองผู้ใช้ใน Tab 1
          document.addEventListener('DOMContentLoaded', () => {
            // อ่านค่าแท็บเริ่มต้นจาก URL param
            const urlParams = new URLSearchParams(window.location.search);
            const initialTab = urlParams.get('tab') || 'user';
            switchAdminTab(initialTab);

            const searchInput = document.getElementById('adminSearchInput');
            const genderFilter = document.getElementById('genderFilter');
            const rows = document.querySelectorAll('.member-row');
            const countDisplay = document.getElementById('memberCountDisplay');

            function applyUserFilter() {
              const query = (searchInput ? searchInput.value : '').toLowerCase().trim();
              const gender = genderFilter ? genderFilter.value : '';
              let visibleCount = 0;

              rows.forEach(row => {
                const name = (row.dataset.fullname || '').toLowerCase();
                const user = (row.dataset.username || '').toLowerCase();
                const phone = (row.dataset.phone || '').toLowerCase();
                const email = (row.dataset.email || '').toLowerCase();
                const prov = (row.dataset.province || '').toLowerCase();
                const rowGender = (row.dataset.gender || '');

                const matchesQuery = !query || name.includes(query) || user.includes(query) || phone.includes(query) || email.includes(query) || prov.includes(query);
                const matchesGender = !gender || (rowGender === gender);

                if (matchesQuery && matchesGender) {
                  row.style.display = '';
                  visibleCount++;
                } else {
                  row.style.display = 'none';
                }
              });

              if (countDisplay) countDisplay.textContent = visibleCount;
            }

            if (searchInput) searchInput.addEventListener('input', applyUserFilter);
            if (genderFilter) genderFilter.addEventListener('change', applyUserFilter);
          });

          // ลบผู้ใช้
          function deleteUser(id, name) {
            if (confirm('คุณต้องการลบข้อมูลสมาชิก "' + name + '" ใช่หรือไม่?')) {
              fetch('api.php?action=delete_user&id=' + id)
                .then(res => res.json())
                .then(data => {
                  alert(data.message || 'ลบข้อมูลสมาชิกสำเร็จ');
                  window.location.href = 'information.php?tab=user';
                })
                .catch(err => {
                  alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                });
            }
          }

          // จัดการกิจกรรมจิตอาสา (job)
          function submitCreateJob(e) {
            e.preventDefault();
            const form = e.target;
            const fileInput = document.getElementById('addJobImageInput');
            if (!fileInput || !fileInput.files || fileInput.files.length === 0) {
              alert('กรุณาเลือกไฟล์รูปภาพเพื่ออัปโหลดใหม่ (ระบบกำหนดให้อัปโหลดรูปภาพใหม่เท่านั้น)');
              return;
            }

            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const origText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังอัปโหลดและบันทึก...';
            }

            fetch('api.php?action=create_job', { method: 'POST', body: formData })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  alert(data.message || 'เพิ่มกิจกรรมจิตอาสาสำเร็จ!');
                  window.location.href = 'information.php?tab=job';
                } else {
                  alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถเพิ่มกิจกรรมได้'));
                }
              })
              .catch(err => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
              })
              .finally(() => {
                if (submitBtn) {
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = origText;
                }
              });
          }

          function openEditJobModalFromBtn(btn) {
            try {
              const jobDataRaw = btn.getAttribute('data-job');
              const job = JSON.parse(jobDataRaw);

              document.getElementById('editJobId').value = job.id;
              document.getElementById('editJobTitle').value = job.title;
              document.getElementById('editJobCategory').value = job.category;
              document.getElementById('editJobLocation').value = job.location;
              document.getElementById('editJobEventDate').value = job.event_date;
              document.getElementById('editJobEventTime').value = job.event_time || '09.00-16.00 น.';
              document.getElementById('editJobHours').value = job.hours;
              document.getElementById('editJobMaxQuota').value = job.max_quota;
              document.getElementById('editJobCurrentQuota').value = job.current_quota || 0;
              document.getElementById('editJobStatus').value = job.status || 'open';
              document.getElementById('editJobImageUrl').value = job.image_url || '';
              
              const prev = document.getElementById('editJobImagePreviewCurrent');
              if (prev) prev.src = job.image_url || 'assets/images/กระดาษทำมือ.jpeg';

              const fileInp = document.getElementById('editJobImageInput');
              if (fileInp) fileInp.value = '';

              document.getElementById('editJobDesc').value = job.description;

              const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('editJobModal'));
              modal.show();
            } catch (err) {
              console.error('Error opening edit job modal:', err);
              alert('เกิดข้อผิดพลาดในการเปิดแบบฟอร์มแก้ไข');
            }
          }

          function submitUpdateJob(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            const origText = submitBtn ? submitBtn.innerHTML : '';
            if (submitBtn) {
              submitBtn.disabled = true;
              submitBtn.innerHTML = '<i class="fa-solid fa-spinner fa-spin me-1"></i> กำลังบันทึกการแก้ไข...';
            }

            fetch('api.php?action=update_job', { method: 'POST', body: formData })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  alert(data.message || 'แก้ไขข้อมูลกิจกรรมจิตอาสาเรียบร้อยแล้ว');
                  window.location.href = 'information.php?tab=job';
                } else {
                  alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถแก้ไขข้อมูลได้'));
                }
              })
              .catch(err => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
              })
              .finally(() => {
                if (submitBtn) {
                  submitBtn.disabled = false;
                  submitBtn.innerHTML = origText;
                }
              });
          }

          function toggleJobStatus(id) {
            fetch('api.php?action=toggle_job_status&id=' + id, { method: 'POST' })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  window.location.href = 'information.php?tab=job';
                } else {
                  alert(data.message || 'ไม่สามารถเปลี่ยนสถานะได้');
                }
              })
              .catch(() => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
              });
          }

          function deleteJobSafe(btn) {
            const id = btn.getAttribute('data-job-id');
            const title = btn.getAttribute('data-job-title') || 'กิจกรรมนี้';
            if (confirm('คุณต้องการลบกิจกรรมจิตอาสา "' + title + '" (ID: #' + id + ') ใช่หรือไม่?')) {
              fetch('api.php?action=delete_job&id=' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                  alert(data.message || 'ลบกิจกรรมจิตอาสาเรียบร้อยแล้ว');
                  window.location.href = 'information.php?tab=job';
                })
                .catch(() => {
                  alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
                });
            }
          }

          // จัดการการตอบรับใบสมัคร (application)
          function filterApplicationTable(status, btn) {
            document.querySelectorAll('#pane-application .btn-outline-secondary, #pane-application .btn-outline-warning, #pane-application .btn-outline-success').forEach(b => b.classList.remove('active'));
            if (btn) btn.classList.add('active');

            const rows = document.querySelectorAll('.app-row');
            rows.forEach(row => {
              if (status === 'all' || row.dataset.status === status) {
                row.style.display = '';
              } else {
                row.style.display = 'none';
              }
            });
          }

          function openRespondAppModal(id, fullname, jobTitle, currentStatus, currentNotes) {
            document.getElementById('respondAppId').value = id;
            document.getElementById('respondAppUserName').value = fullname;
            document.getElementById('respondAppJobTitle').value = jobTitle;
            document.getElementById('respondAppStatus').value = currentStatus;
            document.getElementById('respondAppNotes').value = currentNotes;

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('respondAppModal'));
            modal.show();
          }

          function submitRespondApp(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) submitBtn.disabled = true;

            fetch('api.php?action=respond_application', { method: 'POST', body: formData })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  alert(data.message || 'บันทึกการตอบรับเรียบร้อยแล้ว');
                  window.location.href = 'information.php?tab=application';
                } else {
                  alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกได้'));
                }
              })
              .catch(() => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
              })
              .finally(() => {
                if (submitBtn) submitBtn.disabled = false;
              });
          }

          // จัดการข้อความติดต่อ (contact)
          function openReplyContactModal(id, name, status, replyMsg) {
            document.getElementById('replyContactId').value = id;
            document.getElementById('replyContactName').value = name;
            document.getElementById('replyContactStatus').value = status;
            document.getElementById('replyContactMessage').value = replyMsg;

            const modal = bootstrap.Modal.getOrCreateInstance(document.getElementById('replyContactModal'));
            modal.show();
          }

          function submitReplyContact(e) {
            e.preventDefault();
            const form = e.target;
            const formData = new FormData(form);
            fetch('api.php?action=respond_contact', { method: 'POST', body: formData })
              .then(res => res.json())
              .then(data => {
                if (data.success) {
                  alert(data.message || 'อัปเดตสถานะข้อความติดต่อเรียบร้อยแล้ว');
                  window.location.href = 'information.php?tab=contact';
                } else {
                  alert('เกิดข้อผิดพลาด: ' + (data.message || 'ไม่สามารถบันทึกได้'));
                }
              })
              .catch(() => {
                alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
              });
          }

          function deleteContactMessage(id) {
            if (confirm('คุณต้องการลบข้อความติดต่อนี้ใช่หรือไม่?')) {
              fetch('api.php?action=delete_contact&id=' + id, { method: 'POST' })
                .then(res => res.json())
                .then(data => {
                  alert(data.message || 'ลบข้อความติดต่อเรียบร้อยแล้ว');
                  window.location.href = 'information.php?tab=contact';
                })
                .catch(() => {
                  alert('เกิดข้อผิดพลาดในการเชื่อมต่อ');
                });
            }
          }
        </script>

      <?php else: ?>
        <!-- ===================================================================
             ยังไม่ได้เข้าสู่ระบบ Admin (แอดมินดูได้เท่านั้น)
             =================================================================== -->
        <div class="row justify-content-center">
          <div class="col-md-8 col-lg-5 reveal">
            <div class="card border-0 rounded-4 shadow-lg overflow-hidden text-center" style="background-color: var(--bg-surface); border: 1px solid var(--border-color) !important;">
              <div class="p-4 p-md-5 text-white" style="background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%);">
                <div class="brand-icon mx-auto mb-3" style="width: 65px; height: 65px; font-size: 1.8rem; background: rgba(255,255,255,0.25);">
                  <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="fw-bold fs-3 mb-1">พื้นที่สำหรับแอดมินเท่านั้น</h2>
                <p class="mb-0 text-white-50 small">หน้านี้สงวนไว้สำหรับผู้ดูแลระบบ Ground Team</p>
              </div>

              <div class="p-4 p-md-5">
                <?php if ($current_user): ?>
                  <div class="alert alert-warning rounded-3 small py-3 mb-4">
                    <i class="fa-solid fa-triangle-exclamation fs-5 d-block mb-1"></i>
                    คุณกำลังเข้าสู่ระบบในชื่อ <strong><?php echo htmlspecialchars($current_user['fullname']); ?></strong> (สมาชิกทั่วไป) ซึ่งไม่มีสิทธิ์เข้าถึงแดชบอร์ดแอดมิน
                  </div>
                  <div class="d-flex flex-column gap-2">
                    <a href="profile.php" class="btn-primary-gradient w-100 justify-content-center py-2">
                      <i class="fa-solid fa-id-card me-1"></i> ไปยังหน้าข้อมูลส่วนตัวของฉัน
                    </a>
                    <a href="auth.php?action=logout" class="btn btn-outline-danger rounded-pill py-2">
                      <i class="fa-solid fa-right-from-bracket me-1"></i> ออกจากระบบเพื่อล็อกอินแอดมิน
                    </a>
                  </div>
                <?php else: ?>
                  <p class="text-muted small mb-4">
                    กรุณาเข้าสู่ระบบด้วยบัญชีแอดมินเพื่อจัดการข้อมูลระบบ
                  </p>
                  <form action="auth.php?action=login" method="POST">
                    <input type="hidden" name="login_type" value="admin">
                    <div class="mb-3 text-start">
                      <label class="form-label-custom">Admin Username</label>
                      <input type="text" name="username" class="form-control-custom" value="admin" required autofocus>
                    </div>
                    <div class="mb-4 text-start">
                      <label class="form-label-custom">Admin Password</label>
                      <input type="password" name="password" class="form-control-custom" placeholder="รหัสผ่านแอดมิน (0000)" required>
                    </div>
                    <button type="submit" class="btn-primary-gradient w-100 justify-content-center py-3 fs-6 mb-3">
                      <i class="fa-solid fa-key me-2"></i> เข้าสู่ระบบแอดมิน
                    </button>
                  </form>
                  <div class="p-2 rounded bg-light border text-muted small mt-2">
                    <i class="fa-solid fa-circle-info text-info"></i> ข้อมูลแอดมิน: <code>username: admin</code> | <code>password: 0000</code>
                  </div>
                <?php endif; ?>

                <div class="mt-4 pt-3 border-top">
                  <a href="index.html" class="text-muted small text-decoration-none">
                    <i class="fa-solid fa-arrow-left me-1"></i> กลับสู่หน้าหลัก
                  </a>
                </div>
              </div>
            </div>
          </div>
        </div>
      <?php endif; ?>

    </div>
  </main>

  <div id="footer-placeholder"></div>
  <button id="btn-scroll-top" aria-label="เลื่อนขึ้นด้านบน"><i class="fa-solid fa-arrow-up"></i></button>

  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="assets/js/components.js"></script>
  <script src="assets/js/main.js"></script>
</body>
</html>
