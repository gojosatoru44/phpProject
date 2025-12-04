<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get statistics
$total_orders_sql = "SELECT COUNT(*) as count FROM orders";
$total_orders = $conn->query($total_orders_sql)->fetch_assoc()['count'];

$pending_orders_sql = "SELECT COUNT(*) as count FROM orders WHERE status = 'pending'";
$pending_orders = $conn->query($pending_orders_sql)->fetch_assoc()['count'];

$total_menu_items_sql = "SELECT COUNT(*) as count FROM menu_items";
$total_menu_items = $conn->query($total_menu_items_sql)->fetch_assoc()['count'];

$today_sales_sql = "SELECT SUM(total_amount) as total FROM orders WHERE DATE(order_date) = CURDATE() AND status != 'cancelled'";
$today_sales_result = $conn->query($today_sales_sql)->fetch_assoc();
$today_sales = $today_sales_result['total'] ? $today_sales_result['total'] : 0;

// Get recent orders
$recent_orders_sql = "SELECT * FROM orders ORDER BY order_date DESC LIMIT 5";
$recent_orders = $conn->query($recent_orders_sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Campus Cafeteria</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="admin-container">
        <div class="admin-header">
            <div>
                <h2>🍽️ Campus Cafeteria - Admin Panel</h2>
                <p>Welcome, <?php echo $_SESSION['admin_name']; ?>!</p>
            </div>
            <div class="admin-nav">
                <a href="dashboard.php" class="active">Dashboard</a>
                <a href="menu.php">Menu Items</a>
                <a href="orders.php">Orders</a>
                <a href="reports.php">Reports</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="dashboard-cards">
            <div class="card">
                <h3>📦 Total Orders</h3>
                <div class="number"><?php echo $total_orders; ?></div>
            </div>
            <div class="card">
                <h3>⏳ Pending Orders</h3>
                <div class="number"><?php echo $pending_orders; ?></div>
            </div>
            <div class="card">
                <h3>🍔 Menu Items</h3>
                <div class="number"><?php echo $total_menu_items; ?></div>
            </div>
            <div class="card">
                <h3>💰 Today's Sales</h3>
                <div class="number"><?php echo number_format($today_sales, 2); ?> SAR</div>
            </div>
        </div>

        <!-- Recent Orders -->
        <div class="table-container">
            <h3>Recent Orders</h3>
            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($recent_orders->num_rows > 0): ?>
                        <?php while ($order = $recent_orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['student_email']); ?></td>
                                <td><?php echo number_format($order['total_amount'], 2); ?> SAR</td>
                                <td>
                                    <span class="badge badge-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85em;">View</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No orders yet</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
            <div style="margin-top: 20px;">
                <a href="orders.php" class="btn btn-primary">View All Orders</a>
            </div>
        </div>
    </div>
</body>
</html>