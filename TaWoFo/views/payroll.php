<?php
// Memulai session untuk mengecek apakah user sudah login
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php'); // Arahkan ke halaman login jika belum login
    exit();
}

require_once '../config/koneksi.php'; // Menghubungkan dengan database

// Tambah atau Update Data Payroll
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id']; // Mendapatkan ID karyawan
    $salary = $_POST['salary']; // Mendapatkan gaji
    $month = $_POST['month']; // Mendapatkan bulan
    $year = $_POST['year']; // Mendapatkan tahun
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null; // Jika ada ID, lakukan update, jika tidak, insert data baru

    try {
        if ($id) {
            // Update Payroll jika ID ditemukan
            $sql = "UPDATE payroll SET employee_id = ?, salary = ?, month = ?, year = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employee_id, $salary, $month, $year, $id]);
        } else {
            // Insert data Payroll baru
            $sql = "INSERT INTO payroll (employee_id, salary, month, year) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employee_id, $salary, $month, $year]);
        }
        header('Location: payroll.php'); // Redirect ke halaman payroll setelah berhasil
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Menampilkan pesan error jika terjadi kesalahan
    }
}

// Hapus Data Payroll
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id']; // Mendapatkan ID yang akan dihapus

    try {
        $sql = "DELETE FROM payroll WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$delete_id]); // Eksekusi perintah DELETE
        header('Location: payroll.php'); // Redirect ke halaman payroll setelah penghapusan
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Menampilkan pesan error jika terjadi kesalahan
    }
}

// Load data untuk edit
$payroll = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id']; // Mendapatkan ID yang akan diedit
    try {
        $sql = "SELECT * FROM payroll WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$edit_id]);
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC); // Mengambil data untuk edit
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage(); // Menampilkan pesan error jika terjadi kesalahan
    }
}

// Pencarian Payroll
$search = isset($_GET['search']) ? $_GET['search'] : ''; // Mengambil nilai pencarian
$payrolls = []; // Menyimpan data payroll yang ditemukan
try {
    // Query untuk mencari payroll berdasarkan nama karyawan
    $sql = "SELECT p.id, p.employee_id, e.name, e.position, p.salary, p.month, p.year 
            FROM payroll p 
            JOIN employees e ON p.employee_id = e.id 
            WHERE e.name LIKE :search
            ORDER BY p.year DESC, p.month DESC"; // Mengurutkan berdasarkan tahun dan bulan
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$search%"]); // Eksekusi query pencarian
    $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC); // Menyimpan hasil pencarian
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // Menampilkan pesan error jika terjadi kesalahan
}

