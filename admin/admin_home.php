<?php
session_start();
include('config.php');

// Check if the user is logged in as an admin
if (!isset($_SESSION["usertype"]) || $_SESSION["usertype"] !== "admin") {
    header("Location: admin_login.php");
    exit;
}

// Fetch total counts
$sql_counts = "SELECT 
    (SELECT COUNT(*) FROM users) AS total_buyers,
    (SELECT COUNT(*) FROM categories) AS total_categories,
    (SELECT COUNT(*) FROM products) AS total_products,
    (SELECT COUNT(*) FROM orders) AS total_orders,
    (SELECT COUNT(*) FROM complaints) AS total_complaints,
    (SELECT COUNT(*) FROM reviews WHERE status = 'pending') AS pending_reviews";
$result = mysqli_query($conn, $sql_counts);
$counts = mysqli_fetch_assoc($result);

// Fetch order status counts
$sql_order_status = "SELECT OrderStatus, COUNT(*) as count FROM orders GROUP BY OrderStatus";
$order_status_result = mysqli_query($conn, $sql_order_status);
$order_status = [];
while($row = mysqli_fetch_assoc($order_status_result)) {
    $order_status[$row['OrderStatus']] = $row['count'];
}

// Fetch payment status counts
$sql_payment_status = "SELECT PaymentStatus, COUNT(*) as count FROM orders GROUP BY PaymentStatus";
$payment_status_result = mysqli_query($conn, $sql_payment_status);
$payment_status = [];
while($row = mysqli_fetch_assoc($payment_status_result)) {
    $payment_status[$row['PaymentStatus']] = $row['count'];
}

// Fetch low stock products (less than 10 items)
$sql_low_stock = "SELECT ProductName, StockQuantity FROM products WHERE StockQuantity < 10 ORDER BY StockQuantity ASC LIMIT 5";
$low_stock_result = mysqli_query($conn, $sql_low_stock);

// Fetch monthly orders for the current year
$sql_monthly_orders = "SELECT MONTH(orders.Timestamp) as month, COUNT(*) as count 
                      FROM orders 
                      WHERE YEAR(orders.Timestamp) = YEAR(CURRENT_DATE)
                      GROUP BY MONTH(orders.Timestamp)";
$monthly_orders_result = mysqli_query($conn, $sql_monthly_orders);
$monthly_orders = array_fill(1, 12, 0); // Initialize all months with 0
while($row = mysqli_fetch_assoc($monthly_orders_result)) {
    $monthly_orders[$row['month']] = $row['count'];
}

// Fetch category distribution
$sql_categories = "SELECT c.name, COUNT(p.ProductID) as product_count 
                  FROM categories c 
                  LEFT JOIN products p ON c.id = p.CategoryID 
                  GROUP BY c.id";
$categories_result = mysqli_query($conn, $sql_categories);

// Fetch recent actions (latest 5 orders and complaints combined)
$sql_recent = "SELECT 'order' as type, OrderID as id, Timestamp, 'New order received' as description 
              FROM orders 
              UNION ALL 
              SELECT 'complaint' as type, ComplaintID as id, SubmissionDate as Timestamp, ComplaintReason as description 
              FROM complaints 
              ORDER BY Timestamp DESC LIMIT 5";
$recent_result = mysqli_query($conn, $sql_recent);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Dashboard</title>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css">
    <link href="https://cdn.jsdelivr.net/npm/boxicons@2.0.7/css/boxicons.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <link rel="stylesheet" href="./css/style.css">

    <style>
        .dashboard-card {
            border-radius: 10px;
            box-shadow: 0 0 10px rgba(0,0,0,0.1);
            transition: transform 0.3s;
        }
        .dashboard-card:hover {
            transform: translateY(-5px);
        }
        .status-badge {
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 0.8rem;
        }
    </style>
