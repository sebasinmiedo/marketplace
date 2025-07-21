<?php
include 'check.php';

if (isset($_GET['order_id']) && isset($_SESSION['id'])) {
    $order_id = $_GET['order_id'];
    $user_id = $_SESSION['id'];

    // Verificar que la orden pertenece al usuario
    $order = $query->select('orders', '*', "WHERE id = $order_id AND user_id = $user_id")[0] ?? null;

    if (!$order) {
        echo json_encode(['success' => false, 'message' => 'Orden no encontrada']);
        exit;
    }

    // Obtener los items de la orden
    $items = $query->select(
        'order_items oi',
        'oi.*, p.name as product_name',
        "JOIN products p ON p.id = oi.product_id WHERE oi.order_id = $order_id"
    );

    echo json_encode([
        'success' => true,
        'items' => $items,
        'total' => $order['total_amount']
    ]);
} else {
    echo json_encode(['success' => false, 'message' => 'Datos inválidos']);
}
?> 