// Ambil data karyawan untuk dropdown
$employees = []; // Menyimpan data karyawan
try {
    $sql = "SELECT id, name FROM employees"; // Query untuk mengambil data karyawan
    $stmt = $pdo->query($sql);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC); // Menyimpan data karyawan ke dalam array
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage(); // Menampilkan pesan error jika terjadi kesalahan
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaWoFo Payroll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet"> <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .main-content {
            padding: 20px;
            flex-grow: 1;
        }

        .table thead {
            background-color: #007bff;
            color: white;
        }

        .btn-primary {
            background-color: #007bff;
            border: none;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .btn-secondary {
            background-color: #6c757d;
            border: none;
        }

        .btn-secondary:hover {
            background-color: #5a6268;
        }

        .btn-warning {
            background-color: #ffc107;
            border: none;
        }

        .btn-warning:hover {
            background-color: #e0a800;
        }

        .btn-danger {
            background-color: #dc3545;
            border: none;
        }

        .btn-danger:hover {
            background-color: #c82333;
        }
    </style>
</head>
<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-4">
            <h2>TaWoFo</h2>
            <ul class="nav flex-column">
                <li class="nav-item">
                    <a href="index.php" class="nav-link"><i class="fas fa-home me-2"></i>Dashboard</a>
                </li>
                <li class="nav-item">
                    <a href="employees.php" class="nav-link"><i class="fas fa-users me-2"></i>Employees</a>
                </li>
                <li class="nav-item">
                    <a href="payroll.php" class="nav-link active"><i class="fas fa-dollar-sign me-2"></i>Payroll</a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link "><i class="fas fa-chart-bar me-2"></i>Reports</a>
                </li>
                <li class="nav-item mt-auto">
                    <a href="logout.php" class="nav-link bg-danger text-white"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <h4 class="mb-4">Manajemen Penggajian</h4>

            <!-- Search Form -->
            <form method="GET" class="mb-4">
                <div class="input-group">
                    <input type="text" name="search" class="form-control" placeholder="Cari berdasarkan nama karyawan" value="<?php echo htmlspecialchars($search); ?>">
                    <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                </div>
            </form>

            <!-- Add/Edit Payroll Form -->
            <form method="POST" class="mb-4">
                <input type="hidden" name="id" value="<?php echo isset($payroll['id']) ? htmlspecialchars($payroll['id']) : ''; ?>">
                <div class="row g-3">
                    <div class="col-md-3">
                        <label for="employee_id" class="form-label">Karyawan</label>
                        <select name="employee_id" class="form-select" required>
                            <option value="" disabled selected>Pilih Karyawan</option>
                            <?php foreach ($employees as $employee): ?>
                                <option value="<?php echo $employee['id']; ?>" <?php echo isset($payroll['employee_id']) && $payroll['employee_id'] == $employee['id'] ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($employee['name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="salary" class="form-label">Gaji</label>
                        <input type="number" name="salary" class="form-control" value="<?php echo isset($payroll['salary']) ? htmlspecialchars($payroll['salary']) : ''; ?>" required>
                    </div>

                    <div class="col-md-3">
                        <label for="month" class="form-label">Bulan</label>
                        <select name="month" class="form-select" required>
                            <option value="" disabled selected>Pilih Bulan</option>
                            <?php for ($i = 1; $i <= 12; $i++): ?>
                                <option value="<?php echo $i; ?>" <?php echo isset($payroll['month']) && $payroll['month'] == $i ? 'selected' : ''; ?>>
                                    <?php echo date('F', mktime(0, 0, 0, $i, 1)); ?>
                                </option>
                            <?php endfor; ?>
                        </select>
                    </div>

                    <div class="col-md-3">
                        <label for="year" class="form-label">Tahun</label>
                        <input type="number" name="year" class="form-control" value="<?php echo isset($payroll['year']) ? htmlspecialchars($payroll['year']) : ''; ?>" required>
                    </div>
                </div>

                <div class="mt-3">
                    <button type="submit" class="btn btn-primary">Simpan</button>
                    <a href="payroll.php" class="btn btn-secondary">Batal</a>
                </div>
            </form>

            <!-- Payroll Table -->
            <div class="table-responsive">
                <table class="table table-bordered">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Karyawan</th>
                            <th>Posisi</th>
                            <th>Gaji</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($payrolls as $index => $payroll): ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($payroll['name']); ?></td>
                                <td><?php echo htmlspecialchars($payroll['position']); ?></td>
                                <td><?php echo number_format($payroll['salary'], 0, ',', '.'); ?></td>
                                <td><?php echo date('F', mktime(0, 0, 0, $payroll['month'], 1)); ?></td>
                                <td><?php echo $payroll['year']; ?></td>
                                <td>
                                    <a href="payroll.php?edit_id=<?php echo $payroll['id']; ?>" class="btn btn-warning btn-sm"><i class="fas fa-edit"></i> Edit</a>
                                    <a href="payroll.php?delete_id=<?php echo $payroll['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?')"><i class="fas fa-trash-alt"></i> Hapus</a>
                                </td>
                            </tr>
                        <?php
 endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script> <!-- Bootstrap JS -->
</body>
</html>
