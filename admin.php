<?php
error_reporting(0);
?>

<!DOCTYPE html>
<html>
<head>
    <title>Admin Pannel</title>
    <meta charset="UTF-8">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
</head>
<body>
    <center>
    <div class="container mt-5">
        <?php
        session_start();
        include "connect.php";
        $expected_username = "admin";
        $expected_password = "admin";

        if (isset($_GET['logout'])) {
            session_unset();
            session_destroy();
            header("Location: ?");
            exit;
        }

        if (isset($_GET['username']) && isset($_GET['password'])) {
            $entered_username = filter_var($_GET['username'], FILTER_SANITIZE_STRING);
            $entered_password = filter_var($_GET['password'], FILTER_SANITIZE_STRING);

            if ($entered_username === $expected_username && $entered_password === $expected_password) {
                $_SESSION['authenticated'] = true;
            }
        }

        if (!isset($_SESSION['authenticated'])) { ?>
            <h1 class="mb-4">Login</h1>
            <form method="get">
                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" class="form-control" required>
                </div>
                <div class="form-group">
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password" class="form-control" required>
                </div>
                <br>
                <button type="submit" class="btn btn-primary">Login</button>
            </form>
        <?php } else { ?>
            <h1 class="mb-4">Admin Dashboard</h1>
            <a href="?logout" class="btn btn-primary mb-3">Logout</a>
            <table border='2' class="table">
                <thead>
                    <tr align='center'>
                        <th>Order ID</th>
                        <th>Email</th>
                        <th>Payment Method</th>
                        <th>Amount[USDT]</th>
                        <th>Payment Details</th>
                        <th>Transaction ID</th>
                        <th>Status</th>
                        <th>Image</th>
                        <th>Update Status</th>
                    </tr>
                </thead>
                <tbody>
                    <?php

                    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                        $order_id = filter_var($_POST['order_id'], FILTER_SANITIZE_STRING);
                        $new_status = filter_var($_POST['new_status'], FILTER_SANITIZE_STRING);

                        // Update the status in the database
                        $update_query1 = "UPDATE orders SET status = '$new_status' WHERE order_id = '$order_id'";
                        

                        if ($new_status === 'success') {
                            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                                $image_name = $_FILES['image']['name'];
                                $image_tmp = $_FILES['image']['tmp_name'];
                                $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                                if (in_array($image_extension, $allowed_extensions)) {
                                    $new_image_name = $order_id . '.' . $image_extension;
                                    $image_path = "proofs/" . $new_image_name;
                                    move_uploaded_file($image_tmp, $image_path);
                                    $update_image_query = "UPDATE orders SET image_url = '$image_path' WHERE order_id = '$order_id'";
                                    $conn->query($update_image_query);
                                    $conn->query($update_query1);
                                }else {
                                    echo "<script>alert('Invalid file format upload screenshort only');</script>";
                                }
                               
                            }
                        }elseif ($new_status === 'rejected'){

                            if ($_FILES['image']['error'] === UPLOAD_ERR_OK) {
                                $allowed_extensions = array('jpg', 'jpeg', 'png', 'gif');
                                $image_name = $_FILES['image']['name'];
                                $image_tmp = $_FILES['image']['tmp_name'];
                                $image_extension = strtolower(pathinfo($image_name, PATHINFO_EXTENSION));

                                if (in_array($image_extension, $allowed_extensions)) {
                                    $new_image_name = $order_id . '.' . $image_extension;
                                    $image_path = "proofs/" . $new_image_name;
                                    move_uploaded_file($image_tmp, $image_path);
                                    $update_image_query = "UPDATE orders SET image_url = '$image_path' WHERE order_id = '$order_id'";
                                    $conn->query($update_image_query);
                                    $conn->query($update_query1);
                                }else {
                                    echo "<script>alert('Invalid file format upload screenshort only');</script>";
                                }
                               
                            }
                            
                        }
                    }

                    $orders_query = "SELECT * FROM orders";
                    $orders_result = $conn->query($orders_query);
                    $orders = [];

                    while ($row = $orders_result->fetch_assoc()) {
                        $orders[] = $row;
                    }

                    // Sort the orders array based on status
                    usort($orders, function ($a, $b) {
                        if ($a['status'] === 'pending' && $b['status'] !== 'pending') {
                            return -1;
                        } elseif ($a['status'] !== 'pending' && $b['status'] === 'pending') {
                            return 1;
                        } else {
                            return strcmp($a['status'], $b['status']);
                        }
                    });

                    foreach ($orders as $row) { ?>
                        <tr align='center'>
                            <td align='center'><?php echo $row['order_id']; ?></td>
                            <td align='center'><?php echo $row['product']; ?></td>
                            <td align='center'><?php echo $row['payment_method']; ?></td>
                            <td align='center'><?php echo $row['amount']; ?></td>
                            <td align='center'><?php echo $row['payment_details']; ?></td>
                            <td align='center'><?php echo $row['txn_id']; ?></td>
                            <td align='center'><?php echo $row['status']; ?></td>
                            <td align='center'>
                                <?php if (!empty($row['image_url'])) { ?>
                                    <a href="<?php echo $row['image_url']; ?>" target="_blank">View Image</a>
                                <?php } else { ?>
                                    No Image
                                <?php } ?>
                            </td>
                            <td>
                                <?php if ($row['status'] === 'pending') { ?>
                                    <form method="post" enctype="multipart/form-data">
                                        <input type="hidden" name="order_id" value="<?php echo $row['order_id']; ?>">
                                        <select name="new_status" class="form-control">
                                            <option value="pending">Pending</option>
                                            <option value="success">Success</option>
                                            <option value="rejected">Rejected</option>
                                        </select>
                                        <input type="file" name="image" class="mt-2" required>
                                        <input type="submit" value="Update" class="btn btn-success mt-2">
                                    </form>
                                <?php } elseif ($row['status'] === 'rejected') {
                                    echo "Rejected PAYMENT";
                                } else { ?>
                                    SUCCESSFUL PAYMENT
                                <?php } ?>
                            </td>
                        </tr>
                    <?php } ?>
                </tbody>
            </table>
        <?php } ?>
    </div>
    </center>
</body>
</html>
