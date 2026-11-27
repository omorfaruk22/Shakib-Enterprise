# SAKIB ENTERPRISE — Local PC তে সেটআপ গাইড (Windows)

> পুরো গাইডটি ধাপে ধাপে অনুসরণ করুন — ১০ মিনিটের মধ্যে সাইট চলবে!

---

## ✅ প্রয়োজনীয় জিনিসপত্র
- Windows 10/11
- ইন্টারনেট সংযোগ (ডাউনলোডের জন্য)
- WinRAR বা 7-Zip (ZIP extract করার জন্য)

---

## 📥 ধাপ ১: কোড ডাউনলোড করা

### বিকল্প A — ZIP ডাউনলোড (সবচেয়ে সহজ):
1. ব্রাউজারে এই লিঙ্কে যান:
   ```
   https://github.com/omorfaruk22/Shakib-Enterprise
   ```
2. সবুজ **"<> Code"** বাটনে ক্লিক করুন
3. **"Download ZIP"** এ ক্লিক করুন
4. `Shakib-Enterprise-main.zip` ডাউনলোড হবে
5. ZIP ফাইলে রাইট-ক্লিক করে **"Extract All..."** বা **"Extract Here"** করুন

### বিকল্প B — Git দিয়ে (যদি Git থাকে):
```bash
git clone https://github.com/omorfaruk22/Shakib-Enterprise.git
```

---

## 🛠️ ধাপ ২: XAMPP ডাউনলোড ও ইনস্টল করা

1. এই লিঙ্কে যান:
   ```
   https://www.apachefriends.org/download.html
   ```
2. **PHP 8.2** ভার্সন সহ XAMPP for Windows ডাউনলোড করুন
3. ডাউনলোড করা ইনস্টলার ফাইলে ডাবল-ক্লিক করে ইনস্টল করুন
4. ইনস্টলেশনের সময় সবকিছু ডিফল্ট রেখে **Next** করতে থাকুন
   - Install path: `C:\xampp` (এইটাই রাখুন)
   - Components: Apache + MySQL + PHP + phpMyAdmin (এগুলো অবশ্যই চেক থাকতে হবে)
5. ইনস্টল শেষে **"Finish"** ক্লিক করুন

---

## ▶️ ধাপ ৩: XAMPP চালু করা

1. **XAMPP Control Panel** ওপেন করুন (Desktop shortcut বা Start Menu থেকে)
2. **Apache** এর ডান পাশে **"Start"** বাটনে ক্লিক করুন
   - ✅ Apache সবুজ রঙে "Running" দেখাবে
3. **MySQL** এর ডান পাশে **"Start"** বাটনে ক্লিক করুন
   - ✅ MySQL সবুজ রঙে "Running" দেখাবে
4. যদি Windows Firewall popup আসে, **"Allow access"** ক্লিক করুন

> ❌ সমস্যা: Apache start হচ্ছে না → সম্ভবত Skype বা অন্য প্রোগ্রাম ৮০ port ব্যবহার করছে।
> Skype বন্ধ করুন, তারপর আবার Apache Start করুন।

---

## 📁 ধাপ ৪: প্রজেক্ট ফাইল সঠিক জায়গায় রাখা

1. `C:\xampp\htdocs\` ফোল্ডারে যান
2. `htdocs` এর ভেতরে নতুন একটা ফোল্ডার তৈরি করুন, নাম দিন **`sakib`**
3. এখন যে ফোল্ডারটি GitHub থেকে ডাউনলোড/extract করেছেন (যেখানে `app`, `public`, `resources`, `install.php` ফাইলগুলো আছে), সেই সব ফাইল কপি করুন
4. কপি করা সব ফাইল `C:\xampp\htdocs\sakib\` ফোল্ডারে পেস্ট করুন
5. ফাইল স্ট্রাকচার এমন হবে:
   ```
   C:\xampp\htdocs\sakib\
   ├── app\
   ├── config\
   ├── database\
   ├── docs\
   ├── public\
   │   ├── assets\
   │   ├── index.php
   │   └── .htaccess
   ├── resources\
   │   └── views\
   ├── routes\
   ├── storage\
   ├── tests\
   ├── bootstrap.php
   ├── install.php
   ├── README.md
   └── .env.example
   ```
6. **গুরুত্বপূর্ণ সাফাই:**
   - ❌ `api/` ফোল্ডারটি ডিলিট করুন (Vercel এর জন্য ছিলো)
   - ❌ `vercel.json` ফাইলটি ডিলিট করুন
   - ❌ `.vercelignore` ফাইলটি ডিলিট করুন
   - ❌ `preview-homepage.jpg` (যদি থাকে) ডিলিট করুন
   - ❌ `.env` ফাইল (যদি থাকে) ডিলিট করুন — আমরা নতুন বানাবো

---

## ⚙️ ধাপ ৫: .env কনফিগ ফাইল তৈরি করা

1. `C:\xampp\htdocs\sakib\` ফোল্ডারে যান
2. `.env.example` ফাইলটির কপি তৈরি করুন (Copy → Paste)
3. কপি করা ফাইলের নাম পরিবর্তন করে **`.env`** রাখুন
   - ⚠️ Windows-এ শুধু `.env.` লিখুন (শেষে ডট দিয়ে) — তাহলে স্বয়ংক্রিয়ভাবে `.env` হয়ে যাবে
   - অথবা Notepad দিয়ে Save As করুন, File name: `.env`, Save as type: All Files
4. `.env` ফাইলটি Notepad বা VS Code দিয়ে খুলুন
5. পুরো কন্টেন্ট মুছে নিচের কোডটি পেস্ট করুন:

```env
APP_NAME="SAKIB ENTERPRISE"
APP_URL=http://localhost/sakib
APP_ENV=local
APP_DEBUG=true

DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=sakib_enterprise
DB_USERNAME=root
DB_PASSWORD=

SESSION_SECURE=false
SESSION_LIFETIME=7200
UPLOAD_MAX_SIZE=5242880
CSRF_TOKEN_EXPIRY=7200
LOGIN_MAX_ATTEMPTS=5
LOGIN_LOCKOUT=900
RATE_LIMIT_PER_MINUTE=30
```

6. ফাইল সেভ করুন (Ctrl+S)

> 💡 **ব্যাখ্যা:** XAMPP-এ MySQL-এর default username `root`, password ফাঁকা থাকে — এটাই ব্যবহার করছি।

---

## 🗄️ ধাপ ৬: Database তৈরি করা

1. ব্রাউজারে এই লিঙ্কে যান:
   ```
   http://localhost/phpmyadmin
   ```
2. বাম পাশের সাইডবার থেকে **"New"** (নতুন) ক্লিক করুন
3. **Database name:** বক্সে লিখুন: `sakib_enterprise`
4. ডান পাশের dropdown থেকে **Collation** বেছে নিন: `utf8mb4_unicode_ci`
5. **"Create"** (তৈরি করুন) বাটনে ক্লিক করুন
6. ✅ Database তৈরি হয়ে গেলে বাম পাশে `sakib_enterprise` দেখাবে

> ❌ "Access denied" error আসলে: XAMPP Control Panel এ MySQL চলছে কিনা চেক করুন।

---

## 🔑 ধাপ ৭: Folder Permission চেক করা (Windows)

Windows-এ সাধারণত দরকার হয় না, তবে যদি লগ/ক্যাশ/আপলোড এরর আসে:
1. `C:\xampp\htdocs\sakib\storage` ফোল্ডারে রাইট-ক্লিক → **Properties**
2. **Security** ট্যাবে যান
3. **Edit...** ক্লিক করুন
4. **Everyone** user কে খুঁজে (না থাকলে Add করুন), **Full control** চেক করুন
5. **Apply** → **OK**
6. একইভাবে `public/assets/uploads` ফোল্ডারের জন্য করুন

---

## 🚀 ধাপ ৮: ইনস্টলেশন চালানো

1. ব্রাউজারে নতুন ট্যাব খুলে এই লিঙ্কে যান:
   ```
   http://localhost/sakib/install.php
   ```
2. আপনি "SAKIB ENTERPRISE - Installation" পেজ দেখতে পাবেন
3. পেজটি সবকিছু সবুজ ✅ দেখাচ্ছে কিনা চেক করুন:
   - PHP Version: 8.2+ ✅
   - PDO Extension: ✅
   - storage/ writable: ✅
   - public/assets/uploads/ writable: ✅
   - .env writable: ✅
4. নিচের ফর্মে Super Admin তথ্য দিন:
   - **Full Name:** (আপনার নাম, যেমন: `Md. Omar Faruk`)
   - **Email:** (আপনার যেকোনো ইমেইল — Gmail, Yahoo, Outlook সব চলবে)
     - যেমন: `sakib.enterprise.bd@gmail.com`
   - **Password:** (কমপক্ষে ৬ অক্ষর, মনে রাখার মতো)
   - **Confirm Password:** (একই পাসওয়ার্ড আবার)
5. **"Install & Create Admin Account"** বাটনে ক্লিক করুন
6. ✅ "Installation successful!" মেসেজ দেখালে সফল!

> ⚠️ **গুরুত্বপূর্ণ:** ইনস্টল সফল হওয়ার মাত্রই `C:\xampp\htdocs\sakib\install.php` ফাইলটি ডিলিট করে দিন। (এটা security এর জন্য জরুরি)

---

## 🌐 ধাপ ৯: ওয়েবসাইট ও Admin Panel চেক করা

ব্রাউজারে নিচের লিঙ্কগুলোতে গিয়ে চেক করুন:

| পেজ | URL |
|------|-----|
| 🏠 **হোমপেজ** | http://localhost/sakib |
| ✈️ **Air Ticket** | http://localhost/sakib/air-ticket |
| 🚢 **Ship Ticket** | http://localhost/sakib/ship-ticket |
| 🚌 **Bus Ticket** | http://localhost/sakib/bus-ticket |
| 🏝️ **Tours** | http://localhost/sakib/tours |
| 🌴 **Holiday Packages** | http://localhost/sakib/holidays |
| 📞 **Contact** | http://localhost/sakib/contact |
| 🔐 **Admin Login** | http://localhost/sakib/admin/login |
| 📝 **Customer Register** | http://localhost/sakib/register |
| 👤 **Customer Login** | http://localhost/sakib/login |

### Admin Login করার জন্য:
1. http://localhost/sakib/admin/login এ যান
2. যে ইমেইল ও পাসওয়ার্ড ধাপ ৮-এ দিয়েছেন সেটা দিন
3. **Sign In** ক্লিক করুন
4. Dashboard ওপেন হবে!

---

## 📝 ধাপ ১০: ওয়েবসাইটে কন্টেন্ট যোগ করা

Admin panel এ লগইন করে এই কাজগুলো করুন:

### ১️⃣ Settings (সাইটের মৌলিক তথ্য):
- Settings এ গিয়ে কোম্পানির ফোন, WhatsApp, ঠিকানা, email, কাজের সময় দিন
- WhatsApp number এ 880 দিয়ে শুরু করুন (যেমন: 8801700000000)
- হোমপেজ সেকশনের টাইটেল/সাবটাইটেল পরিবর্তন করুন

### ২️⃣ Services (টিকেট সেবা):
- Services → Air Ticket এ ছবি ও বিস্তারিত বিবরণ যোগ করুন
- একইভাবে Ship Ticket ও Bus Ticket আপডেট করুন

### ৩️⃣ Ship Routes (জাহাজ রুট):
- Cox's Bazar → Saint Martin, Cox's Bazar → Teknaf রুট যোগ করুন

### ৪️⃣ Destinations (গন্তব্য):
- Cox's Bazar, Saint Martin, Teknaf, Bandarban, Sylhet ইত্যাদি যোগ করুন

### ৫️⃣ Tours (ট্যুর প্যাকেজ):
- নতুন নতুন ট্যুর প্যাকেজ তৈরি করুন (নাম, মূল্য, বিবরণ, ছবি সহ)

### ৬️⃣ Holiday Packages (হলিডে প্যাকেজ):
- বিশেষ হলিডে প্যাকেজ (Eid, Puja, New Year) যোগ করুন

### ৭️⃣ Testimonials (গ্রাহকের মতামত):
- কিছু customer review যোগ করুন

---

## 🔄 প্রতিবার সাইট চালানোর জন্য:
1. **XAMPP Control Panel** ওপেন করুন
2. **Apache** Start করুন
3. **MySQL** Start করুন
4. ব্রাউজারে যান: http://localhost/sakib

কাজ শেষে XAMPP Control Panel থেকে Apache ও MySQL Stop করুন।

---

## ❌ সাধারণ সমস্যা ও সমাধান

### সমস্যা ১: Apache Start হচ্ছে না
**কারণ:** Skype, TeamViewer বা অন্য প্রোগ্রাম 80 port ব্যবহার করছে।
**সমাধান:**
- Skype বন্ধ করুন
- অথবা XAMPP Control Panel → Apache → Config → `httpd.conf` খুলে `Listen 80` কে `Listen 8080` করুন, তারপর http://localhost:8080/sakib এ যান

### সমস্যা ২: MySQL Start হচ্ছে না
**কারণ:** আগের MySQL process চলছে।
**সমাধান:**
- Task Manager খুলে (Ctrl+Shift+Esc) → `mysqld.exe` বা `mysqlid.exe` চললে End Task করুন
- তারপর XAMPP থেকে MySQL Start করুন

### সমস্যা ৩: সাদা পেজ / 500 Internal Server Error
**কারণ:** PHP error, file permission, বা .env সমস্যা।
**সমাধান:**
- `.env` ফাইল আছে কিনা ও সঠিক তথ্য আছে কিনা চেক করুন
- `storage/` ফোল্ডারে write permission দিন (ধাপ ৭ দেখুন)
- `public/assets/uploads/` ফোল্ডারেও write permission দিন
- ব্রাউজারে গিয়ে Ctrl+Shift+R দিয়ে hard refresh করুন

### সমস্যা ৪: CSS/ছবি/style লোড হচ্ছে না
**কারণ:** Apache mod_rewrite চালু নেই।
**সমাধান:**
1. XAMPP Control Panel → Apache → Config → `httpd.conf` খুলুন
2. `#LoadModule rewrite_module modules/mod_rewrite.so` লাইনটি খুঁজুন
3. সামনের `#` চিহ্ন সরিয়ে দিন: `LoadModule rewrite_module modules/mod_rewrite.so`
4. ফাইল সেভ করে Apache restart করুন

