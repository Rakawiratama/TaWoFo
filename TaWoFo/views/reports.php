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
    <!-- CSS Dependencies -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        /* General Styles */
        .navbar {
            background-color: #007bff;
        }

        .navbar-brand {
            font-size: 22px;
            font-weight: bold;
            color: #ffffff;
        }
        /* Search Form */
        .search-form .input-group {
            max-width: 500px;
            margin: 20px auto;
        }

        /* Card Summary */
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
            background-color: #f8f9fa;
            border-radius: 8px;
            text-align: center;
            border: 1px solid #dee2e6;
        }

        /* Table Styles */
        .table {
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.1);
        }

        .table th {
            background-color: #007bff;
            color: white;
            text-align: center;
        }

        .table td {
            text-align: center;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .sidebar {
                width: 200px;
            }

            .search-form .input-group {
                width: 100%;
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
                    <a href="payroll.php" class="nav-link"><i class="fas fa-dollar-sign me-2"></i>Payroll</a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link active"><i class="fas fa-chart-bar me-2"></i>Reports</a>
                </li>
                <li class="nav-item mt-auto">
                    <a href="logout.php" class="nav-link bg-danger text-white"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content flex-grow-1 p-4">
            <!-- Navbar -->
            <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
                <div class="container-fluid">
                    <a class="navbar-brand" href="#">Laporan Gaji Karyawan</a>
                </div>
            </nav>

            <!-- Search Form -->
            <div class="search-form">
                <form method="GET" action="reports.php">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari nama atau posisi karyawan" value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="fas fa-search"></i> Cari
                        </button>
                    </div>
                </form>
            </div>

            <!-- Card Summary -->
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

            <!-- Report Table -->
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
                    <?php if (count($reports) > 0) : ?>
                        <?php $no = 1; ?>
                        <?php foreach ($reports as $report) : ?>
                            <tr>
                                <td><?php echo $no++; ?></td>
                                <td><?php echo $report['name']; ?></td>
                                <td><?php echo $report['position']; ?></td>
                                <td>Rp <?php echo number_format($report['salary'], 0, ',', '.'); ?></td>
                                <td><?php echo $report['month']; ?></td>
                                <td><?php echo $report['year']; ?></td>
                                <td>
                                    <a href="reports.php?delete_id=<?php echo $report['payroll_id']; ?>" class="btn btn-danger">
                                        <i class="fas fa-trash-alt"></i> Hapus
                                    </a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else : ?>
                        <tr>
                            <td colspan="7" class="text-center">Tidak ada laporan yang ditemukan.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div class="text-end">
                <a href="export_pdf.php?search=<?php echo urlencode($searchTerm); ?>" class="btn btn-success">
                    <i class="fas fa-file-pdf"></i> Ekspor ke PDF
                </a>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
