<?php
include 'check.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reportes y Estadísticas | Admin Panel</title>
    <?php include 'includes/css.php'; ?>
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <?php include 'includes/navbar.php'; ?>
        <?php 
        include 'includes/aside.php';
        active('reports', 'reports');
        ?>

        <div class="content-wrapper">
            <?php
            $arr = array(
                ["title" => "Home", "url" => "/"],
                ["title" => "Reportes", "url" => "#"],
                ["title" => "Dashboard", "url" => "#"],
            );
            pagePath('Dashboard de Reportes', $arr);
            ?>

            <section class="content">
                <!-- Métricas Principales -->
                <div class="row">
                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-info">
                            <div class="inner">
                                <?php
                                $total_sales = $query->custom("SELECT SUM(total_amount) as total FROM orders WHERE status != 'cancelled'")[0]['total'] ?? 0;
                                ?>
                                <h3>$<?php echo number_format($total_sales, 2); ?></h3>
                                <p>Ventas Totales</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-dollar-sign"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-success">
                            <div class="inner">
                                <?php
                                $total_orders = $query->custom("SELECT COUNT(*) as total FROM orders WHERE status != 'cancelled'")[0]['total'] ?? 0;
                                ?>
                                <h3><?php echo $total_orders; ?></h3>
                                <p>Pedidos Totales</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-shopping-cart"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-warning">
                            <div class="inner">
                                <?php
                                $total_users = $query->custom("SELECT COUNT(*) as total FROM accounts WHERE role = 'user'")[0]['total'] ?? 0;
                                ?>
                                <h3><?php echo $total_users; ?></h3>
                                <p>Usuarios Registrados</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-users"></i>
                            </div>
                        </div>
                    </div>

                    <div class="col-lg-3 col-6">
                        <div class="small-box bg-danger">
                            <div class="inner">
                                <?php
                                $total_products = $query->custom("SELECT COUNT(*) as total FROM products")[0]['total'] ?? 0;
                                ?>
                                <h3><?php echo $total_products; ?></h3>
                                <p>Productos Totales</p>
                            </div>
                            <div class="icon">
                                <i class="fas fa-box"></i>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Gráficos -->
                <div class="row">
                    <!-- Gráfico de Ventas por Mes -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Ventas por Mes</h3>
                                <div class="card-tools">
                                    <div class="btn-group">
                                        <a href="export_report.php?type=sales&format=csv" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-csv"></i> Exportar CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="salesChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>

                    <!-- Gráfico de Productos más Vendidos -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Top 5 Productos más Vendidos</h3>
                                <div class="card-tools">
                                    <div class="btn-group">
                                        <a href="export_report.php?type=products&format=csv" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-csv"></i> Exportar CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body">
                                <canvas id="productsChart" style="min-height: 250px; height: 250px; max-height: 250px; max-width: 100%;"></canvas>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Tablas de Datos -->
                <div class="row">
                    <!-- Últimos Pedidos -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Últimos Pedidos</h3>
                                <div class="card-tools">
                                    <div class="btn-group">
                                        <a href="export_report.php?type=sales&format=csv" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-csv"></i> Exportar CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>ID</th>
                                            <th>Cliente</th>
                                            <th>Total</th>
                                            <th>Estado</th>
                                            <th>Fecha</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $recent_orders = $query->custom("
                                            SELECT o.*, u.username 
                                            FROM orders o 
                                            JOIN accounts u ON o.user_id = u.id 
                                            WHERE o.status != 'cancelled'
                                            ORDER BY o.order_date DESC 
                                            LIMIT 5
                                        ");

                                        foreach ($recent_orders as $order) {
                                            echo "<tr>";
                                            echo "<td>#" . $order['id'] . "</td>";
                                            echo "<td>" . htmlspecialchars($order['username']) . "</td>";
                                            echo "<td>$" . number_format($order['total_amount'], 2) . "</td>";
                                            echo "<td><span class='badge bg-" . 
                                                ($order['status'] == 'delivered' ? 'success' : 
                                                ($order['status'] == 'pending' ? 'warning' : 
                                                ($order['status'] == 'cancelled' ? 'danger' : 'info'))) . 
                                                "'>" . ucfirst($order['status']) . "</span></td>";
                                            echo "<td>" . date('Y-m-d H:i', strtotime($order['order_date'])) . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    <!-- Productos con Bajo Stock -->
                    <div class="col-md-6">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Productos con Bajo Stock</h3>
                                <div class="card-tools">
                                    <div class="btn-group">
                                        <a href="export_report.php?type=products&format=csv" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-csv"></i> Exportar CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Producto</th>
                                            <th>Stock Actual</th>
                                            <th>Precio</th>
                                            <th>Estado</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $low_stock = $query->custom("
                                            SELECT * FROM products 
                                            WHERE quantity <= 10 
                                            ORDER BY quantity ASC 
                                            LIMIT 5
                                        ");

                                        foreach ($low_stock as $product) {
                                            $stock_status = $product['quantity'] <= 5 ? 'danger' : 'warning';
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($product['name']) . "</td>";
                                            echo "<td>" . $product['quantity'] . "</td>";
                                            echo "<td>$" . number_format($product['price_current'], 2) . "</td>";
                                            echo "<td><span class='badge bg-" . $stock_status . "'>" . 
                                                ($product['quantity'] <= 5 ? 'Crítico' : 'Bajo') . 
                                                "</span></td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Reporte de Usuarios -->
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Reporte de Usuarios</h3>
                                <div class="card-tools">
                                    <div class="btn-group">
                                        <a href="export_report.php?type=users&format=csv" class="btn btn-sm btn-info">
                                            <i class="fas fa-file-csv"></i> Exportar CSV
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <div class="card-body table-responsive p-0">
                                <table class="table table-hover">
                                    <thead>
                                        <tr>
                                            <th>Usuario</th>
                                            <th>Email</th>
                                            <th>Fecha Registro</th>
                                            <th>Total Pedidos</th>
                                            <th>Total Gastado</th>
                                            <th>Último Pedido</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $user_report = $query->custom("
                                            SELECT 
                                                u.username,
                                                u.email,
                                                u.registration_date,
                                                COUNT(DISTINCT o.id) as total_orders,
                                                SUM(o.total_amount) as total_spent,
                                                MAX(o.order_date) as last_order_date
                                            FROM accounts u
                                            LEFT JOIN orders o ON u.id = o.user_id AND o.status != 'cancelled'
                                            WHERE u.role = 'user'
                                            GROUP BY u.id
                                            ORDER BY total_spent DESC
                                            LIMIT 5
                                        ");

                                        foreach ($user_report as $user) {
                                            echo "<tr>";
                                            echo "<td>" . htmlspecialchars($user['username']) . "</td>";
                                            echo "<td>" . htmlspecialchars($user['email']) . "</td>";
                                            echo "<td>" . date('Y-m-d', strtotime($user['registration_date'])) . "</td>";
                                            echo "<td>" . $user['total_orders'] . "</td>";
                                            echo "<td>$" . number_format($user['total_spent'] ?? 0, 2) . "</td>";
                                            echo "<td>" . ($user['last_order_date'] ? date('Y-m-d', strtotime($user['last_order_date'])) : 'N/A') . "</td>";
                                            echo "</tr>";
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </section>
        </div>

        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- SCRIPTS -->
    <script src="../src/js/jquery.min.js"></script>
    <script src="../src/js/adminlte.js"></script>
    <script src="../src/js/bootstrap.bundle.min.js"></script>

    <script>
    // Datos para el gráfico de ventas por mes
    <?php
    $monthly_sales = $query->custom("
        SELECT 
            DATE_FORMAT(order_date, '%Y-%m') as month,
            SUM(total_amount) as total
        FROM orders 
        WHERE status != 'cancelled'
        AND order_date >= DATE_SUB(NOW(), INTERVAL 6 MONTH)
        GROUP BY DATE_FORMAT(order_date, '%Y-%m')
        ORDER BY month ASC
    ");

    $months = array_column($monthly_sales, 'month');
    $sales = array_column($monthly_sales, 'total');
    ?>

    // Gráfico de Ventas por Mes
    new Chart(document.getElementById('salesChart'), {
        type: 'line',
        data: {
            labels: <?php echo json_encode($months); ?>,
            datasets: [{
                label: 'Ventas Mensuales',
                data: <?php echo json_encode($sales); ?>,
                borderColor: 'rgb(75, 192, 192)',
                tension: 0.1,
                fill: false
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        callback: function(value) {
                            return '$' + value;
                        }
                    }
                }
            }
        }
    });

    // Datos para el gráfico de productos más vendidos
    <?php
    $top_products = $query->custom("
        SELECT 
            p.name,
            SUM(oi.quantity) as total_quantity
        FROM order_items oi
        JOIN products p ON oi.product_id = p.id
        JOIN orders o ON oi.order_id = o.id
        WHERE o.status != 'cancelled'
        GROUP BY p.id
        ORDER BY total_quantity DESC
        LIMIT 5
    ");

    $product_names = array_column($top_products, 'name');
    $quantities = array_column($top_products, 'total_quantity');
    ?>

    // Gráfico de Productos más Vendidos
    new Chart(document.getElementById('productsChart'), {
        type: 'bar',
        data: {
            labels: <?php echo json_encode($product_names); ?>,
            datasets: [{
                label: 'Unidades Vendidas',
                data: <?php echo json_encode($quantities); ?>,
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgb(54, 162, 235)',
                borderWidth: 1
            }]
        },
        options: {
            responsive: true,
            scales: {
                y: {
                    beginAtZero: true,
                    ticks: {
                        stepSize: 1
                    }
                }
            }
        }
    });
    </script>
</body>
</html> 