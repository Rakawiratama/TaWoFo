<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

require_once '../config/koneksi.php';

// Tambah atau Update Data Payroll
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $employee_id = $_POST['employee_id'];
    $salary = $_POST['salary'];
    $month = $_POST['month'];
    $year = $_POST['year'];
    $id = isset($_POST['id']) && !empty($_POST['id']) ? $_POST['id'] : null;

    try {
        if ($id) {
            // Update Payroll
            $sql = "UPDATE payroll SET employee_id = ?, salary = ?, month = ?, year = ? WHERE id = ?";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employee_id, $salary, $month, $year, $id]);
        } else {
            // Insert Payroll
            $sql = "INSERT INTO payroll (employee_id, salary, month, year) VALUES (?, ?, ?, ?)";
            $stmt = $pdo->prepare($sql);
            $stmt->execute([$employee_id, $salary, $month, $year]);
        }
        header('Location: payroll.php');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Delete Data Payroll
if (isset($_GET['delete_id'])) {
    $delete_id = $_GET['delete_id'];

    try {
        $sql = "DELETE FROM payroll WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$delete_id]);
        header('Location: payroll.php');
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Load data for edit
$payroll = null;
if (isset($_GET['edit_id'])) {
    $edit_id = $_GET['edit_id'];
    try {
        $sql = "SELECT * FROM payroll WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$edit_id]);
        $payroll = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Search Payroll
$search = isset($_GET['search']) ? $_GET['search'] : '';
$payrolls = [];
try {
    $sql = "SELECT p.id, p.employee_id, e.name, e.position, p.salary, p.month, p.year 
            FROM payroll p 
            JOIN employees e ON p.employee_id = e.id 
            WHERE e.name LIKE :search
            ORDER BY p.year DESC, p.month DESC";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%$search%"]);
    $payrolls = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Fetch employee data for dropdown
$employees = [];
try {
    $sql = "SELECT id, name FROM employees";
    $stmt = $pdo->query($sql);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaWoFo Payroll</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        body { 
            background-color: #f8f9fa; 
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
<div class="main-content container-fluid">
    <h4 class="mb-3">Management Penggajian</h4>

    <!-- Search Form -->
    <form method="GET" class="mb-4">
        <div class="input-group">
            <input type="text" name="search" class="form-control" placeholder="Pencarian berdasarkan nama" value="<?php echo htmlspecialchars($search); ?>">
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
        <div class="col-md-2">
            <label for="month" class="form-label">Bulan</label>
            <input type="number" name="month" class="form-control" value="<?php echo isset($payroll['month']) ? htmlspecialchars($payroll['month']) : ''; ?>" required>
        </div>
        <div class="col-md-2">
            <label for="year" class="form-label">Tahun</label>
            <input type="number" name="year" class="form-control" value="<?php echo isset($payroll['year']) ? htmlspecialchars($payroll['year']) : ''; ?>" required>
        </div>
        <div class="col-md-2 d-flex align-items-end">
            <button type="submit" class="btn btn-primary w-100">Simpan</button>
        </div>
    </div>
</form>

    <!-- Payroll Table -->
    <div class="table-responsive">
        <table class="table table-hover table-bordered">
            <thead class="table-dark">
                <tr>
                    <th>ID</th>
                    <th>Nama</th>
                    <th>Jabatan</th>
                    <th>Gaji</th>
                    <th>Bulan</th>
                    <th>Tahun</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($payrolls as $payroll): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($payroll['employee_id']); ?></td>
                        <td><?php echo htmlspecialchars($payroll['name']); ?></td>
                        <td><?php echo htmlspecialchars($payroll['position']); ?></td>
                        <td>Rp <?php echo number_format($payroll['salary'], 2, ',', '.'); ?></td>
                        <td><?php echo htmlspecialchars($payroll['month']); ?></td>
                        <td><?php echo htmlspecialchars($payroll['year']); ?></td>
                        <td>
                            <a href="?edit_id=<?php echo $payroll['id']; ?>" class="btn btn-warning btn-sm" class="fas fa-edit">Edit</a>
                            <a href="?delete_id=<?php echo $payroll['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure?')">Delete</a>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
</div>

    </div>
</body>

</html>
