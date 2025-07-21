<?php
include 'check.php';

header('Content-Type: application/json');

try {
    if (!isset($_POST['order_id']) || !isset($_SESSION['id'])) {
        throw new Exception('Datos inválidos');
    }

    $order_id = intval($_POST['order_id']);
    $user_id = intval($_SESSION['id']);

    if ($order_id <= 0 || $user_id <= 0) {
        throw new Exception('ID de orden o usuario inválido');
    }

    // Verificar que la orden pertenece al usuario
    $order = $query->select('orders', '*', "WHERE id = $order_id AND user_id = $user_id")[0] ?? null;

    if (!$order) {
        throw new Exception('Orden no encontrada');
    }

    // Verificar que la orden está pendiente y no han pasado 24 horas
    $order_date = strtotime($order['order_date']);
    $current_time = time();
    $hours_diff = ($current_time - $order_date) / 3600;

    if ($order['status'] !== 'pending') {
        throw new Exception('Solo se pueden cancelar órdenes pendientes');
    }

    if ($hours_diff > 24) {
        throw new Exception('Solo se pueden cancelar órdenes dentro de las 24 horas posteriores a la compra');
    }

    $query->conn->begin_transaction();

    try {
        // Actualizar el estado de la orden
        $update_result = $query->executeQuery("UPDATE orders SET status = 'cancelled' WHERE id = $order_id AND user_id = $user_id");
        
        if ($query->conn->affected_rows === 0) {
            throw new Exception('No se pudo actualizar la orden');
        }

        // Devolver el stock a los productos
        $order_items = $query->select('order_items', '*', "WHERE order_id = $order_id");
        foreach ($order_items as $item) {
            $product = $query->select('products', '*', "WHERE id = {$item['product_id']}")[0];
            if (!$product) {
                throw new Exception('Producto no encontrado');
            }
            $new_quantity = $product['quantity'] + $item['quantity'];
            $update_stock = $query->executeQuery("UPDATE products SET quantity = $new_quantity WHERE id = {$item['product_id']}");
            if ($query->conn->affected_rows === 0) {
                throw new Exception('No se pudo actualizar el stock del producto');
            }
        }

        $query->conn->commit();
        echo json_encode([
            'success' => true, 
            'message' => 'Orden cancelada exitosamente',
            'order_id' => $order_id
        ]);

    } catch (Exception $e) {
        $query->conn->rollback();
        throw new Exception('Error al procesar la cancelación: ' . $e->getMessage());
    }

} catch (Exception $e) {
    echo json_encode([
        'success' => false, 
        'message' => $e->getMessage()
    ]);
}
?> 