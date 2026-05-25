<?php
/**
 * File: barang_list.php
 * Deskripsi: Halaman daftar barang dengan Tailwind CSS
 */

// Set judul halaman
$page_title = 'Data Barang';

// Include koneksi dan auth
require_once '../config/koneksi.php';

// Include auth helper
require_once '../config/auth.php';

// Include header
include '../includes/header.php';

// ============================================
// HANDLE SEARCH
// ============================================
$keyword = "";
if (isset($_GET['q'])) {
    $keyword = clean_input($_GET['q']);
    $query = "SELECT * FROM barang WHERE 
              nama LIKE '%$keyword%' OR 
              kode LIKE '%$keyword%' OR 
              kategori LIKE '%$keyword%' 
              ORDER BY created_at DESC";
} else {
    $query = "SELECT * FROM barang ORDER BY created_at DESC";
}

$result = query($query);

// Handle Success/Error Messages from URL
$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'ditambah') $success_msg = "Barang berhasil ditambahkan!";
    elseif ($_GET['success'] == 'diedit') $success_msg = "Barang berhasil diperbarui!";
    elseif ($_GET['success'] == 'dihapus') $success_msg = "Barang berhasil dihapus!";
    elseif ($_GET['success'] == 'barang_marked_rusak') $success_msg = "Barang status berubah menjadi RUSAK karena sedang dipinjam!";
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
                <i class="bi bi-box-seam text-teal-600"></i> Data Barang
            </h1>
            <p class="text-gray-500 mt-1">Kelola inventaris barang sekolah dengan mudah.</p>
        </div>
        
        <?php if (is_admin()): ?>
        <a href="barang_tambah.php" class="inline-flex items-center justify-center px-4 py-2 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 transition shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
            <i class="bi bi-plus-lg mr-2"></i> Tambah Barang
        </a>
        <?php endif; ?>
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
            <div class="flex-1 relative">
                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                    <i class="bi bi-search text-gray-400"></i>
                </div>
                <input type="text" 
                       name="q" 
                       value="<?php echo $keyword; ?>" 
                       class="w-full pl-10 pr-4 py-2.5 border border-gray-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-teal-500 focus:border-transparent transition-all" 
                       placeholder="Cari nama barang, kode, atau kategori...">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-900 transition shadow-sm">
                    Cari
                </button>
                <?php if (!empty($keyword)): ?>
                    <a href="barang_list.php" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-300 transition">
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
                        <th class="px-6 py-4 font-semibold tracking-wider">Barang</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Kategori</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Stok</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Status</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-teal-50 rounded-lg flex items-center justify-center text-teal-600 mr-3 group-hover:bg-teal-100 transition-colors">
                                            <i class="bi bi-box-seam text-lg"></i>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo $row['nama']; ?></p>
                                            <p class="text-xs text-gray-500 font-mono bg-gray-100 px-1.5 py-0.5 rounded inline-block mt-1"><?php echo $row['kode']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="text-gray-600"><?php echo $row['kategori']; ?></span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <span class="font-medium text-gray-800"><?php echo $row['stok']; ?></span> <span class="text-gray-400 text-xs">unit</span>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <?php if ($row['status'] == 'tersedia'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-green-100 text-green-800">
                                            <span class="w-1.5 h-1.5 bg-green-500 rounded-full mr-1.5"></span> Tersedia
                                        </span>
                                    <?php elseif ($row['status'] == 'tidak_tersedia'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800">
                                            <span class="w-1.5 h-1.5 bg-gray-500 rounded-full mr-1.5"></span> Habis
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-red-100 text-red-800">
                                            <span class="w-1.5 h-1.5 bg-red-500 rounded-full mr-1.5"></span> Rusak
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <?php if (is_admin()): ?>
                                        <div class="flex items-center justify-end space-x-2">
                                            <a href="barang_edit.php?id=<?php echo $row['id']; ?>" 
                                               class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="barang_delete.php?id=<?php echo $row['id']; ?>" 
                                               class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" 
                                               onclick="return confirmDelete('Apakah Anda yakin ingin menghapus barang ini?')"
                                               title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        </div>
                                    <?php else: ?>
                                        <span class="text-xs text-gray-400 italic">View Only</span>
                                    <?php endif; ?>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="bi bi-inbox text-4xl mb-3 block text-gray-300"></i>
                                <p>Tidak ada data barang ditemukan.</p>
                                <?php if (!empty($keyword)): ?>
                                    <p class="text-sm mt-1">Coba kata kunci pencarian lain.</p>
                                <?php elseif (is_admin()): ?>
                                    <a href="barang_tambah.php" class="text-teal-600 hover:underline mt-2 inline-block">Tambah Barang Baru</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <!-- Pagination (Static for now, can be dynamic later) -->
        <div class="px-6 py-4 border-t border-gray-100 bg-gray-50 flex items-center justify-between">
            <span class="text-xs text-gray-500">Menampilkan <?php echo $result ? $result->num_rows : 0; ?> data</span>
            <div class="flex space-x-1">
                <button class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-white disabled:opacity-50" disabled>Previous</button>
                <button class="px-3 py-1 text-xs border border-gray-300 rounded hover:bg-white disabled:opacity-50" disabled>Next</button>
            </div>
        </div>
    </div>
</div>

<script>
function confirmDelete(message) {
    return confirm(message);
}
</script>

<?php include '../includes/footer.php'; ?>
