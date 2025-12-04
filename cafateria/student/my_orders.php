
<?php
require_once '../config/database.php';

// Get email from session or form
$student_email = '';
if (isset($_POST['email'])) {
    $student_email = $conn->real_escape_string($_POST['email']);
    $_SESSION['student_email'] = $student_email;
} elseif (isset($_SESSION['student_email'])) {
    $student_email = $_SESSION['student_email'];
}

// Get orders for this email
$orders = null;
if ($student_email) {
    $sql = "SELECT * FROM orders WHERE student_email = '$student_email' ORDER BY order_date DESC";
    $orders = $conn->query($sql);
}

// Get specific order details if order_id is provided
$selected_order = null;
$order_items = null;
if (isset($_GET['order_id'])) {
    $order_id = (int)$_GET['order_id'];
    $order_sql = "SELECT * FROM orders WHERE id = $order_id AND student_email = '$student_email'";
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
    <title>My Orders - Campus Cafeteria</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="menu-container">
        <div class="menu-header">
            <div>
                <h2>📦 My Orders</h2>
                <p>Track your order status</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="menu.php" class="btn btn-primary">Browse Menu</a>
                <a href="cart.php" class="btn btn-success">🛒 Cart</a>
                <a href="../index.php" class="btn btn-secondary">Home</a>
            </div>
        </div>

        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success">
                <strong>✅ Order placed successfully!</strong><br>
                Your order has been received. You'll be notified when it's ready for pickup.
            </div>
        <?php endif; ?>

        <?php if (!$student_email): ?>
            <!-- Email Input Form -->
            <div class="form-container">
                <h3>View Your Orders</h3>
                <p>Enter your email address to view your order history</p>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Email Address</label>
                        <input type="email" name="email" required placeholder="your.email@example.com">
                    </div>
                    <div class="form-group">
                        <button type="submit" class="btn btn-primary" style="width: 100%;">
                            View My Orders
                        </button>
                    </div>
                </form>
            </div>
        <?php else: ?>
            <!-- Orders List -->
            <div class="table-container">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                    <h3>Orders for: <?php echo htmlspecialchars($student_email); ?></h3>
                    <form method="POST" action="" style="margin: 0;">
                        <input type="hidden" name="email" value="">
                        <button type="submit" class="btn btn-secondary">Change Email</button>
                    </form>
                </div>

                <?php if ($orders && $orders->num_rows > 0): ?>
                    <table>
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Total Amount</th>
                                <th>Status</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php while ($order = $orders->fetch_assoc()): ?>
                                <tr>
                                    <td>#<?php echo $order['id']; ?></td>
                                    <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo number_format($order['total_amount'], 2); ?> SAR</td>
                                    <td>
                                        <span class="badge badge-<?php echo $order['status']; ?>">
                                            <?php echo ucfirst($order['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="my_orders.php?order_id=<?php echo $order['id']; ?>" 
                                           class="btn btn-primary" style="padding: 5px 10px; font-size: 0.85em;">
                                            View Details
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                <?php else: ?>
                    <div style="text-align: center; padding: 40px;">
                        <h3>No orders found</h3>
                        <p>You haven't placed any orders yet.</p>
                        <a href="menu.php" class="btn btn-primary" style="margin-top: 20px;">Start Ordering</a>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($selected_order): ?>
                <!-- Order Details -->
                <div class="table-container" style="margin-top: 20px;">
                    <h3>Order Details - #<?php echo $selected_order['id']; ?></h3>
                    
                    <div style="display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; margin-bottom: 20px; padding: 20px; background: #f8f9fa; border-radius: 10px;">
                        <div>
                            <p><strong>Order Date:</strong> <?php echo date('Y-m-d H:i:s', strtotime($selected_order['order_date'])); ?></p>
                            <p><strong>Status:</strong> 
                                <span class="badge badge-<?php echo $selected_order['status']; ?>">
                                    <?php echo ucfirst($selected_order['status']); ?>
                                </span>
                            </p>
                        </div>
                        <div>
                            <p><strong>Total Amount:</strong> <?php echo number_format($selected_order['total_amount'], 2); ?> SAR</p>
                            <p><strong>Special Notes:</strong> <?php echo htmlspecialchars($selected_order['notes']) ?: 'None'; ?></p>
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
                        <a href="my_orders.php" class="btn btn-secondary">← Back to Orders</a>
                    </div>

                    <?php if ($selected_order['status'] == 'ready'): ?>
                        <div class="alert alert-success" style="margin-top: 20px;">
                            <strong>🎉 Your order is ready for pickup!</strong><br>
                            Please collect your order from the cafeteria counter.
                        </div>
                    <?php elseif ($selected_order['status'] == 'preparing'): ?>
                        <div class="alert alert-info" style="margin-top: 20px;">
                            <strong>👨‍🍳 Your order is being prepared</strong><br>
                            We'll notify you when it's ready!
                        </div>
                    <?php elseif ($selected_order['status'] == 'pending'): ?>
                        <div class="alert alert-warning" style="margin-top: 20px;">
                            <strong>⏳ Order received</strong><br>
                            Your order is in queue and will be prepared soon.
                        </div>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>
