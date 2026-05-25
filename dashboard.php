<?php
/**
 * File: dashboard.php
 * Deskripsi: Halaman dashboard admin dengan Tailwind CSS
 */

// Set judul halaman untuk header
$page_title = 'Dashboard';

// Include koneksi database
require_once 'config/koneksi.php';

// Include auth helper
require_once 'config/auth.php';

// Hanya admin yang bisa akses dashboard ini
require_admin();

// Include header (sudah ada session_start dan cek login di dalamnya)
include 'includes/header.php';

// ============================================
// QUERY STATISTIK
// ============================================

// 1. Total Barang
$query_barang = "SELECT COUNT(*) as total FROM barang";
$result_barang = query($query_barang);
$total_barang = $result_barang->fetch_assoc()['total'];

// 2. Total Peminjaman (Aktif)
$query_pinjam = "SELECT COUNT(*) as total FROM peminjaman WHERE status_kembali = 'dipinjam' AND status_approval = 'approved'";
$result_pinjam = query($query_pinjam);
$total_pinjam = $result_pinjam->fetch_assoc()['total'];

// 3. Pending Approval
$query_pending = "SELECT COUNT(*) as total FROM peminjaman WHERE status_approval = 'pending'";
$result_pending = query($query_pending);
$total_pending = $result_pending->fetch_assoc()['total'];

// 4. Barang Rusak
$query_rusak = "SELECT COUNT(*) as total FROM barang WHERE status = 'rusak'";
$result_rusak = query($query_rusak);
$total_rusak = $result_rusak->fetch_assoc()['total'];

// 5. Recent Activity (5 Peminjaman Terakhir)
$query_recent = "SELECT p.*, u.nama_lengkap, b.nama as nama_barang 
                 FROM peminjaman p 
                 JOIN users u ON p.user_id = u.id 
                 JOIN barang b ON p.barang_id = b.id 
                 ORDER BY p.created_at DESC LIMIT 5";
$result_recent = query($query_recent);

?>

