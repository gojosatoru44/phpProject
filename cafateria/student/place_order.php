<?php
require_once '../config/database.php';

// Check if cart is empty
if (!isset($_SESSION['cart']) || empty($_SESSION['cart'])) {
    header('Location: menu.php');
    exit;
}

// Calculate total
$ids = array_keys($_SESSION['cart']);
$ids_str = implode(',', $ids);
$sql = "SELECT * FROM menu_items WHERE id IN ($ids_str) AND availability = 'available'";
$result = $conn->query($sql);

$cart_items = array();
$total = 0;

while ($item = $result->fetch_assoc()) {
    $item['quantity'] = $_SESSION['cart'][$item['id']];
    $item['subtotal'] = $item['price'] * $item['quantity'];
    $total += $item['subtotal'];
    $cart_items[] = $item;
}

$error = '';
$success = '';

// Handle order submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $student_name = $conn->real_escape_string(trim($_POST['student_name']));
    $student_email = $conn->real_escape_string(trim($_POST['student_email']));
    $student_phone = $conn->real_escape_string(trim($_POST['student_phone']));
    $notes = $conn->real_escape_string(trim($_POST['notes']));
    
    // Validate
    if (empty($student_name) || empty($student_email) || empty($student_phone)) {
        $error = "Please fill in all required fields";
    } else {
        // Start transaction
        $conn->begin_transaction();
        
        try {
            // Insert order
            $insert_order = "INSERT INTO orders (student_name, student_email, student_phone, total_amount, notes, status) 
                            VALUES ('$student_name', '$student_email', '$student_phone', $total, '$notes', 'pending')";
            
            if ($conn->query($insert_order)) {
                $order_id = $conn->insert_id;
                
                // Insert order items
                foreach ($cart_items as $item) {
                    $menu_item_id = $item['id'];
                    $quantity = $item['quantity'];
                    $price = $item['price'];
                    
                    $insert_item = "INSERT INTO order_items (order_id, menu_item_id, quantity, price) 
                                   VALUES ($order_id, $menu_item_id, $quantity, $price)";
                    $conn->query($insert_item);
                }
                
                // Commit transaction
                $conn->commit();
                
                // Clear cart
                $_SESSION['cart'] = array();
                
                // Redirect to success page
                header('Location: my_orders.php?order_id=' . $order_id . '&success=1');
                exit;
            }
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Error placing order. Please try again.";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Place Order - Campus Cafeteria</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="menu-container">
        <div class="menu-header">
            <div>
                <h2>📝 Checkout</h2>
                <p>Complete your order</p>
            </div>
            <div>
                <a href="cart.php" class="btn btn-secondary">← Back to Cart</a>
            </div>
        </div>

        <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 30px;">
            <!-- Order Form -->
            <div class="form-container">
                <h3>Your Information</h3>
                
                <?php if ($error): ?>
                    <div class="alert alert-error"><?php echo $error; ?></div>
                <?php endif; ?>
                
                <form method="POST" action="">
                    <div class="form-group">
                        <label>Full Name *</label>
                        <input type="text" name="student_name" required 
                               value="<?php echo isset($_POST['student_name']) ? htmlspecialchars($_POST['student_name']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Email Address *</label>
                        <input type="email" name="student_email" required 
                               value="<?php echo isset($_POST['student_email']) ? htmlspecialchars($_POST['student_email']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Phone Number *</label>
                        <input type="tel" name="student_phone" required 
                               value="<?php echo isset($_POST['student_phone']) ? htmlspecialchars($_POST['student_phone']) : ''; ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Special Instructions (Optional)</label>
                        <textarea name="notes" rows="4" 
                                  placeholder="Any special requests or dietary requirements?"><?php echo isset($_POST['notes']) ? htmlspecialchars($_POST['notes']) : ''; ?></textarea>
                    </div>
                    
                    <div class="form-group">
                        <button type="submit" class="btn btn-success" style="width: 100%; font-size: 1.1em; padding: 15px;">
                            Place Order
                        </button>
                    </div>
                </form>
            </div>

            <!-- Order Summary -->
            <div class="cart-container">
                <h3>Order Summary</h3>
                
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Qty</th>
                            <th>Price</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo $item['quantity']; ?></td>
                                <td><?php echo number_format($item['subtotal'], 2); ?> SAR</td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
                
                <div class="cart-total">
                    <h3>Total: <?php echo number_format($total, 2); ?> SAR</h3>
                </div>
                
                <div class="alert alert-info">
                    <strong>📱 Payment Method:</strong><br>
                    Pay when you pick up your order at the cafeteria counter.
                </div>
            </div>
        </div>
    </div>
</body>
</html>