<?php
require_once '../config/database.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle cart updates
if (isset($_POST['update_cart'])) {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        $_SESSION['cart'][$item_id] = $quantity;
    } else {
        unset($_SESSION['cart'][$item_id]);
    }
    $success = "Cart updated!";
}

// Handle remove from cart
if (isset($_GET['remove'])) {
    $item_id = (int)$_GET['remove'];
    unset($_SESSION['cart'][$item_id]);
    $success = "Item removed from cart!";
}

// Handle clear cart
if (isset($_GET['clear'])) {
    $_SESSION['cart'] = array();
    $success = "Cart cleared!";
}

// Get cart items details
$cart_items = array();
$total = 0;

if (!empty($_SESSION['cart'])) {
    $ids = array_keys($_SESSION['cart']);
    $ids_str = implode(',', $ids);
    
    $sql = "SELECT * FROM menu_items WHERE id IN ($ids_str) AND availability = 'available'";
    $result = $conn->query($sql);
    
    while ($item = $result->fetch_assoc()) {
        $item['quantity'] = $_SESSION['cart'][$item['id']];
        $item['subtotal'] = $item['price'] * $item['quantity'];
        $total += $item['subtotal'];
        $cart_items[] = $item;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Shopping Cart - Campus Cafeteria</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="menu-container">
        <div class="menu-header">
            <div>
                <h2>🛒 Shopping Cart</h2>
                <p>Review your order</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="menu.php" class="btn btn-primary">← Continue Shopping</a>
                <a href="my_orders.php" class="btn btn-secondary">My Orders</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <div class="cart-container">
            <?php if (empty($cart_items)): ?>
                <div style="text-align: center; padding: 40px;">
                    <h3>Your cart is empty</h3>
                    <p>Add some delicious items from our menu!</p>
                    <a href="menu.php" class="btn btn-primary" style="margin-top: 20px;">Browse Menu</a>
                </div>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>Item</th>
                            <th>Price</th>
                            <th>Quantity</th>
                            <th>Subtotal</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($cart_items as $item): ?>
                            <tr>
                                <td>
                                    <strong><?php echo htmlspecialchars($item['name']); ?></strong><br>
                                    <small><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</small>
                                </td>
                                <td><?php echo number_format($item['price'], 2); ?> SAR</td>
                                <td>
                                    <form method="POST" action="" style="display: flex; align-items: center; gap: 10px;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="<?php echo $item['quantity']; ?>" 
                                               min="1" max="10" style="width: 60px; padding: 5px; text-align: center;">
                                        <button type="submit" name="update_cart" class="btn btn-primary" 
                                                style="padding: 5px 10px; font-size: 0.85em;">Update</button>
                                    </form>
                                </td>
                                <td><?php echo number_format($item['subtotal'], 2); ?> SAR</td>
                                <td>
                                    <a href="cart.php?remove=<?php echo $item['id']; ?>" 
                                       class="btn btn-danger" style="padding: 5px 10px; font-size: 0.85em;"
                                       onclick="return confirm('Remove this item from cart?')">Remove</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <div class="cart-total">
                    <h3>Total: <?php echo number_format($total, 2); ?> SAR</h3>
                </div>

                <div style="display: flex; justify-content: space-between; padding: 20px; border-top: 2px solid #eee;">
                    <a href="cart.php?clear=1" class="btn btn-danger" 
                       onclick="return confirm('Clear all items from cart?')">Clear Cart</a>
                    <a href="place_order.php" class="btn btn-success" style="font-size: 1.1em; padding: 15px 40px;">
                        Proceed to Checkout →
                    </a>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>