<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get menu item ID
if (!isset($_GET['id'])) {
    header('Location: menu.php');
    exit;
}

$id = (int)$_GET['id'];

// Get categories
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($categories_sql);

// Get menu item details
$item_sql = "SELECT * FROM menu_items WHERE id = $id";
$item_result = $conn->query($item_sql);

if ($item_result->num_rows === 0) {
    header('Location: menu.php');
    exit;
}

$item = $item_result->fetch_assoc();
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $availability = $_POST['availability'];
    
    // Update menu item
    $sql = "UPDATE menu_items SET 
            category_id = $category_id,
            name = '$name',
            description = '$description',
            price = $price,
            availability = '$availability'
            WHERE id = $id";
    
    if ($conn->query($sql)) {
        header('Location: menu.php?success=updated');
        exit;
    } else {
        $error = 'Error updating menu item: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Menu Item - Campus Cafeteria</title>
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
                <a href="menu.php" class="active">Menu Items</a>
                <a href="orders.php">Orders</a>
                <a href="reports.php">Reports</a>
                <a href="logout.php">Logout</a>
            </div>
        </div>

        <div class="form-container">
            <h3>Edit Menu Item</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" value="<?php echo htmlspecialchars($item['name']); ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php 
                        $categories->data_seek(0); // Reset pointer
                        while ($category = $categories->fetch_assoc()): 
                        ?>
                            <option value="<?php echo $category['id']; ?>" 
                                <?php echo ($category['id'] == $item['category_id']) ? 'selected' : ''; ?>>
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" required><?php echo htmlspecialchars($item['description']); ?></textarea>
                </div>
                
                <div class="form-group">
                    <label>Price (SAR) *</label>
                    <input type="number" name="price" step="0.01" min="0" value="<?php echo $item['price']; ?>" required>
                </div>
                
                <div class="form-group">
                    <label>Availability *</label>
                    <select name="availability" required>
                        <option value="available" <?php echo ($item['availability'] == 'available') ? 'selected' : ''; ?>>Available</option>
                        <option value="unavailable" <?php echo ($item['availability'] == 'unavailable') ? 'selected' : ''; ?>>Unavailable</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Update Menu Item</button>
                    <a href="menu.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>