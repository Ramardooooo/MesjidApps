-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               8.0.30 - MySQL Community Server - GPL
-- Server OS:                    Win64
-- HeidiSQL Version:             12.1.0.6537
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table mesjid_website.banners: ~3 rows (approximately)
INSERT INTO `banners` (`id`, `judul`, `subjudul`, `gambar`, `link_url`, `tipe`, `urutan`, `is_active`, `created_at`) VALUES
	(1, 'Selamat Datang di Masjid Jami\' Nurul Iman', 'Pusat Ibadah Khusyuk, Tarbiyah Generasi Qur\'ani, dan Kebangkitan Ekonomi Ummat', '', 'home/profil.php', 'slider', 1, 1, '2026-09-07 03:15:41'),
	(2, 'Mari Berwakaf untuk Menara & Tempat Wudhu', 'Alirkan pahala jariyah tanpa putus dengan membantu penyelesaian sarana ibadah jamaah', '', 'home/program-detail.php?id=1', 'slider', 2, 1, '2026-09-07 03:15:41'),
	(3, 'Transparansi Kas Masjid Terbuka & Amanah', 'Pantau arus penerimaan infaq dan penyaluran kas masjid secara realtime dan akuntabel', '', 'home/transparansi.php', 'slider', 3, 1, '2026-09-07 03:15:41');

-- Dumping data for table mesjid_website.berita: ~3 rows (approximately)
INSERT INTO `berita` (`id`, `judul`, `slug`, `isi`, `ringkasan`, `thumbnail`, `kategori`, `penulis_id`, `tanggal_publikasi`, `status`, `views`, `created_at`, `updated_at`) VALUES
	(1, 'Semarak Peringatan Maulid Nabi Muhammad SAW 1448 H', 'semarak-peringatan-maulid-nabi-muhammad-saw', '<p>Alhamdulillah, segenap pengurus DKM Masjid Jami\' Nurul Iman bersama panitia hari besar Islam mengundang seluruh kaum muslimin dan muslimat untuk hadir dalam Peringatan Maulid Nabi Muhammad SAW 1448 H.</p><p>Acara ini insya Allah akan diselenggarakan pada hari Ahad malam Senin, dengan menghadirkan penceramah utama Al-Mukarram KH. Hasanuddin dari Jawa Timur. Agenda akan diawali dengan pembacaan Maulid Simthudduror ba\'da shalat Isya berjamaah.</p><p>Mari ajak sanak keluarga untuk bersama-sama meneguhkan rasa cinta kepada Rasulullah SAW serta meneladani akhlak mulia beliau dalam kehidupan sehari-hari.</p>', 'Undangan peringatan Maulid Nabi Muhammad SAW bersama KH. Hasanuddin ba\'da Isya di ruang utama masjid.', 'uploads/berita/1788843148_93c407590bf9.png', 'Kajian & Kegiatan', 1, '2026-09-03', 'published', 245, '2026-09-07 03:15:41', '2026-09-08 04:52:28'),
	(2, 'Laporan Penyaluran Santunan Pendidikan 50 Anak Yatim Periode September', 'laporan-penyaluran-santunan-pendidikan-50-anak-yatim', '<p>Segala puji bagi Allah Rabb semesta alam. Pada hari Jumat penuh berkah kemarin, Bidang Sosial DKM Masjid Jami\' Nurul Iman telah menyalurkan santunan pendidikan dan paket perlengkapan sekolah kepada 50 anak yatim binaan masjid.</p><p>Setiap anak menerima santunan tunai sebesar Rp 300.000 serta paket tas, buku tulis, dan sembako untuk keluarga. Program ini terwujud berkat kemurahan hati para donatur dan muhsinin sekalian.</p><p>Kami mengucapkan jazakumullahu khairan katsiran atas kepercayaan yang telah diamanahkan kepada panitia sosial masjid.</p>', 'Penyaluran beasiswa dan paket santunan berkala untuk 50 adik-adik yatim piatu binaan DKM.', 'uploads/berita/1788843140_178d51dc114f.png', 'Sosial Ummat', 1, '2026-09-02', 'published', 190, '2026-09-07 03:15:41', '2026-09-08 04:52:20'),
	(3, 'Jadwal Kajian Rutin Fiqih Ibadah Setiap Ahad Subuh', 'jadwal-kajian-rutin-fiqih-ibadah-ahad-subuh', '<p>Diberitahukan kepada seluruh jamaah Masjid Jami\' Nurul Iman, bahwa kajian rutin Fiqih Ibadah tematik karya Syaikh Sayyid Sabiq diasuh langsung oleh Ust. H. Mahfuzh Amin, Lc.</p><p>Kajian diadakan setiap Ahad Subuh setelah shalat Subuh berjamaah hingga waktu Syuruq, dilanjutkan dengan sarapan bubur berkah bersama di serambi masjid. Jamaah muslimin dan muslimat dipersilakan hadir.</p>', 'Kajian kitab Fiqih Sunnah setiap Ahad Subuh ba\'da shalat berjamaah hingga waktu syuruq.', 'uploads/berita/1788843132_b2c81f69a7b9.png', 'Jadwal Kajian', 1, '2026-09-01', 'published', 313, '2026-09-07 03:15:41', '2026-09-08 04:52:12');

