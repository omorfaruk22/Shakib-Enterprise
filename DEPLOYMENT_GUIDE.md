# 🚀 cPanel / Shared Hosting Deployment Guide

SAKIB ENTERPRISE ওয়েবসাইট যেকোনো সাধারণ cPanel হোস্টিং (Hostinger, ExonHost, Namecheap, Bluehost, ইত্যাদি) এ ১০ মিনিটে লাইভ করা যায়। কোনো VPS বা SSH দরকার নেই।

---

## 📋 প্রয়োজনীয়তা
- PHP 8.0 বা তার উপরে (৮.২ recommended)
- MySQL 5.7 বা MariaDB 10.3+
- PDO, GD, JSON extensions (সাধারণত সব হোস্টিং-এ থাকে)
- mod_rewrite enabled (cPanel/Apache-এ ডিফল্ট থাকে)
- writable `storage/` এবং `public/assets/uploads/` ফোল্ডার

---

## 📤 ধাপ ১: ফাইল আপলোড করা

### বিকল্প A — Git Clone (যদি Git access থাকে):
```bash
git clone https://github.com/omorfaruk22/Shakib-Enterprise.git
```

### বিকল্প B — ZIP আপলোড (সহজ):
1. https://github.com/omorfaruk22/Shakib-Enterprise এ যান
2. **<> Code → Download ZIP**
3. cPanel → **File Manager** খুলুন
4. `public_html` ফোল্ডারে প্রবেশ করুন
5. ZIP ফাইলটি আপলোড করুন → **Extract** করুন
6. এক্সট্র্যাক্ট হওয়া `Shakib-Enterprise-main` ফোল্ডারের ভেতরের সব ফাইল কেটে `public_html`-এর রুটে নিয়ে আসুন

### ফাইল স্ট্রাকচার হবে এমন:
```
public_html/
├── app/
├── config/
├── database/
├── public/           ← assets/, .htaccess, index.php
├── resources/
├── routes/
├── storage/
├── bootstrap.php
├── install.php
├── .htaccess
└── .env.example
```

> ⚠️ **গুরুত্বপূর্ণ:** যদি সাবডোমেইন বা সাবফোল্ডারে ইন্সটল করেন (যেমন `public_html/sakib/`), তাহলে `.htaccess` ফাইল এডিট করে `RewriteBase /sakib/` বসাতে হবে।

---

## 🗄️ ধাপ ২: Database তৈরি করা

1. cPanel → **MySQL Databases**
2. **Create New Database:** একটা নাম দিন (যেমন `u123_sakib`) → Create
3. **Add New User:** নতুন ইউজার তৈরি করুন (নাম+পাসওয়ার্ড মনে রাখুন)
4. **Add User to Database:** ইউজারকে ডাটাবেসে **All Privileges** দিয়ে যোগ করুন

মনে রাখুন:
- Database name: (cPanel username prefix সহ, যেমন `u123_sakib`)
- Database username: (একই prefix সহ, যেমন `u123_sakibusr`)
- Database password: (আপনার সেট করা)
- Host: সাধারণত `localhost`

---

## ⚙️ ধাপ ৩: .env কনফিগ করা

1. File Manager-এ `public_html` এ গিয়ে `.env.example` ফাইলটি কপি করে `.env` নামে নতুন ফাইল তৈরি করুন
2. `.env` ফাইলটি Edit করে নিচের মতো তথ্য বসান:

```env
APP_NAME="SAKIB ENTERPRISE"
APP_URL=https://your-domain.com
APP_ENV=production
APP_DEBUG=false

DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=u123_sakib
DB_USERNAME=u123_sakibusr
DB_PASSWORD=your_db_password_here

SESSION_SECURE=true
SESSION_LIFETIME=7200
UPLOAD_MAX_SIZE=5242880
CSRF_TOKEN_EXPIRY=7200
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT=900
RATE_LIMIT_PER_MINUTE=30
```

> ⚠️ **APP_DEBUG=false** করতে ভুলবেন না — লাইভ সাইটে error details দেখানো নিরাপদ নয়।
> ⚠️ **SESSION_SECURE=true** HTTPS সাইটের জন্য জরুরি।

---

## 🔐 ধাপ ৪: Folder Permission (CHMOD)

File Manager-এ নিচের ফোল্ডারগুলো select করে **Permissions** এ 755 (recursive) দিন:
- `storage/` (সব সাবফোল্ডারসহ)
- `public/assets/uploads/`

যদি image upload বা cache-এ error আসে, temporarily 777 দিন (কিন্তু production-এ 755 ই ভালো)।

---

## 🚀 ধাপ ৫: Installer চালানো