</head>
<body class="bg-light">
    <?php include 'admin_navbar.php'; ?>
    
    <div class="container-fluid py-4">
        <h2 class="mb-4 text-center">Dashboard Overview</h2>
        
        <!-- Main Stats -->
        <div class="row g-4 mb-4">
            <div class="col-md-2">
                <div class="dashboard-card card bg-primary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Customers</h6>
                        <h3 class="mb-0"><?php echo $counts['total_buyers']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="dashboard-card card bg-success text-white">
                    <div class="card-body">
                        <h6 class="card-title">Categories</h6>
                        <h3 class="mb-0"><?php echo $counts['total_categories']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="dashboard-card card bg-info text-white">
                    <div class="card-body">
                        <h6 class="card-title">Products</h6>
                        <h3 class="mb-0"><?php echo $counts['total_products']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="dashboard-card card bg-warning text-white">
                    <div class="card-body">
                        <h6 class="card-title">Total Orders</h6>
                        <h3 class="mb-0"><?php echo $counts['total_orders']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="dashboard-card card bg-danger text-white">
                    <div class="card-body">
                        <h6 class="card-title">Complaints</h6>
                        <h3 class="mb-0"><?php echo $counts['total_complaints']; ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-2">
                <div class="dashboard-card card bg-secondary text-white">
                    <div class="card-body">
                        <h6 class="card-title">Pending Reviews</h6>
                        <h3 class="mb-0"><?php echo $counts['pending_reviews']; ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Order Status and Charts -->
        <div class="row g-4 mb-4">
            <!-- Order Status -->
            <div class="col-md-4">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Order Status</h5>
                    </div>
                    <div class="card-body">
                        <div class="d-flex flex-column gap-2">
                            <?php
                            $status_colors = [
                                'Pending' => 'warning',
                                'Processing' => 'info',
                                'Shipped' => 'primary',
                                'Delivered' => 'success',
                                'Cancelled' => 'danger'
                            ];
                            foreach($order_status as $status => $count) {
                                $color = $status_colors[$status] ?? 'secondary';
                                echo "<div class='d-flex justify-content-between align-items-center'>";
                                echo "<span class='status-badge bg-{$color} m-2'>{$status}</span>";
                                echo "<span class='fw-bold m-2'>{$count}</span>";
                                echo "</div>";
                            }
                            ?>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Monthly Orders Chart -->
            <div class="col-md-4">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Monthly Orders</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="monthlyOrdersChart"></canvas>
                    </div>
                </div>
            </div>

            <!-- Category Distribution -->
            <div class="col-md-4">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Category Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="categoryChart"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <!-- Low Stock and Recent Actions -->
        <div class="row g-4">
            <!-- Low Stock Products -->
            <div class="col-md-6">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Low Stock Alert</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-sm">
                                <thead>
                                    <tr>
                                        <th>Product</th>
                                        <th>Stock Left</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while($row = mysqli_fetch_assoc($low_stock_result)) { ?>
                                    <tr>
                                        <td><?php echo $row['ProductName']; ?></td>
                                        <td>
                                            <span class="badge bg-danger"><?php echo $row['StockQuantity']; ?></span>
                                        </td>
                                    </tr>
                                    <?php } ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Recent Actions -->
            <div class="col-md-6">
                <div class="dashboard-card card">
                    <div class="card-header">
                        <h5 class="card-title mb-0">Recent Activities</h5>
                    </div>
                    <div class="card-body">
                        <div class="timeline">
                            <?php while($row = mysqli_fetch_assoc($recent_result)) { ?>
                            <div class="d-flex mb-3">
                                <div class="flex-shrink-0">
                                    <i class='bx bx-<?php echo $row['type'] == 'order' ? 'package' : 'message-square-dots'; ?> text-primary'></i>
                                </div>
                                <div class="flex-grow-1 ms-3">
                                    <h6 class="mb-1"><?php echo $row['description']; ?></h6>
                                    <small class="text-muted">
                                        <?php echo date('M d, Y H:i', strtotime($row['Timestamp'])); ?>
                                    </small>
                                </div>
                            </div>
                            <?php } ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Monthly Orders Chart
        new Chart(document.getElementById('monthlyOrdersChart'), {
            type: 'bar',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'],
                datasets: [{
                    label: 'Orders',
                    data: <?php echo json_encode(array_values($monthly_orders)); ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.5)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                indexAxis: 'y',
                scales: {
                    x: {
                        beginAtZero: true
                    }
                }
            }
        });

        // Category Distribution Chart
        new Chart(document.getElementById('categoryChart'), {
            type: 'pie',
            data: {
                labels: [<?php 
                    $labels = [];
                    $data = [];
                    while($row = mysqli_fetch_assoc($categories_result)) {
                        $labels[] = $row['name'];
                        $data[] = $row['product_count'];
                    }
                    echo "'" . implode("','", $labels) . "'";
                ?>],
                datasets: [{
                    data: <?php echo '[' . implode(',', $data) . ']'; ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.5)',
                        'rgba(54, 162, 235, 0.5)',
                        'rgba(255, 206, 86, 0.5)',
                        'rgba(75, 192, 192, 0.5)',
                        'rgba(153, 102, 255, 0.5)'
                    ]
                }]
            }
        });
    </script>
</body>
</html>