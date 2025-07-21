<?php
include 'check.php';

if (!isset($_GET['order_id'])) {
    die('Order ID is required');
}

$orderId = intval($_GET['order_id']);

// Obtener detalles de la orden
$orderData = $query->custom("SELECT 
    o.*,
    u.username,
    u.email,
    o.shipping_address,
    u.number
FROM orders o
JOIN accounts u ON o.user_id = u.id
WHERE o.id = $orderId");

$order = $orderData[0];

// Obtener items de la orden
$itemsData = $query->custom("SELECT 
    oi.*,
    p.name,
    p.price_current as price
FROM order_items oi
JOIN products p ON oi.product_id = p.id
WHERE oi.order_id = $orderId");
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-6">
            <h6>Customer Information</h6>
            <table class="table table-sm">
                <tr>
                    <th>Name:</th>
                    <td><?php echo htmlspecialchars($order['username']); ?></td>
                </tr>
                <tr>
                    <th>Email:</th>
                    <td><?php echo htmlspecialchars($order['email']); ?></td>
                </tr>
                <tr>
                    <th>Phone:</th>
                    <td><?php echo htmlspecialchars($order['number']); ?></td>
                </tr>
                <tr>
                    <th>Address:</th>
                    <td><?php echo htmlspecialchars($order['shipping_address']); ?></td>
                </tr>
            </table>
        </div>
        <div class="col-md-6">
            <h6>Order Information</h6>
            <table class="table table-sm">
                <tr>
                    <th>Order ID:</th>
                    <td>#<?php echo $order['id']; ?></td>
                </tr>
                <tr>
                    <th>Date:</th>
                    <td><?php echo date('Y-m-d H:i', strtotime($order['order_date'])); ?></td>
                </tr>
                <tr>
                    <th>Status:</th>
                    <td>
                        <span class="badge bg-<?php 
                            echo $order['status'] == 'completed' ? 'success' : 
                                ($order['status'] == 'pending' ? 'warning' : 
                                ($order['status'] == 'cancelled' ? 'danger' : 'info')); 
                        ?>">
                            <?php echo ucfirst($order['status']); ?>
                        </span>
                    </td>
                </tr>
            </table>
        </div>
    </div>

    <div class="row mt-4">
        <div class="col-12">
            <h6>Order Items</h6>
            <table class="table table-sm">
                <thead>
                    <tr>
                        <th>Product</th>
                        <th>Price</th>
                        <th>Quantity</th>
                        <th>Subtotal</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach($itemsData as $item): ?>
                    <tr>
                        <td><?php echo htmlspecialchars($item['name']); ?></td>
                        <td>$<?php echo number_format($item['price'], 2); ?></td>
                        <td><?php echo $item['quantity']; ?></td>
                        <td>$<?php echo number_format($item['price'] * $item['quantity'], 2); ?></td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
                <tfoot>
                    <tr>
                        <th colspan="3" class="text-end">Total:</th>
                        <th>$<?php echo number_format($order['total_amount'], 2); ?></th>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div> 