/**
 * ==========================================================================
 * Project: Ground Team - Volunteer & Social Impact Platform
 * Main JavaScript Interactions & Animations
 * ==========================================================================
 * - Preloader Fade Out
 * - Scroll to Top Button
 * - Scroll Reveal Animation (Intersection Observer)
 * - Animated Numbers / Statistics Counter
 * - Volunteer Activity Filter
 * - Form Validation & Toast Notification
 * - Smooth Scrolling
 */

document.addEventListener('DOMContentLoaded', () => {
  'use strict';

  // 1. Loading Preloader Animation
  const preloader = document.getElementById('preloader');
  if (preloader) {
    window.addEventListener('load', () => {
      setTimeout(() => {
        preloader.classList.add('fade-out');
      }, 400);
    });

    // Fallback: หากรูปภาพโหลดช้า บังคับซ่อนหลังจาก 1.5 วินาที
    setTimeout(() => {
      if (!preloader.classList.contains('fade-out')) {
        preloader.classList.add('fade-out');
      }
    }, 1500);
  }

  // 2. Scroll to Top & Navbar Scroll Effects
  const btnScrollTop = document.getElementById('btn-scroll-top');
  const navbar = document.querySelector('.navbar-custom');

  window.addEventListener('scroll', () => {
    const scrollY = window.scrollY;

    // Toggle Scroll to Top button visibility
    if (btnScrollTop) {
      if (scrollY > 350) {
        btnScrollTop.classList.add('show');
      } else {
        btnScrollTop.classList.remove('show');
      }
    }

    // Toggle Navbar shadow and compact size
    if (navbar) {
      if (scrollY > 60) {
        navbar.classList.add('scrolled');
      } else {
        navbar.classList.remove('scrolled');
      }
    }
  });

  if (btnScrollTop) {
    btnScrollTop.addEventListener('click', () => {
      window.scrollTo({
        top: 0,
        behavior: 'smooth'
      });
    });
  }

  // 3. Scroll Reveal Animation (Intersection Observer)
  const revealElements = document.querySelectorAll('.reveal');
  if ('IntersectionObserver' in window && revealElements.length > 0) {
    const revealObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          entry.target.classList.add('active');
          observer.unobserve(entry.target);
        }
      });
    }, {
      root: null,
      threshold: 0.12,
      rootMargin: '0px 0px -40px 0px'
    });

    revealElements.forEach(el => revealObserver.observe(el));
  } else {
    // Fallback สำหรับบราวเซอร์เก่า
    revealElements.forEach(el => el.classList.add('active'));
  }

  // 4. Animated Number Counters
  const counterElements = document.querySelectorAll('.counter-val');
  if ('IntersectionObserver' in window && counterElements.length > 0) {
    const counterObserver = new IntersectionObserver((entries, observer) => {
      entries.forEach(entry => {
        if (entry.isIntersecting) {
          const el = entry.target;
          const target = parseInt(el.getAttribute('data-target'), 10) || 0;
          const duration = 1800; // milliseconds
          const stepTime = 25;
          const steps = duration / stepTime;
          const increment = target / steps;
          let current = 0;

          const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
              el.innerText = target.toLocaleString('en-US');
              clearInterval(timer);
            } else {
              el.innerText = Math.floor(current).toLocaleString('en-US');
            }
          }, stepTime);

          observer.unobserve(el);
        }
      });
    }, { threshold: 0.3 });

    counterElements.forEach(el => counterObserver.observe(el));
  }

  // 5. Activity Search, Category Filter & Pagination Controller (หน้ารวมงานกิจกรรม)
  const activityGrid = document.getElementById('activityGrid');
  const activityItems = Array.from(document.querySelectorAll('.activity-item'));
  const filterBtns = document.querySelectorAll('.btn-filter');
  const searchInput = document.getElementById('activitySearchInput');
  const searchBtn = document.getElementById('activitySearchBtn');
  const clearSearchBtn = document.getElementById('clearSearchBtn');
  const searchActiveIndicator = document.getElementById('searchActiveIndicator');
  const searchKeywordText = document.getElementById('searchKeywordText');
  const noActivityFound = document.getElementById('noActivityFound');
  const resetFilterBtn = document.getElementById('resetFilterBtn');
  const paginationContainer = document.getElementById('activityPagination');
  const totalCountEl = document.getElementById('totalMatchingCount');
  const pageIndicatorEl = document.getElementById('pageIndicatorText');

  if (activityItems.length > 0) {
    // สถานะปัจจุบันของตัวกรองและการแบ่งหน้า
    let currentCategory = 'all';
    let searchQuery = '';
    let currentPage = 1;
    const itemsPerPage = 6; // แสดงหน้าละ 6 รายการ (รวม 12 รายการจะมี 2 หน้า)

    // ตรวจสอบ Parameter จาก URL (เช่น activities.html?cat=nature)
    const urlParams = new URLSearchParams(window.location.search);
    const catParam = urlParams.get('cat');
    if (catParam) {
      currentCategory = catParam;
      filterBtns.forEach(btn => {
        if (btn.getAttribute('data-filter') === catParam) {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
    }

    // ฟังก์ชันหลักในการกรองและแสดงผลกิจกรรมตามหน้า
    function updateActivityView(scrollToTop = false) {
      // 1. คัดกรองรายการที่ตรงกับหมวดหมู่และคำค้นหา
      const query = searchQuery.trim().toLowerCase();
      const matchingItems = activityItems.filter(item => {
        // ตรวจสอบหมวดหมู่ (รองรับหลายหมวดหมู่ใน 1 การ์ด เช่น data-category="recruiting,edu" หรือคั่นด้วยเครื่องหมายจุลภาค/เว้นวรรค)
        const itemCatAttr = item.getAttribute('data-category') || '';
        const itemCategories = itemCatAttr
          .split(/[\s,]+/)
          .map(c => c.trim().toLowerCase())
          .filter(Boolean);

        const matchCategory = (
          currentCategory === 'all' ||
          itemCategories.includes(currentCategory.toLowerCase())
        );

        // ตรวจสอบคำค้นหา (ค้นหาจากข้อความทั้งหมดภายในการ์ด: ชื่อ, รายละเอียด, แท็ก, สถานที่, วันที่)
        let matchSearch = true;
        if (query !== '') {
          const fullCardText = item.textContent.toLowerCase();
          const categoryAttr = (item.getAttribute('data-category') || '').toLowerCase();
          matchSearch = fullCardText.includes(query) || categoryAttr.includes(query);
        }

        return matchCategory && matchSearch;
      });

      const totalItems = matchingItems.length;
      const totalPages = Math.max(1, Math.ceil(totalItems / itemsPerPage));

      // ปรับปรุง currentPage ให้อยู่ในช่วงที่ถูกต้อง
      if (currentPage > totalPages) currentPage = totalPages;
      if (currentPage < 1) currentPage = 1;

      // 2. จัดการการแสดงผลของ Card แต่ละใบ
      const startIndex = (currentPage - 1) * itemsPerPage;
      const endIndex = startIndex + itemsPerPage;

      // ซ่อนทุกรายการก่อน
      activityItems.forEach(item => {
        item.style.display = 'none';
      });

      // แสดงเฉพาะรายการที่อยู่ในหน้าปัจจุบัน
      matchingItems.forEach((item, index) => {
        if (index >= startIndex && index < endIndex) {
          item.style.display = 'block';
          item.style.opacity = '1';
        }
      });

      // 3. ปรับปรุงสถานะสถิติและ Empty State
      if (totalCountEl) totalCountEl.textContent = totalItems;
      if (pageIndicatorEl) {
        if (totalItems > 0) {
          pageIndicatorEl.textContent = `หน้า ${currentPage} จาก ${totalPages}`;
        } else {
          pageIndicatorEl.textContent = 'ไม่พบผลลัพธ์';
        }
      }

      if (noActivityFound) {
        noActivityFound.style.display = totalItems === 0 ? 'block' : 'none';
      }

      // แสดง indicator สถานะคำค้นหา
      if (searchActiveIndicator && searchKeywordText) {
        if (query !== '') {
          searchActiveIndicator.style.display = 'block';
          searchKeywordText.textContent = `"${searchQuery}"`;
        } else {
          searchActiveIndicator.style.display = 'none';
        }
      }

      // 4. สร้างปุ่ม Pagination แบบ Interactive (ออกแบบให้เข้ากับธีมเว็บ)
      renderPagination(totalPages, totalItems);

      // เลื่อนหน้าจอกลับมาที่หัวข้อกิจกรรมอย่างนุ่มนวลเมื่อเปลี่ยนหน้า
      if (scrollToTop) {
        const actSection = document.getElementById('activities-section');
        if (actSection) {
          actSection.scrollIntoView({ behavior: 'smooth', block: 'start' });
        }
      }
    }

    // ฟังก์ชันสร้างปุ่มตัวเลขหน้าและปุ่มก่อนหน้า/ถัดไป ให้เข้ากับธีมเว็บไซต์
    function renderPagination(totalPages, totalItems) {
      if (!paginationContainer) return;

      // หากไม่มีผลลัพธ์ ให้ซ่อน pagination
      if (totalItems === 0) {
        paginationContainer.innerHTML = '';
        paginationContainer.style.display = 'none';
        return;
      }

      paginationContainer.style.display = 'inline-flex';
      let paginationHTML = '';

      // ปุ่ม ก่อนหน้า (Previous)
      const prevDisabled = currentPage <= 1 ? 'disabled' : '';
      paginationHTML += `
        <li class="page-item ${prevDisabled}">
          <a class="page-link page-link-prev" href="#" data-page="${currentPage - 1}" aria-label="Previous" ${prevDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
            <i class="fa-solid fa-chevron-left me-1"></i> ก่อนหน้า
          </a>
        </li>
      `;

      // ปุ่ม ตัวเลขหน้า
      for (let p = 1; p <= totalPages; p++) {
        const activeClass = p === currentPage ? 'active' : '';
        paginationHTML += `
          <li class="page-item ${activeClass}">
            <a class="page-link page-link-num" href="#" data-page="${p}" aria-label="หน้า ${p}">${p}</a>
          </li>
        `;
      }

      // ปุ่ม ถัดไป (Next)
      const nextDisabled = currentPage >= totalPages ? 'disabled' : '';
      paginationHTML += `
        <li class="page-item ${nextDisabled}">
          <a class="page-link page-link-next" href="#" data-page="${currentPage + 1}" aria-label="Next" ${nextDisabled ? 'tabindex="-1" aria-disabled="true"' : ''}>
            ถัดไป <i class="fa-solid fa-chevron-right ms-1"></i>
          </a>
        </li>
      `;

      paginationContainer.innerHTML = paginationHTML;

      // ผูก Event การคลิกเปลี่ยนหน้า
      const pageLinks = paginationContainer.querySelectorAll('.page-link');
      pageLinks.forEach(link => {
        link.addEventListener('click', (e) => {
          e.preventDefault();
          if (link.closest('.page-item.disabled')) return;
          const targetPage = parseInt(link.getAttribute('data-page'), 10);
          if (!isNaN(targetPage) && targetPage >= 1 && targetPage <= totalPages && targetPage !== currentPage) {
            currentPage = targetPage;
            updateActivityView(true);
          }
        });
      });
    }

    // ผูก Event การคลิกแท็บหมวดหมู่
    filterBtns.forEach(btn => {
      btn.addEventListener('click', () => {
        filterBtns.forEach(b => b.classList.remove('active'));
        btn.classList.add('active');
        currentCategory = btn.getAttribute('data-filter') || 'all';
        currentPage = 1; // รีเซ็ตกลับไปหน้า 1
        updateActivityView(false);
      });
    });

    // ผูก Event การค้นหา
    function handleSearch() {
      if (searchInput) {
        searchQuery = searchInput.value.trim();
        currentPage = 1;
        updateActivityView(false);
      }
    }

    if (searchBtn) {
      searchBtn.addEventListener('click', handleSearch);
    }

    if (searchInput) {
      // ค้นหาแบบเรียลไทม์ขณะพิมพ์
      searchInput.addEventListener('input', () => {
        searchQuery = searchInput.value.trim();
        currentPage = 1;
        updateActivityView(false);
      });

      // ค้นหาเมื่อกด Enter
      searchInput.addEventListener('keydown', (e) => {
        if (e.key === 'Enter') {
          e.preventDefault();
          handleSearch();
        }
      });
    }

    // ปุ่มล้างการค้นหา
    function resetSearchAndFilter() {
      if (searchInput) searchInput.value = '';
      searchQuery = '';
      currentCategory = 'all';
      currentPage = 1;
      filterBtns.forEach(btn => {
        if (btn.getAttribute('data-filter') === 'all') {
          btn.classList.add('active');
        } else {
          btn.classList.remove('active');
        }
      });
      updateActivityView(false);
    }

    if (clearSearchBtn) {
      clearSearchBtn.addEventListener('click', resetSearchAndFilter);
    }

    if (resetFilterBtn) {
      resetFilterBtn.addEventListener('click', resetSearchAndFilter);
    }

    // เริ่มต้นแสดงผลครั้งแรก
    updateActivityView(false);
  }

  // 6. Hook Quick Apply Buttons on cards to lock activity in Modal
  document.addEventListener('click', (e) => {
    const applyBtn = e.target.closest('.btn-quick-apply');
    if (applyBtn) {
      const actId = applyBtn.getAttribute('data-activity-id');
      const selectElem = document.getElementById('modalActivitySelect');
      const displayElem = document.getElementById('modalActivityDisplay');
      
      const card = applyBtn.closest('.activity-card') || applyBtn.closest('.activity-item');
      let actTitle = '';
      if (card) {
        const titleEl = card.querySelector('.activity-title');
        if (titleEl) actTitle = titleEl.textContent.trim();
      }

      if (displayElem && actTitle) {
        displayElem.value = actTitle;
      }
      if (selectElem && actId) {
        selectElem.value = actId;
      }
    }
  });

  // 7. Volunteer Application Form Submission
  document.addEventListener('submit', (e) => {
    if (e.target && e.target.id === 'volunteerApplyForm') {
      e.preventDefault();
      const modalEl = document.getElementById('volunteerModal');
      const modalInstance = bootstrap.Modal.getInstance(modalEl);
      if (modalInstance) {
        modalInstance.hide();
      }

      showNotification('🎉 การสมัครเสร็จสมบูรณ์!', 'เจ้าหน้าที่ Ground Team ได้รับข้อมูลของคุณแล้ว และจะติดต่อกลับเพื่อแจ้งรายละเอียดการเข้าร่วมกิจกรรมทางอีเมล/เบอร์โทรศัพท์ ขอขอบคุณที่ร่วมเป็นพลังแห่งการเปลี่ยนแปลง');
      e.target.reset();
    }

    // Contact Form Submission
    if (e.target && e.target.id === 'contactForm') {
      e.preventDefault();
      showNotification('✉️ ส่งข้อความเรียบร้อยแล้ว!', 'ขอบคุณที่ติดต่อ Ground Team ทางทีมงานจะตอบกลับข้อความของคุณภายใน 24 ชั่วโมง');
      e.target.reset();
    }
  });

  // Custom Toast Notification System
  function showNotification(title, message) {
    let toastContainer = document.getElementById('custom-toast-container');
    if (!toastContainer) {
      toastContainer = document.createElement('div');
      toastContainer.id = 'custom-toast-container';
      toastContainer.style.cssText = `
        position: fixed;
        bottom: 24px;
        left: 50%;
        transform: translateX(-50%);
        z-index: 10500;
        display: flex;
        flex-direction: column;
        gap: 10px;
        max-width: 90%;
        width: 480px;
        pointer-events: none;
      `;
      document.body.appendChild(toastContainer);
    }

    const toast = document.createElement('div');
    toast.className = 'custom-toast';
    toast.style.cssText = `
      background: var(--bg-surface, #ffffff);
      color: var(--text-main, #0f172a);
      border-left: 5px solid var(--color-green, #10b981);
      border-radius: 12px;
      padding: 16px 20px;
      box-shadow: 0 15px 35px rgba(0,0,0,0.2);
      border: 1px solid var(--border-color, #e2e8f0);
      pointer-events: auto;
      transform: translateY(20px);
      opacity: 0;
      transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
    `;

    toast.innerHTML = `
      <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 6px;">
        <strong style="font-size: 1.05rem; color: var(--color-green, #10b981);">${title}</strong>
        <button style="border: none; background: transparent; cursor: pointer; color: #94a3b8; font-size: 1.1rem;">&times;</button>
      </div>
      <p style="margin: 0; font-size: 0.9rem; line-height: 1.5; color: var(--text-muted, #64748b);">${message}</p>
    `;

    toastContainer.appendChild(toast);

    // Animate in
    setTimeout(() => {
      toast.style.transform = 'translateY(0)';
      toast.style.opacity = '1';
    }, 20);

    const closeBtn = toast.querySelector('button');
    closeBtn.addEventListener('click', () => removeToast(toast));

    // Auto remove after 5 seconds
    setTimeout(() => {
      removeToast(toast);
    }, 5500);

    function removeToast(el) {
      el.style.transform = 'translateY(20px)';
      el.style.opacity = '0';
      setTimeout(() => {
        if (el.parentNode) el.parentNode.removeChild(el);
      }, 400);
    }
  }

});

