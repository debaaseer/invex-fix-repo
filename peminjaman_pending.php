<?php
/**
 * File: peminjaman_pending.php
 * Deskripsi: Halaman approval peminjaman dengan Tailwind CSS
 */

// Set judul halaman
$page_title = 'Approval Peminjaman';

// Include koneksi
require_once '../config/koneksi.php';

// Include auth helper
require_once '../config/auth.php';

// Hanya admin yang bisa akses
require_admin();

// Include header
include '../includes/header.php';

// Query peminjaman dengan status pending
$query = "SELECT p.*, u.nama_lengkap, u.role, b.nama as nama_barang, b.kode as kode_barang, b.stok as stok_saat_ini 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.id 
          JOIN barang b ON p.barang_id = b.id 
          WHERE p.status_approval = 'pending' 
          ORDER BY p.created_at ASC";

$result = query($query);

// Handle Messages
$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'approved') $success_msg = "Peminjaman berhasil disetujui!";
    elseif ($_GET['success'] == 'rejected') $success_msg = "Peminjaman berhasil ditolak!";
}

if (isset($_GET['error'])) {
    $error_msg = clean_input($_GET['error']);
}
?>

<div class="container mx-auto">
    
    <!-- Page Header -->
    <div class="flex flex-col md:flex-row md:items-center justify-between mb-8 gap-4">
        <div>
            <h1 class="text-3xl font-bold text-gray-800 flex items-center gap-2">
                <i class="bi bi-clock-history text-amber-500"></i> Pending Approval
            </h1>
            <p class="text-gray-500 mt-1">Daftar permintaan peminjaman yang menunggu persetujuan.</p>
        </div>
        
        <a href="../peminjaman/peminjaman_list.php" class="inline-flex items-center text-gray-600 hover:text-gray-900 transition font-medium">
            <i class="bi bi-arrow-left mr-2"></i> Kembali ke Daftar
        </a>
    </div>

    <!-- Alert Messages -->
    <?php if (!empty($success_msg)): ?>
        <div class="bg-green-50 border-l-4 border-green-500 p-4 mb-6 rounded-r-xl shadow-sm animate-fade-in-down">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="bi bi-check-circle-fill text-green-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-green-700 font-medium"><?php echo $success_msg; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <?php if (!empty($error_msg)): ?>
        <div class="bg-red-50 border-l-4 border-red-500 p-4 mb-6 rounded-r-xl shadow-sm animate-fade-in-down">
            <div class="flex">
                <div class="flex-shrink-0">
                    <i class="bi bi-exclamation-triangle-fill text-red-500 text-xl"></i>
                </div>
                <div class="ml-3">
                    <p class="text-sm text-red-700 font-medium"><?php echo $error_msg; ?></p>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Request Grid -->
    <?php if ($result && $result->num_rows > 0): ?>
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            <?php while ($row = $result->fetch_assoc()): ?>
                <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden hover:shadow-md transition-shadow">
                    <!-- Status Header -->
                    <div class="bg-amber-50 px-6 py-3 border-b border-amber-100 flex justify-between items-center">
                        <span class="text-xs font-semibold text-amber-700 bg-amber-100 px-2.5 py-0.5 rounded-full uppercase tracking-wide">Pending Review</span>
                        <span class="text-xs text-amber-600 font-medium">
                            <i class="bi bi-calendar3 mr-1"></i> <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                        </span>
                    </div>
                    
                    <div class="p-6">
                        <!-- User Info -->
                        <div class="flex items-center mb-4">
                            <div class="w-10 h-10 bg-gray-100 rounded-full flex items-center justify-center text-gray-500 font-bold mr-3">
                                <?php echo strtoupper(substr($row['nama_lengkap'], 0, 1)); ?>
                            </div>
                            <div>
                                <h3 class="text-sm font-bold text-gray-800"><?php echo $row['nama_lengkap']; ?></h3>
                                <p class="text-xs text-gray-500 capitalize"><?php echo $row['role']; ?></p>
                            </div>
                        </div>

                        <!-- Item Info -->
                        <div class="bg-gray-50 rounded-xl p-4 mb-6">
                            <div class="flex justify-between items-start mb-2">
                                <div>
                                    <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Meminjam Barang</p>
                                    <p class="font-bold text-gray-800 text-lg"><?php echo $row['nama_barang']; ?></p>
                                    <p class="text-xs text-gray-500 font-mono"><?php echo $row['kode_barang']; ?></p>
                                </div>
                                <div class="w-10 h-10 bg-white rounded-lg border border-gray-200 flex items-center justify-center text-teal-600">
                                    <i class="bi bi-box-seam text-xl"></i>
                                </div>
                            </div>
                            <div class="mt-2 pt-2 border-t border-gray-200 flex justify-between">
                                <span class="text-xs text-gray-500">Durasi:</span>
                                <span class="text-xs font-medium text-gray-800">
                                    <?php echo date('d/m', strtotime($row['tgl_pinjam'])); ?> - <?php echo date('d/m', strtotime($row['tgl_kembali'])); ?>
                                </span>
                            </div>
                            <div class="mt-1 flex justify-between">
                                <span class="text-xs text-gray-500">Jumlah Diminta:</span>
                                <span class="text-xs font-bold text-gray-800">
                                    <?php echo $row['jumlah']; ?> unit
                                </span>
                            </div>
                            <div class="mt-1 flex justify-between">
                                <span class="text-xs text-gray-500">Stok Tersedia:</span>
                                <span class="text-xs font-bold <?php echo $row['stok_saat_ini'] >= $row['jumlah'] ? 'text-green-600' : 'text-red-600'; ?>">
                                    <?php echo $row['stok_saat_ini']; ?> unit
                                </span>
                            </div>
                        </div>

                        <!-- Alasan -->
                        <div class="mb-6">
                            <p class="text-xs text-gray-400 uppercase tracking-wide font-semibold mb-1">Keperluan</p>
                            <p class="text-sm text-gray-600 italic">"<?php echo !empty($row['keterangan']) ? $row['keterangan'] : '-'; ?>"</p>
                        </div>

                        <!-- Action Buttons -->
                        <div class="grid grid-cols-2 gap-3">
                            <a href="peminjaman_reject.php?id=<?php echo $row['id']; ?>" 
                               class="flex items-center justify-center px-4 py-2 border border-red-500 text-red-500 font-medium rounded-xl hover:bg-red-50 transition"
                               onclick="return confirm('Apakah Anda yakin ingin menolak pengajuan ini?')">
                                <i class="bi bi-x-lg mr-2"></i> Tolak
                            </a>
                            
                            <?php if ($row['stok_saat_ini'] >= $row['jumlah']): ?>
                                <a href="peminjaman_approve.php?id=<?php echo $row['id']; ?>" 
                                   class="flex items-center justify-center px-4 py-2 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 transition shadow-sm hover:shadow-md">
                                    <i class="bi bi-check-lg mr-2"></i> Setujui
                                </a>
                            <?php else: ?>
                                <button disabled class="flex items-center justify-center px-4 py-2 bg-gray-300 text-gray-500 font-medium rounded-xl cursor-not-allowed" title="Stok tidak mencukupi untuk jumlah yang diminta">
                                    Stok Kurang
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    <?php else: ?>
        <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-12 text-center">
            <div class="w-20 h-20 bg-green-50 rounded-full flex items-center justify-center mx-auto mb-6">
                <i class="bi bi-check-lg text-4xl text-green-500"></i>
            </div>
            <h3 class="text-xl font-bold text-gray-800 mb-2">Semua Bersih!</h3>
            <p class="text-gray-500">Tidak ada pengajuan peminjaman yang menunggu persetujuan saat ini.</p>
        </div>
    <?php endif; ?>

</div>

<?php include '../includes/footer.php'; ?>
