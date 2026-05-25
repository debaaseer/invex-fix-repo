<?php
/**
 * File: peminjaman_tambah.php
 * Deskripsi: Form tambah peminjaman manual oleh admin dengan Tailwind CSS
 */

// Set judul halaman
$page_title = 'Tambah Peminjaman Manual';

// Include koneksi
require_once '../config/koneksi.php';

// Include auth helper
require_once '../config/auth.php';

// Hanya admin yang bisa akses
require_admin();

// Ambil daftar user
$query_users = "SELECT * FROM users ORDER BY nama_lengkap ASC";
$result_users = query($query_users);

// Ambil daftar barang tersedia
$query_barang = "SELECT * FROM barang WHERE status = 'tersedia' AND stok > 0 ORDER BY nama ASC";
$result_barang = query($query_barang);

$error = '';

// Proses Form
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $user_id = clean_input($_POST['user_id']);
    $barang_id = clean_input($_POST['barang_id']);
    $jumlah = clean_input($_POST['jumlah']);
    $tgl_pinjam = clean_input($_POST['tgl_pinjam']);
    $tgl_kembali = clean_input($_POST['tgl_kembali']);
    $keterangan = clean_input($_POST['keterangan']);

    if (empty($user_id) || empty($barang_id) || empty($tgl_pinjam) || empty($tgl_kembali) || empty($jumlah)) {
        $error = 'Semua field wajib diisi!';
    } elseif ($jumlah < 1) {
        $error = 'Jumlah peminjaman minimal 1!';
    } else {
        // Cek stok lagi untuk keamanan
        $cek_stok = query("SELECT stok FROM barang WHERE id = '$barang_id'");
        $stok_saat_ini = $cek_stok->fetch_assoc()['stok'];

        if ($stok_saat_ini < $jumlah) {
            $error = "Stok barang tidak mencukupi! (Sisa: $stok_saat_ini)";
        } else {
            $conn->begin_transaction();

            try {
                // 1. Insert ke tabel peminjaman (Langsung Approved karena admin yang input)
                $query_insert = "INSERT INTO peminjaman (user_id, barang_id, jumlah, tgl_pinjam, tgl_kembali, status_approval, status_kembali, keterangan) 
                                VALUES ('$user_id', '$barang_id', '$jumlah', '$tgl_pinjam', '$tgl_kembali', 'approved', 'dipinjam', '$keterangan')";
                
                if (!execute($query_insert)) {
                    throw new Exception("Gagal insert peminjaman");
                }

                // 2. Kurangi stok barang
                $query_update_stok = "UPDATE barang SET stok = stok - $jumlah WHERE id = '$barang_id'";
                if (!execute($query_update_stok)) {
                    throw new Exception("Gagal update stok");
                }

                // 3. Update status barang jika stok habis
                $query_cek_stok = "UPDATE barang SET status = 'tidak_tersedia' WHERE id = '$barang_id' AND stok = 0";
                execute($query_cek_stok);

                $conn->commit();
                header("Location: peminjaman_list.php?success=ditambah_manual");
                exit();

            } catch (Exception $e) {
                $conn->rollback();
                $error = 'Terjadi kesalahan sistem: ' . $e->getMessage();
            }
        }
    }
}

// Include header
include '../includes/header.php';
?>

