# PRD: Sistem Penginputan Nilai Ujian Satuan Pendidikan (US)
**Tech Stack:** Laravel 12, Filament PHP, MySQL.

## 1. Tujuan Produk (Objective)
Menyediakan platform terpusat bagi guru untuk menginput, mengelola, dan memvalidasi nilai US siswa guna mempermudah bagian kurikulum dalam mengolah nilai akhir dan kelulusan.

---

## 2. Target Pengguna & Peran (User Roles)
| Role | Deskripsi Wewenang |
| :--- | :--- |
| **Super Admin** | Manajemen user (Guru), manajemen Mapel, manajemen Kelas, dan akses penuh ke database. |
| **Guru Mata Pelajaran** | Menginput nilai pada mapel dan kelas yang diampu saja. Import/Export nilai Excel. |
| **Wali Kelas** | Memantau kelengkapan nilai siswa di kelasnya (Read-only). |
| **Kepala Sekolah / Kurikulum** | Memantau progres penginputan nilai secara keseluruhan dan mendownload laporan akhir. |

---

## 3. Ruang Lingkup Fitur (Functional Requirements)

### 3.1. Manajemen Master Data (Admin)
* **Tahun Ajaran & Semester:** Pengaturan tahun aktif (agar data tahun lalu tetap tersimpan/arsip).
* **Data Siswa:** Import massal via Excel (Nama, NIS, NISN, Kelas).
* **Data Mapel:** Pengelompokan mapel (Muatan Nasional, Kewilayahan, Peminatan).
* **Mapping Guru-Mapel:** Menentukan guru A mengajar mapel B di kelas X.

### 3.2. Penginputan Nilai (Guru)
* **Bulk Input UI:** Form input yang responsif (grid) sehingga guru bisa input nilai ke bawah seperti Excel tanpa pindah halaman.
* **Auto-Save:** Menggunakan fitur *reactive* Filament/Livewire agar data tersimpan saat berpindah kolom (meminimalisir data hilang akibat koneksi).
* **Fitur Import Excel:** Download template yang sudah berisi nama siswa, isi nilai di Excel, lalu upload kembali.
* **Validasi Range:** Nilai hanya boleh $0 - 100$.

### 3.3. Monitoring & Validasi (Kurikulum/Wali Kelas)
* **Dashboard Progres:** Presentase jumlah nilai yang sudah masuk per mapel/kelas.
* **Locking System:** Admin bisa mengunci penginputan jika batas waktu sudah habis (guru tidak bisa mengubah nilai lagi).

---

## 4. Alur Kerja (Workflow)
1. **Persiapan:** Admin input Master Data Siswa dan Mapel.
2. **Distribusi:** Admin membuat akun Guru dan melakukan *mapping* pengampu mapel.
3. **Penginputan:** Guru masuk ke panel Filament, memilih kelas, dan mengisi nilai (langsung atau via Excel).
4. **Verifikasi:** Wali kelas mengecek apakah ada siswa yang nilainya masih kosong (Null).
5. **Finalisasi:** Kurikulum mengunci data dan mendownload rekapitulasi nilai akhir.

---

## 5. Spesifikasi Teknis & Database
### Skema Tabel Kunci (Logika Celah)
* `users` (id, name, email, role, etc)
* `students` (id, nisn, name, class_id)
* `subjects` (id, code, name, category)
* `subject_teacher` (id, subject_id, teacher_id, class_id) -> *Pivot untuk kontrol akses*
* `scores` (id, student_id, subject_id, teacher_id, score_us, academic_year) -> *Unique constraint pada student_id + subject_id + academic_year.*

---

## 6. Analisis Risiko & Solusi (Kritik Realistis)
* **Risiko 1: Beban Server saat Concurrent User.** * *Masalah:* 50+ guru akses bareng saat jam terakhir deadline.
    * *Solusi:* Gunakan **Database Indexing** pada kolom `student_id` dan `subject_id`. Hindari relasi yang terlalu dalam di tabel nilai.
* **Risiko 2: Human Error (Salah Input).**
    * *Masalah:* Guru salah input kelas atau mapel.
    * *Solusi:* Implementasikan **Activity Log** (Spatie). Admin harus bisa melacak siapa yang mengubah nilai si A dari 60 ke 90.
* **Risiko 3: Kehilangan Data.**
    * *Masalah:* Listrik padam/koneksi mati saat input manual.
    * *Solusi:* Gunakan **Filament Actions** dengan konfirmasi save, atau integrasikan mekanisme *Draft/Publish*.

---

## 7. Kriteria Keberhasilan (Acceptance Criteria)
1. Guru dapat menyelesaikan penginputan 1 kelas (36 siswa) dalam waktu kurang dari 5 menit via web.
2. Sistem dapat menghasilkan file Excel rekapitulasi seluruh siswa dalam satu kali klik.
3. Siswa yang tidak memiliki nilai akan terdeteksi otomatis oleh sistem monitoring.

* **Naming Convention:** ("Gunakan Bahasa Inggris untuk nama kolom di database, tapi Bahasa Indonesia untuk Label di UI").
* **Validation Logic:** AI sering lupa membatasi hak akses. Tekankan di PRD bahwa: "Teacher can ONLY see their own assigned subjects."
