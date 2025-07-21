<?php include './check.php'; ?>


<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta http-equiv="x-ua-compatible" content="ie=edge">
    <link rel="icon" href="../favicon.ico">
    <title>AdminLTE 3 | Products</title>
    <!-- CSS -->
    <?php include 'includes/css.php'; ?>
</head>

<body class="hold-transition sidebar-mini">
    <div class="wrapper">

        <!-- Navbar -->
        <?php include './includes/navbar.php'; ?>

        <!-- Main Sidebar Container -->
        <?php
        include './includes/aside.php';
        active('products', 'products');
        ?>

        <!-- Content Wrapper. Contains page content -->
        <div class="content-wrapper">

            <!-- Content Header (Page header) -->
            <?php
            $arr = array(
                ["title" => "Home", "url" => "/"],
                ["title" => "Products", "url" => "#"],
            );
            pagePath('Products', $arr);
            ?>

            <section class="content">                  

                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title">Products List</h3>
                            </div>
                             <!-- /Boton Agregar y editar-->
                            <div class="card-header d-flex justify-content">
                                <button class="btn btn-warning" data-toggle="modal" data-target="#addProductModal">Add Product</button>
                                <button class="btn btn-warning" data-toggle="modal" data-target="#editProductModal">Editar Producto</button>
                                <button class="btn btn-danger" data-toggle="modal" data-target="#deleteProductModal">Eliminar Producto</button>
                              </div>
                            
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="example2" class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>Product</th>
                                            <th>Price Old</th>
                                            <th>Price Current</th>
                                            <th>Description</th>
                                            <th>Rating</th>
                                            <th>Quantity</th>
                                            <th>Added to site</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php
$data = $query->custom("SELECT 
    p.id, 
    p.name, 
    p.price_old, 
    p.price_current, 
    p.description, 
    p.rating, 
    p.quantity, 
    p.added_to_site, 
    (SELECT image_url FROM product_images WHERE product_id = p.id LIMIT 1) as image_url
FROM products p");

foreach ($data as $row) {
    echo '<tr>';
    // Usa una ruta relativa para las imágenes
    echo '<td>' . htmlspecialchars($row['name']) . '</td>';
    echo '<td>' . htmlspecialchars($row['price_old']) . '</td>';
    echo '<td>' . htmlspecialchars($row['price_current']) . '</td>';
    echo '<td>' . htmlspecialchars($row['description']) . '</td>';
    echo '<td>' . htmlspecialchars($row['rating']) . '</td>';
    echo '<td>' . htmlspecialchars($row['quantity']) . '</td>';
    echo '<td>' . htmlspecialchars($row['added_to_site']) . '</td>';
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
        <?php include './includes/footer.php'; ?>
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
            $('#example2').DataTable({
                "paging": true,
                "lengthChange": false,
                "searching": true,
                "ordering": true,
                "info": true,
                "autoWidth": true,
                "responsive": true,
            });
        });
    </script>

    <script>
        function changeStatus(userId, newStatus) {
            window.location.href = "./change_status.php?userId=" + userId + "&newStatus=" + newStatus + "&userrole=user";
        }
    </script>
    

  <!--Modal Add Products -->
<div class="modal fade" id="addProductModal" tabindex="-1" role="dialog" aria-labelledby="addProductModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="addProductModalLabel">Add New Product</h5>
        <button type="button" class="close" data-dismiss="modal" aria-label="Close">
          <span aria-hidden="true">&times;</span>
        </button>
      </div>
      <div class="modal-body p-0">
        <iframe src="./add_product.php" frameborder="0" style="width:100%; height:600px;"></iframe>
      </div>
    </div>
  </div>
</div>