<div class="container mx-auto max-w-3xl">
    
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-3xl font-bold text-gray-800">Input Peminjaman Manual</h1>
            <p class="text-gray-500 mt-1">Form khusus admin untuk mencatat peminjaman secara langsung.</p>
        </div>
        <a href="peminjaman_list.php" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition font-medium">
            <i class="bi bi-arrow-left mr-2"></i> Kembali
        </a>
    </div>

    <!-- Alert -->
    <?php if ($error): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow-sm">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill text-red-500"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium"><?php echo $error; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Form Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="p-8">
            <form method="POST" class="space-y-6">
                
                <!-- Peminjam -->
                <div>
                    <label for="user_id" class="block text-sm font-semibold text-gray-700 mb-2">Peminjam <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select id="user_id" name="user_id" class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all bg-white appearance-none" required>
                            <option value="">-- Pilih Peminjam --</option>
                            <?php while ($u = $result_users->fetch_assoc()): ?>
                                <option value="<?php echo $u['id']; ?>"><?php echo $u['nama_lengkap']; ?> (<?php echo ucfirst($u['role']); ?>)</option>
                            <?php endwhile; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <i class="bi bi-chevron-down text-xs"></i>
                        </div>
                    </div>
                </div>

                <!-- Barang -->
                <div>
                    <label for="barang_id" class="block text-sm font-semibold text-gray-700 mb-2">Barang <span class="text-red-500">*</span></label>
                    <div class="relative">
                        <select id="barang_id" name="barang_id" class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all bg-white appearance-none" required>
                            <option value="">-- Pilih Barang --</option>
                            <?php while ($b = $result_barang->fetch_assoc()): ?>
                                <option value="<?php echo $b['id']; ?>">
                                    <?php echo $b['nama']; ?> (Kode: <?php echo $b['kode']; ?>) - Stok: <?php echo $b['stok']; ?>
                                </option>
                            <?php endwhile; ?>
                        </select>
                        <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                            <i class="bi bi-chevron-down text-xs"></i>
                        </div>
                    </div>
                    <?php if ($result_barang->num_rows == 0): ?>
                        <p class="text-xs text-red-500 mt-1">Tidak ada barang tersedia untuk dipinjam.</p>
                    <?php endif; ?>
                </div>

                <!-- Jumlah -->
                <div>
                    <label for="jumlah" class="block text-sm font-semibold text-gray-700 mb-2">Jumlah <span class="text-red-500">*</span></label>
                    <input type="number" id="jumlah" name="jumlah" min="1" value="1" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all" required>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Tgl Pinjam -->
                    <div>
                        <label for="tgl_pinjam" class="block text-sm font-semibold text-gray-700 mb-2">Tanggal Pinjam <span class="text-red-500">*</span></label>
                        <input type="date" id="tgl_pinjam" name="tgl_pinjam" value="<?php echo date('Y-m-d'); ?>" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all" required>
                    </div>

                    <!-- Tgl Kembali -->
                    <div>
                        <label for="tgl_kembali" class="block text-sm font-semibold text-gray-700 mb-2">Rencana Kembali <span class="text-red-500">*</span></label>
                        <input type="date" id="tgl_kembali" name="tgl_kembali" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all" required>
                    </div>
                </div>

                <!-- Keperluan -->
                <div>
                    <label for="keterangan" class="block text-sm font-semibold text-gray-700 mb-2">Keperluan</label>
                    <textarea id="keterangan" name="keterangan" rows="3" class="w-full px-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all placeholder-gray-400" placeholder="Contoh: Untuk kegiatan praktikum Biologi kelas XII"></textarea>
                </div>

                <!-- Info Box -->
                <div class="bg-blue-50 p-4 rounded-xl border border-blue-100 flex items-start gap-3">
                    <i class="bi bi-info-circle-fill text-blue-500 mt-0.5"></i>
                    <div>
                        <p class="text-sm font-medium text-blue-800">Catatan Otomatis</p>
                        <p class="text-xs text-blue-600 mt-1">
                            Peminjaman yang diinput melalui halaman ini akan <strong>otomatis disetujui (Approved)</strong> dan status barang akan langsung menjadi <strong>Dipinjam</strong>. Stok barang akan berkurang secara otomatis.
                        </p>
                    </div>
                </div>

                <!-- Actions -->
                <div class="flex items-center justify-end space-x-4 pt-4 border-t border-gray-100">
                    <a href="peminjaman_list.php" class="px-6 py-2.5 border border-gray-300 text-gray-700 font-medium rounded-xl hover:bg-gray-50 transition">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2.5 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 shadow-sm hover:shadow-md transition transform hover:-translate-y-0.5">
                        <i class="bi bi-save mr-2"></i> Simpan Data
                    </button>
                </div>

            </form>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
