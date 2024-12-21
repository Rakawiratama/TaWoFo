<?php
session_start();

// Redirect to login if user is not logged in
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit();
}

// Set default username if not set
if (!isset($_SESSION['username'])) {
    $_SESSION['username'] = 'Admin';
}

require_once '../config/koneksi.php';

$employees = [];
$search = '';

// Handle search input
if (isset($_POST['search'])) {
    $search = $_POST['search'];
}

// Search query
try {
    $sql = "SELECT id, name, email, phone, position, salary FROM employees WHERE name LIKE :search OR email LIKE :search OR position LIKE :search";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%" . $search . "%"]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle form submission for adding or updating employee
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Check if it's add or update operation
    if (isset($_POST['add_employee'])) {
        $name = htmlspecialchars($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $phone = htmlspecialchars($_POST['phone']);
        $position = htmlspecialchars($_POST['position']);
        $salary = (float)$_POST['salary'];

        if ($email) {
            try {
                $sql = "INSERT INTO employees (name, email, phone, position, salary) VALUES (?, ?, ?, ?, ?)";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email, $phone, $position, $salary]);
                header("Location: employees.php");
                exit();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Invalid email format.";
        }
    }

    // Update employee details
    if (isset($_POST['update_employee'])) {
        $id = $_POST['id'];
        $name = htmlspecialchars($_POST['name']);
        $email = filter_var($_POST['email'], FILTER_VALIDATE_EMAIL);
        $phone = htmlspecialchars($_POST['phone']);
        $position = htmlspecialchars($_POST['position']);
        $salary = (float)$_POST['salary'];

        if ($email) {
            try {
                $sql = "UPDATE employees SET name = ?, email = ?, phone = ?, position = ?, salary = ? WHERE id = ?";
                $stmt = $pdo->prepare($sql);
                $stmt->execute([$name, $email, $phone, $position, $salary, $id]);
                header("Location: employees.php");
                exit();
            } catch (PDOException $e) {
                echo "Error: " . $e->getMessage();
            }
        } else {
            echo "Invalid email format.";
        }
    }
}

// Fetch data for editing employee
$employee = [];
if (isset($_GET['edit'])) {
    $id = $_GET['edit'];

    try {
        $sql = "SELECT * FROM employees WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        $employee = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle Delete
if (isset($_GET['delete'])) {
    $id = $_GET['delete'];

    try {
        $sql = "DELETE FROM employees WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$id]);
        header("Location: employees.php");
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TaWoFo Employees</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <link rel="stylesheet" href="../css/style.css">
    <style>
        .table-responsive {
            background-color: #fff;
            padding: 20px;
            border-radius: 8px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .card {
            margin-bottom: 20px;
        }

        .form-control {
            border-radius: 5px;
            height: 45px;
        }

        .btn-primary {
            border-radius: 5px;
            padding: 10px 20px;
        }

        .btn-primary:hover {
            background-color: #0056b3;
        }

        .table th, .table td {
            vertical-align: middle;
        }

        .table-hover tbody tr:hover {
            background-color: #f1f1f1;
        }

        .table th {
            background-color: #007bff;
            color: white;
        }

        .table td {
            color: #333;
        }
    </style>
</head>
<body>

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
<div class="main-content">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container-fluid">
            <a class="navbar-brand" href="#">Karyawan</a>
        </div>
    </nav>

    <!-- Search Form -->
    <div class="card mb-4">
        <div class="card-body">
            <form action="employees.php" method="POST">
                <div class="mb-3">
                    <input type="text" class="form-control" name="search" placeholder="Search by name, email, or position" value="<?php echo htmlspecialchars($search); ?>">
                </div>
                <button type="submit" class="btn btn-primary">Search</button>
            </form>
        </div>
    </div>

    <!-- New Employee Form -->
    <div class="card mb-4">
        <div class="card-header">
            <h4>Input Data Karyawan</h4>
        </div>
        <div class="card-body">
            <form action="employees.php" method="POST">
                <div class="mb-3">
                    <label for="name" class="form-label">Nama</label>
                    <input type="text" class="form-control" id="name" name="name" required>
                </div>
                <div class="mb-3">
                    <label for="email" class="form-label">Email</label>
                    <input type="email" class="form-control" id="email" name="email" required>
                </div>
                <div class="mb-3">
                    <label for="phone" class="form-label">No Telp</label>
                    <input type="text" class="form-control" id="phone" name="phone" required>
                </div>
                <div class="mb-3">
                    <label for="position" class="form-label">Jabatan</label>
                    <select class="form-control" id="position" name="position">
                        <option>Manager</option>
                        <option>Staff</option>
                        <option>Teknisi</option>
                        <option>Data Analyst</option>
                        <option>Full Stack Development</option>
                        <option>Back End Developer</option>
                        <option>UI/UX Designer</option>
                        <option>Front End Developer</option>
                        <option>Mobile Developer</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label for="salary" class="form-label">Gaji</label>
                    <input type="number" class="form-control" id="salary" name="salary" required>
                </div>
                <button type="submit" name="add_employee" class="btn btn-primary">Tambah Karyawan</button>
            </form>
        </div>
    </div>

    <!-- Employee Table -->
    <div class="card">
        <div class="card-header">
            <h4>Daftar Karyawan</h4>
        </div>
        <div class="card-body">
            <table class="table table-hover table-responsive">
                <thead>
                    <tr>
                        <th>No</th>
                        <th>Nama</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Position</th>
                        <th>Gaji</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($employees as $index => $employee): ?>
                    <tr>
                        <td><?php echo $index + 1; ?></td>
                        <td><?php echo htmlspecialchars($employee['name']); ?></td>
                        <td><?php echo htmlspecialchars($employee['email']); ?></td>
                        <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                        <td><?php echo htmlspecialchars($employee['position']); ?></td>
                        <td><?php echo "Rp " . number_format($employee['salary'], 0, ',', '.'); ?></td>
                        <td>
                            <a href="employees.php?edit=<?php echo $employee['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="employees.php?delete=<?php echo $employee['id']; ?>" class="btn btn-danger btn-sm">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>