### সমস্যা ৫: Database connection error
**সমাধান:**
- MySQL চলছে কিনা চেক করুন (XAMPP Control Panel এ সবুজ Running?)
- `.env` ফাইলে DB_DATABASE=`sakib_enterprise`, DB_USERNAME=`root`, DB_PASSWORD=ফাঁকা আছে কিনা
- phpMyAdmin এ `sakib_enterprise` database তৈরি করা আছে কিনা

### সমস্যা ৬: Admin Login হচ্ছে না
**সমাধান:**
- ইমেইল ও পাসওয়ার্ড ভুল (install.php এ যা দিয়েছেন তা দিন)
- Cookies enabled আছে কিনা ব্রাউজারে চেক করুন
- ৫ বার ভুল পাসওয়ার্ড দিলে ১৫ মিনিটের জন্য lock হয় — ১৫ মিনিট অপেক্ষা করুন

### সমস্যা ৭: ছবি আপলোড হচ্ছে না
**সমাধান:**
- `public/assets/uploads/` ফোল্ডারে write permission দিন
- ছবির সাইজ 5MB-এর বেশি হলে আপলোড হবে না
- শুধু JPG, JPEG, PNG, WebP, GIF ফরম্যাট অনুমোদিত

---

## 🛡️ নিরাপত্তা টিপস (Production এর জন্য)
- `install.php` ফাইল অবশ্যই ডিলিট করুন
- `.env` ফাইল কারো সাথে শেয়ার করবেন না
- পাসওয়ার্ড শক্তিশালী ব্যবহার করুন (অক্ষর+সংখ্যা+চিহ্ন)
- `APP_DEBUG=false` করুন লাইভে যাওয়ার আগে