-- Dumping data for table mesjid_website.donasi: ~4 rows (approximately)
INSERT INTO `donasi` (`id`, `nama_donatur`, `jenis_donasi`, `jumlah`, `keterangan`, `tanggal_donasi`, `created_at`) VALUES
	(1, 'Bpk. Ahmad', 'uang', 1000000.00, 'Untuk pembangunan', '2026-09-01', '2026-09-03 02:03:27'),
	(2, 'Ibu Siti', 'beras', 5555.00, '10 kg beras', '2026-09-02', '2026-09-03 02:03:27'),
	(3, 'Ramardo', 'beras', 1000000.00, '20kg Beras', '2026-09-03', '2026-09-03 02:31:05'),
	(4, 'fz', 'uang', 7.00, 'fz', '2026-09-03', '2026-09-03 02:55:40');

-- Dumping data for table mesjid_website.donasi_online: ~6 rows (approximately)
INSERT INTO `donasi_online` (`id`, `no_donasi`, `user_id`, `nama_donatur`, `is_anonim`, `email`, `no_wa`, `program_id`, `nominal`, `metode_pembayaran`, `bank_tujuan`, `bukti_pembayaran`, `doa_donatur`, `status`, `catatan_admin`, `verified_by`, `verified_at`, `transaksi_id`, `tanggal_donasi`, `created_at`) VALUES
	(1, 'DON-202609-0001', 4, 'Bpk. H. Bambang Subagyo', 0, 'donatur@gmail.com', '081234567893', 1, 5000000.00, 'transfer', 'Bank Syariah Indonesia (BSI)', NULL, 'Semoga pembangunan menara berjalan lancar dan berkah untuk seluruh jamaah.', 'diverifikasi', NULL, NULL, NULL, NULL, '2026-09-02', '2026-09-07 03:15:41'),
	(2, 'DON-202609-0002', NULL, 'Hamba Allah', 1, 'hambaallah@gmail.com', '081987654321', 2, 500000.00, 'qris', 'QRIS Dinamis', NULL, 'Mohon doa agar keluarga senantiasa diberi kesehatan dan anak-anak sholeh.', 'diverifikasi', NULL, NULL, NULL, NULL, '2026-09-03', '2026-09-07 03:15:41'),
	(3, 'DON-202609-0003', NULL, 'Ibu Hj. Aminah', 0, 'aminah@yahoo.com', '081345678912', 3, 250000.00, 'qris', 'QRIS Dinamis', NULL, 'Infaq operasional kebersihan masjid tercinta.', 'ditolak', 'Bukti Transfer Kurang Bagus', 1, '2026-09-08 11:53:20', NULL, '2026-09-04', '2026-09-07 03:15:41'),
	(4, 'DON-202609-0004', 6, 'itihi dongo', 0, 'ipitf991@gmail.com', '088245012642', 3, 1000000.00, 'qris', 'QRIS Dinamis', 'uploads/bukti/1788836548_ce9f8533e4d5.png', 'aaa', 'diverifikasi', NULL, 1, '2026-09-08 11:03:05', 9, '2026-09-08', '2026-09-08 03:02:28'),
	(5, 'DON-202609-0005', 4, 'Bpk. H. Bambang Subagyo', 0, 'donatur@gmail.com', '088245012642', 2, 1000000.00, 'qris', 'QRIS Dinamis', NULL, 'y', 'diverifikasi', NULL, 1, '2026-09-08 12:50:06', 10, '2026-09-08', '2026-09-08 04:49:09'),
	(6, 'DON-202609-0006', 4, 'Bpk. H. Bambang Subagyo', 0, 'donatur@gmail.com', '088245012642', 1, 1000000.00, 'qris', 'QRIS Dinamis', 'uploads/bukti/1788843055_945e2abe869b.png', 'yggg', 'diverifikasi', NULL, 1, '2026-09-08 12:51:16', 11, '2026-09-08', '2026-09-08 04:50:55');

