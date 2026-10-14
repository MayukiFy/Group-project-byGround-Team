<?php
require_once __DIR__ . '/auth.php';

// หากเข้าสู่ระบบอยู่แล้ว ให้เปลี่ยนเส้นทางตามสิทธิ์
if (is_logged_in()) {
    if (is_admin()) {
        header('Location: information.php');
    } else {
        header('Location: profile.php');
    }
    exit;
}

$error_msg = $_GET['error'] ?? '';
$success_msg = '';
if (isset($_GET['msg']) && $_GET['msg'] === 'logout_success') {
    $success_msg = 'ออกจากระบบเรียบร้อยแล้ว';
}

// กำหนดแท็บที่แสดงเริ่มต้น (user หรือ admin)
$active_tab = (isset($_GET['tab']) && $_GET['tab'] === 'admin') ? 'admin' : 'user';
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>เข้าสู่ระบบ | Ground Team แพลตฟอร์มอาสาสมัครเพื่อสังคม</title>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
  <style>
    /* สไตล์เฉพาะสำหรับ Tab สลับเข้าสู่ระบบ User / Admin */
    .login-nav-tabs {
      background-color: var(--bg-subtle);
      padding: 6px;
      border-radius: var(--radius-pill);
      display: flex;
      gap: 6px;
      border: 1px solid var(--border-color);
    }
    .login-nav-tab-btn {
      flex: 1;
      padding: 10px 16px;
      border-radius: var(--radius-pill);
      border: none;
      background: transparent;
      color: var(--text-muted);
      font-weight: 600;
      font-size: 0.95rem;
      transition: all var(--transition-fast);
      display: flex;
      align-items: center;
      justify-content: center;
      gap: 8px;
    }
    .login-nav-tab-btn:hover {
      color: var(--text-main);
    }
    .login-nav-tab-btn.active.tab-user {
      background: var(--grad-nature);
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(16, 185, 129, 0.35);
    }
    .login-nav-tab-btn.active.tab-admin {
      background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%);
      color: #ffffff;
      box-shadow: 0 4px 12px rgba(245, 158, 11, 0.4);
    }
    .admin-login-banner {
      background: linear-gradient(135deg, #d97706 0%, #b45309 50%, #78350f 100%);
    }
  </style>
</head>
<body data-page="login">

  <div id="preloader">
    <div class="preloader-spinner"></div>
    <div class="preloader-text">
      <i class="fa-solid fa-leaf text-success me-1"></i> กำลังโหลดหน้าเข้าสู่ระบบ...
    </div>
  </div>

  <div id="navbar-placeholder"></div>

  <main class="py-5" style="background: var(--grad-soft-bg); min-height: 80vh;">
    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-md-9 col-lg-6 reveal">
          
          <!-- แท็บเลือกระหว่าง สมาชิกทั่วไป กับ ผู้ดูแลระบบ (แยกกันอย่างเด็ดขาด) -->
          <div class="mb-4">
            <div class="login-nav-tabs">
              <button type="button" class="login-nav-tab-btn tab-user <?php echo $active_tab === 'user' ? 'active' : ''; ?>" id="tabBtnUser" onclick="switchLoginTab('user')">
                <i class="fa-solid fa-user-check"></i> เข้าสู่ระบบสมาชิก
              </button>
              <button type="button" class="login-nav-tab-btn tab-admin <?php echo $active_tab === 'admin' ? 'active' : ''; ?>" id="tabBtnAdmin" onclick="switchLoginTab('admin')">
                <i class="fa-solid fa-shield-halved"></i> เข้าสู่ระบบแอดมิน
              </button>
            </div>
          </div>

          <!-- Alert แจ้งเตือนข้อผิดพลาด -->
          <?php if (!empty($error_msg)): ?>
            <div class="alert alert-danger rounded-4 py-3 px-3 small d-flex align-items-center gap-2 mb-4 shadow-sm">
              <i class="fa-solid fa-circle-exclamation fs-5 flex-shrink-0"></i>
              <span><?php echo htmlspecialchars($error_msg); ?></span>
            </div>
          <?php endif; ?>

          <!-- Alert แจ้งเตือนสำเร็จ -->
          <?php if (!empty($success_msg)): ?>
            <div class="alert alert-success rounded-4 py-3 px-3 small d-flex align-items-center gap-2 mb-4 shadow-sm">
              <i class="fa-solid fa-circle-check fs-5 flex-shrink-0"></i>
              <span><?php echo htmlspecialchars($success_msg); ?></span>
            </div>
          <?php endif; ?>

          <!-- ============================================================= -->
          <!-- 1. ส่วนเข้าสู่ระบบสำหรับ: สมาชิกทั่วไป (User Login) -->
          <!-- ============================================================= -->
          <div id="userLoginCard" style="display: <?php echo $active_tab === 'user' ? 'block' : 'none'; ?>;">
            <div class="card border-0 rounded-4 shadow-lg overflow-hidden" style="background-color: var(--bg-surface); border: 1px solid var(--border-color) !important;">
              <div class="p-4 p-md-5 text-center text-white position-relative" style="background: var(--grad-nature);">
                <div class="brand-icon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.6rem; background: rgba(255,255,255,0.25);">
                  <i class="fa-solid fa-users"></i>
                </div>
                <h2 class="fw-bold fs-3 mb-1">เข้าสู่ระบบสมาชิกจิตอาสา</h2>
                <p class="mb-0 text-white-50 small">เข้าสู่ระบบเพื่อสมัครเข้าร่วมโครงการจิตอาสาและสะสมชั่วโมง</p>
              </div>

              <div class="p-4 p-md-5">
                <form action="auth.php?action=login" method="POST" id="userLoginForm">
                  <!-- ระบุ login_type เป็น user อย่างชัดเจน -->
                  <input type="hidden" name="login_type" value="user">

                  <div class="mb-3">
                    <label class="form-label-custom">ชื่อผู้ใช้งานสมาชิก (Username) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text border-end-0 bg-transparent text-muted" style="border-color: var(--border-color);">
                        <i class="fa-solid fa-user"></i>
                      </span>
                      <input type="text" name="username" class="form-control-custom border-start-0" placeholder="ระบุ username สมาชิกของคุณ" required <?php echo $active_tab === 'user' ? 'autofocus' : ''; ?>>
                    </div>
                  </div>

                  <div class="mb-4">
                    <label class="form-label-custom mb-1">รหัสผ่าน (Password) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text border-end-0 bg-transparent text-muted" style="border-color: var(--border-color);">
                        <i class="fa-solid fa-lock"></i>
                      </span>
                      <input type="password" name="password" class="form-control-custom border-start-0" placeholder="ระบุรหัสผ่านของคุณ" required>
                    </div>
                  </div>

                  <button type="submit" class="btn-primary-gradient w-100 justify-content-center py-3 fs-6 mb-3">
                    <i class="fa-solid fa-right-to-bracket me-2"></i> เข้าสู่ระบบสมาชิก
                  </button>
                </form>
                <!-- ลิงก์ไปหน้าสมัครสมาชิก -->
                <div class="text-center mt-4 pt-3 border-top">
                  <span class="text-muted small">ยังไม่มีบัญชีสมาชิก?</span>
                  <a href="register.php" class="text-success fw-bold small ms-1 text-decoration-none">
                    สมัครสมาชิกใหม่ที่นี่ <i class="fa-solid fa-arrow-right"></i>
                  </a>
                </div>
              </div>
            </div>
          </div>

          <!-- ============================================================= -->
          <!-- 2. ส่วนเข้าสู่ระบบสำหรับ: ผู้ดูแลระบบ (Admin Login) -->
          <!-- ============================================================= -->
          <div id="adminLoginCard" style="display: <?php echo $active_tab === 'admin' ? 'block' : 'none'; ?>;">
            <div class="card border-0 rounded-4 shadow-lg overflow-hidden" style="background-color: var(--bg-surface); border: 1px solid var(--border-color) !important;">
              <div class="p-4 p-md-5 text-center text-white position-relative admin-login-banner">
                <div class="brand-icon mx-auto mb-3" style="width: 58px; height: 58px; font-size: 1.6rem; background: rgba(255,255,255,0.25);">
                  <i class="fa-solid fa-shield-halved"></i>
                </div>
                <h2 class="fw-bold fs-3 mb-1">เข้าสู่ระบบผู้ดูแลระบบ (Admin)</h2>
                <p class="mb-0 text-white-50 small">พื้นที่เฉพาะเจ้าหน้าที่ / แอดมิน Ground Team เพื่อจัดการข้อมูลสมาชิก</p>
              </div>

              <div class="p-4 p-md-5">
                <form action="auth.php?action=login" method="POST" id="adminLoginForm">
                  <!-- ระบุ login_type เป็น admin อย่างชัดเจน -->
                  <input type="hidden" name="login_type" value="admin">

                  <div class="mb-3">
                    <label class="form-label-custom">ชื่อผู้ดูแลระบบ (Admin Username) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text border-end-0 bg-transparent text-warning" style="border-color: var(--border-color);">
                        <i class="fa-solid fa-user-shield"></i>
                      </span>
                      <input type="text" name="username" class="form-control-custom border-start-0" placeholder="ระบุ username แอดมิน (เช่น admin)" required <?php echo $active_tab === 'admin' ? 'autofocus' : ''; ?>>
                    </div>
                  </div>

                  <div class="mb-4">
                    <label class="form-label-custom mb-1">รหัสผ่านแอดมิน (Admin Password) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text border-end-0 bg-transparent text-warning" style="border-color: var(--border-color);">
                        <i class="fa-solid fa-key"></i>
                      </span>
                      <input type="password" name="password" class="form-control-custom border-start-0" placeholder="ระบุรหัสผ่านแอดมิน" required>
                    </div>
                  </div>

                  <button type="submit" class="btn btn-warning w-100 justify-content-center py-3 fs-6 fw-bold mb-3 rounded-pill shadow-sm text-dark">
                    <i class="fa-solid fa-shield-halved me-2"></i> เข้าสู่ระบบแอดมิน (Admin Portal)
                  </button>
                </form>
                <!-- สลับกลับไปแท็บสมาชิก -->
                <div class="text-center mt-4 pt-3 border-top">
                  <span class="text-muted small">หากคุณเป็นสมาชิกจิตอาสาทั่วไป?</span>
                  <a href="javascript:void(0)" onclick="switchLoginTab('user')" class="text-success fw-bold small ms-1 text-decoration-none">
                    <i class="fa-solid fa-user me-1"></i> ไปที่เข้าสู่ระบบสมาชิก
                  </a>
                </div>
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
  <script>
    function switchLoginTab(type) {
      const userCard = document.getElementById('userLoginCard');
      const adminCard = document.getElementById('adminLoginCard');
      const btnUser = document.getElementById('tabBtnUser');
      const btnAdmin = document.getElementById('tabBtnAdmin');

      if (type === 'admin') {
        userCard.style.display = 'none';
        adminCard.style.display = 'block';
        btnUser.classList.remove('active');
        btnAdmin.classList.add('active');
        const adminInput = adminCard.querySelector('input[name="username"]');
        if (adminInput) adminInput.focus();
        window.history.replaceState(null, '', 'login.php?tab=admin');
      } else {
        adminCard.style.display = 'none';
        userCard.style.display = 'block';
        btnAdmin.classList.remove('active');
        btnUser.classList.add('active');
        const userInput = userCard.querySelector('input[name="username"]');
        if (userInput) userInput.focus();
        window.history.replaceState(null, '', 'login.php?tab=user');
      }
    }
  </script>
</body>
</html>
