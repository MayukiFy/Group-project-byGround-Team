<?php
// ==========================================================================
// Project: Ground Team - Volunteer & Social Impact Platform
// Page: information.php - หน้าระบบจัดการข้อมูลสมาชิก (แอดมินดูได้เท่านั้น)
// ==========================================================================

require_once __DIR__ . '/auth.php';

// ตรวจสอบสิทธิ์ว่าผู้ใช้เป็น Admin หรือไม่
$admin_logged_in = is_admin();
$current_user = get_current_user_data();

// อ่านข้อมูลสมาชิกทั้งหมดจาก data/users.json
$users = get_all_users();

// คำนวณสถิติภาพรวมสำหรับแอดมิน
$total_users = count($users);
$count_male = 0;
$count_female = 0;
$count_lgbt = 0;
$provinces_count = [];

foreach ($users as $u) {
    $g = $u['gender'] ?? '';
    if ($g === 'ชาย') $count_male++;
    elseif ($g === 'หญิง') $count_female++;
    elseif (strtolower($g) === 'lgbtq+') $count_lgbt++;

    $prov = $u['province'] ?? 'ไม่ระบุ';
    $provinces_count[$prov] = ($provinces_count[$prov] ?? 0) + 1;
}

// จัดการกรณี Action ลบสมาชิก (โดยแอดมิน)
if ($admin_logged_in && isset($_GET['delete_id'])) {
    $del_id = (int)$_GET['delete_id'];
    delete_user_by_id($del_id);
    header('Location: information.php?msg=deleted');
    exit;
}
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ข้อมูลสมาชิกและผู้ลงทะเบียน | Ground Team (Admin Only)</title>
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
    .admin-stat-card {
      background: var(--bg-surface);
      border: 1px solid var(--border-color);
      border-radius: var(--radius-md);
      padding: 1.5rem;
      box-shadow: var(--shadow-sm);
      transition: all var(--transition-smooth);
    }
    .admin-stat-card:hover {
      transform: translateY(-4px);
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
      padding: 1rem 0.75rem;
      vertical-align: middle;
      border-bottom: 1px solid var(--border-color);
      color: var(--text-main);
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
      <i class="fa-solid fa-leaf text-success me-1"></i> กำลังตรวจสอบสิทธิ์ผู้ดูแลระบบ...
    </div>
  </div>

  <div id="navbar-placeholder"></div>

  <main class="py-5" style="background: var(--grad-soft-bg); min-height: 85vh;">
    <div class="container py-3">

      <?php if ($admin_logged_in): ?>
        <!-- ===================================================================
             กรณีที่ 1: เข้าสู่ระบบในฐานะ Admin แล้ว (แสดงข้อมูลสมาชิกทั้งหมด)
             =================================================================== -->
        <div class="reveal">
          <!-- Header Dashboard -->
          <div class="d-flex flex-wrap justify-content-between align-items-center gap-3 mb-4">
            <div>
              <div class="d-flex flex-wrap align-items-center gap-2 mb-2">
                <div class="admin-badge">
                  <i class="fa-solid fa-shield-halved"></i> พื้นที่ผู้ดูแลระบบ (Admin Only)
                </div>
                <span class="badge bg-light text-dark border rounded-pill px-3 py-2 shadow-sm">
                  <i class="fa-solid fa-database text-success me-1"></i> Database: <strong class="text-success">groundteam</strong> &bull; Table: <strong class="text-primary">tbground</strong>
                </span>
              </div>
              <h1 class="section-title mb-1">
                ระบบตรวจสอบข้อมูลสมาชิก <span class="gradient-text-nature">Ground Team</span>
              </h1>
              <p class="text-muted small mb-0">
                ข้อมูลสมาชิกทั้งหมดที่ลงทะเบียนเพื่อรับข่าวสารและเข้าร่วมกิจกรรมจิตอาสา (แอดมินดูได้เท่านั้น)
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

          <!-- Alert แจ้งเตือนเมื่อลบข้อมูล -->
          <?php if (isset($_GET['msg']) && $_GET['msg'] === 'deleted'): ?>
            <div class="alert alert-success alert-dismissible fade show rounded-3 small py-2" role="alert">
              <i class="fa-solid fa-check-circle me-1"></i> ลบข้อมูลสมาชิกเรียบร้อยแล้ว
              <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
          <?php endif; ?>

          <!-- สรุปสถิติ 4 กล่อง (Overview Cards) -->
          <div class="row g-3 mb-4">
            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center">
                <div class="text-success fs-3 mb-1"><i class="fa-solid fa-users"></i></div>
                <div class="fw-bold fs-3 text-main"><?php echo $total_users; ?></div>
                <div class="text-muted small">สมาชิกทั้งหมดในระบบ</div>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center">
                <div class="text-primary fs-3 mb-1"><i class="fa-solid fa-mars"></i></div>
                <div class="fw-bold fs-3 text-main"><?php echo $count_male; ?></div>
                <div class="text-muted small">เพศชาย</div>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center">
                <div class="text-danger fs-3 mb-1"><i class="fa-solid fa-venus"></i></div>
                <div class="fw-bold fs-3 text-main"><?php echo $count_female; ?></div>
                <div class="text-muted small">เพศหญิง</div>
              </div>
            </div>

            <div class="col-md-3 col-6">
              <div class="admin-stat-card text-center">
                <div class="text-warning fs-3 mb-1"><i class="fa-solid fa-rainbow"></i></div>
                <div class="fw-bold fs-3 text-main"><?php echo $count_lgbt; ?></div>
                <div class="text-muted small">เพศ lgbtq+</div>
              </div>
            </div>
          </div>

          <!-- เครื่องมือค้นหาและกรองข้อมูล (Filter & Search) -->
          <div class="card border-0 rounded-4 p-3 shadow-sm mb-4 no-print" style="background-color: var(--bg-surface); border: 1px solid var(--border-color) !important;">
            <div class="row g-2 align-items-center">
              <div class="col-md-6">
                <div class="input-group">
                  <input type="text" id="adminSearchInput" class="form-control-custom border-start-0" placeholder="ค้นหาตามชื่อ, username, เบอร์โทร, email หรือจังหวัด...">
                </div>
              </div>
              <div class="col-md-3 col-6">
                <select id="genderFilter" class="form-select-custom">
                  <option value="">เพศทั้งหมด</option>
                  <option value="ชาย">ชาย</option>
                  <option value="หญิง">หญิง</option>
                  <option value="lgbtq+">lgbtq+</option>
                </select>
              </div>
              <div class="col-md-3 col-6 text-end">
                <span class="text-muted small">แสดงผล: <strong id="memberCountDisplay"><?php echo $total_users; ?></strong> รายการ</span>
              </div>
            </div>
          </div>

          <!-- ตารางแสดงรายชื่อสมาชิกทั้งหมด -->
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
                    <td>
                      <a href="tel:<?php echo htmlspecialchars($u['phone']); ?>" class="text-decoration-none text-muted">
                        <i class="fa-solid fa-phone text-success small me-1"></i><?php echo htmlspecialchars($u['phone']); ?>
                      </a>
                    </td>
                    <td>
                      <a href="mailto:<?php echo htmlspecialchars($u['email']); ?>" class="text-decoration-none text-muted">
                        <i class="fa-solid fa-envelope text-info small me-1"></i><?php echo htmlspecialchars($u['email']); ?>
                      </a>
                    </td>
                    <td>
                      <span class="badge rounded-pill bg-light text-dark border px-2 py-1">
                        <i class="fa-solid fa-location-dot text-danger me-1"></i><?php echo htmlspecialchars($u['province']); ?>
                      </span>
                    </td>
                    <td class="text-center fw-bold"><?php echo htmlspecialchars($u['age'] ?? '-'); ?> ปี</td>
                    <td class="text-center">
                      <span class="badge <?php echo $badge; ?> rounded-pill px-2 py-1">
                        <?php echo htmlspecialchars($g); ?>
                      </span>
                    </td>
                    <td class="text-muted small"><?php echo htmlspecialchars($u['registered_at'] ?? '-'); ?></td>
                    <td class="text-center no-print">
                      <?php if (!$is_admin_user): ?>
                        <a href="information.php?delete_id=<?php echo $u['id']; ?>" 
                           class="btn btn-sm btn-outline-danger rounded-circle" 
                           onclick="return confirm('ต้องการลบข้อมูลสมาชิก <?php echo htmlspecialchars($u['fullname']); ?> ใช่หรือไม่?')"
                           title="ลบข้อมูล">
                          <i class="fa-solid fa-trash-can"></i>
                        </a>
                      <?php else: ?>
                        <span class="text-muted small fst-italic">หลัก</span>
                      <?php endif; ?>
                    </td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>

          <div class="text-end no-print">
            <a href="index.html" class="btn btn-outline-secondary rounded-pill px-4">
              <i class="fa-solid fa-house me-1"></i> กลับหน้าหลัก
            </a>
          </div>
        </div>

        <!-- Script ค้นหาและกรองตารางแบบ Real-time -->
        <script>
          document.addEventListener('DOMContentLoaded', () => {
            const searchInput = document.getElementById('adminSearchInput');
            const genderFilter = document.getElementById('genderFilter');
            const rows = document.querySelectorAll('.member-row');
            const countDisplay = document.getElementById('memberCountDisplay');

            function applyFilter() {
              const query = searchInput.value.toLowerCase().trim();
              const gender = genderFilter.value;
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

              if (countDisplay) {
                countDisplay.textContent = visibleCount;
              }
            }

            if (searchInput) searchInput.addEventListener('input', applyFilter);
            if (genderFilter) genderFilter.addEventListener('change', applyFilter);
          });
        </script>

      <?php else: ?>
        <!-- ===================================================================
             กรณีที่ 2: ยังไม่ได้เข้าสู่ระบบ Admin (แอดมินดูได้เท่านั้น)
             =================================================================== -->
        <div class="row justify-content-center">
          <div class="col-md-8 col-lg-5 reveal">
            <div class="card border-0 rounded-4 shadow-lg overflow-hidden text-center" style="background-color: var(--bg-surface); border: 1px solid var(--border-color) !important;">
              
              <!-- ส่วนหัวเตือนสิทธิ์ -->
              <div class="p-4 p-md-5 text-white" style="background: linear-gradient(135deg, #ef4444 0%, #f59e0b 100%);">
                <div class="brand-icon mx-auto mb-3" style="width: 65px; height: 65px; font-size: 1.8rem; background: rgba(255,255,255,0.25);">
                  <i class="fa-solid fa-lock"></i>
                </div>
                <h2 class="fw-bold fs-3 mb-1">พื้นที่สำหรับแอดมินเท่านั้น</h2>
                <p class="mb-0 text-white-50 small">หน้านี้สงวนไว้สำหรับผู้ดูแลระบบ (Admin Only)</p>
              </div>

              <div class="p-4 p-md-5">
                <?php if ($current_user): ?>
                  <!-- ผู้ใช้ล็อกอินอยู่ แต่ไม่ใช่แอดมิน -->
                  <div class="alert alert-warning rounded-3 small py-3 mb-4">
                    <i class="fa-solid fa-triangle-exclamation fs-5 d-block mb-1"></i>
                    คุณกำลังเข้าสู่ระบบในชื่อ <strong><?php echo htmlspecialchars($current_user['fullname']); ?></strong> (สมาชิกทั่วไป) ซึ่งไม่มีสิทธิ์เข้าถึงข้อมูลของสมาชิกท่านอื่น
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
                  <!-- ยังไม่ได้ล็อกอินเลย -->
                  <p class="text-muted small mb-4">
                    กรุณาเข้าสู่ระบบด้วยบัญชีแอดมินเพื่อตรวจสอบรายชื่อสมาชิกทั้งหมด
                  </p>

                  <form action="auth.php?action=login" method="POST">
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
