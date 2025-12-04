<?php
require_once '../config/database.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = (int)$_POST['order_id'];
    $status = $conn->real_escape_string($_POST['status']);
    
    $sql = "UPDATE orders SET status = '$status' WHERE id = $order_id";
    
    if ($conn->query($sql)) {
        header('Location: orders.php?id=' . $order_id . '&success=1');
    } else {
        header('Location: orders.php?id=' . $order_id . '&error=1');
    }
} else {
    header('Location: orders.php');
}
exit;
?>