-- Dumping data for table mesjid_website.kategori_transaksi: ~13 rows (approximately)
INSERT INTO `kategori_transaksi` (`id`, `jenis`, `nama_kategori`, `deskripsi`, `urutan`) VALUES
	(1, 'pemasukan', 'Infaq Kotak Amal Jumat', 'Penerimaan kotak infaq dari jamaah shalat Jumat', 1),
	(2, 'pemasukan', 'Kotak Amal Harian & Subuh', 'Kotak infaq harian di selasar dan pintu masuk masjid', 2),
	(3, 'pemasukan', 'Donasi Program Khusus', 'Penerimaan donasi terikat program pembangunan/sosial', 3),
	(4, 'pemasukan', 'Infaq Transfer & QRIS', 'Donasi digital via QRIS dan transfer rekening bank', 4),
	(5, 'pemasukan', 'Zakat, Infaq & Sedekah (ZIS)', 'Penyaluran dana zakat fitrah & mal', 5),
	(6, 'pemasukan', 'Penerimaan Kas Lainnya', 'Sewa aula, parkir berkah, dll', 6),
	(7, 'pengeluaran', 'Tagihan Listrik PLN & Air PDAM', 'Pembayaran utilitas bulanan masjid', 1),
	(8, 'pengeluaran', 'Honorarium Petugas (Marbot & Imam)', 'Insentif bulanan marbot, muadzin, dan imam rawatib', 2),
	(9, 'pengeluaran', 'Kebersihan & Perlengkapan Sanitasi', 'Pembersih lantai, pewangi karpet, sabun, kantong kresek', 3),
	(10, 'pengeluaran', 'Kajian & Kegiatan Hari Besar Islam', 'Konsumsi taklim, honor pemateri mubaligh kajian rutin', 4),
	(11, 'pengeluaran', 'Santunan Sosial & Yatim Dhuafa', 'Penyaluran bantuan tunai dan logistik dhuafa', 5),
	(12, 'pengeluaran', 'Pemeliharaan Gedung & Sarana Sound', 'Perbaikan kran wudhu, service AC, amplifier sound system', 6),
	(13, 'pengeluaran', 'Pengeluaran Kas Lainnya', 'Biaya administrasi kantor DKM', 7);

-- Dumping data for table mesjid_website.kegiatan: ~1 rows (approximately)
INSERT INTO `kegiatan` (`id`, `judul`, `isi`, `tanggal`, `created_at`) VALUES
	(1, 'Pengajian Rutin Jumat', 'Kajian kitab setiap Jumat malam ba\'da Maghrib.', '2026-09-05', '2026-09-03 02:03:28');

