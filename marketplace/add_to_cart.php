<?php include 'check.php';

if (isset($_GET['product_id']) && isset($_SESSION['id'])) {
    $productId = $_GET['product_id'];
    $quantity = isset($_GET['quantity']) ? $_GET['quantity'] : 1;
    $userId = $_SESSION['id'];

    // Verificar stock disponible
    $product = $query->select('products', '*', "WHERE id = $productId")[0];
    $currentStock = $product['quantity'];
    
    // Verificar si ya existe en el carrito
    $existingCartItem = $query->select('cart', "number_of_products", "WHERE product_id = $productId AND user_id = $userId")[0] ?? null;
    $currentCartQuantity = $existingCartItem ? $existingCartItem['number_of_products'] : 0;
    
    // Verificar si hay suficiente stock
    if ($currentStock >= ($currentCartQuantity + $quantity)) {
        $cartData = array(
            'user_id' => $userId,
            'product_id' => $productId,
            'number_of_products' => $quantity
        );

        if (!isset($query->select('cart', "id", "WHERE product_id = $productId AND user_id = $userId")[0]['id'])) {
            $query->insert('cart', $cartData);
        } else {
            // Actualizar cantidad si ya existe en el carrito
            $newQuantity = $currentCartQuantity + $quantity;
            $query->executeQuery("UPDATE cart SET number_of_products = $newQuantity WHERE product_id = $productId AND user_id = $userId");
        }
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'message' => 'No hay suficiente stock disponible']);
    }
}
