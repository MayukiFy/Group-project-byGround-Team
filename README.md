# Ground Team - แพลตฟอร์มอาสาสมัคร & กิจกรรมเพื่อสังคม (Volunteer Platform)

เว็บไซต์แพลตฟอร์มศูนย์รวมงานจิตอาสาและกิจกรรมเพื่อสังคมและสิ่งแวดล้อม ออกแบบตามหลัก UI/UX สมัยใหม่ (Modern, Creative, Minimal, Premium Gradient) ในธีมสีธรรมชาติ **เขียว - ฟ้า - เหลือง** พร้อมระบบสมาชิกและการควบคุมสิทธิ์เต็มรูปแบบ

---

## 📁 โครงสร้างโปรเจกต์ (Project Structure)

```text
Group project byGround Team/
├── index.html              # หน้าแรก (Home) พร้อมฟอร์มสมัครสมาชิกรับข้อมูล
├── activities.html         # หน้างานจิตอาสาทั้งหมด พร้อมระบบค้นหาและ Filter
├── how-to-apply.html       # หน้าวิธีสมัคร, เช็กลิสต์การเตรียมตัว และ FAQ
├── about.html              # หน้าเกี่ยวกับเรา วิสัยทัศน์ พันธกิจ และทีมงาน
├── contact.html            # หน้าติดต่อเรา ฟอร์มส่งข้อความ และแผนที่
├── login.php               # หน้าเข้าสู่ระบบสำหรับ User และ Admin
├── register.php            # หน้าสมัครสมาชิกใหม่ (บันทึกข้อมูลและส่งไป information.php)
├── profile.php             # ★ หน้าให้ผู้ใช้ดูข้อมูลส่วนตัวของตนเอง
├── information.php         # ★ หน้าจัดการข้อมูลสมาชิก (แอดมินดูได้เท่านั้น)
├── auth.php                # ตัวควบคุมระบบ Authentication, Session & สิทธิ์การเข้าถึง
├── data/
│   └── users.json          # ไฟล์ฐานข้อมูลจัดเก็บสมาชิกและแอดมิน
├── README.md               # คู่มือการใช้งานและโครงสร้างโปรเจกต์
└── assets/
    ├── css/
    │   └── style.css       # สไตล์ชีตหลัก, Dark/Light mode, Animations
    ├── js/
    │   ├── components.js   # Navbar, Footer, Auth Gate & Dynamic User UI
    │   └── main.js         # Interactive features (Preloader, Counter, Scroll Reveal)
    └── images/             # โฟลเดอร์เก็บรูปภาพ
```

---

## 🔐 บัญชีสำหรับเข้าสู่ระบบและทดสอบ (Authentication Credentials)

1. **ผู้ดูแลระบบ (Admin)**
   - **Username**: `admin`
   - **Password**: `0000`
   - สิทธิ์: เข้าถึงหน้า `information.php` เพื่อดูข้อมูลสมาชิกทั้งหมด ค้นหา กรองข้อมูลตามเพศ/จังหวัด และลบสมาชิกได้
2. **สมาชิกทั่วไป (User)**
   - **Username**: `somchai`
   - **Password**: `1234`
   - หรือสามารถกด **"สมัครสมาชิกใหม่"** ได้ทันที

---

## 🌟 ฟังก์ชันใหม่ตามข้อกำหนด

### 1. ระบบสมัครสมาชิก & จัดเก็บข้อมูลส่งไปยัง `information.php` (แอดมินดูได้เท่านั้น)
- ฟอร์มสมัครสมาชิกรองรับและจัดเก็บข้อมูลครบถ้วน:
  - **Username**
  - **Password**
  - **ชื่อ - สกุล**
  - **เบอร์โทรศัพท์**
  - **Email**
  - **จังหวัดที่อยู่** (รายการตัวเลือก dropdown ครบ 77 จังหวัด)
  - **อายุ**
  - **เพศ** (ตัวเลือกเป็น `ชาย` / `หญิง` / `lgbtq+`)
