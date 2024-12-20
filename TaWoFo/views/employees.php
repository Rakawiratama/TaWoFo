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

try {
    // Search query
    $sql = "SELECT id, name, email, phone, position, salary FROM employees WHERE name LIKE :search OR email LIKE :search OR position LIKE :search";
    $stmt = $pdo->prepare($sql);
    $stmt->execute(['search' => "%" . $search . "%"]);
    $employees = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    echo "Error: " . $e->getMessage();
}

// Handle form submission for adding new employee
if ($_SERVER['REQUEST_METHOD'] == 'POST' && !isset($_POST['search']) && !isset($_POST['update'])) {
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];

    try {
        $sql = "INSERT INTO employees (name, email, phone, position, salary) VALUES (?, ?, ?, ?, ?)";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $phone, $position, $salary]);
        header("Location: employees.php"); // Redirect after successful insert
        exit();
    } catch (PDOException $e) {
        echo "Error: " . $e->getMessage();
    }
}

// Handle Edit
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

// Handle Update
if (isset($_POST['update'])) {
    $id = $_POST['id'];
    $name = $_POST['name'];
    $email = $_POST['email'];
    $phone = $_POST['phone'];
    $position = $_POST['position'];
    $salary = $_POST['salary'];

    try {
        $sql = "UPDATE employees SET name = ?, email = ?, phone = ?, position = ?, salary = ? WHERE id = ?";
        $stmt = $pdo->prepare($sql);
        $stmt->execute([$name, $email, $phone, $position, $salary, $id]);
        header("Location: employees.php"); // Redirect after successful update
        exit();
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
        header("Location: employees.php"); // Redirect after successful deletion
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
    <style>
        body {
            background-color: #f8f9fa;
            display: flex;
            min-height: 100vh;
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
            <div class="container-fluid"><a class="navbar-brand" href="#">Karyawan</a></div>
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
            <div class="card-header"><h4>Input Data Karyawan</h4></div>
            <div class="card-body">
                <form action="employees.php" method="POST">
                    <div class="mb-3">
                        <label for="id" class="form-label">Id</label>
                        <input type="text" class="form-control" id="id" name="id">
                    </div>
                    <div class="mb-3">
                        <label for="name" class="form-label">Nama</label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    <div class="mb-3">
                        <label for="email" class="form-label">Email </label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    <div class="mb-3">
                        <label for="phone" class="form-label">No Telp</label>
                        <input type="text" class="form-control" id="phone" name="phone" required>
                    </div>
                    <div class="mb-3">
                        <label for="position" class="form-label">Jabatan</label>
                        <select class="form-control" id="position" name="position" required>
                            <option value="Manager">Manager</option>
                            <option value="Staff">Staff</option>
                            <option value="HR">HR</option>
                            <option value="Developer">Developer</option>
                            <option value="Sales">Sales</option>
                            <option value="Admin">Admin</option>
                            <option value="Accountant">Accountant</option>
                            <option value="Designer">Designer</option>
                            <option value="Marketing">Marketing</option>
                            <option value="Product Manager">Product Manager</option>
                            <option value="Operations">Operations</option>
                            <option value="CTO">CTO</option>
                            <option value="CEO">CEO</option>
                            <option value="Customer Support">Customer Support</option>
                            <option value="UX/UI Designer">UX/UI Designer</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="salary" class="form-label">Gaji</label>
                        <input type="number" class="form-control" id="salary" name="salary" required>
                    </div>
                    <button type="submit" class="btn btn-primary">Tambah Karyawan</button>
                </form>
            </div>
        </div>

        <!-- Edit Employee Form -->
<?php if (isset($employee) && $employee): ?>
<div class="card mb-4">
    <div class="card-header"><h4>Edit Employee</h4></div>
    <div class="card-body">
        <form action="employees.php" method="POST">
            <!-- ID field for update -->
            <div class="mb-3">
                <label for="id" class="form-label">ID</label>
                <input type="text" class="form-control" id="id" name="id" value="<?php echo $employee['id']; ?>">
            </div>
            <div class="mb-3">
                <label for="name" class="form-label">Name</label>
                <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($employee['name']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="email" class="form-label">Email</label>
                <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($employee['email']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="phone" class="form-label">Phone</label>
                <input type="text" class="form-control" id="phone" name="phone" value="<?php echo htmlspecialchars($employee['phone']); ?>" required>
            </div>
            <div class="mb-3">
                <label for="position" class="form-label">Position</label>
                <select class="form-control" id="position" name="position" required>
                    <option value="Manager" <?php echo ($employee['position'] == 'Manager') ? 'selected' : ''; ?>>Manager</option>
                    <option value="Staff" <?php echo ($employee['position'] == 'Staff') ? 'selected' : ''; ?>>Staff</option>
                    <option value="HR" <?php echo ($employee['position'] == 'HR') ? 'selected' : ''; ?>>HR</option>
                    <option value="Developer" <?php echo ($employee['position'] == 'Developer') ? 'selected' : ''; ?>>Developer</option>
                    <option value="Sales" <?php echo ($employee['position'] == 'Sales') ? 'selected' : ''; ?>>Sales</option>
                    <option value="Admin" <?php echo ($employee['position'] == 'Admin') ? 'selected' : ''; ?>>Admin</option>
                    <option value="Accountant" <?php echo ($employee['position'] == 'Accountant') ? 'selected' : ''; ?>>Accountant</option>
                    <option value="Designer" <?php echo ($employee['position'] == 'Designer') ? 'selected' : ''; ?>>Designer</option>
                    <option value="Marketing" <?php echo ($employee['position'] == 'Marketing') ? 'selected' : ''; ?>>Marketing</option>
                    <option value="Product Manager" <?php echo ($employee['position'] == 'Product Manager') ? 'selected' : ''; ?>>Product Manager</option>
                    <option value="Operations" <?php echo ($employee['position'] == 'Operations') ? 'selected' : ''; ?>>Operations</option>
                    <option value="CTO" <?php echo ($employee['position'] == 'CTO') ? 'selected' : ''; ?>>CTO</option>
                    <option value="CEO" <?php echo ($employee['position'] == 'CEO') ? 'selected' : ''; ?>>CEO</option>
                    <option value="Customer Support" <?php echo ($employee['position'] == 'Customer Support') ? 'selected' : ''; ?>>Customer Support</option>
                    <option value="UX/UI Designer" <?php echo ($employee['position'] == 'UX/UI Designer') ? 'selected' : ''; ?>>UX/UI Designer</option>
                </select>
            </div>
            <div class="mb-3">
                <label for="salary" class="form-label">Salary</label>
                <input type="number" class="form-control" id="salary" name="salary" value="<?php echo htmlspecialchars($employee['salary']); ?>" required>
            </div>
            <button type="submit" class="btn btn-warning" name="update">Update Employee</button>
        </form>
    </div>
</div>
<?php endif; ?>


        <!-- Employee Table -->
        <div class="card">
            <div class="card-header"><h4>Employee List</h4></div>
            <div class="card-body">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>Id</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Position</th>
                            <th>Salary</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($employees as $employee): ?>
                            <tr>
                                <td><?php echo $employee['id']; ?></td>
                                <td><?php echo htmlspecialchars($employee['name']); ?></td>
                                <td><?php echo htmlspecialchars($employee['email']); ?></td>
                                <td><?php echo htmlspecialchars($employee['phone']); ?></td>
                                <td><?php echo htmlspecialchars($employee['position']); ?></td>
                                <td><?php echo htmlspecialchars($employee['salary']); ?></td>
                                <td>
                                    <a href="employees.php?edit=<?php echo $employee['id']; ?>" class="btn btn-warning btn-sm">Edit</a>
                                    <a href="employees.php?delete=<?php echo $employee['id']; ?>" class="btn btn-danger btn-sm" onclick="return confirm('Are you sure you want to delete?')">Delete</a>
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
