<?php
require_once '../config/database.php';

// Initialize cart if not exists
if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = array();
}

// Handle add to cart
if (isset($_POST['add_to_cart'])) {
    $item_id = (int)$_POST['item_id'];
    $quantity = (int)$_POST['quantity'];
    
    if ($quantity > 0) {
        if (isset($_SESSION['cart'][$item_id])) {
            $_SESSION['cart'][$item_id] += $quantity;
        } else {
            $_SESSION['cart'][$item_id] = $quantity;
        }
        $success = "Item added to cart!";
    }
}

// Get all available menu items grouped by category
$sql = "SELECT m.*, c.name as category_name 
        FROM menu_items m 
        LEFT JOIN categories c ON m.category_id = c.id 
        WHERE m.availability = 'available'
        ORDER BY c.name, m.name";
$menu_items = $conn->query($sql);

// Group items by category
$items_by_category = array();
while ($item = $menu_items->fetch_assoc()) {
    $category = $item['category_name'] ?: 'Other';
    if (!isset($items_by_category[$category])) {
        $items_by_category[$category] = array();
    }
    $items_by_category[$category][] = $item;
}

// Count cart items
$cart_count = array_sum($_SESSION['cart']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu - Campus Cafeteria</title>
    <link rel="stylesheet" href="../css/style.css">
</head>
<body>
    <div class="menu-container">
        <div class="menu-header">
            <div>
                <h2>🍽️ Campus Cafeteria Menu</h2>
                <p>Browse our delicious selection</p>
            </div>
            <div style="display: flex; gap: 10px;">
                <a href="cart.php" class="btn btn-success">
                    🛒 Cart (<?php echo $cart_count; ?>)
                </a>
                <a href="my_orders.php" class="btn btn-primary">My Orders</a>
                <a href="../index.php" class="btn btn-secondary">Home</a>
            </div>
        </div>

        <?php if (isset($success)): ?>
            <div class="alert alert-success"><?php echo $success; ?></div>
        <?php endif; ?>

        <?php foreach ($items_by_category as $category => $items): ?>
            <div style="margin-bottom: 40px;">
                <h3 style="color: #667eea; margin-bottom: 20px; padding: 10px; background: white; border-radius: 10px;">
                    <?php echo htmlspecialchars($category); ?>
                </h3>
                
                <div class="menu-grid">
                    <?php foreach ($items as $item): ?>
                        <div class="menu-item">
                            <div class="menu-item-image">
                                🍽️
                            </div>
                            <div class="menu-item-content">
                                <h3><?php echo htmlspecialchars($item['name']); ?></h3>
                                <p><?php echo htmlspecialchars($item['description']); ?></p>
                                
                                <div class="menu-item-footer">
                                    <div class="price"><?php echo number_format($item['price'], 2); ?> SAR</div>
                                    
                                    <form method="POST" action="" style="display: flex; align-items: center; gap: 10px;">
                                        <input type="hidden" name="item_id" value="<?php echo $item['id']; ?>">
                                        <input type="number" name="quantity" value="1" min="1" max="10" 
                                               style="width: 60px; padding: 5px; border: 1px solid #ddd; border-radius: 5px; text-align: center;">
                                        <button type="submit" name="add_to_cart" class="btn btn-primary" 
                                                style="padding: 8px 15px; font-size: 0.9em;">
                                            Add to Cart
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        <?php endforeach; ?>

        <?php if (empty($items_by_category)): ?>
            <div class="card" style="text-align: center; padding: 40px;">
                <h3>No items available at the moment</h3>
                <p>Please check back later!</p>
            </div>
        <?php endif; ?>
    </div>
</body>
</html>