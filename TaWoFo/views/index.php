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
        /* Custom Styles */
        body {
            font-family: Arial, sans-serif;
            background-color: #f7f7f7;
            color: #333;
        }
        
        .navbar {
            background-color: #007bff;
        }
        .navbar .navbar-brand {
            color: white;
        }
        .card {
            border-radius: 10px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            transition: transform 0.3s;
        }
        .card:hover {
            transform: translateY(-5px);
        }
        .card h5 {
            font-size: 1.2rem;
            margin-bottom: 20px;
        }
        .card p {
            font-size: 1.5rem;
        }
        .card a {
            font-size: 1.2rem;
            color: #007bff;
            text-decoration: none;
        }
        .card a:hover {
            text-decoration: underline;
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
                    <a href="employees.php" class="nav-link active"><i class="fas fa-users me-2"></i>Employees</a>
                </li>
                <li class="nav-item">
                    <a href="payroll.php" class="nav-link"><i class="fas fa-dollar-sign me-2"></i>Payroll</a>
                </li>
                <li class="nav-item">
                    <a href="reports.php" class="nav-link"><i class="fas fa-chart-bar me-2"></i>Reports</a>
                </li>
                <li class="nav-item mt-auto">
                    <a href="logout.php" class="nav-link bg-danger text-white"><i class="fas fa-sign-out-alt me-2"></i>Logout</a>
                </li>
            </ul>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <nav class="navbar navbar-expand-lg navbar-dark mb-4">
                <div class="container-fluid">
                    <span class="navbar-brand">Dashboard</span>
                    <span class="navbar-text text-white">Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?>!</span>
                </div>
            </nav>

            <!-- Cards Section -->
            <div class="row">
                <div class="col-sm-12 col-md-4 mb-3">
                    <div class="card bg-info text-white p-4">
                        <h5>Total Employees</h5>
                        <p><?php echo $totalEmployees; ?></p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-3">
                    <div class="card bg-success text-white p-4">
                        <h5>Total Salary</h5>
                        <p>Rp <?php echo number_format($totalSalary, 2, ',', '.'); ?></p>
                    </div>
                </div>
                <div class="col-sm-12 col-md-4 mb-3">
                    <div class="card bg-warning text-white p-4">
                        <h5>Reports</h5>
                        <a href="reports.php">View Detailed Reports</a>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
