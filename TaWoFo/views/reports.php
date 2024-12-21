<?php
session_start();

// Redirect ke halaman login jika pengguna belum login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set username default jika belum ada
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Admin';
}

require_once '../config/koneksi.php';

// Inisialisasi variabel untuk laporan dan total
$reports = [];
$totalSalary = 0;
$totalEmployees = 0;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Menangani aksi Delete
if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Menghapus data payroll yang dipilih
    $deleteSql = "DELETE FROM payroll WHERE id = :id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute(['id' => $deleteId]);

    // Setelah menghapus, redirect ke halaman laporan
    header("Location: reports.php");
    exit();
}

// Mengubah query SQL berdasarkan kata pencarian
try {
    $sql = "SELECT e.name, e.position, p.salary, p.month, p.year, p.id AS payroll_id
            FROM payroll p 
            JOIN employees e ON p.employee_id = e.id 
            WHERE e.name LIKE :search OR e.position LIKE :search
            ORDER BY p.year DESC, p.month DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => '%' . $searchTerm . '%']);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Menghitung total gaji dan jumlah karyawan
    $totalEmployees = count($reports);
    foreach ($reports as $report) {
        $totalSalary += $report['salary'];
    }
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaWoFo Reports</title>
    <!-- Menambahkan link ke CSS Bootstrap dan Font Awesome -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* Gaya untuk navbar */
        .navbar {
            background-color: #007bff;
            margin-bottom: 20px;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
        }

        /* Gaya untuk form pencarian */
        .search-form .input-group {
            max-width: 400px;
            margin: 20px auto;
        }

        .search-form input {
            border-radius: 5px;
            padding: 10px;
            font-size: 16px;
        }

        .search-form button {
            border-radius: 5px;
            padding: 10px 15px;
            font-size: 16px;
        }

        /* Gaya untuk tabel laporan */
        .table {
            border: 1px solid #ddd;
            border-radius: 5px;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
            background-color: white;
        }

        .table th {
            background-color: #007bff;
            color: white;
            text-align: center;
            font-weight: bold;
        }

        .table td {
            padding: 10px;
            text-align: center;
        }

        .table td a {
            color: white;
            text-decoration: none;
        }

        .table td a:hover {
            color: #343a40;
        }

        /* Gaya untuk tombol aksi di tabel */
        .action-buttons .btn {
            border-radius: 5px;
            margin: 0 5px;
            padding: 5px 10px;
            font-size: 14px;
        }

        .action-buttons .btn-danger {
            background-color: #dc3545;
            color: #fff;
        }

        .action-buttons .btn-danger:hover {
            background-color: #c82333;
        }

        /* Gaya untuk card summary */
        .card {
            border: none;
            border-radius: 10px;
            box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
        }

        .card-header {
            background-color: #007bff;
            color: white;
            padding: 15px;
            font-size: 18px;
            text-align: center;
        }

        .card-body {
            padding: 20px;
        }

        .card-body .row {
            display: flex;
            justify-content: space-between;
        }

        .card-body .col-md-6 {
            padding: 15px;
            background-color: #f1f1f1;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            text-align: center;
        }

        .card-body h5 {
            font-size: 18px;
            margin-bottom: 10px;
        }

        .card-body .display-6 {
            font-size: 24px;
            font-weight: bold;
        }

        /* Penyesuaian untuk tampilan mobile */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .main-content {
                margin-left: 0;
            }

            .search-form .input-group {
                width: 100%;
            }

            .table th,
            .table td {
                font-size: 14px;
            }

            .card-body .row {
                flex-direction: column;
                align-items: center;
            }

            .card-body .col-md-6 {
                margin-bottom: 20px;
                width: 100%;
            }
        }
    </style>
</head>

<body>
    <div class="d-flex">
        <!-- Sidebar -->
        <div class="sidebar p-3">
            <h2 class="text-center">TaWoFo</h2>
            <ul class="nav flex-column">
                <li class="nav-item"><a href="index.php" class="nav-link"><i class="fas fa-home me-2"></i>Dashboard</a></li>
                <li class="nav-item"><a href="employees.php" class="nav-link active"><i class="fas fa-users me-2"></i>Employees</a></li>
                <li class="nav-item"><a href="payroll.php" class="nav-link"><i class="fas fa-dollar-sign me-2"></i>Payroll</a></li>
                <li class="nav-item"><a href="reports.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i>Reports</a></li>
                <li class="nav-item mt-auto"><a href="logout.php" class="nav-link bg-danger"><i class="fas fa-sign-out-alt me-2"></i>Logout</a></li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content p-4">
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
                <div class="container-fluid"><a class="navbar-brand" href="#">Laporan Gaji Karyawan</a></div>
            </nav>

            <!-- Form Pencarian -->
            <div class="search-form">
                <form method="GET" action="reports.php">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari nama atau posisi karyawan"
                            value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                    </div>
                </form>
            </div>

            <!-- Card Ringkasan Laporan -->
            <div class="card mb-4">
                <div class="card-header">
                    <h4>Ringkasan Laporan</h4>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <h5>Total Gaji</h5>
                            <p class="display-6">Rp <?php echo number_format($totalSalary, 0, ',', '.'); ?></p>
                        </div>
                        <div class="col-md-6">
                            <h5>Total Karyawan</h5>
                            <p class="display-6"><?php echo $totalEmployees; ?> karyawan</p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Tabel Laporan -->
            <table class="table table-striped">
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
                    <?php if (empty($reports)) : ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada data laporan ditemukan</td>
                        </tr>
                    <?php else : ?>
                        <?php foreach ($reports as $index => $report) : ?>
                            <tr>
                                <td><?php echo $index + 1; ?></td>
                                <td><?php echo htmlspecialchars($report['name']); ?></td>
                                <td><?php echo htmlspecialchars($report['position']); ?></td>
                                <td>Rp <?php echo number_format($report['salary'], 0, ',', '.'); ?></td>
                                <td><?php echo date("F", mktime(0, 0, 0, $report['month'], 10)); ?></td>
                                <td><?php echo $report['year']; ?></td>
                                <td class="action-buttons">
                                    <a href="?delete_id=<?php echo $report['payroll_id']; ?>" class="btn btn-danger" onclick="return confirm('Yakin ingin menghapus data ini?');">
                                        <i class="fas fa-trash"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Bootstrap JS dan dependensi -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>

</html>