-- Dumping data for table mesjid_website.notifikasi: ~9 rows (approximately)
INSERT INTO `notifikasi` (`id`, `user_id`, `judul`, `pesan`, `tipe`, `link`, `is_read`, `created_at`) VALUES
	(1, 4, 'Donasi Terverifikasi!', 'Alhamdulillah, donasi Anda sebesar Rp 5.000.000 untuk program Pembangunan Menara telah diverifikasi bendahara. e-Kwitansi resmi telah diterbitkan.', 'donasi', 'donatur/kwitansi.php?no=DON-202609-0001', 0, '2026-09-07 03:15:41'),
	(2, 5, 'Selamat Datang di Portal Donatur!', 'Ahlan wa sahlan! Akun donatur Anda telah aktif. Anda dapat memantau riwayat donasi dan mengunduh kwitansi resmi di sini.', 'sistem', NULL, 0, '2026-09-07 06:40:03'),
	(3, 6, 'Selamat Datang di Portal Donatur!', 'Ahlan wa sahlan! Akun donatur Anda telah aktif. Anda dapat memantau riwayat donasi dan mengunduh kwitansi resmi di sini.', 'sistem', NULL, 0, '2026-09-08 02:59:29'),
	(4, 6, 'Donasi Baru Tercatat #DON-202609-0004', 'Donasi Anda sebesar Rp 1.000.000 berhasil diajukan dan sedang menunggu verifikasi bendahara.', 'donasi', '../donatur/kwitansi.php?no=DON-202609-0004', 0, '2026-09-08 03:02:28'),
	(5, 6, 'Donasi #DON-202609-0004 Terverifikasi!', 'Alhamdulillah, donasi Anda sebesar Rp 1.000.000 telah diverifikasi bendahara dan dicatat pada buku kas masjid.', 'donasi', '../donatur/kwitansi.php?no=DON-202609-0004', 0, '2026-09-08 03:03:05'),
	(6, 4, 'Donasi Baru Tercatat #DON-202609-0005', 'Donasi Anda sebesar Rp 1.000.000 berhasil diajukan dan sedang menunggu verifikasi bendahara.', 'donasi', '../donatur/kwitansi.php?no=DON-202609-0005', 0, '2026-09-08 04:49:09'),
	(7, 4, 'Donasi #DON-202609-0005 Terverifikasi!', 'Alhamdulillah, donasi Anda sebesar Rp 1.000.000 telah diverifikasi bendahara dan dicatat pada buku kas masjid.', 'donasi', '../donatur/kwitansi.php?no=DON-202609-0005', 0, '2026-09-08 04:50:06'),
	(8, 4, 'Donasi Baru Tercatat #DON-202609-0006', 'Donasi Anda sebesar Rp 1.000.000 berhasil diajukan dan sedang menunggu verifikasi bendahara.', 'donasi', '../donatur/kwitansi.php?no=DON-202609-0006', 0, '2026-09-08 04:50:55'),
	(9, 4, 'Donasi #DON-202609-0006 Terverifikasi!', 'Alhamdulillah, donasi Anda sebesar Rp 1.000.000 telah diverifikasi bendahara dan dicatat pada buku kas masjid.', 'donasi', '../donatur/kwitansi.php?no=DON-202609-0006', 0, '2026-09-08 04:51:16'),
	(10, 7, 'Selamat Datang di Portal Donatur!', 'Ahlan wa sahlan! Akun donatur Anda telah aktif. Anda dapat memantau riwayat donasi dan mengunduh kwitansi resmi di sini.', 'sistem', NULL, 0, '2026-09-10 01:46:53');

-- Dumping data for table mesjid_website.otp_codes: ~7 rows (approximately)
INSERT INTO `otp_codes` (`id`, `email`, `otp_code`, `purpose`, `expires_at`, `used`, `created_at`) VALUES
	(7, 'lrozak646@gmail.com', '922538', 'register', '2026-09-07 06:22:25', 0, '2026-09-07 14:12:25'),
	(13, 'ramaxkont1@gmail.com', '200318', 'register', '2026-09-07 14:49:40', 1, '2026-09-07 14:39:40'),
	(20, 'ramaxkont1@gmail.com', '619265', 'forgot_password', '2026-09-08 10:43:42', 0, '2026-09-08 10:33:42'),
	(21, 'ipitf991i@gmail.com', '199913', 'register', '2026-09-08 11:07:56', 0, '2026-09-08 10:57:56'),
	(22, 'ipitf991@gmail.com', '942339', 'register', '2026-09-08 11:08:54', 1, '2026-09-08 10:58:54'),
	(23, 'favrama6@gmail.com', '812275', 'register', '2026-09-10 09:25:39', 0, '2026-09-10 09:15:39'),
	(26, 'yandexanjas@gmail.com', '773582', 'register', '2026-09-10 09:56:23', 1, '2026-09-10 09:46:23');

-- Dumping data for table mesjid_website.pengurus_masjid: ~6 rows (approximately)
INSERT INTO `pengurus_masjid` (`id`, `nama`, `jabatan`, `bidang`, `no_hp`, `foto`, `urutan`, `created_at`) VALUES
	(1, 'H. Ahmad Syukron, S.Ag', 'Ketua Umum DKM', 'Pimpinan Utama', '081234567890', NULL, 1, '2026-09-07 03:15:40'),
	(2, 'Drs. H. Abdul Wahab, M.Pd.I', 'Wakil Ketua DKM', 'Pimpinan', '081234567895', NULL, 2, '2026-09-07 03:15:40'),
	(3, 'Ust. H. Mahfuzh Amin, Lc', 'Sekretaris Umum', 'Kesekretariatan & Dakwah', '081234567896', NULL, 3, '2026-09-07 03:15:40'),
	(4, 'H. Muhammad Ridwan, SE', 'Bendahara Umum', 'Keuangan & Aset', '081234567891', NULL, 4, '2026-09-07 03:15:40'),
	(5, 'Ust. Fajar Ramadhan', 'Koordinator Media & IT', 'Humas & Dakwah Digital', '081234567892', NULL, 5, '2026-09-07 03:15:40'),
	(6, 'Bpk. Suparman', 'Koordinator Pemeliharaan', 'Sarana & Marbot', '081234567897', NULL, 6, '2026-09-07 03:15:40');

