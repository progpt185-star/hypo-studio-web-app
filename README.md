# Segmentasi Pelanggan Konveksi Berdasarkan Pola Pemesanan Menggunakan Metode K-Means Clustering

Aplikasi web admin dashboard untuk melakukan segmentasi pelanggan konveksi berdasarkan pola pemesanan menggunakan metode K-Means Clustering. Repo ini berisi fitur analisis, visualisasi hasil clustering, dan export laporan (CSV/XLSX/PDF).

## 📋 Spesifikasi

- **Framework**: Laravel 11
- **Database**: MySQL
- **Frontend**: HTML, CSS (Bootstrap), jQuery
- **Clustering**: PHP-ML (K-Means Algorithm)
- **Export**: PDF & Excel (Maatwebsite)

## 🚀 Fitur Utama

1. **Halaman Login Admin** - Autentikasi pengguna dengan username & password
2. **Dashboard** - Ringkasan statistik dan visualisasi data
3. **Data Pelanggan** - CRUD lengkap dengan pencarian
4. **Data Pemesanan** - CRUD dengan filter dan import Excel/CSV
5. **Analisis K-Means** - Segmentasi pelanggan otomatis
6. **Hasil Segmentasi** - Visualisasi dan statistik cluster
7. **Laporan & Ekspor** - Download PDF dan Excel

## 📦 Instalasi

### Prerequisites
- PHP 8.2+
- Composer
- MySQL 8.0+
- Node.js (opsional, untuk build assets)

### Langkah Instalasi

```bash
# 1. Clone atau extract project
git clone <repository-url>
cd hypo-studio

# 2. Install dependencies
composer install

# 3. Copy environment file
cp .env.example .env

# 4. Generate app key
php artisan key:generate

# 5. Setup database
# Edit .env untuk konfigurasi database
# Kemudian jalankan migration
php artisan migrate

# 6. Seed database (optional, untuk dummy data)
php artisan db:seed

# 7. Run development server
php artisan serve
```

Server akan berjalan di `http://localhost:8000`

### Default Login Credentials
- **Username**: admin
- **Password**: password

## 🗂️ Struktur Folder

```
hypo-studio/
├── app/
│   ├── Http/Controllers/        # Controllers
│   ├── Models/                  # Database Models
│   └── Services/                # Business Logic Services
├── database/
│   ├── migrations/              # Database Migrations
│   └── seeders/                 # Database Seeders
├── resources/
│   ├── views/                   # Blade Templates
│   ├── css/                     # Stylesheets
│   └── js/                      # JavaScript Files
├── routes/
│   └── web.php                  # Web Routes
├── public/                       # Public Assets
├── storage/                      # Storage (logs, cache)
├── tests/                        # Unit & Feature Tests
├── .env.example                 # Environment Template
├── composer.json                # PHP Dependencies
└── artisan                       # CLI Tool
```

## 🔧 Konfigurasi

### Database Configuration (.env)
```
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hypo_studio
DB_USERNAME=root
DB_PASSWORD=
```

### Mail Configuration (untuk reset password)
```
MAIL_MAILER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password
```

## 📊 Penggunaan

### 1. Mengelola Data Pelanggan
- Navigasi ke **Data Pelanggan**
- Klik tombol **Tambah** untuk menambah pelanggan baru
- Gunakan fitur **Pencarian** untuk menemukan pelanggan
- Klik **Edit** atau **Hapus** untuk mengubah atau menghapus

### 2. Mengelola Data Pemesanan
- Navigasi ke **Data Pemesanan**
- Input pesanan secara manual atau **Import dari Excel**
- Gunakan filter tanggal/pelanggan untuk penyaringan

### 3. Melakukan Analisis K-Means
- Navigasi ke **Analisis K-Means**
- Input jumlah cluster (k) yang diinginkan
- Klik tombol **Jalankan Analisis**
- Tunggu proses selesai dan lihat hasil

### 4. Melihat Hasil Segmentasi
- Hasil akan ditampilkan secara otomatis setelah analisis
- Lihat tabel pelanggan per cluster
- Analisis statistik setiap cluster
- Download laporan dalam format PDF atau Excel

## 🔐 Keamanan

- Semua route dilindungi middleware `auth`
- Password di-hash menggunakan bcrypt
- CSRF protection aktif di semua form
- SQL Injection prevention dengan Eloquent ORM
- Input validation di semua controller

## 🐛 Troubleshooting

### Masalah: "SQLSTATE[HY000]: General error"
**Solusi**: Jalankan `php artisan migrate` untuk membuat database schema

### Masalah: "Class not found: App\Models\Customer"
**Solusi**: Pastikan semua file model sudah di tempat yang benar dan jalankan `composer dump-autoload`

### Masalah: File upload gagal
**Solusi**: Pastikan folder `storage/` memiliki write permission

## 📝 Development

### Menambah Route Baru
Tambahkan di `routes/web.php`:
```php
Route::get('path', [ControllerName::class, 'method'])->name('name');
```

### Membuat Model Baru
```bash
php artisan make:model ModelName -m
```

### Membuat Controller Baru
```bash
php artisan make:controller ControllerName
```

## 📚 Resources

- [Laravel Documentation](https://laravel.com/docs)
- [PHP-ML Documentation](https://php-ml.readthedocs.io/)
- [Maatwebsite Excel](https://docs.laravel-excel.com/)

## 📄 License

MIT License - Silakan gunakan untuk keperluan Hypo Studio

## 👨‍💻 Support

Untuk dukungan teknis atau pertanyaan, silakan hubungi tim development Hypo Studio.

---
**Version**: 1.0.0  
**Last Updated**: November 2025