- ข้อมูลทั้งหมดจะถูกบันทึกลงในระบบ ซึ่งแอดมินสามารถดูรายชื่อสมาชิกทั้งหมดได้ที่หน้า [information.php](file:///c:/xampp/htdocs/Group%20project%20byGround%20Team/information.php)
- หากผู้ใช้ทั่วไปหรือคนที่ยังไม่ได้ล็อกอินพยายามเข้า `information.php` ระบบจะทำการล็อกสิทธิ์และแสดงหน้าแจ้งเตือนทันที

### 2. หน้าให้ผู้ใช้ดูข้อมูลตัวเองได้ ([profile.php](file:///c:/xampp/htdocs/Group%20project%20byGround%20Team/profile.php))
- แสดงข้อมูลโปรไฟล์ของผู้ใช้ที่ล็อกอินอยู่: Username, ชื่อ-สกุล, เบอร์โทร, Email, จังหวัด, อายุ, เพศ พร้อม Badge สถานะ
- แสดงสถิติจิตอาสาและชั่วโมงสะสม
- มีปุ่มทางลัดไปยังหน้าสมัครกิจกรรมจิตอาสา และปุ่มออกจากระบบ

### 3. ระบบเข้าสู่ระบบ (Login) สำหรับ User และ Admin ([login.php](file:///c:/xampp/htdocs/Group%20project%20byGround%20Team/login.php))
- ตรวจสอบสิทธิ์อัตโนมัติ: หากเป็นแอดมินนำทางไปยัง `information.php`, หากเป็นสมาชิกทั่วไปนำทางไปยัง `profile.php`

### 4. การจำกัดสิทธิ์การสมัครโครงการ (Auth Gate)
- **ถ้าผู้ใช้ไม่ Login จะไม่สามารถสมัครเข้าร่วมโครงการได้**
- เมื่อกดปุ่ม "สมัครจิตอาสา" หรือ "สมัครร่วมโครงการนี้" ระบบจะตรวจสถานะการล็อกอินอัตโนมัติ:
  - หากยังไม่ได้ Login: จะบล็อกการเปิดแบบฟอร์มสมัคร และเปิดป๊อปอัปแจ้งเตือนให้เข้าสู่ระบบหรือสมัครสมาชิกก่อน
  - หาก Login แล้ว: จะเปิดแบบฟอร์มสมัครจิตอาสา พร้อมดึงข้อมูลชื่อ เบอร์โทร และอีเมลของผู้ใช้มากรอกให้โดยอัตโนมัติ (Auto Pre-fill)

---

## 🗄️ โครงสร้างฐานข้อมูล (Database Design)

- **Database Name**: `groundteam`
- **ไฟล์ SQL Schema**: [groundteam.sql](file:///c:/xampp/htdocs/Group%20project%20byGround%20Team/groundteam.sql)
- **ไฟล์จัดการการเชื่อมต่อและ Data Helper**: [db.php](file:///c:/xampp/htdocs/Group%20project%20byGround%20Team/db.php)
- **API Controller สำหรับตอบโต้ระหว่างผู้ใช้กับแอดมิน**: [api.php](file:///c:/xampp/htdocs/Group%20project%20byGround%20Team/api.php)

### 1. Table `user` (รับข้อมูลของผู้ใช้และแอดมิน)
| ชื่อคอลัมน์ (Field) | ประเภทข้อมูล (Type) | คำอธิบาย (Description) |
|---|---|---|
| `id` | INT AUTO_INCREMENT | รหัสลำดับสมาชิก (Primary Key) |
| `username` | VARCHAR(50) UNIQUE | ชื่อผู้ใช้สำหรับเข้าสู่ระบบ |
| `password` | VARCHAR(255) | รหัสผ่านสำหรับเข้าสู่ระบบ |
| `fullname` | VARCHAR(150) | ชื่อ - สกุล |
| `phone` | VARCHAR(20) | หมายเลขโทรศัพท์ |
| `email` | VARCHAR(100) | อีเมล |
| `province` | VARCHAR(100) | จังหวัดที่อยู่ (รายการตัวเลือก) |
| `age` | INT(3) | อายุ |
| `gender` | ENUM('ชาย', 'หญิง', 'lgbtq+') | เพศ |
| `role` | VARCHAR(20) | สิทธิ์ผู้ใช้งาน (`admin` / `user`) |
| `created_at` | DATETIME | วันที่และเวลาที่สมัครสมาชิก |

### 2. Table `job` (ข้อมูลกิจกรรมจิตอาสา - แอดมินเพิ่มและแก้ไขได้)
| ชื่อคอลัมน์ (Field) | ประเภทข้อมูล (Type) | คำอธิบาย (Description) |
|---|---|---|
| `id` | INT AUTO_INCREMENT | รหัสกิจกรรมจิตอาสา (Primary Key) |
| `title` | VARCHAR(255) | ชื่อโครงการจิตอาสา |
| `description` | TEXT | รายละเอียดของกิจกรรม |
| `category` | VARCHAR(100) | หมวดหมู่ (เช่น recruiting, nature, edu, food, donate, artificial) |
| `event_date` | VARCHAR(100) | วันที่จัดกิจกรรม |
| `event_time` | VARCHAR(100) | เวลาจัดกิจกรรม |
| `location` | VARCHAR(150) | สถานที่จัดกิจกรรม / จังหวัด |
| `hours` | INT(5) | จำนวนชั่วโมงจิตอาสาที่ได้รับ |
| `max_quota` | INT(5) | จำนวนรับสมัครทั้งหมด (คน) |
| `current_quota` | INT(5) | จำนวนที่สมัครแล้ว (คน) |
| `image_url` | VARCHAR(255) | พาธรูปภาพประกอบกิจกรรม (อัปโหลดรูปภาพใหม่เสมอ) |
| `status` | ENUM('open', 'closed') | สถานะการเปิดรับสมัคร |
| `created_at` | DATETIME | วันเวลาที่เพิ่มกิจกรรม |
| `updated_at` | DATETIME | วันเวลาที่แก้ไขล่าสุด |

> **หมายเหตุพิเศษสำหรับการเพิ่มกิจกรรม:** แอดมินต้องอัปโหลดรูปภาพใหม่เข้าไปเท่านั้น ระบบรองรับไฟล์รูปภาพ JPG, JPEG, PNG, WEBP และจะสร้างชื่อไฟล์แบบ Unique อัตโนมัติในโฟลเดอร์ `assets/images/`

### 3. Table `application` (รับข้อมูลที่ผู้ใช้สมัครกิจกรรม - แอดมินตอบรับได้)
| ชื่อคอลัมน์ (Field) | ประเภทข้อมูล (Type) | คำอธิบาย (Description) |
|---|---|---|
| `id` | INT AUTO_INCREMENT | รหัสการสมัคร (Primary Key) |
| `user_id` | INT(11) | รหัสผู้ใช้งาน (FK user.id หรือ null สำหรับบุคคลทั่วไป) |
| `job_id` | INT(11) | รหัสกิจกรรมที่สมัคร (FK job.id) |
| `job_title` | VARCHAR(255) | ชื่อโครงการจิตอาสาที่สมัคร |
| `fullname` | VARCHAR(150) | ชื่อ-สกุลผู้สมัคร |
| `phone` | VARCHAR(20) | หมายเลขโทรศัพท์ |
| `email` | VARCHAR(100) | อีเมล |
| `age` | INT(3) | อายุ |
| `skills` | TEXT | ทักษะความสามารถหรือประสบการณ์จิตอาสา |
| `status` | ENUM('pending', 'approved', 'rejected') | สถานะการตอบรับ (`pending`=รอตอบรับ, `approved`=ตอบรับแล้ว, `rejected`=ปฏิเสธ) |
| `admin_notes` | TEXT | ข้อความตอบกลับหรือคำแนะนำจากแอดมิน |
| `applied_at` | DATETIME | วันเวลาที่สมัคร |
| `updated_at` | DATETIME | วันเวลาที่แอดมินตอบรับหรืออัปเดต |

### 4. Table `contact` (รับข้อมูลในหน้า Contact ที่ผู้ใช้ส่งให้เรา)
| ชื่อคอลัมน์ (Field) | ประเภทข้อมูล (Type) | คำอธิบาย (Description) |
|---|---|---|
| `id` | INT AUTO_INCREMENT | รหัสข้อความติดต่อ (Primary Key) |
| `name` | VARCHAR(150) | ชื่อ-นามสกุลผู้ส่ง |
| `email` | VARCHAR(100) | อีเมลติดต่อกลับ |
| `phone` | VARCHAR(20) | เบอร์โทรศัพท์ |
| `subject` | VARCHAR(150) | หัวข้อที่ติดต่อ |
| `message` | TEXT | ข้อความรายละเอียดที่ส่งถึงเรา |
| `status` | ENUM('unread', 'read', 'replied') | สถานะข้อความ (`unread`=ยังไม่อ่าน, `read`=อ่านแล้ว, `replied`=ตอบกลับแล้ว) |
| `reply_message` | TEXT | ข้อความตอบกลับหรือบันทึกของแอดมิน |
| `created_at` | DATETIME | วันเวลาที่ส่งข้อความ |

---

## 👥 สมาชิกผู้จัดทำโครงการ (Team Members)

1. **ชยันต์ บูชาพันธ์** (รหัสนิสิต: 67011213014) - หัวหน้าโครงการ / ผู้รับผิดชอบหลัก
2. **นัฐภูมิ ธงศรี** (รหัสนิสิต: 67011213047) - ผู้พัฒนาและออกแบบเว็บไซต์
3. **อลิน ฉ่ำมณี** (รหัสนิสิต: 67011213149) - ผู้รวบรวมข้อมูล
4. **สวิตต์ วงศสิงห์** (รหัสนิสิต: 67011213146) - ผู้ประสานงานและจัดเตรียมเนื้อหา

---

<div align="center">
  <p>© 2026 Group project | Designed by Ground Team</p>
</div>