-- Dumping data for table mesjid_website.pesan_kontak: ~0 rows (approximately)

-- Dumping data for table mesjid_website.profil_masjid: ~1 rows (approximately)
INSERT INTO `profil_masjid` (`id`, `nama_masjid`, `sebutan`, `slogan`, `sejarah`, `visi`, `misi`, `alamat`, `kota`, `google_maps_embed`, `whatsapp`, `email`, `instagram`, `youtube`, `facebook`, `foto_masjid`, `saldo_awal_kas`, `updated_at`) VALUES
	(1, 'Masjid Jami\' Nurul Iman', 'Pusat Peradaban, Ibadah, dan Pemberdayaan Ummat', 'Menegakkan Sunnah, Memakmurkan Masjid, Mensejahterakan Ummat', 'Masjid Jami\' Nurul Iman didirikan pada tahun 1982 atas prakarsa tokoh masyarakat dan alim ulama setempat yang mendambakan sarana ibadah dan pusat tarbiyah Islam yang representatif di tengah masyarakat. Seiring perkembangan jamaah, masjid telah mengalami beberapa tahap pemugaran dengan memadukan gaya arsitektur Timur Tengah klasik yang anggun dan kearifan arsitektur lokal Nusantara.', 'Menjadi pusat peradaban Islam yang mandiri, makmur, berakhlak mulia, serta unggul dalam pelayanan ibadah jamaah dan pemberdayaan sosial ekonomi ummat.', '1. Menyelenggarakan ibadah fardhu dan sunnah secara berjamaah dengan tertib, bersih, nyaman, dan khusyuk.\n2. Mengembangkan majelis taklim, tahsin, tahfidz Al-Qur\'an, dan kajian keislaman kontemporer untuk seluruh kalangan usia.\n3. Mengelola dana zakat, infaq, sedekah, dan wakaf (ZISWAF) secara amanah, transparan, dan profesional berbasis teknologi informasi.\n4. Menyelenggarakan program santunan sosial berkala, pembinaan generasi muda Islam, dan kepedulian dhuafa.', 'Jl. Mesjid Raya No. 45, Kompleks Permata Indah, Kelurahan Sungai Miai, Kec. Banjarmasin Utara', 'Kota Banjarmasin', 'https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d15932.748383823485!2d114.5828453!3d-3.3085289!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x2de423c6a6f69dd3%3A0xb3041c2c8f8fa958!2sMasjid%20Raya%20Sabilal%20Muhtadin!5e0!3m2!1sid!2sid!4v1700000000000!5m2!1sid!2sid', '6281255557890', 'kontak@masjidnuruliman.id', 'masjidnuruliman.official', 'MasjidNurulImanTV', 'MasjidNurulImanOfficial', NULL, 18500000.00, '2026-09-07 03:15:40');

