<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get categories
$categories_sql = "SELECT * FROM categories ORDER BY name";
$categories = $conn->query($categories_sql);

$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $conn->real_escape_string($_POST['name']);
    $category_id = (int)$_POST['category_id'];
    $description = $conn->real_escape_string($_POST['description']);
    $price = (float)$_POST['price'];
    $availability = $_POST['availability'];
    
    // Insert menu item
    $sql = "INSERT INTO menu_items (category_id, name, description, price, availability) 
            VALUES ($category_id, '$name', '$description', $price, '$availability')";
    
    if ($conn->query($sql)) {
        header('Location: menu.php?success=added');
        exit;
    } else {
        $error = 'Error adding menu item: ' . $conn->error;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Menu Item - Campus Cafeteria</title>
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
            <h3>Add New Menu Item</h3>
            
            <?php if ($error): ?>
                <div class="alert alert-error"><?php echo $error; ?></div>
            <?php endif; ?>
            
            <form method="POST" action="">
                <div class="form-group">
                    <label>Item Name *</label>
                    <input type="text" name="name" required>
                </div>
                
                <div class="form-group">
                    <label>Category *</label>
                    <select name="category_id" required>
                        <option value="">Select Category</option>
                        <?php while ($category = $categories->fetch_assoc()): ?>
                            <option value="<?php echo $category['id']; ?>">
                                <?php echo htmlspecialchars($category['name']); ?>
                            </option>
                        <?php endwhile; ?>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Description *</label>
                    <textarea name="description" required></textarea>
                </div>
                
                <div class="form-group">
                    <label>Price (SAR) *</label>
                    <input type="number" name="price" step="0.01" min="0" required>
                </div>
                
                <div class="form-group">
                    <label>Availability *</label>
                    <select name="availability" required>
                        <option value="available">Available</option>
                        <option value="unavailable">Unavailable</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <button type="submit" class="btn btn-success">Add Menu Item</button>
                    <a href="menu.php" class="btn btn-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</body>
</html>