<!-- Modal para editar -->
<div class="modal fade" id="editProductModal" tabindex="-1" role="dialog" aria-labelledby="editProductLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg" role="document">
    <form id="editProductForm" method="POST" action="./edit_product.php">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title">Editar Producto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>

        <div class="modal-body">
          <!-- Selección de producto -->
          <div class="form-group">
            <label for="selectProduct">Selecciona un producto</label>
            <select class="form-control" id="selectProduct">
              <option value="">-- Selecciona --</option>
              <?php
              // Asume que tienes una variable $productos con todos los productos
                $productos = $query->custom("
                    SELECT 
                        products.id, 
                        products.name, 
                        products.price_old, 
                        products.price_current, 
                        products.description, 
                        products.rating, 
                        products.quantity, 
                        products.category_id, 
                        c.category_name
                    FROM products
                    JOIN categories c ON products.category_id = c.id
                ");
                foreach ($productos as $producto) {
                    echo '<option value="' . $producto['id'] . '" 
                        data-name="' . htmlspecialchars($producto['name']) . '" 
                        data-price_old="' . $producto['price_old'] . '" 
                        data-price_current="' . $producto['price_current'] . '" 
                        data-description="' . htmlspecialchars($producto['description']) . '" 
                        data-rating="' . $producto['rating'] . '" 
                        data-quantity="' . $producto['quantity'] . '" 
                        data-category_id="' . $producto['category_id'] . '">'
                        . htmlspecialchars($producto['name']) . ' (' . htmlspecialchars($producto['category_name']) . ')'
                        . '</option>';
                }
              ?>
            </select>
          </div>

          <!-- Campos editables -->
          <input type="hidden" name="id" id="editProductId">

          <div class="form-group">
            <label>Nombre</label>
            <input type="text" name="name" class="form-control" id="editName">
          </div>

          <div class="form-group">
            <label>Precio anterior</label>
            <input type="text" name="price_old" class="form-control" id="editPriceOld">
          </div>

          <div class="form-group">
            <label>Precio actual</label>
            <input type="text" name="price_current" class="form-control" id="editPriceCurrent">
          </div>

          <div class="form-group">
            <label>Descripción</label>
            <textarea name="description" class="form-control" id="editDescription"></textarea>
          </div>

          <div class="form-group">
            <label>Rating</label>
            <input type="number" name="rating" class="form-control" id="editRating">
          </div>

          <div class="form-group">
            <label>Cantidad</label>
            <input type="number" name="quantity" class="form-control" id="editQuantity">
          </div>

          <div class="form-group">
            <label>Categoría</label>
            <select name="category_id" id="editCategory" class="form-control">
                <option value="">-- Selecciona una categoría --</option>
                <?php
                // Suponiendo que tienes un array $categorias con id y nombre de cada categoría
                $categorias = $query->custom("SELECT id,category_name FROM categories");
                foreach ($categorias as $cat) {
                    echo '<option value="' . $cat['id'] . '">' . htmlspecialchars($cat['category_name']) . '</option>';
                }
                ?>
            </select>
            </div>

        </div>

        <div class="modal-footer">
          <button type="submit" class="btn btn-success">Guardar cambios</button>
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
        </div>
      </div>
    </form>
  </div>
</div>
<script>
    document.getElementById('selectProduct').addEventListener('change', function() {
        const selected = this.options[this.selectedIndex];

        if (selected.value !== "") {
            document.getElementById('editProductId').value = selected.value;
            document.getElementById('editName').value = selected.getAttribute('data-name');
            document.getElementById('editPriceOld').value = selected.getAttribute('data-price_old');
            document.getElementById('editPriceCurrent').value = selected.getAttribute('data-price_current');
            document.getElementById('editDescription').value = selected.getAttribute('data-description');
            document.getElementById('editRating').value = selected.getAttribute('data-rating');
            document.getElementById('editQuantity').value = selected.getAttribute('data-quantity');
            document.getElementById('editCategory').value = selected.getAttribute('data-category_id');
        }
    });
    </script>
  <!-- Modal para eliminar producto -->
<div class="modal fade" id="deleteProductModal" tabindex="-1" role="dialog" aria-labelledby="deleteProductModalLabel" aria-hidden="true">
  <div class="modal-dialog" role="document">
    <form id="deleteProductForm" action="./delete_product.php" method="POST">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title" id="deleteProductModalLabel">Eliminar Producto</h5>
          <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
            <span aria-hidden="true">&times;</span>
          </button>
        </div>
        <div class="modal-body">
          <label for="productSelect">Selecciona el producto a eliminar:</label>
          <select class="form-control" id="productSelect" name="productId" required>
            <?php
            // Consulta a la tabla de productos
            $productos = $query->custom("SELECT id, name FROM products");
            foreach ($productos as $producto) {
                echo '<option value="' . $producto['id'] . '">' . htmlspecialchars($producto['name']) . '</option>';
            }   
            ?>
          </select>
        </div>

        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
          <button type="submit" class="btn btn-danger">Eliminar</button>
        </div>
      </div>
    </form>
  </div>
</div>
</body>

</html>