-- Dumping data for table mesjid_website.program_donasi: ~4 rows (approximately)
INSERT INTO `program_donasi` (`id`, `nama_program`, `slug`, `kategori`, `deskripsi`, `deskripsi_lengkap`, `gambar`, `target_donasi`, `dana_terkumpul`, `tanggal_mulai`, `tanggal_selesai`, `status`, `is_featured`, `created_at`, `updated_at`) VALUES
	(1, 'Pembangunan Menara & Perluasan Tempat Wudhu', 'pembangunan-menara-dan-tempat-wudhu', 'operasional', 'Renovasi dan perluasan fasilitas tempat wudhu ramah lansia serta penyelesaian lantai 2 menara kumandang adzan.', 'Program renovasi fisik meliputi pelebaran selasar wudhu ikhwan dan akhwat, penggantian keramik anti-selip, pemasangan kran hemat air otomatis, serta finishing menara setinggi 21 meter sebagai syiar dakwah adzan masjid.', 'uploads/program/1788843175_8492c90bfe78.png', 7500000000.00, 49500000.00, '2026-08-01', '2026-11-30', 'aktif', 1, '2026-09-07 03:15:41', '2026-09-08 04:52:56'),
	(2, 'Santunan Bulanan 50 Anak Yatim & Dhuafa', 'santunan-bulanan-anak-yatim-dan-dhuafa', 'sosial', 'Pemberian paket sembako nutrisi dan beasiswa pendidikan berkala bagi 50 adik-adik yatim piatu di lingkungan masjid.', 'Masjid membina 50 anak yatim dan piatu di lingkungan sekitar kelurahan. Bantuan disalurkan setiap awal bulan berupa santunan tunai pendidikan sebesar Rp 300.000/anak dan paket sembako keluarga.', 'uploads/program/1788843168_8619e308b1cd.png', 2500000000.00, 20200000.00, '2026-09-01', '2026-12-31', 'aktif', 1, '2026-09-07 03:15:41', '2026-09-08 04:52:48'),
	(3, 'Operasional Kebersihan, Listrik AC & Genset', 'operasional-kebersihan-listrik-dan-genset', 'operasional', 'Dukungan kelancaran operasional ibadah harian jamaah: listrik AC sejuk, kebersihan karpet wangi, dan perawatan sound system.', 'Kebutuhan operasional bulanan masjid untuk memastikan kenyamanan jamaah dalam menjalankan shalat fardhu lima waktu, shalat Jumat, dan kajian taklim.', 'uploads/program/1788843161_3b847de76f98.png', 1500000000.00, 12800000.00, '2026-09-01', '2026-09-30', 'aktif', 1, '2026-09-07 03:15:41', '2026-09-08 04:52:41'),
	(4, 'Wakaf 500 Mushaf Al-Qur\'an Standar Madinah', 'wakaf-mushaf-al-quran-madinah', 'keagamaan', 'Pengadaan mushaf Al-Qur\'an cetakan berstandar internasional untuk para santri Rumah Tahfidz dan jamaah masjid.', 'Penyediaan 500 mushaf Al-Qur\'an baru berukuran besar yang nyaman dibaca oleh jamaah sepuh maupun para santri penghafal Al-Qur\'an.', NULL, 35000000.00, 35000000.00, '2026-07-01', '2026-08-31', 'terpenuhi', 0, '2026-09-07 03:15:41', '2026-09-07 03:15:41');

-- Dumping data for table mesjid_website.rekening_donasi: ~5 rows (approximately)
INSERT INTO `rekening_donasi` (`id`, `nama_bank`, `nomor_rekening`, `atas_nama`, `kategori_donasi`, `logo_bank`, `qris_image`, `is_active`, `urutan`, `created_at`) VALUES
	(1, 'Bank Syariah Indonesia (BSI)', '7123-4567-89', 'MASJID JAMI NURUL IMAN', 'Kas Operasional & Infaq Umum', NULL, NULL, 1, 1, '2026-09-07 03:15:40'),
	(2, 'Bank Muamalat', '101-002-3456', 'DKM MASJID NURUL IMAN', 'Sedekah Subuh & Anak Yatim', NULL, NULL, 1, 2, '2026-09-07 03:15:40'),
	(3, 'Bank Rakyat Indonesia (BRI)', '0021-01-003456-50-8', 'MASJID JAMI NURUL IMAN', 'Pembangunan & Renovasi Sarana', NULL, NULL, 1, 3, '2026-09-07 03:15:40'),
	(4, 'Bank Central Asia (BCA)', '782-098-7654', 'H AHMAD SYUKRON QQ NURUL IMAN', 'Infaq Jumat & Sosial Ummat', NULL, NULL, 1, 4, '2026-09-07 03:15:40'),
	(5, 'Dana', '088245012642', 'Ramardo Ganteng', 'Kas Operasional & Infaq Umum', NULL, NULL, 1, 1, '2026-09-08 04:55:08');

