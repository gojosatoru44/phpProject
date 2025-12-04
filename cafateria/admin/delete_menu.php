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

// Delete menu item
$sql = "DELETE FROM menu_items WHERE id = $id";

if ($conn->query($sql)) {
    header('Location: menu.php?success=deleted');
} else {
    header('Location: menu.php?error=delete_failed');
}
exit;
?>