---

## 📞 কোনো ধাপে আটকে গেলে?
- যে ধাপে সমস্যা হচ্ছে স্ক্রিনশট নিন
- Error message-টা কপি করুন
- বলুন কোন step-এ আছেন
- আমি সাথে সাথে সমাধান দিচ্ছি!

---

## 🎉 চেকলিস্ট

সেটআপ শেষ হয়েছে কিনা নিচের লিস্ট দিয়ে চেক করুন:

- [ ] XAMPP ইনস্টল করেছেন
- [ ] XAMPP Control Panel এ Apache Running (সবুজ)
- [ ] XAMPP Control Panel এ MySQL Running (সবুজ)
- [ ] ফাইলগুলো `C:\xampp\htdocs\sakib\` এ রেখেছেন
- [ ] `api/` ফোল্ডার ও `vercel.json` ডিলিট করেছেন
- [ ] `.env` ফাইল তৈরি করেছেন ও সঠিক তথ্য দিয়েছেন
- [ ] phpMyAdmin এ `sakib_enterprise` database তৈরি করেছেন
- [ ] http://localhost/sakib/install.php চালিয়ে Admin তৈরি করেছেন
- [ ] `install.php` ডিলিট করেছেন
- [ ] http://localhost/sakib এ হোমপেজ দেখা যাচ্ছে
- [ ] Admin login সফল হচ্ছে
- [ ] Admin panel থেকে content যোগ করছেন

সব চেকমার্ক হয়ে গেলে — অভিনন্দন! 🎊 আপনার লোকাল সার্ভার সম্পূর্ণ চালু।
