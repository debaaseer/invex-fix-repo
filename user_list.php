<?php
/**
 * File: user_list.php
 * Deskripsi: Halaman daftar user dengan Tailwind CSS
 */

// Set judul halaman
$page_title = 'User Management';

// Include koneksi
require_once '../config/koneksi.php';

// Include auth helper
require_once '../config/auth.php';

// Hanya admin yang bisa akses
require_admin();

// Include header
include '../includes/header.php';

// Handle Search
$keyword = "";
if (isset($_GET['q'])) {
    $keyword = clean_input($_GET['q']);
    $query = "SELECT * FROM users WHERE 
              nama_lengkap LIKE '%$keyword%' OR 
              username LIKE '%$keyword%' OR 
              email LIKE '%$keyword%' 
              ORDER BY nama_lengkap ASC";
} else {
    $query = "SELECT * FROM users ORDER BY nama_lengkap ASC";
}

$result = query($query);

// Handle Messages
$success_msg = '';
$error_msg = '';

if (isset($_GET['success'])) {
    if ($_GET['success'] == 'user_berhasil_ditambah') $success_msg = "User berhasil ditambahkan!";
    elseif ($_GET['success'] == 'user_berhasil_diedit') $success_msg = "User berhasil diperbarui!";
    elseif ($_GET['success'] == 'user_berhasil_dihapus') $success_msg = "User berhasil dihapus!";
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
                <i class="bi bi-people text-teal-600"></i> User Management
            </h1>
            <p class="text-gray-500 mt-1">Kelola akun pengguna (Admin & Guru).</p>
        </div>
        
        <a href="user_tambah.php" class="inline-flex items-center justify-center px-4 py-2 bg-teal-600 text-white font-medium rounded-xl hover:bg-teal-700 transition shadow-sm hover:shadow-md transform hover:-translate-y-0.5">
            <i class="bi bi-person-plus mr-2"></i> Tambah User
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

    <!-- Search Card -->
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
                       placeholder="Cari nama, username, atau email...">
            </div>
            <div class="flex gap-2">
                <button type="submit" class="px-6 py-2.5 bg-gray-800 text-white font-medium rounded-xl hover:bg-gray-900 transition shadow-sm">
                    Cari
                </button>
                <?php if (!empty($keyword)): ?>
                    <a href="user_list.php" class="px-6 py-2.5 bg-gray-200 text-gray-700 font-medium rounded-xl hover:bg-gray-300 transition">
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
                        <th class="px-6 py-4 font-semibold tracking-wider">User</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Username</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Role</th>
                        <th class="px-6 py-4 font-semibold tracking-wider">Tgl Terdaftar</th>
                        <th class="px-6 py-4 font-semibold tracking-wider text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
                            <tr class="hover:bg-gray-50 transition-colors group">
                                <td class="px-6 py-4 align-middle">
                                    <div class="flex items-center">
                                        <div class="w-10 h-10 bg-gradient-to-br from-gray-100 to-gray-200 rounded-full flex items-center justify-center text-gray-500 font-bold mr-3 border border-gray-200">
                                            <?php echo strtoupper(substr($row['nama_lengkap'], 0, 1)); ?>
                                        </div>
                                        <div>
                                            <p class="font-medium text-gray-900"><?php echo $row['nama_lengkap']; ?></p>
                                            <p class="text-xs text-gray-500"><?php echo $row['email']; ?></p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4 align-middle font-mono text-gray-600">
                                    @<?php echo $row['username']; ?>
                                </td>
                                <td class="px-6 py-4 align-middle">
                                    <?php if ($row['role'] == 'admin'): ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-purple-100 text-purple-800">
                                            <i class="bi bi-shield-lock-fill mr-1"></i> Admin
                                        </span>
                                    <?php else: ?>
                                        <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800">
                                            <i class="bi bi-person-badge mr-1"></i> Guru
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td class="px-6 py-4 align-middle text-gray-500">
                                    <?php echo date('d M Y', strtotime($row['created_at'])); ?>
                                </td>
                                <td class="px-6 py-4 align-middle text-right">
                                    <div class="flex items-center justify-end space-x-2">
                                        <a href="user_edit.php?id=<?php echo $row['id']; ?>" 
                                           class="p-2 text-blue-600 hover:bg-blue-50 rounded-lg transition-colors" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </a>
                                        <?php if ($row['id'] != $_SESSION['user_id']): // Prevent delete own account ?>
                                            <a href="user_delete.php?id=<?php echo $row['id']; ?>" 
                                               class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors" 
                                               onclick="return confirm('Apakah Anda yakin ingin menghapus user ini?')"
                                               title="Hapus">
                                                <i class="bi bi-trash"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center text-gray-500">
                                <i class="bi bi-people text-4xl mb-3 block text-gray-300"></i>
                                <p>Tidak ada data user ditemukan.</p>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<?php include '../includes/footer.php'; ?>
