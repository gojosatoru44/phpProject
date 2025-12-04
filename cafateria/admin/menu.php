<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

// Get all menu items with category names
$sql = "SELECT m.*, c.name as category_name 
        FROM menu_items m 
        LEFT JOIN categories c ON m.category_id = c.id 
        ORDER BY c.name, m.name";
$menu_items = $conn->query($sql);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Management - Campus Cafeteria</title>
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

        <div class="table-container">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <h3>Menu Items Management</h3>
                <a href="add_menu.php" class="btn btn-success">+ Add New Item</a>
            </div>

            <?php if (isset($_GET['success'])): ?>
                <div class="alert alert-success">
                    <?php 
                    if ($_GET['success'] == 'added') echo 'Menu item added successfully!';
                    if ($_GET['success'] == 'updated') echo 'Menu item updated successfully!';
                    if ($_GET['success'] == 'deleted') echo 'Menu item deleted successfully!';
                    ?>
                </div>
            <?php endif; ?>

            <table>
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Category</th>
                        <th>Description</th>
                        <th>Price (SAR)</th>
                        <th>Availability</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($menu_items->num_rows > 0): ?>
                        <?php while ($item = $menu_items->fetch_assoc()): ?>
                            <tr>
                                <td><?php echo $item['id']; ?></td>
                                <td><?php echo htmlspecialchars($item['name']); ?></td>
                                <td><?php echo htmlspecialchars($item['category_name']); ?></td>
                                <td><?php echo htmlspecialchars(substr($item['description'], 0, 50)); ?>...</td>
                                <td><?php echo number_format($item['price'], 2); ?></td>
                                <td>
                                    <span class="badge badge-<?php echo $item['availability']; ?>">
                                        <?php echo ucfirst($item['availability']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="edit_menu.php?id=<?php echo $item['id']; ?>" class="btn btn-warning" style="padding: 5px 10px; font-size: 0.85em;">Edit</a>
                                    <a href="delete_menu.php?id=<?php echo $item['id']; ?>" class="btn btn-danger" style="padding: 5px 10px; font-size: 0.85em;" onclick="return confirm('Are you sure you want to delete this item?')">Delete</a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="7" style="text-align: center;">No menu items found</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>