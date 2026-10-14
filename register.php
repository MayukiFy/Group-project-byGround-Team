<?php
require_once __DIR__ . '/auth.php';

// หากเข้าสู่ระบบอยู่แล้ว ให้เปลี่ยนเส้นทาง
if (is_logged_in()) {
    header('Location: profile.php');
    exit;
}

$error_msg = $_GET['error'] ?? '';

// รายชื่อจังหวัด 77 จังหวัดในประเทศไทย
$provinces = [
  'กรุงเทพมหานคร', 'กระบี่', 'กาญจนบุรี', 'กาฬสินธุ์', 'กำแพงเพชร', 'ขอนแก่น', 'จันทบุรี', 'ฉะเชิงเทรา', 'ชลบุรี', 'ชัยนาท',
  'ชัยภูมิ', 'ชุมพร', 'เชียงราย', 'เชียงใหม่', 'ตรัง', 'ตราด', 'ตาก', 'นครนายก', 'นครปฐม', 'นครพนม',
  'นครราชสีมา', 'นครศรีธรรมราช', 'นครสวรรค์', 'นนทบุรี', 'นราธิวาส', 'น่าน', 'บึงกาฬ', 'บุรีรัมย์', 'ปทุมธานี', 'ประจวบคีรีขันธ์',
  'ปราจีนบุรี', 'ปัตตานี', 'พระนครศรีอยุธยา', 'พะเยา', 'พังงา', 'พัทลุง', 'พิจิตร', 'พิษณุโลก', 'เพชรบุรี', 'เพชรบูรณ์',
  'แพร่', 'ภูเก็ต', 'มหาสารคาม', 'มุกดาหาร', 'แม่ฮ่องสอน', 'ยโสธร', 'ยะลา', 'ร้อยเอ็ด', 'ระนอง', 'ระยอง',
  'ราชบุรี', 'ลพบุรี', 'ลำปาง', 'ลำพูน', 'เลย', 'ศรีสะเกษ', 'สกลนคร', 'สงขลา', 'สตูล', 'สมุทรปราการ',
  'สมุทรสงคราม', 'สมุทรสาคร', 'สระแก้ว', 'สระบุรี', 'สิงห์บุรี', 'สุโขทัย', 'สุพรรณบุรี', 'สุราษฎร์ธานี', 'สุรินทร์', 'หนองคาย',
  'หนองบัวลำภู', 'อ่างทอง', 'อำนาจเจริญ', 'อุดรธานี', 'อุตรดิตถ์', 'อุทัยธานี', 'อุบลราชธานี'
];
?>
<!DOCTYPE html>
<html lang="th">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>สมัครสมาชิกเพื่อรับข้อมูล | Ground Team แพลตฟอร์มจิตอาสา</title>
  <link rel="icon" type="image/svg+xml" href="data:image/svg+xml,<svg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 100 100'><text y='.9em' font-size='90'>🌱</text></svg>">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
  <link rel="stylesheet" href="assets/css/style.css">
