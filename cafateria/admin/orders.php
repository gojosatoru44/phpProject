<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get filter
$status_filter = isset($_GET['status']) ? $_GET['status'] : 'all';

// Build query
$sql = "SELECT * FROM orders";
if ($status_filter != 'all') {
    $sql .= " WHERE status = '" . $conn->real_escape_string($status_filter) . "'";
}
$sql .= " ORDER BY order_date DESC";

$orders = $conn->query($sql);

// Get order details if ID is provided
$selected_order = null;
$order_items = null;
if (isset($_GET['id'])) {
    $order_id = (int)$_GET['id'];
    $order_sql = "SELECT * FROM orders WHERE id = $order_id";
    $order_result = $conn->query($order_sql);
    if ($order_result->num_rows > 0) {
        $selected_order = $order_result->fetch_assoc();
        
        // Get order items
        $items_sql = "SELECT oi.*, m.name as item_name 
                      FROM order_items oi 
                      JOIN menu_items m ON oi.menu_item_id = m.id 
                      WHERE oi.order_id = $order_id";
        $order_items = $conn->query($items_sql);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - Campus Cafeteria</title>
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
                <a href="orders.php" class="active">Orders</a>
                <a href="reports.php">Reports</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">Order status updated successfully!</div>
        <?php endif; ?>

        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Orders Management</h3>
                <div>
                    <label>Filter by Status: </label>
                    <select onchange="window.location.href='orders.php?status=' + this.value" style="padding: 8px; border-radius: 5px;">
                        <option value="all" <?php echo ($status_filter == 'all') ? 'selected' : ''; ?>>All Orders</option>
                        <option value="pending" <?php echo ($status_filter == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="preparing" <?php echo ($status_filter == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                        <option value="ready" <?php echo ($status_filter == 'ready') ? 'selected' : ''; ?>>Ready</option>
                        <option value="completed" <?php echo ($status_filter == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($status_filter == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                </div>
            </div>

            <table>
                <thead>
                    <tr>
                        <th>Order ID</th>
                        <th>Student Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Total Amount</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($orders->num_rows > 0): ?>
                        <?php while ($order = $orders->fetch_assoc()): ?>
                            <tr>
                                <td>#<?php echo $order['id']; ?></td>
                                <td><?php echo htmlspecialchars($order['student_name']); ?></td>
                                <td><?php echo htmlspecialchars($order['student_email']); ?></td>
                                <td><?php echo htmlspecialchars($order['student_phone']); ?></td>
                                <td><?php echo number_format($order['total_amount'], 2); ?> SAR</td>
                                <td>
                                    <span class="badge badge-<?php echo $order['status']; ?>">
                                        <?php echo ucfirst($order['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="orders.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85em;">View Details</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="8" style="text-align: center;">No orders found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>

        <?php if ($selected_order): ?>
        <div class="table-container" style="margin-top: 20px;">
            <h3>Order Details - #<?php echo $selected_order['id']; ?></h3>
            
            <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px;">
                <div>
                    <p><strong>Student Name:</strong> <?php echo htmlspecialchars($selected_order['student_name']); ?></p>
                    <p><strong>Email:</strong> <?php echo htmlspecialchars($selected_order['student_email']); ?></p>
                    <p><strong>Phone:</strong> <?php echo htmlspecialchars($selected_order['student_phone']); ?></p>
                </div>
                <div>
                    <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($selected_order['order_date'])); ?></p>
                    <p><strong>Total Amount:</strong> <?php echo number_format($selected_order['total_amount'], 2); ?> SAR</p>
                    <p><strong>Notes:</strong> <?php echo htmlspecialchars($selected_order['notes']) ?: 'None'; ?></p>
                </div>
            </div>

            <h4>Order Items</h4>
            <table>
                <thead>
                    <tr>
                        <th>Item Name</th>
                        <th>Quantity</th>
                        <th>Price</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($item = $order_items->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($item['item_name']); ?></td>
                            <td><?php echo $item['quantity']; ?></td>
                            <td><?php echo number_format($item['price'], 2); ?> SAR</td>
                            <td><?php echo number_format($item['price'] * $item['quantity'], 2); ?> SAR</td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>

            <div style="margin-top: 20px;">
                <h4>Update Order Status</h4>
                <form method="POST" action="update_order_status.php" style="display: flex; gap: 10px; align-items: center;">
                    <input type="hidden" name="order_id" value="<?php echo $selected_order['id']; ?>">
                    <select name="status" style="padding: 10px; border-radius: 5px; border: 1px solid #ddd;">
                        <option value="pending" <?php echo ($selected_order['status'] == 'pending') ? 'selected' : ''; ?>>Pending</option>
                        <option value="preparing" <?php echo ($selected_order['status'] == 'preparing') ? 'selected' : ''; ?>>Preparing</option>
                        <option value="ready" <?php echo ($selected_order['status'] == 'ready') ? 'selected' : ''; ?>>Ready</option>
                        <option value="completed" <?php echo ($selected_order['status'] == 'completed') ? 'selected' : ''; ?>>Completed</option>
                        <option value="cancelled" <?php echo ($selected_order['status'] == 'cancelled') ? 'selected' : ''; ?>>Cancelled</option>
                    </select>
                    <button type="submit" class="btn btn-success">Update Status</button>
                    <a href="orders.php" class="btn btn-secondary">Back to Orders</a>
                </form>
            </div>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>