<!-- Dashboard Content -->
<div class="container mx-auto">
    
    <!-- Welcome Header -->
    <div class="mb-8">
        <h1 class="text-3xl font-bold text-gray-800">Dashboard Overview</h1>
        <p class="text-gray-500 mt-2">Selamat datang kembali, <span class="font-semibold text-teal-600"><?php echo $_SESSION['nama_lengkap']; ?></span>!</p>
    </div>

    <!-- Stats Grid -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        
        <!-- Card 1: Total Barang -->
        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition-shadow border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-blue-100 rounded-xl flex items-center justify-center text-blue-600">
                    <i class="bi bi-box-seam text-2xl"></i>
                </div>
                <span class="text-xs font-semibold px-2 py-1 bg-green-100 text-green-600 rounded-lg">+12%</span>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Total Barang</h3>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_barang; ?></p>
        </div>

        <!-- Card 2: Peminjaman Aktif -->
        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition-shadow border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-purple-100 rounded-xl flex items-center justify-center text-purple-600">
                    <i class="bi bi-arrow-left-right text-2xl"></i>
                </div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Sedang Dipinjam</h3>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_pinjam; ?></p>
        </div>

        <!-- Card 3: Pending Approval -->
        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition-shadow border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-orange-100 rounded-xl flex items-center justify-center text-orange-600">
                    <i class="bi bi-clock-history text-2xl"></i>
                </div>
                <?php if ($total_pending > 0): ?>
                    <span class="flex h-3 w-3 relative">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 bg-orange-500"></span>
                    </span>
                <?php endif; ?>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Pending Approval</h3>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_pending; ?></p>
            <?php if ($total_pending > 0): ?>
                <a href="admin/peminjaman_pending.php" class="text-xs text-orange-600 font-medium hover:underline mt-2 inline-block">Review Request &rarr;</a>
            <?php endif; ?>
        </div>

        <!-- Card 4: Barang Rusak -->
        <div class="bg-white rounded-2xl shadow-sm p-6 hover:shadow-md transition-shadow border border-gray-100">
            <div class="flex items-center justify-between mb-4">
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center text-red-600">
                    <i class="bi bi-exclamation-triangle text-2xl"></i>
                </div>
            </div>
            <h3 class="text-gray-500 text-sm font-medium">Barang Rusak</h3>
            <p class="text-3xl font-bold text-gray-800 mt-1"><?php echo $total_rusak; ?></p>
        </div>
    </div>

    <!-- Recent Activity & Quick Actions -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        
        <!-- Left Column: Recent Activity Table -->
        <div class="lg:col-span-2 bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
            <div class="p-6 border-b border-gray-100 flex justify-between items-center">
                <h3 class="font-bold text-gray-800 text-lg">Aktivitas Terakhir</h3>
                <a href="peminjaman/peminjaman_list.php" class="text-teal-600 text-sm font-medium hover:underline">Lihat Semua</a>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-500 uppercase text-xs">
                        <tr>
                            <th class="px-6 py-3 font-medium">User</th>
                            <th class="px-6 py-3 font-medium">Barang</th>
                            <th class="px-6 py-3 font-medium">Tanggal</th>
                            <th class="px-6 py-3 font-medium">Status</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-100">
                        <?php if ($result_recent->num_rows > 0): ?>
                            <?php while ($row = $result_recent->fetch_assoc()): ?>
                                <tr class="hover:bg-gray-50 transition-colors">
                                    <td class="px-6 py-4 font-medium text-gray-800">
                                        <?php echo $row['nama_lengkap']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-600">
                                        <?php echo $row['nama_barang']; ?>
                                    </td>
                                    <td class="px-6 py-4 text-gray-500">
                                        <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                    </td>
                                    <td class="px-6 py-4">
                                        <?php if ($row['status_approval'] == 'pending'): ?>
                                            <span class="px-2 py-1 rounded-full bg-orange-100 text-orange-600 text-xs font-semibold">Pending</span>
                                        <?php elseif ($row['status_kembali'] == 'dipinjam'): ?>
                                            <span class="px-2 py-1 rounded-full bg-blue-100 text-blue-600 text-xs font-semibold">Dipinjam</span>
                                        <?php else: ?>
                                            <span class="px-2 py-1 rounded-full bg-green-100 text-green-600 text-xs font-semibold">Selesai</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500">
                                    Belum ada aktivitas terbaru.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Right Column: Quick Actions -->
        <div class="space-y-6">
            
            <!-- Quick Action Card -->
            <div class="bg-gradient-to-br from-teal-500 to-teal-700 rounded-2xl shadow-lg p-6 text-white relative overflow-hidden">
                <!-- Decorative Circle -->
                <div class="absolute top-0 right-0 -mt-4 -mr-4 w-24 h-24 bg-white opacity-10 rounded-full blur-xl"></div>
                
                <h3 class="text-xl font-bold mb-2">Aksi Cepat</h3>
                <p class="text-teal-100 text-sm mb-6">Akses menu penting dengan satu klik untuk efisiensi kerja.</p>
                
                <div class="space-y-3">
                    <a href="barang/barang_tambah.php" class="flex items-center justify-between bg-white/10 hover:bg-white/20 p-3 rounded-xl transition-colors backdrop-blur-sm cursor-pointer group">
                        <div class="flex items-center space-x-3">
                            <i class="bi bi-plus-square text-lg"></i>
                            <span class="font-medium">Tambah Barang Baru</span>
                        </div>
                        <i class="bi bi-chevron-right text-xs opacity-50 group-hover:opacity-100 transition-opacity"></i>
                    </a>

                    <a href="users/user_tambah.php" class="flex items-center justify-between bg-white/10 hover:bg-white/20 p-3 rounded-xl transition-colors backdrop-blur-sm cursor-pointer group">
                        <div class="flex items-center space-x-3">
                            <i class="bi bi-person-plus text-lg"></i>
                            <span class="font-medium">Tambah User Baru</span>
                        </div>
                        <i class="bi bi-chevron-right text-xs opacity-50 group-hover:opacity-100 transition-opacity"></i>
                    </a>
                </div>
            </div>

            <!-- Important Info Card -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6">
                <h3 class="font-bold text-gray-800 mb-4">Informasi Sistem</h3>
                <div class="space-y-4">
                    <div class="flex items-start space-x-3">
                        <i class="bi bi-shield-check text-green-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800">Sistem Berjalan Normal</p>
                            <p class="text-xs text-gray-500">Database terhubung dengan baik.</p>
                        </div>
                    </div>
                     <div class="flex items-start space-x-3">
                        <i class="bi bi-calendar-check text-blue-500 mt-1"></i>
                        <div>
                            <p class="text-sm font-medium text-gray-800"><?php echo date('d F Y'); ?></p>
                            <p class="text-xs text-gray-500">Tanggal hari ini.</p>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
