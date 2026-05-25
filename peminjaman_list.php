<?php
/**
 * File: peminjaman_list.php
 * Deskripsi: Halaman daftar peminjaman dengan Tailwind CSS
 */

// Set judul halaman
$page_title = 'Data Peminjaman';

// Include koneksi dan auth
require_once '../config/koneksi.php';

// Include auth helper
require_once '../config/auth.php';

// Hanya admin yang bisa akses
require_admin();

// Include header
include '../includes/header.php';

// ============================================
// HANDLE SEARCH & FILTER
// ============================================
$where_clauses = [];
if (isset($_GET['status']) && $_GET['status'] != '') {
    $status = clean_input($_GET['status']);
    $where_clauses[] = "p.status_kembali = '$status'";
}

if (isset($_GET['q']) && $_GET['q'] != '') {
    $keyword = clean_input($_GET['q']);
    $where_clauses[] = "(u.nama_lengkap LIKE '%$keyword%' OR b.nama LIKE '%$keyword%' OR b.kode LIKE '%$keyword%')";
}

$where_sql = "";
if (!empty($where_clauses)) {
    $where_sql = "WHERE " . implode(" AND ", $where_clauses);
}

$query = "SELECT p.*, u.nama_lengkap, b.nama as nama_barang, b.kode as kode_barang 
          FROM peminjaman p 
          JOIN users u ON p.user_id = u.id 
          JOIN barang b ON p.barang_id = b.id 
          $where_sql 
          ORDER BY p.created_at DESC";

$result = query($query);

// Handle Messages
$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'pengembalian_berhasil') $success_msg = "Barang berhasil dikembalikan!";
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
                <i class="bi bi-arrow-left-right text-teal-600"></i> Data Peminjaman
            </h1>
            <p class="text-gray-500 mt-1">Daftar riwayat dan status peminjaman barang.</p>
        </div>
        
        <div class="flex gap-2">
            <a href="../admin/peminjaman_pending.php" class="inline-flex items-center justify-center px-4 py-2 bg-amber-500 text-white font-medium rounded-xl hover:bg-amber-600 transition shadow-sm hover:shadow-md">
                <i class="bi bi-clock-history mr-2"></i> Review Pending
            </a>
            <a href="peminjaman_tambah.php" class="inline-flex items-center justify-center px-4 py-2 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 transition shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
                <i class="bi bi-plus-lg mr-2"></i> Tambah Peminjaman
            </a>
        </div>
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
                <div class="ml-auto pl-3">
                    <div class="-mx-1.5 -my-1.5">
                        <button type="button" onclick="this.parentElement.parentElement.parentElement.parentElement.remove()" class="inline-flex bg-green-50 rounded-md p-1.5 text-green-500 hover:bg-green-100 focus:outline-none">
                            <i class="bi bi-x text-xl"></i>
                        </button>
                    </div>
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

    <!-- Search & Filter Card -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-6 mb-6">
        <form action="" method="GET" class="flex flex-col md:flex-row gap-4">
            <div class="md:w-1/4">
                <div class="relative">
                    <select name="status" class="w-full pl-4 pr-10 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all appearance-none bg-white">
                        <option value="">-- Semua Status --</option>
                        <option value="dipinjam" <?php if(isset($_GET['status']) && $_GET['status'] == 'dipinjam') echo 'selected'; ?>>Sedang Dipinjam</option>
                        <option value="dikembalikan" <?php if(isset($_GET['status']) && $_GET['status'] == 'dikembalikan') echo 'selected'; ?>>Sudah Kembali</option>
                    </select>
                    <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-4 text-gray-500">
                        <i class="bi bi-chevron-down text-xs"></i>
                    </div>
                </div>
            </div>
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
                <input type="text" 
                       name="q" 
                       value="<?php echo isset($_GET['q']) ? $_GET['q'] : ''; ?>" 
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all" 
                       placeholder="Cari user atau barang...">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-900 transition shadow-sm">
                    Filter
                </button>
                <?php if (isset($_GET['q']) || isset($_GET['status'])): ?>
                    <a href="peminjaman_list.php" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-300 transition">
                        Reset
                    </a>
                <?php endif; ?>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600 uppercase text-xs">
                    <tr>
                        <th class="px-6 py-4 font-semibold tracking-wider">Peminjam</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Barang</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Tgl Pinjam</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Tgl Kembali</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Status Approval</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Status Barang</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4 align-middle font-medium text-gray-900">
                                    <?php echo $row['nama_lengkap']; ?>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center">
                                        <div class="w-8 h-8 bg-teal-50 rounded-lg flex items-center justify-center text-teal-600 mr-3">
                                            <i class="bi bi-box-seam"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo $row['nama_barang']; ?></p>
                                            <p class="text-xs text-gray-500 font-mono">
                                                <?php echo $row['kode_barang']; ?> 
                                                <span class="ml-1 bg-gray-100 text-gray-600 px-1.5 py-0.5 rounded text-[10px]">Qty: <?php echo $row['jumlah']; ?></span>
                                            </p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle text-gray-600">
                                    <?php echo date('d/m/Y', strtotime($row['tgl_pinjam'])); ?>
                                </td>
                                <td class="px-6 py-4 align-middle text-gray-600">
                                    <?php 
                                        if ($row['tgl_dikembalikan']) {
                                            echo date('d/m/Y', strtotime($row['tgl_dikembalikan']));
                                        } else {
                                            echo '-';
                                        }
                                    ?>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <?php if ($row['status_approval'] == 'pending'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-amber-100 text-amber-800">
                                            <i class="bi bi-clock mr-1"></i> Pending
                                        </span>
                                    <?php elseif ($row['status_approval'] == 'approved'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-teal-100 text-teal-800">
                                            <i class="bi bi-check-circle mr-1"></i> Approved
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <i class="bi bi-x-circle mr-1"></i> Rejected
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <?php if ($row['status_kembali'] == 'dipinjam'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            Dipinjam
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            Dikembalikan
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <?php if ($row['status_approval'] == 'pending'): ?>
                                        <a href="../admin/peminjaman_pending.php" class="text-amber-600 hover:text-amber-800 text-xs font-medium bg-amber-50 hover:bg-amber-100 px-3 py-1.5 rounded-lg transition">
                                            Review
                                        </a>
                                    <?php elseif ($row['status_approval'] == 'approved' && $row['status_kembali'] == 'dipinjam'): ?>
                                        <a href="peminjaman_kembali.php?id=<?php echo $row['id']; ?>" 
                                           class="text-teal-600 hover:text-teal-800 text-xs font-medium bg-teal-50 hover:bg-teal-100 px-3 py-1.5 rounded-lg transition"
                                           onclick="return confirm('Apakah barang ini sudah dikembalikan dan stok akan ditambahkan kembali?')">
                                            <i class="bi bi-arrow-return-left mr-1"></i> Kembalikan
                                        </a>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">Selesai</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" class="px-6 py-12 text-center text-gray-500">
                                <i class="bi bi-inbox text-4xl mb-3 block text-gray-300"></i>
                                <p>Tidak ada data peminjaman ditemukan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