-- Dumping data for table mesjid_website.transaksi_keuangan: ~11 rows (approximately)
INSERT INTO `transaksi_keuangan` (`id`, `no_transaksi`, `tanggal_transaksi`, `jenis`, `kategori_id`, `program_id`, `akun_kas`, `nominal`, `keterangan`, `bukti_transaksi`, `user_id`, `metode_pembayaran`, `is_published`, `created_at`, `updated_at`) VALUES
	(1, 'TRX-IN-202609-001', '2026-09-01', 'pemasukan', 1, NULL, 'Kas Tunai Utama', 4850000.00, 'Perolehan Infaq Kotak Amal Shalat Jumat Pekan I September', NULL, 2, 'tunai', 1, '2026-09-07 03:15:41', '2026-09-07 03:15:41'),
	(2, 'TRX-IN-202609-002', '2026-09-02', 'pemasukan', 4, 1, 'Bank BSI', 5000000.00, 'Infaq transfer dari H. Bambang untuk program pembangunan menara', NULL, 2, 'transfer', 1, '2026-09-07 03:15:41', '2026-09-07 03:15:41'),
	(3, 'TRX-IN-202609-003', '2026-09-02', 'pemasukan', 2, NULL, 'Kas Tunai Utama', 1250000.00, 'Perolehan kotak amal Subuh Berkah', NULL, 2, 'tunai', 1, '2026-09-07 03:15:41', '2026-09-07 03:15:41'),
	(4, 'TRX-IN-202609-004', '2026-09-03', 'pemasukan', 4, 2, 'Bank Muamalat', 2500000.00, 'Donasi santunan anak yatim dari jamaah hamba Allah via QRIS', NULL, 2, 'qris', 1, '2026-09-07 03:15:41', '2026-09-07 03:15:41'),
	(5, 'TRX-OUT-202609-001', '2026-09-02', 'pengeluaran', 7, NULL, 'Kas Tunai Utama', 950000.00, 'Pembayaran tagihan listrik PLN operasional masjid bulan berjalan', NULL, 2, 'tunai', 1, '2026-09-07 03:15:41', '2026-09-07 03:15:41'),
	(6, 'TRX-OUT-202609-002', '2026-09-03', 'pengeluaran', 9, NULL, 'Kas Tunai Utama', 350000.00, 'Pembelian sabun cair cuci tangan, pembersih lantai, pewangi karpet', NULL, 2, 'tunai', 1, '2026-09-07 03:15:41', '2026-09-07 03:15:41'),
	(7, 'TRX-OUT-202609-003', '2026-09-03', 'pengeluaran', 12, 1, 'Kas Tunai Utama', 3200000.00, 'Pembelian 40 sak semen dan besi behel pengerjaan menara tahap 2', NULL, 2, 'tunai', 1, '2026-09-07 03:15:41', '2026-09-07 03:15:41'),
	(8, 'TRX-OUT-202609-004', '2026-09-04', 'pengeluaran', 8, NULL, 'Kas Tunai Utama', 2000000.00, 'Insentif dan mukafaah 2 orang marbot kebersihan bulan Agustus (Internal)', NULL, 2, 'tunai', 1, '2026-09-07 03:15:41', '2026-09-08 03:20:23'),
	(9, 'TRX-IN-202609-00-3', '2026-09-08', 'pemasukan', 4, 3, 'Bank BSI', 1000000.00, 'Donasi online (DON-202609-0004) dari itihi dongo untuk Operasional Kebersihan, Listrik AC & Genset', 'uploads/bukti/1788836548_ce9f8533e4d5.png', 1, 'qris', 1, '2026-09-08 03:03:05', '2026-09-08 03:03:05'),
	(10, 'TRX-IN-202609-0001', '2026-09-08', 'pemasukan', 4, 2, 'Bank BSI', 1000000.00, 'Donasi online (DON-202609-0005) dari Bpk. H. Bambang Subagyo untuk Santunan Bulanan 50 Anak Yatim & Dhuafa', NULL, 1, 'qris', 1, '2026-09-08 04:50:05', '2026-09-08 04:50:05'),
	(11, 'TRX-IN-202609-0002', '2026-09-08', 'pemasukan', 4, 1, 'Bank BSI', 1000000.00, 'Donasi online (DON-202609-0006) dari Bpk. H. Bambang Subagyo untuk Pembangunan Menara & Perluasan Tempat Wudhu', 'uploads/bukti/1788843055_945e2abe869b.png', 1, 'qris', 1, '2026-09-08 04:51:16', '2026-09-08 04:51:16');