</head>
<body data-page="register">

  <div id="preloader">
    <div class="preloader-spinner"></div>
    <div class="preloader-text">
      <i class="fa-solid fa-leaf text-success me-1"></i> กำลังโหลดหน้าสมัครสมาชิก...
    </div>
  </div>

  <div id="navbar-placeholder"></div>

  <main class="py-5" style="background: var(--grad-soft-bg); min-height: 85vh;">
    <div class="container py-4">
      <div class="row justify-content-center">
        <div class="col-lg-8 reveal">
          
          <div class="card border-0 rounded-4 shadow-lg overflow-hidden" style="background-color: var(--bg-surface); border: 1px solid var(--border-color) !important;">
            <!-- ส่วนหัว Card -->
            <div class="p-4 p-md-5 text-center text-white position-relative" style="background: var(--grad-nature);">
              <div class="brand-icon mx-auto mb-3" style="width: 60px; height: 60px; font-size: 1.6rem; background: rgba(255,255,255,0.25);">
                <i class="fa-solid fa-user-plus"></i>
              </div>
              <h1 class="fw-bold fs-3 mb-1">สมัครสมาชิก Ground Team</h1>
              <p class="mb-0 text-white-50 small">ลงทะเบียนเพื่อรับข้อมูลข่าวสาร เข้าร่วมกิจกรรมจิตอาสา และสะสมชั่วโมงเพื่อรับเกียรติบัตร</p>
            </div>

            <!-- ส่วนฟอร์มรับข้อมูล (ตามข้อกำหนดที่ 1) -->
            <div class="p-4 p-md-5">
              <?php if (!empty($error_msg)): ?>
                <div class="alert alert-danger rounded-3 py-2 px-3 small d-flex align-items-center gap-2 mb-4">
                  <i class="fa-solid fa-circle-exclamation fs-5"></i>
                  <span><?php echo htmlspecialchars($error_msg); ?></span>
                </div>
              <?php endif; ?>

              <form action="auth.php?action=register" method="POST" id="registerForm">
                
                <h5 class="fw-bold fs-6 text-success mb-3 pb-2 border-bottom">
                  <i class="fa-solid fa-lock me-1"></i> 1. บัญชีผู้ใช้งานสำหรับเข้าสู่ระบบ
                </h5>

                <div class="row g-3 mb-4">
                  <!-- Username -->
                  <div class="col-md-6">
                    <label class="form-label-custom">ชื่อผู้ใช้งาน (Username) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text bg-transparent text-muted" style="border-color: var(--border-color);"><i class="fa-solid fa-user"></i></span>
                      <input type="text" name="username" class="form-control-custom border-start-0" placeholder="ภาษาอังกฤษหรือตัวเลข เช่น somchai99" required autofocus>
                    </div>
                  </div>

                  <!-- Password -->
                  <div class="col-md-6">
                    <label class="form-label-custom">รหัสผ่าน (Password) <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text bg-transparent text-muted" style="border-color: var(--border-color);"><i class="fa-solid fa-key"></i></span>
                      <input type="password" name="password" class="form-control-custom border-start-0" placeholder="กำหนดรหัสผ่าน" required>
                    </div>
                  </div>
                </div>

                <h5 class="fw-bold fs-6 text-success mb-3 pb-2 border-bottom">
                  <i class="fa-solid fa-address-card me-1"></i> 2. ข้อมูลส่วนบุคคล (จัดเก็บเพื่อส่งข้อมูลไปยัง information.php)
                </h5>

                <div class="row g-3 mb-4">
                  <!-- ชื่อสกุล -->
                  <div class="col-md-6">
                    <label class="form-label-custom">ชื่อ - สกุล <span class="text-danger">*</span></label>
                    <input type="text" name="fullname" class="form-control-custom" placeholder="เช่น รัชชานนท์ วงศ์ดี" required>
                  </div>

                  <!-- เบอร์โทร -->
                  <div class="col-md-6">
                    <label class="form-label-custom">เบอร์โทรศัพท์ <span class="text-danger">*</span></label>
                    <input type="tel" name="phone" class="form-control-custom" placeholder="เช่น 0812345678" required>
                  </div>

                  <!-- Email -->
                  <div class="col-md-6">
                    <label class="form-label-custom">Email <span class="text-danger">*</span></label>
                    <input type="email" name="email" class="form-control-custom" placeholder="name@email.com" required>
                  </div>

                  <!-- จังหวัดที่อยู่ (รายการตัวเลือก) -->
                  <div class="col-md-6">
                    <label class="form-label-custom">จังหวัดที่อยู่ (รายการตัวเลือก) <span class="text-danger">*</span></label>
                    <select name="province" class="form-select-custom" required>
                      <option value="" selected disabled>-- กรุณาเลือกจังหวัดของคุณ --</option>
                      <?php foreach ($provinces as $p): ?>
                        <option value="<?php echo htmlspecialchars($p); ?>"><?php echo htmlspecialchars($p); ?></option>
                      <?php endforeach; ?>
                    </select>
                  </div>

                  <!-- อายุ -->
                  <div class="col-md-6">
                    <label class="form-label-custom">อายุ (ปี) <span class="text-danger">*</span></label>
                    <input type="number" name="age" class="form-control-custom" min="10" max="100" placeholder="เช่น 22" required>
                  </div>

                  <!-- เพศ (ตัวเลือกเป็น ชาย / หญิง / lgbtq+) -->
                  <div class="col-md-6">
                    <label class="form-label-custom">เพศ <span class="text-danger">*</span></label>
                    <select name="gender" class="form-select-custom" required>
                      <option value="ชาย">👨 ชาย</option>
                      <option value="หญิง">👩 หญิง</option>
                      <option value="lgbtq+">🌈 lgbtq+</option>
                    </select>
                  </div>

                  <!-- ยอมรับข้อกำหนด -->
                  <div class="col-12 mt-3">
                    <div class="form-check">
                      <input class="form-check-input" type="checkbox" id="regConsent" required checked>
                      <label class="form-check-label text-muted small" for="regConsent">
                        ข้าพเจ้ายินยอมให้ Ground Team จัดเก็บข้อมูลข้างต้นเพื่อการติดต่อประสานงาน และรับข้อมูลข่าวสารจิตอาสา
                      </label>
                    </div>
                  </div>
                </div>

                <button type="submit" class="btn-primary-gradient w-100 justify-content-center py-3 fs-6 mb-3">
                  <i class="fa-solid fa-user-check me-2"></i> ยืนยันการสมัครสมาชิก
                </button>
              </form>

              <div class="text-center mt-3 pt-3 border-top">
                <span class="text-muted small">มีบัญชีสมาชิกอยู่แล้ว?</span>
                <a href="login.php" class="text-success fw-bold small ms-1 text-decoration-none">
                  เข้าสู่ระบบที่นี่ <i class="fa-solid fa-arrow-right"></i>
                </a>
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

