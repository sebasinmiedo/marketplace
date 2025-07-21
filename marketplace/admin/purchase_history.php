<?php include 'check.php'; ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="icon" href="../favicon.ico">
    <title>AdminLTE 3 | Purchase History</title>
    <!-- CSS -->
    <?php include 'includes/css.php'; ?>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">
        <!-- Navbar -->
        <?php include 'includes/navbar.php'; ?>

        <!-- Main Sidebar Container -->
        <?php
        include 'includes/aside.php';
        active('purchase_history', 'purchase_history');
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">
            <!-- Content Header (Page header) -->
            <?php
            $arr = array(
                ["title" => "Home", "url" => "/"],
                ["title" => "Purchase History", "url" => "#"],
            );
            pagePath('Purchase History', $arr);
            ?>

            <section class="content">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Purchase History List</h3>
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="purchaseHistoryTable" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Order ID</th>
                                            <th>Date</th>
                                            <th>Customer</th>
                                            <th>Email</th>
                                            <th>Products</th>
                                            <th>Total Amount</th>
                                            <th>Status</th>
                                            <th>Actions</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
                                        $data = $query->custom("SELECT 
                                            o.id as order_id,
                                            o.order_date,
                                            o.total_amount,
                                            o.status,
                                            u.username,
                                            u.email,
                                            GROUP_CONCAT(p.name SEPARATOR ', ') as products
                                        FROM orders o
                                        JOIN accounts u ON o.user_id = u.id
                                        JOIN order_items oi ON o.id = oi.order_id
                                        JOIN products p ON oi.product_id = p.id
                                        GROUP BY o.id
                                        ORDER BY o.order_date DESC");

                                        foreach ($data as $row) {
                                            echo '<tr>';
                                            echo '<td>#' . $row['order_id'] . '</td>';
                                            echo '<td>' . date('Y-m-d H:i', strtotime($row['order_date'])) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['username']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                                            echo '<td>' . htmlspecialchars($row['products']) . '</td>';
                                            echo '<td>$' . number_format($row['total_amount'], 2) . '</td>';
                                            echo '<td><span class="badge bg-' . 
                                                ($row['status'] == 'completed' ? 'success' : 
                                                ($row['status'] == 'pending' ? 'warning' : 
                                                ($row['status'] == 'cancelled' ? 'danger' : 'info'))) . 
                                                '">' . ucfirst($row['status']) . '</span></td>';
                                            echo '<td>
                                                <button type="button" class="btn btn-info btn-sm" 
                                                        onclick="viewOrderDetails(' . $row['order_id'] . ')">
                                                    <i class="fas fa-eye"></i> View
                                                </button>
                                            </td>';
                                            echo '</tr>';
                                        }
                                        ?>
                                    </tbody>
                                </table>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
            </section>
        </div>

        <!-- Main Footer -->
        <?php include 'includes/footer.php'; ?>
    </div>

    <!-- Modal para ver detalles de la orden -->
    <div class="modal fade" id="orderDetailsModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Order Details</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body" id="orderDetailsContent">
                    <!-- El contenido se cargará dinámicamente -->
                </div>
            </div>
        </div>
    </div>

    <!-- SCRIPTS -->
    <script src="../src/js/jquery.min.js"></script>
    <script src="../src/js/adminlte.js"></script>
    <!-- Bootstrap 4 -->
    <script src="../src/js/bootstrap.bundle.min.js"></script>
    <!-- DataTables -->
    <script src="../src/js/jquery.dataTables.min.js"></script>
    <script src="../src/js/dataTables.bootstrap4.min.js"></script>

    <script>
        $(function () {
            $('#purchaseHistoryTable').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
            });
        });

        function viewOrderDetails(orderId) {
            $.get('get_order_details.php', {order_id: orderId}, function(data) {
                $('#orderDetailsContent').html(data);
                $('#orderDetailsModal').modal('show');
            });
        }
    </script>
</body>
</html> 