1. ব্রাউজারে যান: `https://your-domain.com/install.php`
2. সবকিছু সবুজ ✅ দেখাচ্ছে কিনা চেক করুন
3. আপনার Super Admin তথ্য দিন (নাম, ইমেইল, পাসওয়ার্ড)
4. **Install & Create Admin Account** ক্লিক করুন
5. সফল হলে সব টেবিল তৈরি হয়ে যাবে

---

## ⚠️ ধাপ ৬: install.php ডিলিট করা

**সবচেয়ে গুরুত্বপূর্ণ ধাপ:** ইন্সটল সফল হওয়ার মাত্রই File Manager থেকে `install.php` ফাইলটি ডিলিট করে দিন।
এটা না করলে কেউ সাইট রিইন্সটল করে আপনার admin password reset করতে পারবে।

---

## ✅ ধাপ ৭: ওয়েবসাইট চেক করা

| পেজ | URL |
|-----|-----|
| হোমপেজ | `https://your-domain.com` |
| Admin Login | `https://your-domain.com/admin/login` |
| Contact | `https://your-domain.com/contact` |
| Tours | `https://your-domain.com/tours` |

Admin login দিয়ে প্রথমে এই কাজগুলো করুন:
1. **Settings** → ফোন, WhatsApp, ঠিকানা, email সেট করুন
2. **Destinations** → Cox's Bazar, Saint Martin, Teknaf, Bandarban যোগ করুন
3. **Services** → Air/Ship/Bus Ticket service তিনটি edit করুন (ছবি/বিবরণ)
4. **Tour Packages** → নতুন ট্যুর প্যাকেজ যোগ করুন
5. **Holiday Packages** → হলিডে প্যাকেজ যোগ করুন
6. **Ship Routes** → কক্সবাজার-সেন্টমার্টিন রুট যোগ করুন
7. **Testimonials** → কয়েকটি review যোগ করুন
8. **Why Us Cards** / **Process Steps** / **Company Stats** → ইচ্ছেমতো পরিবর্তন করুন (default content ই আছে)

---

## 📧 ধাপ ৮: Email/SMS/WhatsApp (optional)

- **WhatsApp:** Settings-এ আপনার WhatsApp number দিন (880 দিয়ে শুরু করে) → floating button automatically দেখাবে
- **Email (SMTP):** ভবিষ্যতে যোগ করা হবে; বর্তমানে সব inquiry admin panel-এ দেখা যাবে + WhatsApp-এ ক্লিক করে মেসেজ পাঠানো যাবে

---

## 🔍 Troubleshooting

| সমস্যা | সমাধান |
|--------|--------|
| **500 Internal Server Error** | `.htaccess` আছে কিনা দেখুন; `storage/` permission 755 দিন; `.env`-এ DB info চেক করুন |
| **CSS/Image লোড হচ্ছে না** | `public/.htaccess` আছে কিনা; Apache mod_rewrite enabled কিনা; cPanel → MultiPHP Manager → PHP version চেক |
| **Database connection error** | DB host সাধারণত `localhost`; username/password/db name-এ cPanel prefix সহ দিন |
| **404 on all pages except homepage** | `public/.htaccess` ও root `.htaccess` উভয়ই আপলোড হয়েছে কিনা |
| **CSRF mismatch** | Cookie domain চেক; HTTPS-এ `SESSION_SECURE=true`; session cookie work করছে কিনা |
| **File upload not working** | `public/assets/uploads/` permission 755/777 দিন; PHP `upload_max_filesize` চেক |
| **White screen only** | `APP_DEBUG=true` করে error দেখুন; সমাধান করে আবার `false` করুন |

---

## 🔒 Production Security Checklist

- [x] `install.php` ডিলিট করেছেন
- [x] `.env` file public access-এ নেই (সার্ভার root-এ রেখেছেন, document root-এ নয়)
- [x] `APP_DEBUG=false`
- [x] `SESSION_SECURE=true` (HTTPS এ)
- [x] Strong admin password ব্যবহার করেছেন (অক্ষর+সংখ্যা+চিহ্ন)
- [x] `storage/` ও `uploads/` বাদে সব ফোল্ডার writable নয়
- [x] PHP version 8.0+ ব্যবহার করছেন
- [x] Regular database backup নিচ্ছেন (cPanel → Backup Wizard)

---

## 📊 Post-Launch

1. **Google Search Console** এ সাইট সাবমিট করুন (sitemap: `https://your-domain.com/sitemap.xml`)
2. Facebook/Instagram page লিঙ্ক করুন
3. WhatsApp number ভেরিফাই করুন
4. প্রথম কিছু দিন bookings/check test করুন
5. cPanel থেকে weekly backup চালু করুন

---

## 🆘 সমস্যা হলে

কোনো error বা সমস্যা হলে:
1. `.env`-এ `APP_DEBUG=true` করে error message দেখুন
2. `storage/logs/` এ log file চেক করুন
3. আমাকে error message-টা জানান, সাথে সাথে সমাধান দিচ্ছি।
