<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get date range
$start_date = isset($_GET['start_date']) ? $_GET['start_date'] : date('Y-m-d', strtotime('-7 days'));
$end_date = isset($_GET['end_date']) ? $_GET['end_date'] : date('Y-m-d');

// Daily sales report
$daily_sales_sql = "SELECT DATE(order_date) as date, 
                    COUNT(*) as total_orders,
                    SUM(total_amount) as total_sales
                    FROM orders 
                    WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'
                    AND status != 'cancelled'
                    GROUP BY DATE(order_date)
                    ORDER BY date DESC";
$daily_sales = $conn->query($daily_sales_sql);

// Top selling items
$top_items_sql = "SELECT m.name, 
                  SUM(oi.quantity) as total_quantity,
                  SUM(oi.quantity * oi.price) as total_revenue
                  FROM order_items oi
                  JOIN menu_items m ON oi.menu_item_id = m.id
                  JOIN orders o ON oi.order_id = o.id
                  WHERE DATE(o.order_date) BETWEEN '$start_date' AND '$end_date'
                  AND o.status != 'cancelled'
                  GROUP BY m.id, m.name
                  ORDER BY total_quantity DESC
                  LIMIT 10";
$top_items = $conn->query($top_items_sql);

// Order status summary
$status_summary_sql = "SELECT status, COUNT(*) as count, SUM(total_amount) as total
                       FROM orders
                       WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'
                       GROUP BY status";
$status_summary = $conn->query($status_summary_sql);

// Total revenue
$total_revenue_sql = "SELECT SUM(total_amount) as total FROM orders 
                      WHERE DATE(order_date) BETWEEN '$start_date' AND '$end_date'
                      AND status != 'cancelled'";
$total_revenue_result = $conn->query($total_revenue_sql)->fetch_assoc();
$total_revenue = $total_revenue_result['total'] ? $total_revenue_result['total'] : 0;
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sales Reports - Campus Cafeteria</title>
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
                <a href="dashboard.php">Dashboard</a>
                <a href="menu.php">Menu Items</a>
                <a href="orders.php">Orders</a>
                <a href="reports.php" class="active">Reports</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <!-- Date Range Filter -->
        <div class="card" style="margin-bottom: 20px;">
            <h3>Select Date Range</h3>
            <form method="GET" action="" style="display: flex; gap: 15px; align-items: end;">
                <div class="form-group" style="margin: 0;">
                    <label>Start Date</label>
                    <input type="date" name="start_date" value="<?php echo $start_date; ?>" style="padding: 8px;">
                </div>
                <div class="form-group" style="margin: 0;">
                    <label>End Date</label>
                    <input type="date" name="end_date" value="<?php echo $end_date; ?>" style="padding: 8px;">
                </div>
                <button type="submit" class="btn btn-primary">Filter</button>
            </form>
        </div>

        <!-- Summary Cards -->
        <div class="dashboard-cards">
            <div class="card">
                <h3>💰 Total Revenue</h3>
                <div class="number"><?php echo number_format($total_revenue, 2); ?> SAR</div>
                <p style="color: #666; margin-top: 5px;">From <?php echo $start_date; ?> to <?php echo $end_date; ?></p>
            </div>
        </div>

        <!-- Daily Sales Report -->
        <div class="table-container" style="margin-top: 20px;">
            <h3>Daily Sales Report</h3>
            <table>
                <thead>
                    <tr>
                        <th>Date</th>
                        <th>Total Orders</th>
                        <th>Total Sales (SAR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($daily_sales->num_rows > 0): ?>
                        <?php while ($row = $daily_sales->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $row['date']; ?></td>
                                <td><?php echo $row['total_orders']; ?></td>
                                <td><?php echo number_format($row['total_sales'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No sales data for selected period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Top Selling Items -->
        <div class="table-container" style="margin-top: 20px;">
            <h3>Top Selling Items</h3>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Total Quantity Sold</th>
                        <th>Total Revenue (SAR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($top_items->num_rows > 0): ?>
                        <?php while ($row = $top_items->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($row['name']); ?></td>
                                <td><?php echo $row['total_quantity']; ?></td>
                                <td><?php echo number_format($row['total_revenue'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No items sold in selected period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <!-- Order Status Summary -->
        <div class="table-container" style="margin-top: 20px;">
            <h3>Order Status Summary</h3>
            <table>
                <thead>
                    <tr>
                        <th>Status</th>
                        <th>Number of Orders</th>
                        <th>Total Amount (SAR)</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($status_summary->num_rows > 0): ?>
                        <?php while ($row = $status_summary->fetch_assoc()): ?>
                            <tr>
                                <td>
                                    <span class="badge badge-<?php echo $row['status']; ?>">
                                        <?php echo ucfirst($row['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo $row['count']; ?></td>
                                <td><?php echo number_format($row['total'], 2); ?></td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align: center;">No orders in selected period</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>