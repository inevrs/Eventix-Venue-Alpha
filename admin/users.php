<?php
session_start();
require_once '../includes/db.php';
require_once '../includes/auth.php';
requireLogin('admin');

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_id'])) {
    $id = (int)$_POST['delete_id'];
    mysqli_query($connect, "DELETE FROM users WHERE id = $id AND role != 'admin'");
}

$search = mysqli_real_escape_string($connect, $_GET['search'] ?? '');
$filter = mysqli_real_escape_string($connect, $_GET['role'] ?? '');

$where = "WHERE role != 'admin'";
if ($search) $where .= " AND (full_name LIKE '%$search%' OR email LIKE '%$search%')";
if ($filter) $where .= " AND role = '$filter'";

$users = mysqli_query($connect, "SELECT * FROM users $where ORDER BY created_at DESC");
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Manage Users — Eventix</title>
    <link rel="stylesheet" href="/eventix/css/style.css">
</head>
<body>

<?php include '../includes/navbar.php'; ?>

<div class="layout-sidebar">
    <aside class="sidebar">
        <p class="sidebar-section">Overview</p>
        <ul class="sidebar-menu">
            <li><a href="dashboard.php">📊 Dashboard</a></li>
        </ul>
        <p class="sidebar-section">Manage</p>
        <ul class="sidebar-menu">
            <li><a href="users.php" class="active">👥 Users</a></li>
            <li><a href="venues.php">🏛️ Venues</a></li>
            <li><a href="bookings.php">📅 Bookings</a></li>
            <li><a href="payments.php">💳 Payments</a></li>
        </ul>
    </aside>

    <main class="main-content">
        <div class="page-header">
            <h1>Manage Users</h1>
            <p>View and remove platform users</p>
        </div>

        <form method="GET" class="search-bar">
            <input type="text" name="search" placeholder="Search name or email..." value="<?= htmlspecialchars($search) ?>">
            <select name="role" style="padding:12px 16px;border:1.5px solid var(--pink-light);border-radius:30px;font-size:14px;outline:none;">
                <option value="">All Roles</option>
                <option value="customer" <?= $filter === 'customer' ? 'selected' : '' ?>>Customer</option>
                <option value="manager"  <?= $filter === 'manager'  ? 'selected' : '' ?>>Manager</option>
            </select>
            <button type="submit">Search</button>
        </form>

        <div class="card">
            <div class="table-wrap">
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Phone</th>
                            <th>Role</th>
                            <th>Joined</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; while ($row = mysqli_fetch_assoc($users)): ?>
                        <tr>
                            <td><?= $i++ ?></td>
                            <td><?= htmlspecialchars($row['full_name']) ?></td>
                            <td><?= htmlspecialchars($row['email']) ?></td>
                            <td><?= htmlspecialchars($row['phone']) ?></td>
                            <td><span class="badge badge-info"><?= ucfirst($row['role']) ?></span></td>
                            <td><?= date('d M Y', strtotime($row['created_at'])) ?></td>
                            <td>
                                <form method="POST" onsubmit="return confirm('Delete this user?')">
                                    <input type="hidden" name="delete_id" value="<?= $row['id'] ?>">
                                    <button type="submit" class="btn btn-danger btn-sm">Delete</button>
                                </form>
                            </td>
                        </tr>
                        <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </main>
</div>

</body>
</html>
