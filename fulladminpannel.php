<?php
session_start();
error_reporting(0);
include "connect.php";

$correctUsername = "admin";
$correctPassword = "admin";

if (isset($_POST['login'])) {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $password = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);

    if ($username === $correctUsername && $password === $correctPassword) {
        $_SESSION['admin_logged_in'] = true;
    }
}

if (isset($_GET['logout'])) {
    session_destroy();
    header("Location: index.php");
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <title>Admin Panel</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <style>
        .container {
            max-width: 800px;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
            padding: 20px;
            border-radius: 5px;
            margin-top: 50px;
        }
    </style>
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
  <style>
    .switch-container {
      display: flex;
      justify-content: center;
      align-items: flex-start;
      margin-top: 20px;
    }

    .switch {
      position: relative;
      display: inline-block;
      width: 60px;
      height: 34px;
    }

    .switch input {
      opacity: 0;
      width: 0;
      height: 0;
    }

    .slider {
      position: absolute;
      cursor: pointer;
      top: 0;
      left: 0;
      right: 0;
      bottom: 0;
      background-color: #ccc;
      transition: .4s;
    }

    .slider:before {
      position: absolute;
      content: "";
      height: 26px;
      width: 26px;
      left: 4px;
      bottom: 4px;
      background-color: white;
      transition: .4s;
    }

    input:checked + .slider {
      background-color: #2196F3;
    }

    input:focus + .slider {
      box-shadow: 0 0 1px #2196F3;
    }

    input:checked + .slider:before {
      transform: translateX(26px);
    }

    /* Rounded sliders */
    .slider.round {
      border-radius: 34px;
    }

    .slider.round:before {
      border-radius: 50%;
    }

    /* Additional styles for content display */
    .content {
      text-align: center;
      padding: 20px;
      font-size: 15px;
    }

    .hidden {
      display: none;
    }
  </style>
  <title>Switch Example</title>
</head>
<body>
  <div class="switch-container">
    <label class="switch">
      <input type="checkbox" id="switch">
      <span class="slider round"></span>
    </label>
  </div>
  <div class="content" id="content1">
  <center>
        <div class="container">
            <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) { ?>
                <h1 class="mb-4">Admin Dashboard</h1>
                <a href="?logout" class="btn btn-primary mb-3">Logout</a>

                <input type="text" id="searchInput1" class="form-control mb-3" placeholder="Search by Order ID">

                <form method="post">
                    <div>
                        <button type="submit" name="delete_selected" class="btn btn-danger">Delete Selected</button>
                    </div>
                    <br>
                    <table class="table table-bordered">
                    <thead>
                    <tr>
                        <th scope="col">Select</th>
                        <th scope="col">Order ID</th>
                        <th scope="col">Email</th>
                        <th scope="col">Payment Method</th>
                        <th scope="col">Amount</th>
                        <th scope="col">Status</th>
                    </tr>
                </thead>
                <tbody id="tableBody">
                <?php
                    $orders_query = "SELECT * FROM orders";
                    $orders_result = $conn->query($orders_query);

                    while ($row = $orders_result->fetch_assoc()) { ?>
                        <tr>
                            <td><input type="checkbox" name="selected_orders[]" value="<?php echo $row['order_id']; ?>"></td>
                            <td><?php echo $row['order_id']; ?></td>
                            <td><?php echo $row['product']; ?></td>
                            <td><?php echo $row['payment_method']; ?></td>
                            <td><?php echo $row['amount']; ?></td>
                            <td><?php echo $row['status']; ?></td>
                        </tr>
                    <?php } ?>
                </tbody>
                    </table>
                </form>

                <?php
                    if (isset($_POST['delete_selected']) && !empty($_POST['selected_orders'])) {
                            $selectedOrders = $_POST['selected_orders'];
                
                            foreach ($selectedOrders as $order_id) {
                                $delete_query = "DELETE FROM orders WHERE order_id = ?";
                                $stmt = $conn->prepare($delete_query);
                                $stmt->bind_param("s", $order_id);
                                
                                if ($stmt->execute()) {
                                    echo '<div class="alert alert-success mt-3">Selected orders deleted successfully.</div>';
                                } else {
                                    echo '<div class="alert alert-danger mt-3">Error deleting orders: ' . $conn->error . '</div>';
                                }
                            }
                        }
                    ?>

                <script>
                    $(document).ready(function () {
                        $("#searchInput1").on("input", function () {
                            var value = $(this).val().toLowerCase();

                            if (value.trim() === "") {
                                $("#tableBody tr").show();
                                return;
                            }

                            $("#tableBody tr").hide().filter(function () {
                                var orderID = $(this).find("td:eq(1)").text().toLowerCase();
                                var email = $(this).find("td:eq(2)").text().toLowerCase();
                                return (orderID.indexOf(value) > -1 || email.indexOf(value) > -1);
                            }).show();
                        });
                    });
                </script>
            <?php } else { ?>
                <h1 class="mb-4">Admin Login</h1>
                <form method="post">
                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" class="form-control" id="username" name="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" class="form-control" id="password" name="password" required>
                    </div>
                    <button type="submit" name="login" class="btn btn-primary">Login</button>
                </form>
            <?php } ?>
        </div>
    </center>
  </div>
  <div class="content hidden" id="content2">
    <center>
      <div class="container">
                <?php if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in']) { ?>
                    <h1 class="mb-4">Admin Ban Dashboard</h1>
                    <a href="?logout" class="btn btn-primary mb-3">Logout</a>

                    <input type="text" id="searchInput2" class="form-control mb-3" placeholder="Search by Order ID">
                    <form method="post">
                        <div>
                            <button type="submit" name="ban_selected" class="btn btn-warning">Ban Selected</button>
                        </div>
                        <br>
                        <table class="table table-bordered">
                            <thead>
                                <tr>
                                    <th scope="col">Select</th>
                                    <th scope="col">Email</th>
                                </tr>
                            </thead>
                            <tbody id="tableBody">
                            <?php
                                $orders_query = "
                                SELECT u.* 
                                FROM users u
                                LEFT JOIN banned_users b ON u.email = b.email
                                WHERE b.id IS NULL
                            ";
                                $orders_result = $conn->query($orders_query);

                                while ($row = $orders_result->fetch_assoc()) { ?>
                                    <tr>
                                        <td><input type="checkbox" name="selected_orders[]" value="<?php echo $row['email']; ?>"></td>
                                        <td><?php echo $row['email']; ?></td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                                </table>
                            </form>
                <?php } else { ?>
                    <h1 class="mb-4">Admin Login</h1>
                    <form method="post">
                        <div class="form-group">
                            <label for="username">Username:</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password:</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" name="login" class="btn btn-primary">Login</button>
                    </form>
                <?php } ?>
            </div>
    </center>
  </div>
  <script>
    const switchElement = document.getElementById("switch");
    const content1 = document.getElementById("content1");
    const content2 = document.getElementById("content2");

    switchElement.addEventListener("change", function () {
      if (this.checked) {
        content1.classList.add("hidden");
        content2.classList.remove("hidden");
      } else {
        content1.classList.remove("hidden");
        content2.classList.add("hidden");
      }
    });
  </script>
  <script>
      $(document).ready(function () {
          $("#searchInput2").on("input", function () {
              var value = $(this).val().toLowerCase();
              if (value.trim() === "") {
                  $("#tableBody tr").show();
                  return;
              }
              $("#tableBody tr").hide().filter(function () {
                  var orderID = $(this).find("td:eq(1)").text().toLowerCase();
                  var email = $(this).find("td:eq(2)").text().toLowerCase();
                  return (orderID.indexOf(value) > -1 || email.indexOf(value) > -1);
              }).show();
          });
      });
  </script>
</body>
</html>
