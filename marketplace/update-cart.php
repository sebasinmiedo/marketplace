<?php
include 'check.php';

if (isset($_POST['item_id']) && isset($_POST['quantity'])) {
    $itemId = $_POST['item_id'];
    $quantity = $_POST['quantity'];
    $userId = $_SESSION['id'];

    // Verificar stock disponible
    $product = $query->select('products', '*', "WHERE id = $itemId")[0];
    $currentStock = $product['quantity'];

    if ($quantity <= $currentStock) {
        $query->executeQuery("UPDATE cart SET number_of_products = $quantity WHERE product_id = $itemId AND user_id = $userId");
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay suficiente stock disponible']);
    }
}
?>