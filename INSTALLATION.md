# 📦 Panduan Instalasi Lengkap - Hypo Studio

## ✅ Persiapan Awal

### System Requirements
- **PHP**: 8.2 atau lebih tinggi
- **MySQL**: 8.0 atau lebih tinggi  
- **Composer**: Version terbaru
- **Git**: (Opsional)

### Cek Versi
```bash
# Cek PHP
php -v

# Cek MySQL  
mysql --version

# Cek Composer
composer --version
```

## 🔧 Instalasi Step-by-Step

### Step 1: Extract File
```bash
# Windows / Mac / Linux
unzip hypo-studio.zip
cd hypo-studio
```

### Step 2: Install PHP Dependencies
```bash
composer install
```
*Proses ini memakan waktu beberapa menit*

### Step 3: Setup Environment File
```bash
# Linux / Mac
cp .env.example .env

# Windows (PowerShell)
Copy-Item .env.example .env
```

### Step 4: Generate Application Key
```bash
php artisan key:generate
```

### Step 5: Create Database
```bash
# Buka MySQL Command Line atau GUI
mysql -u root -p

# Atau gunakan MySQL Workbench:
# 1. Klik New Connection
# 2. Input host, username, password
# 3. Connect

# Di MySQL CLI, jalankan:
CREATE DATABASE hypo_studio;
EXIT;
```

### Step 6: Configure Database (.env)
Buka file `.env` dan sesuaikan:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hypo_studio
DB_USERNAME=root
DB_PASSWORD=your_mysql_password
```

### Step 7: Run Migrations
```bash
php artisan migrate
```

### Step 8: Seed Database (Optional - untuk test data)
```bash
php artisan db:seed
```

### Step 9: Start Development Server
```bash
php artisan serve
```

**Output akan mirip:**
```
Laravel development server started: http://127.0.0.1:8000
```

### Step 10: Login
Buka browser dan akses: **http://localhost:8000**

**Default Credentials:**
- Username: `admin`
- Password: `password`

## 🎯 Verifikasi Instalasi

Setelah login, pastikan:
- ✅ Dashboard muncul dengan statistik
- ✅ Menu sidebar lengkap
- ✅ Bisa akses Data Pelanggan
- ✅ Bisa akses Data Pemesanan
- ✅ Bisa akses Analisis K-Means

Jika semua ✅, instalasi **BERHASIL**! 🎉

## 🔴 Troubleshooting

### Error 1: "Composer not found"
```bash
# Solusi: Install Composer
# Windows: Download dari https://getcomposer.org/download/
# Mac: brew install composer
# Linux: curl -sS https://getcomposer.org/installer | php
```

### Error 2: "SQLSTATE[HY000]"
```bash
# Solusi: 
# 1. Cek koneksi database di .env
# 2. Jalankan: php artisan migrate
```

### Error 3: "No application key has been generated"
```bash
# Solusi:
php artisan key:generate
```

### Error 4: "Class not found: Phpml"
```bash
# Solusi:
composer dump-autoload
php artisan serve
```

### Error 5: File upload / Storage error
```bash
# Solusi:
php artisan storage:link
chmod -R 775 storage bootstrap/cache
```

### Error 6: Port 8000 sudah digunakan
```bash
# Solusi: Gunakan port lain
php artisan serve --port=8001
```

## 📝 Konfigurasi Lanjutan

### Mail Configuration (untuk password reset)
Edit `.env`:
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_NAME="Hypo Studio Admin"
```

### Custom Admin Credentials
Edit `database/seeders/DatabaseSeeder.php` kemudian:
```bash
php artisan db:seed
```

### Production Setup
Untuk deployment:
```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

## 🚀 Start Development

Aplikasi siap digunakan! 

### Next Steps:
1. Tambahkan data pelanggan
2. Tambahkan data pemesanan (manual atau import Excel)
3. Jalankan analisis K-Means
4. Lihat hasil segmentasi
5. Download laporan

## 📞 Support

Jika ada masalah:
1. Cek error message di `storage/logs/laravel.log`
2. Pastikan semua system requirements terpenuhi
3. Hubungi tim development

---

**Happy Coding!** 💻✨
