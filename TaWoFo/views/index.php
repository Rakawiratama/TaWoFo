<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Admin';
}

require_once '../config/koneksi.php';

// Fetch employees data for dashboard summary
$employees = [];
$totalSalary = 0;
$totalEmployees = 0;

try {
    $sql = "SELECT id, name, position, salary FROM employees";
    $stmt = $pdo->query($sql);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);

    $totalEmployees = count($employees);
    foreach ($employees as $employee) {
        $totalSalary += $employee['salary'];
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
    <title>TaWoFo Dashboard</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
       
    </style>
</head>
<body>
    <div class="d-flex flex-column flex-md-row">
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
                <div class="container-fluid"><a class="navbar-brand" href="#">DASHBOARD</a><span class="text-white">Welcome</span></div>
            </nav>

            <!-- Cards Section -->
            <div class="row mb-4">
                <div class="col-sm-12 col-md-4 mb-3">
                    <div class="card bg-info text-white p-3">
                        <h5>Jumlah Karyawan</h5>
                        <p class="fs-4"><?php echo $totalEmployees; ?></p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-3">
                    <div class="card bg-success text-white p-3">
                        <h5>Jumlah Gaji</h5>
                        <p class="fs-4">Rp <?php echo number_format($totalSalary, 2, ',', '.'); ?></p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-3">
                    <div class="card bg-warning text-white p-3">
                        <h5>Laporan</h5>
                        <a href="reports.php" class="text-white fs-4">Lihat Laporan Terperinci</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

