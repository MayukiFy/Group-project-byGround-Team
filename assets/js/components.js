/**
 * ==========================================================================
 * Project: Ground Team - Volunteer & Social Impact Platform
 * Component Loader: Navigation Bar, Footer, Modals & Auth Integration
 * ==========================================================================
 * การแก้ไข Navigation Bar, Footer หรือระบบ Auth Gate ทำได้ที่ไฟล์นี้ไฟล์เดียว
 * อัปเดตไปยังทุกหน้าเว็บ (index, activities, how-to-apply, about, contact, profile, information)
 */

(function () {
  'use strict';

  // กำหนด Theme ให้ HTML ทันทีทั้ง data-theme และ data-bs-theme (สำหรับ Bootstrap 5.3)
  try {
    const initialTheme = localStorage.getItem('ground_theme') || 'light';
    document.documentElement.setAttribute('data-theme', initialTheme);
    document.documentElement.setAttribute('data-bs-theme', initialTheme);
  } catch (e) { }

  // 1. ตรวจสอบชื่อหน้าปัจจุบัน เพื่อทำ Active Navigation
  function getCurrentPage() {
    const path = window.location.pathname;
    const pageName = path.substring(path.lastIndexOf('/') + 1) || 'index.html';

    if (pageName === '' || pageName === 'index.html' || pageName === '/') return 'home';
    if (pageName.includes('activities')) return 'activities';
    if (pageName.includes('how-to-apply')) return 'how-to-apply';
    if (pageName.includes('about')) return 'about';
    if (pageName.includes('contact')) return 'contact';
    if (pageName.includes('profile')) return 'profile';
    if (pageName.includes('information')) return 'information';
    if (pageName.includes('login')) return 'login';
    if (pageName.includes('register')) return 'register';

    const bodyPage = document.body.getAttribute('data-page');
    if (bodyPage) return bodyPage;

    return 'home';
  }

  // 2. สร้างโครงสร้าง HTML สำหรับ Navigation Bar
  function renderNavbar() {
    const activePage = getCurrentPage();

    const navbarHTML = `
      <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
          <!-- Logo & Brand Name -->
          <a class="navbar-brand-custom" href="index.html">
            <div class="brand-icon">
              <i class="fa-solid fa-seedling"></i>
            </div>
            <div>
              <span class="brand-text-highlight">Ground</span>Team
            </div>
          </a>

          <!-- Mobile Toggle & Theme Toggle Button -->
          <div class="d-flex align-items-center gap-2 d-lg-none">
            <button class="btn-theme-toggle" id="theme-toggle-mobile" aria-label="Toggle Theme" title="สลับโหมด มืด/สว่าง">
              <i class="fa-solid fa-moon"></i>
            </button>
            <button class="navbar-toggler navbar-toggler-custom" type="button" data-bs-toggle="collapse" data-bs-target="#mainNavbar" aria-controls="mainNavbar" aria-expanded="false" aria-label="Toggle navigation">
              <i class="fa-solid fa-bars"></i>
            </button>
          </div>

          <!-- Navigation Links -->
          <div class="collapse navbar-collapse" id="mainNavbar">
            <ul class="navbar-nav mx-auto mb-2 mb-lg-0">
              <li class="nav-item">
                <a class="nav-link ${activePage === 'home' ? 'active' : ''}" href="index.html">
                  <i class="fa-solid fa-house-chimney me-1"></i> Home
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link ${activePage === 'activities' ? 'active' : ''}" href="activities.html">
                  <i class="fa-solid fa-hand-holding-heart me-1"></i> งานทั้งหมด
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link ${activePage === 'how-to-apply' ? 'active' : ''}" href="how-to-apply.html">
                  <i class="fa-solid fa-clipboard-list me-1"></i> วิธีสมัคร
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link ${activePage === 'about' ? 'active' : ''}" href="about.html">
                  <i class="fa-solid fa-people-group me-1"></i> About
                </a>
              </li>
              <li class="nav-item">
                <a class="nav-link ${activePage === 'contact' ? 'active' : ''}" href="contact.html">
                  <i class="fa-solid fa-paper-plane me-1"></i> ติดต่อเรา
                </a>
              </li>
            </ul>

            <!-- Right Controls (Desktop): Auth Container -->
            <div class="d-none d-lg-flex align-items-center gap-2" id="nav-auth-desktop">
              <button class="btn-theme-toggle" id="theme-toggle-desktop" aria-label="Toggle Theme" title="สลับโหมด มืด/สว่าง">
                <i class="fa-solid fa-moon"></i>
              </button>
              
              <!-- กล่อง User / Auth Slot (จะถูกอัปเดตแบบ Dynamic) -->
              <div id="auth-controls-desktop" class="d-flex align-items-center gap-2">
                <a href="login.php" class="btn btn-outline-custom rounded-pill py-2 px-3 fs-6 d-flex align-items-center gap-1">
                  <i class="fa-solid fa-right-to-bracket text-primary"></i> เข้าสู่ระบบ
                </a>
                <a href="register.php" class="btn btn-outline-custom rounded-pill py-2 px-3 fs-6 d-flex align-items-center gap-1">
                  <i class="fa-solid fa-user-plus text-success"></i> สมัครสมาชิก
                </a>
              </div>
            </div>

            <!-- Mobile Controls in Dropdown -->
            <div class="d-lg-none mt-3 d-flex flex-column gap-2" id="nav-auth-mobile">
              <div id="auth-controls-mobile" class="d-flex flex-column gap-2">
                <a href="login.php" class="btn btn-outline-custom w-100 justify-content-center">
                  <i class="fa-solid fa-right-to-bracket text-primary me-1"></i> เข้าสู่ระบบ
                </a>
                <a href="register.php" class="btn btn-outline-custom w-100 justify-content-center">
                  <i class="fa-solid fa-user-plus text-success me-1"></i> สมัครสมาชิกใหม่
                </a>
              </div>
            </div>
          </div>
        </div>
      </nav>
    `;

    const navPlaceholder = document.getElementById('navbar-placeholder');
    if (navPlaceholder) {
      navPlaceholder.innerHTML = navbarHTML;
    }
  }

  // 3. สร้างโครงสร้าง HTML สำหรับ Footer (เป็นไปตามข้อกำหนด)
  function renderFooter() {
    const footerHTML = `
      <footer class="footer-custom">
        <div class="container">
          <div class="row g-4 justify-content-between">
            <!-- Brand Column -->
            <div class="col-lg-4 col-md-6">
              <a class="footer-logo" href="index.html">
                <div class="brand-icon">
                  <i class="fa-solid fa-seedling"></i>
                </div>
                <div>
                  <span class="brand-text-highlight">Ground</span>Team
                </div>
              </a>
              <p class="footer-desc">ชยันต์ บูชาพันธ์ 67011213014 <br>นัฐภูมิ ธงศรี 67011213047 <br>อลิน ฉ่ำมณี 67011213149 <br>สวิตต์ วงศสิงห์ 67011213146</p>             
              <div class="social-links mt-3">
                <a href="https://www.facebook.com/profile.php?id=61595107302167" target="_blank" class="social-icon-btn" aria-label="Facebook">
                  <i class="fa-brands fa-facebook-f"></i>
                </a>
                <a href="https://instagram.com/mahasarakhamuniversity?xtok=MW82bXhrYWM2YjhmdA%3D%3D" target="_blank" class="social-icon-btn" aria-label="Instagram">
                  <i class="fa-brands fa-instagram"></i>
                </a>
              </div>
            </div>

            <!-- Quick Links -->
            <div class="col-lg-2 col-md-6 col-6">
              <h5 class="footer-heading">เมนูลัด</h5>
              <ul class="footer-links list-unstyled">
                <li><a href="index.html"><i class="fa-solid fa-angle-right"></i> หน้าแรก</a></li>
                <li><a href="activities.html"><i class="fa-solid fa-angle-right"></i> งานจิตอาสาทั้งหมด</a></li>
                <li><a href="how-to-apply.html"><i class="fa-solid fa-angle-right"></i> ขั้นตอนการสมัคร</a></li>
                <li><a href="about.html"><i class="fa-solid fa-angle-right"></i> เกี่ยวกับเรา</a></li>
                <li><a href="contact.html"><i class="fa-solid fa-angle-right"></i> ติดต่อทีมงาน</a></li>
              </ul>
            </div>

            <!-- Member & Admin Links -->
            <div class="col-lg-3 col-md-6 col-6">
              <h5 class="footer-heading">ระบบสมาชิก & แอดมิน</h5>
              <ul class="footer-links list-unstyled">
                <li><a href="profile.php"><i class="fa-solid fa-user-circle text-primary"></i> ข้อมูลส่วนตัว (ผู้ใช้)</a></li>
                <li><a href="login.php"><i class="fa-solid fa-right-to-bracket text-success"></i> เข้าสู่ระบบ</a></li>
                <li><a href="register.php"><i class="fa-solid fa-user-plus text-info"></i> สมัครสมาชิก</a></li>
                <li><a href="information.php"><i class="fa-solid fa-shield-halved text-warning"></i> จัดการสมาชิก (Admin Only)</a></li>
              </ul>
            </div>

            <!-- Contact Summary -->
            <div class="col-lg-3 col-md-6">
              <h5 class="footer-heading">ติดต่อเรา</h5>
              <ul class="footer-links list-unstyled">
                <li class="d-flex align-items-start gap-2 text-muted mb-2">
                  <i class="fa-solid fa-location-dot mt-1 text-success"></i>
                  <span>2202, Kham Riang, Kantharawichai District, Maha Sarakham 44150</span>
                </li>
                <li class="d-flex align-items-center gap-2 text-muted mb-2">
                  <i class="fa-solid fa-phone text-info"></i>
                  <span>02-222-2222,089-563-6767</span>
                </li>
                <li class="d-flex align-items-center gap-2 text-muted mb-2">
                  <i class="fa-solid fa-envelope text-warning"></i>
                  <span>GroundTeam@gmail.com</span>
                </li>
              </ul>
            </div>
          </div>

          <!-- Bottom Copyright (ข้อกำหนดที่ต้องระบุตรงตามสเปก) -->
          <div class="footer-bottom">
            <p class="footer-copyright">© 2026 Group project</p>
            <p class="footer-designer">Designed by Ground Team</p>
          </div>
        </div>
      </footer>
    `;

    const footerPlaceholder = document.getElementById('footer-placeholder');
    if (footerPlaceholder) {
      footerPlaceholder.innerHTML = footerHTML;
    }
  }

  // รายชื่อโครงการสำหรับจับคู่ ID กับชื่อกิจกรรม
  const activityTitlesMap = {
    '1': 'จิตอาสาทำสมุดทำมือจากกระดาษรีไซเคิลให้น้อง ๆ ด้อยโอกาส (จ.มหาสารคาม)',
    '2': 'โครงการมื้ออาหารกลางวัน เพื่อเด็ก (จ.นครพนม)',
    '3': 'จิตอาสาจัดชุดสุขอนามัยแบ่งปันให้ผู้ยากไร้ (จ.บุรีรัมย์)',
    '4': 'จิตอาสาทำถุงผ้าจากเสื้อผ้าเก่าเพื่อผู้ขาดแคลน (จ.มหาสารคาม)',
    '5': 'จิตอาสาจัดถุงยังชีพแบ่งปันให้ครอบครัวผู้ยากไร้ (จ.นครราชสีมา)',
    '6': 'จิตอาสาพี่สอนน้อง อ่านนิทานสร้างจินตนาการ (จ.กาฬสินธุ์)',
    '7': 'จิตอาสาปลูกต้นไม้เพิ่มพื้นที่สีเขียวให้ชุมชน (จ.ร้อยเอ็ด)',
    '8': 'จิตอาสาเก็บขยะและทำความสะอาดชุมชน (จ.มหาสารคาม)',
    '9': 'จิตอาสาคัดแยกขยะสร้างประโยชน์เพื่อสังคม (จ.ขอนแก่น)',
    '10': 'จิตอาสาจัดชุดอุปกรณ์การเรียนเพื่อน้อง (จ.ขอนแก่น)',
    '11': 'จิตอาสาประดิษฐ์ที่คั่นหนังสือจากกระดาษเหลือใช้ (จ.มหาสารคาม)',
    '12': 'กิจกรรมอาสาสอนผู้สูงอายุใช้สมาร์ทโฟน (จ.นครราชสีมา)',
    '13': 'จิตอาสาประดิษฐ์งานศิลปะส่งกำลังใจให้ผู้ป่วย (จ.ขอนแก่น)',
    '14': 'โครงการจิตอาสาวาดภาพการ์ตูนนิทานช่วยพัฒนาเยาวชน (จ.นครพนม)',
    '15': 'โครงการจิตอาสาเก็บขยะชายหาดจังหวัดชลบุรี (จ.ชลบุรี)',
    '16': 'โครงการมื้ออาหารกลางวันเพื่อเด็กด้อยโอกาส (จ.อุบลราชธานี)',
    '17': 'โครงการจิตอาสาแบ่งปันเพื่อการเปลี่ยนแปลง (จ.อุดรธานี)',
    '18': 'โครงการจิตอาสาคัดแยกหนังสือส่งต่อให้น้องในพื้นที่ห่างไกล (จ.เชียงใหม่)'
  };

  // 4. สร้าง Modal สำหรับสมัครจิตอาสา (ล็อกชื่อโครงการตามกิจกรรมที่กดสมัคร ไม่ให้ผู้ใช้เปลี่ยนได้)
  function renderVolunteerModal() {
    if (document.getElementById('volunteerModal')) return;

    const modalHTML = `
      <div class="modal fade" id="volunteerModal" tabindex="-1" aria-labelledby="volunteerModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
          <div class="modal-content modal-content-custom">
            <div class="modal-header modal-header-gradient">
              <div>
                <h5 class="modal-title fw-bold" id="volunteerModalLabel">
                  <i class="fa-solid fa-hand-holding-heart me-2"></i> แบบฟอร์มสมัครเข้าร่วมจิตอาสา
                </h5>
                <p class="mb-0 small text-white-50">ร่วมเป็นส่วนหนึ่งในการสร้างการเปลี่ยนแปลงเชิงบวกให้กับสังคม</p>
              </div>
              <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4">
              <form id="volunteerApplyForm" method="POST" action="api.php?action=apply_job">
                <div class="row g-3">
                  <!-- โครงการจิตอาสาที่สมัคร (เปลี่ยนเป็นชื่อโครงการนั้นเลย ล็อกไม่ให้ผู้ใช้เปลี่ยนได้) -->
                  <div class="col-md-12">
                    <label class="form-label-custom">โครงการจิตอาสาที่ต้องการสมัคร <span class="text-danger">*</span></label>
                    <div class="input-group">
                      <span class="input-group-text border-end-0 bg-transparent text-success" style="border-color: var(--border-color);">
                        <i class="fa-solid fa-hand-holding-heart"></i>
                      </span>
                      <input type="text" id="modalActivityDisplay" name="job_title" class="form-control-custom border-start-0 fw-bold" readonly style="background-color: var(--bg-subtle); cursor: not-allowed; color: var(--text-main);" placeholder="กำลังเลือกโครงการ..." value="" required>
                      <input type="hidden" id="modalActivitySelect" name="activity_id" value="">
                    </div>
                    <div class="d-flex align-items-center gap-1 mt-1 text-muted small">
                      <i class="fa-solid fa-lock text-warning"></i>
                      <span>ระบบล็อกชื่อโครงการตามกิจกรรมที่คุณเลือกสมัคร (ไม่สามารถเปลี่ยนโครงการได้)</span>
                    </div>
                  </div>

                  <div class="col-md-6">
                    <label class="form-label-custom">ชื่อ - นามสกุล <span class="text-danger">*</span></label>
                    <input type="text" id="applyFullName" name="fullname" class="form-control-custom" placeholder="เช่น สมชาย ใจดี" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label-custom">หมายเลขโทรศัพท์ <span class="text-danger">*</span></label>
                    <input type="tel" id="applyPhone" name="phone" class="form-control-custom" placeholder="เช่น 0812345678" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label-custom">อีเมล <span class="text-danger">*</span></label>
                    <input type="email" id="applyEmail" name="email" class="form-control-custom" placeholder="example@email.com" required>
                  </div>
                  <div class="col-md-6">
                    <label class="form-label-custom">อายุ (ปี) <span class="text-danger">*</span></label>
                    <input type="number" id="applyAge" name="age" class="form-control-custom" min="12" max="80" placeholder="เช่น 22" required>
                  </div>
                  <div class="col-12">
                    <label class="form-label-custom">ทักษะความสามารถหรือประสบการณ์จิตอาสา (ถ้ามี)</label>
                    <textarea name="skills" id="applySkills" class="form-control-custom" rows="2" placeholder="เช่น ปฐมพยาบาลเบื้องต้น, ช่างไม้, ถ่ายภาพ, สอนหนังสือ"></textarea>
                  </div>
                  <div class="col-12">
                    <div class="form-check mt-1">
                      <input class="form-check-input" type="checkbox" id="termsCheck" required checked>
                      <label class="form-check-label text-muted small" for="termsCheck">
                        ข้าพเจ้าเข้าใจและยินดีปฏิบัติตามกฎระเบียบของโครงการจิตอาสา และยินยอมให้ Ground Team นำข้อมูลไปใช้สำหรับการประสานงาน
                      </label>
                    </div>
                  </div>
                </div>
                <div class="mt-4 text-end">
                  <button type="button" class="btn btn-outline-secondary me-2 rounded-pill px-4" data-bs-dismiss="modal">ยกเลิก</button>
                  <button type="submit" class="btn-primary-gradient">
                    <i class="fa-solid fa-paper-plane me-1"></i> ยืนยันการสมัคร
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
  }

  // 5. สร้าง Modal แจ้งเตือนเมื่อยังไม่ได้ Login (ตามข้อกำหนดที่ 3: ถ้าผู้ใช้ไม่ login จะไม่สามารถสมัครเข้าร่วมโครงการได้)
  function renderLoginRequiredModal() {
    if (document.getElementById('loginRequiredModal')) return;

    const modalHTML = `
      <div class="modal fade" id="loginRequiredModal" tabindex="-1" aria-labelledby="loginRequiredModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
          <div class="modal-content modal-content-custom text-center p-4">
            <div class="my-3">
              <div class="brand-icon mx-auto" style="width: 75px; height: 75px; font-size: 2.2rem; background: var(--color-yellow-soft); color: var(--color-yellow); border: 2px solid var(--color-yellow);">
                <i class="fa-solid fa-lock"></i>
              </div>
            </div>
            <h4 class="fw-bold mb-2 text-main">กรุณาเข้าสู่ระบบก่อนสมัคร</h4>
            <p class="text-muted small mb-4">
              ตามข้อกำหนดของแพลตฟอร์ม <strong>ผู้ใช้จะต้องเข้าสู่ระบบก่อน</strong> จึงจะสามารถสมัครเข้าร่วมโครงการจิตอาสาได้ เพื่อความปลอดภัยและการบันทึกชั่วโมงสะสมเกียรติบัตร
            </p>
            <div class="d-flex flex-column gap-2 mb-3">
              <a href="login.php" class="btn-primary-gradient justify-content-center py-2 fs-6">
                <i class="fa-solid fa-right-to-bracket me-1"></i> เข้าสู่ระบบทันที
              </a>
              <a href="register.php" class="btn btn-outline-custom justify-content-center py-2 fs-6">
                <i class="fa-solid fa-user-plus me-1"></i> ยังไม่มีบัญชี? สมัครสมาชิกใหม่
              </a>
            </div>
            <button type="button" class="btn btn-link text-muted btn-sm text-decoration-none" data-bs-dismiss="modal">
              ปิดหน้าต่าง
            </button>
          </div>
        </div>
      </div>
    `;

    document.body.insertAdjacentHTML('beforeend', modalHTML);
  }

  // 6. ดึงข้อมูลสถานะการเข้าสู่ระบบจาก auth.php และอัปเดต Navbar
  window.groundAuth = { logged_in: false, is_admin: false, user: null };

  function fetchAuthStatus() {
    fetch('auth.php?action=status', { credentials: 'same-origin' })
      .then(res => {
        if (!res.ok) throw new Error('Status network error');
        return res.json();
      })
      .then(data => {
        window.groundAuth = data;
        updateNavbarAuthUI(data);
      })
      .catch(() => {
        // Fallback กรณีเปิดแบบ static file://
        const local = localStorage.getItem('ground_auth_user');
        if (local) {
          try {
            window.groundAuth = JSON.parse(local);
            updateNavbarAuthUI(window.groundAuth);
          } catch (e) { }
        }
      });
  }

  function updateNavbarAuthUI(auth) {
    const desktopSlot = document.getElementById('auth-controls-desktop');
    const mobileSlot = document.getElementById('auth-controls-mobile');

    if (!desktopSlot) return;

    if (auth.logged_in && auth.user) {
      const u = auth.user;
      const isAdmin = auth.is_admin;

      // ข้อกำหนดที่ 4: เมื่อแอดมิน login ไม่ต้องแสดงข้อมูลส่วนตัวของแอดมิน
      if (isAdmin) {
        desktopSlot.innerHTML = `
          <a href="information.php" class="btn btn-warning rounded-pill py-2 px-3 fs-6 d-flex align-items-center gap-2 fw-bold shadow-sm" title="จัดการข้อมูลสมาชิกทั้งหมด (แอดมิน)">
            <i class="fa-solid fa-shield-halved"></i> แอดมิน (ข้อมูลสมาชิก)
          </a>
          <a href="auth.php?action=logout" class="btn btn-outline-danger rounded-pill py-2 px-3 fs-6" title="ออกจากระบบ" onclick="return confirm('ต้องการออกจากระบบหรือไม่?')">
            <i class="fa-solid fa-right-from-bracket me-1"></i> ออกจากระบบ
          </a>
        `;

        if (mobileSlot) {
          mobileSlot.innerHTML = `
            <a href="information.php" class="btn btn-warning w-100 justify-content-center fw-bold py-2 mb-2">
              <i class="fa-solid fa-shield-halved me-1"></i> แอดมิน: จัดการข้อมูลสมาชิก
            </a>
            <a href="auth.php?action=logout" class="btn btn-outline-danger w-100 justify-content-center py-2" onclick="return confirm('ต้องการออกจากระบบหรือไม่?')">
              <i class="fa-solid fa-right-from-bracket me-1"></i> ออกจากระบบ
            </a>
          `;
        }
      } else {
        desktopSlot.innerHTML = `
          <a href="profile.php" class="btn btn-outline-custom rounded-pill py-2 px-3 fs-6 d-flex align-items-center gap-2" title="ดูข้อมูลส่วนตัวของฉัน">
            <i class="fa-solid fa-circle-user text-success fs-5"></i>
            <span>${escapeHTML(u.fullname || u.username)}</span>
          </a>
          <a href="auth.php?action=logout" class="btn btn-outline-danger rounded-pill py-2 px-3 fs-6" title="ออกจากระบบ" onclick="return confirm('ต้องการออกจากระบบหรือไม่?')">
            <i class="fa-solid fa-right-from-bracket"></i>
          </a>
        `;

        if (mobileSlot) {
          mobileSlot.innerHTML = `
            <a href="profile.php" class="btn btn-outline-custom w-100 justify-content-center py-2 mb-2">
              <i class="fa-solid fa-circle-user text-success me-1"></i> ข้อมูลของฉัน (${escapeHTML(u.fullname || u.username)})
            </a>
            <a href="auth.php?action=logout" class="btn btn-outline-danger w-100 justify-content-center py-2" onclick="return confirm('ต้องการออกจากระบบหรือไม่?')">
              <i class="fa-solid fa-right-from-bracket me-1"></i> ออกจากระบบ
            </a>
          `;
        }
      }
    }
  }

  function escapeHTML(str) {
    if (!str) return '';
    return str.replace(/[&<>'"]/g, tag => ({
      '&': '&amp;', '<': '&lt;', '>': '&gt;', "'": '&#39;', '"': '&quot;'
    }[tag] || tag));
  }

  // 7. จัดการ Auth Gate ดักจับการกดสมัครกิจกรรมจิตอาสา
  // ข้อกำหนดที่ 3: "ถ้าผู้ใช้ไม่ login จะไม่สามารถสมัครเข้าร่วมโครงการได้"
  function setupVolunteerApplicationGate() {
    const volunteerModalEl = document.getElementById('volunteerModal');
    const loginReqEl = document.getElementById('loginRequiredModal');

    // ดักฟัง Event 'show.bs.modal' ของ Bootstrap โดยตรง ป้องกันไม่ให้เปิดขึ้นมาได้เด็ดขาดหากไม่ได้ล็อกอิน
    if (volunteerModalEl) {
      volunteerModalEl.addEventListener('show.bs.modal', (e) => {
        if (!window.groundAuth || !window.groundAuth.logged_in) {
          e.preventDefault(); // สั่งยกเลิกการเปิด Modal ทันที
          if (loginReqEl) {
            const reqModalInstance = bootstrap.Modal.getOrCreateInstance(loginReqEl);
            reqModalInstance.show();
          }
          return false;
        }
      });
    }

    // ดักการคลิกปุ่มสมัครเข้าร่วมจิตอาสาทุกปุ่มในหน้าเว็บ
    document.addEventListener('click', (e) => {
      const applyBtn = e.target.closest('[data-bs-target="#volunteerModal"], .btn-quick-apply, .btn-nav-apply');
      if (applyBtn) {
        // หากยังไม่ได้เข้าสู่ระบบ
        if (!window.groundAuth || !window.groundAuth.logged_in) {
          e.preventDefault();
          e.stopPropagation();

          // แสดง Login Required Modal ทันที
          if (loginReqEl) {
            const reqModalInstance = bootstrap.Modal.getOrCreateInstance(loginReqEl);
            reqModalInstance.show();
          }
          return false;
        } else {
          // หากล็อกอินแล้ว ให้ดึงข้อมูล user มากรอกอัตโนมัติ (Auto Pre-fill)
          const user = window.groundAuth.user;
          if (user) {
            const fn = document.getElementById('applyFullName');
            const ph = document.getElementById('applyPhone');
            const em = document.getElementById('applyEmail');
            const ag = document.getElementById('applyAge');
            if (fn && user.fullname) fn.value = user.fullname;
            if (ph && user.phone) ph.value = user.phone;
            if (em && user.email) em.value = user.email;
            if (ag && user.age) ag.value = user.age;
          }

          // ข้อกำหนดที่ 2: เปลี่ยนเป็นชื่อโครงการนั้นเลย ไม่ต้องให้ผู้ใช้เปลี่ยนได้ (ล็อกชื่อโครงการ)
          const actId = applyBtn.getAttribute('data-activity-id');
          let actTitle = '';
          const card = applyBtn.closest('.activity-card') || applyBtn.closest('.activity-item');
          if (card) {
            const titleEl = card.querySelector('.activity-title');
            if (titleEl) actTitle = titleEl.textContent.trim();
          }
          if (!actTitle && actId && activityTitlesMap[actId]) {
            actTitle = activityTitlesMap[actId];
          }

          const displayElem = document.getElementById('modalActivityDisplay');
          const hiddenElem = document.getElementById('modalActivitySelect');
          if (displayElem) {
            displayElem.value = actTitle || 'จิตอาสาเพื่อสังคม Ground Team';
          }
          if (hiddenElem) {
            hiddenElem.value = actId || '1';
          }

          // สั่งเปิด Volunteer Modal สำหรับผู้ใช้ที่ล็อกอินแล้ว
          if (volunteerModalEl) {
            const vModalInstance = bootstrap.Modal.getOrCreateInstance(volunteerModalEl);
            vModalInstance.show();
          }
        }
      }
    }, true); // ใช้ Capture Phase เพื่อดักจับก่อน Bootstrap Event
  }

  // 8. ระบบ Dark / Light Mode ควบคุมผ่าน LocalStorage
  function setupThemeToggle() {
    const savedTheme = localStorage.getItem('ground_theme') || 'light';
    applyTheme(savedTheme);

    const toggleBtns = [
      document.getElementById('theme-toggle-desktop'),
      document.getElementById('theme-toggle-mobile')
    ];

    toggleBtns.forEach(btn => {
      if (!btn) return;
      btn.addEventListener('click', () => {
        const currentTheme = document.documentElement.getAttribute('data-theme') || 'light';
        const newTheme = currentTheme === 'dark' ? 'light' : 'dark';
        applyTheme(newTheme);
      });
    });
  }

  function applyTheme(theme) {
    document.documentElement.setAttribute('data-theme', theme);
    document.documentElement.setAttribute('data-bs-theme', theme);
    localStorage.setItem('ground_theme', theme);

    const icons = document.querySelectorAll('.btn-theme-toggle i');
    icons.forEach(icon => {
      if (theme === 'dark') {
        icon.className = 'fa-solid fa-sun text-warning';
      } else {
        icon.className = 'fa-solid fa-moon';
      }
    });
  }

  // ทำการ Render ทันทีเมื่อ DOM โหลด
  document.addEventListener('DOMContentLoaded', () => {
    renderNavbar();
    renderFooter();
    renderVolunteerModal();
    renderLoginRequiredModal();
    setupThemeToggle();
    fetchAuthStatus();
    setupVolunteerApplicationGate();
  });

})();
