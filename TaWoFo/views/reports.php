<?php
session_start();

// Redirect to login page if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Admin';
}

require_once '../config/koneksi.php';

// Initialize variables for reports and totals
$reports = [];
$totalSalary = 0;
$totalEmployees = 0;
$searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

// Handle Edit and Delete actions
if (isset($_GET['edit_id'])) {
    $editId = $_GET['edit_id'];
    // Fetch the current data for the selected employee
    $sql = "SELECT * FROM payroll WHERE id = :id";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['id' => $editId]);
    $editData = $stmt->fetch(PDO::FETCH_ASSOC);

    // If the form is submitted, update the data
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $name = $_POST['name'];
        $position = $_POST['position'];
        $salary = $_POST['salary'];
        $month = $_POST['month'];
        $year = $_POST['year'];

        $updateSql = "UPDATE payroll SET name = :name, position = :position, salary = :salary, month = :month, year = :year WHERE id = :id";
        $updateStmt = $pdo->prepare($updateSql);
        $updateStmt->execute([
            'name' => $name,
            'position' => $position,
            'salary' => $salary,
            'month' => $month,
            'year' => $year,
            'id' => $editId
        ]);

        header("Location: reports.php");
        exit();
    }
}

if (isset($_GET['delete_id'])) {
    $deleteId = $_GET['delete_id'];

    // Delete the selected payroll record
    $deleteSql = "DELETE FROM payroll WHERE id = :id";
    $deleteStmt = $pdo->prepare($deleteSql);
    $deleteStmt->execute(['id' => $deleteId]);

    header("Location: reports.php");
    exit();
}

// Modify the SQL query based on the search term
try {
    $sql = "SELECT e.name, e.position, p.salary, p.month, p.year, p.id AS payroll_id
            FROM payroll p 
            JOIN employees e ON p.employee_id = e.id 
            WHERE e.name LIKE :search OR e.position LIKE :search
            ORDER BY p.year DESC, p.month DESC";

    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => '%' . $searchTerm . '%']);
    $reports = $stmt->fetchAll(PDO::FETCH_ASSOC);

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
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            background-color: #f8f9fa; 
            margin: 0; 
            font-family: 'Arial', sans-serif; 
        }

        .sidebar {
            min-width: 250px;
            background-color: #343a40;
            color: white;
            height: 100vh;
            position: fixed;
            padding-top: 20px;
        }

        .sidebar h2 {
            color: #17a2b8;
            margin-bottom: 20px;
            text-align: center;
            font-size: 24px;
        }

        .sidebar .nav-link {
            color: white;
            padding: 12px 20px;
            border-radius: 4px;
            margin: 5px 0;
            font-size: 16px;
        }

        .sidebar .nav-link:hover {
            background-color: #495057;
        }

        .main-content { 
            margin-left: 260px; 
            padding: 30px; 
            flex-grow: 1; 
            background-color: #fff; 
            min-height: 100vh; 
        }

        .table th { 
            background-color: #007bff; 
            color: white; 
        }

        .table td, .table th {
            text-align: center;
        }

        .search-form {
            margin-bottom: 20px;
        }

        .action-buttons {
            display: flex;
            justify-content: center;
        }

        .action-buttons .btn {
            margin: 0 5px;
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

            <!-- Search Form -->
            <div class="search-form">
                <form action="reports.php" method="get">
                    <div class="input-group">
                        <input type="text" class="form-control" name="search" placeholder="Cari nama atau posisi" value="<?php echo htmlspecialchars($searchTerm); ?>">
                        <button class="btn btn-primary" type="submit"><i class="fas fa-search"></i> Cari</button>
                    </div>
                </form>
            </div>

            <!-- Payroll Report Table -->
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead>
                        <tr>
                            <th>Nama</th>
                            <th>Posisi</th>
                            <th>Gaji</th>
                            <th>Bulan</th>
                            <th>Tahun</th>
                            <th>Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if ($totalEmployees > 0): ?>
                            <?php foreach ($reports as $report): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($report['name']); ?></td>
                                    <td><?php echo htmlspecialchars($report['position']); ?></td>
                                    <td>Rp <?php echo number_format($report['salary'], 2, ',', '.'); ?></td>
                                    <td><?php echo date('F', mktime(0, 0, 0, $report['month'], 10)); ?></td>
                                    <td><?php echo htmlspecialchars($report['year']); ?></td>
                                    <td class="action-buttons">
                                        <a href="reports.php?edit_id=<?php echo $report['payroll_id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                        <a href="reports.php?delete_id=<?php echo $report['payroll_id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Apakah Anda yakin ingin menghapus data ini?');">Delete</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="6" class="text-center">Tidak ada data yang ditemukan</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Report Summary -->
            <div class="mt-4">
                <div class="card">
                    <div class="card-header text-center bg-primary text-white">
                        <h4>Laporan Summary</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Total Gaji Bulanan</h5>
                                <p class="display-6 text-primary">Rp <?php echo number_format($totalSalary, 2, ',', '.'); ?></p>
                            </div>
                            <div class="col-md-6">
                                <h5>Total Karyawan</h5>
                                <p class="display-6 text-primary"><?php echo $totalEmployees; ?></p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