-- Dumping data for table mesjid_website.users: ~6 rows (approximately)
INSERT INTO `users` (`id`, `username`, `email`, `no_hp`, `password`, `nama_lengkap`, `role`, `avatar`, `status`, `reset_token`, `reset_expires`, `created_at`, `updated_at`) VALUES
	(1, 'admin', NULL, NULL, '$2y$10$qL/o1bZUlYeDv3ycHHhaMOUHcDYvJIp9wcoashJ74M45jjtlIQls.', 'H. Ahmad Syukron, S.Ag (Ketua DKM)', 'admin', NULL, 'aktif', NULL, NULL, '2026-09-03 02:03:20', '2026-09-07 03:15:40'),
	(2, 'bendahara', 'bendahara@masjidnuruliman.id', '081234567891', '$2y$10$Cp3vKYOzfcJiY/9/hQkyBO6I8kaDG53cvLwuF.ExIj./5AK/TuUAm', 'H. Muhammad Ridwan, SE (Bendahara)', 'bendahara', NULL, 'aktif', NULL, NULL, '2026-09-07 03:15:40', '2026-09-07 03:15:40'),
	(3, 'konten', 'media@masjidnuruliman.id', '081234567892', '$2y$10$XN4rWKMM92imJ5IaRJnF5OLev6BmLrw.KCJlEJAK/LcgyaMIDQdra', 'Ust. Fajar Ramadhan (Divisi Media)', 'content_admin', NULL, 'aktif', NULL, NULL, '2026-09-07 03:15:40', '2026-09-07 03:15:40'),
	(4, 'donatur', 'donatur@gmail.com', '081234567893', '$2y$10$C0ai0ChW/egf6zs3tEOuWeUXc1AY6epb6HE9V5ltDebiD.9PMCvE.', 'Bpk. H. Bambang Subagyo', 'donatur', NULL, 'aktif', NULL, NULL, '2026-09-07 03:15:40', '2026-09-07 03:15:40'),
	(5, 'ramaxkont185', 'ramaxkont1@gmail.com', 'ramaxkont1@gmail.com', '$2y$10$61ScU0.MpilVFIFLCK2cGeB.etg9JxQgZshGF4UCy623LxOVMV6N6', 'Rama Xkont1', 'donatur', NULL, 'aktif', NULL, NULL, '2026-09-07 06:40:03', '2026-09-07 06:41:17'),
	(6, 'ipitf99166', 'ipitf991@gmail.com', '204258285825925', '$2y$10$S3p.2L6hwpnTeRjm7eoMa.0GsxmA5nQgB3/Pb/FoTe.OcCSBzkELC', 'itihi dongo', 'donatur', NULL, 'aktif', NULL, NULL, '2026-09-08 02:59:29', '2026-09-08 02:59:29'),
	(7, 'yandexanjas56', 'yandexanjas@gmail.com', '0824828925925', '$2y$10$NIzie8az8XDhaAa4OucAhuiaxjNxWX.gCeeOmzKvR1p1p4FcoI2pq', 'yatimpiatu', 'donatur', NULL, 'aktif', NULL, NULL, '2026-09-10 01:46:53', '2026-09-10 01:46:53');

-- Dumping data for table mesjid_website.youtube_videos: ~3 rows (approximately)
INSERT INTO `youtube_videos` (`id`, `judul`, `video_id`, `url_embed`, `kategori`, `deskripsi`, `is_active`, `created_at`) VALUES
	(1, 'Live Streaming Shalat Jumat & Khutbah Masjid Nurul Iman', 'dQw4w9WgXcQ', 'https://www.youtube.com/embed/dQw4w9WgXcQ', 'live_streaming', 'Siaran langsung shalat Jumat mingguan bersama khatib Ust. Dr. H. Mahfuzh Amin, Lc.', 1, '2026-09-07 03:15:41'),
	(2, 'Kajian Tafsir Surah Al-Kahfi - Membentengi Diri dari Fitnah Akhir Zaman', 'M7lc1UVf-VE', 'https://www.youtube.com/embed/M7lc1UVf-VE', 'kajian', 'Kajian tematik tafsir Al-Kahfi diasuh oleh Ust. Dr. H. Mahfuzh Amin, Lc.', 1, '2026-09-07 03:15:41'),
	(3, 'Dokumentasi Penyaluran Santunan Akbar 100 Anak Yatim & Dhuafa', 'jNQXAC9IVRw', 'https://www.youtube.com/embed/jNQXAC9IVRw', 'dokumentasi', 'Dokumentasi kebahagiaan adik-adik yatim binaan Masjid Jami\' Nurul Iman.', 1, '2026-09-07 03:15:41');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
