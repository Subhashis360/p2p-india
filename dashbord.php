<?php
error_reporting(0);
include "connect.php";

if (isset($_COOKIE["user_email"]) && isset($_COOKIE["user_password"])) {
  $email = $_COOKIE["user_email"];
  $password = $_COOKIE["user_password"];

  $loginQuery = "SELECT email, password, verified FROM users WHERE email = ?";
  $stmt = $conn->prepare($loginQuery);
  $stmt->bind_param("s", $email);
  $stmt->execute();
  $result = $stmt->get_result();

  if ($result->num_rows === 1) {
      $userData = $result->fetch_assoc();
      $hashedPassword = $userData["password"];
      $verified = $userData["verified"];

      if ($password === $hashedPassword) {
          if ($verified == "1") {
              // User is verified and authenticated

          } else {
              // User is not verified; remove cookies
              setcookie("user_email", "", time() - 3600, "/");
              echo "<script>window.location.href = 'index.php';</script>";
              setcookie("user_password", "", time() - 3600, "/");
          }
      } else {
          // Invalid password; remove cookies
          setcookie("user_email", "", time() - 3600, "/");
          echo "<script>window.location.href = 'index.php';</script>";
          setcookie("user_password", "", time() - 3600, "/");
      }
  }else {
    setcookie("user_email", "", time() - 3600, "/");
    echo "<script>window.location.href = 'index.php';</script>";
    setcookie("user_password", "", time() - 3600, "/");
  }
} else {
  setcookie("user_email", "", time() - 3600, "/");
  echo "<script>window.location.href = 'index.php';</script>";
  setcookie("user_password", "", time() - 3600, "/");
}



function generateUniqueID() {
  $numbers = '0123456789';
  $uppercaseLetters = 'ABCDEFGHIJKLMNOPQRSTUVWXYZ';
  $lowercaseLetters = 'abcdefghijklmnopqrstuvwxyz';
  $id = '';
  for ($i = 0; $i < 2; $i++) {
    $id .= $uppercaseLetters[rand(0, strlen($uppercaseLetters) - 1)];
  }
  for ($i = 0; $i < 3; $i++) {
    $id .= $lowercaseLetters[rand(0, strlen($lowercaseLetters) - 1)];
  }
  for ($i = 0; $i < 5; $i++) {
    $id .= $numbers[rand(0, strlen($numbers) - 1)];
  }
  $id = str_shuffle($id);
  return $id;
}


if (isset($_POST["submit"])){
  $order_id = generateUniqueID();
  $product = filter_var($_POST['product'], FILTER_SANITIZE_STRING);
  $amount = filter_var($_POST['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
  $payment_method = filter_var($_POST['payment_method'], FILTER_SANITIZE_STRING);
  $txn_id = filter_var($_POST['txn_id'], FILTER_SANITIZE_STRING);
  $recaptcha = $_POST['g-recaptcha-response'];

  $url = "https://www.google.com/recaptcha/api/siteverify";
  $secretKey = "6LephyQnAAAAAAmSOKUjqEzySyzhdloR8DqZdNaG";
  $data = array(
      'secret' => $secretKey,
      'response' => $recaptcha
  );
  $headers = array(
      'Content-Type: application/x-www-form-urlencoded'
  );

  $ch = curl_init();
  curl_setopt($ch, CURLOPT_URL, $url);
  curl_setopt($ch, CURLOPT_POST, true);
  curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $response = curl_exec($ch);
  curl_close($ch);
  $jsonResponse = json_decode($response, true);
  $success = $jsonResponse["success"];
  if($success!=false){
    if ($payment_method === 'bank') {
        $bank_acc_no = $_POST['bank_acc_no'];
        $bank_ifsc = $_POST['bank_ifsc'];
        $payment_details = "Bank Account No: $bank_acc_no, IFSC: $bank_ifsc";
    } else {
        $upi_id = $_POST['upi_id'];
        $payment_details = "UPI ID: $upi_id";
    }
    
    $check_query = "SELECT * FROM orders WHERE txn_id = '$txn_id'";
    $check_result = $conn->query($check_query);
    if ($check_result->num_rows > 0) {
      echo "<script>alert('Transaction ID already exists Try Another');</script>";
    }

    $unique_order_id = false;
    do {
        $check_query = "SELECT * FROM orders WHERE order_id = '$order_id'";
        $check_result = $conn->query($check_query);
        if ($check_result->num_rows > 0) {
            $order_id = generateRandomId();
        } else {
            $unique_order_id = true;
        }
    } while (!$unique_order_id);

    $insert_query = "INSERT INTO orders (order_id, product, amount, payment_method, payment_details, txn_id, status, image_url) VALUES (?, ?, ?, ?, ?, ?, 'pending', '')";
    $stmt = $conn->prepare($insert_query);
    $stmt->bind_param("ssdsss", $order_id, $product, $amount, $payment_method, $payment_details, $txn_id);
    if ($stmt->execute()) {
        echo "<script>alert('Order placed successfully! It will arrive within 24 hours');</script>";
        echo "<meta http-equiv='refresh' content='0;url='>";
      } else {
        echo "<script>alert('Something Went Wrong');</script>";
      }
  } else {
    echo "<script>alert('Captha Expired Or Fill the Captha First');</script>";
    echo "<meta http-equiv='refresh' content='0;url='>";
  }

}


?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="shortcut icon" href="favicon.ico" type="image/x-icon">
    <script src="https://www.google.com/recaptcha/api.js" async defer></script>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js" integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4" crossorigin="anonymous"></script>
    <title>P2P-INDIA 100% SAFE & SECURE</title>
</head>
<body>
<div class="content" id="content">
  <div class="login-root">
    <div class="box-root flex-flex flex-direction--column" style="min-height: 100vh;flex-grow: 1;">
      <div class="loginbackground box-background--white padding-top--64">
        <div class="loginbackground-gridContainer">
          <div class="box-root flex-flex" style="grid-area: top / start / 8 / end;">
            <div class="box-root" style="background-image: linear-gradient(white 0%, rgb(247, 250, 252) 33%); flex-grow: 1;">
            </div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 4 / 2 / auto / 5;">
            <div class="box-root box-divider--light-all-2 animationLeftRight tans3s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 6 / start / auto / 2;">
            <div class="box-root box-background--blue800" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 7 / start / auto / 4;">
            <div class="box-root box-background--blue animationLeftRight" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 8 / 4 / auto / 6;">
            <div class="box-root box-background--gray100 animationLeftRight tans3s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 2 / 15 / auto / end;">
            <div class="box-root box-background--cyan200 animationRightLeft tans4s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 3 / 14 / auto / end;">
            <div class="box-root box-background--blue animationRightLeft" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 4 / 17 / auto / 20;">
            <div class="box-root box-background--gray100 animationRightLeft tans4s" style="flex-grow: 1;"></div>
          </div>
          <div class="box-root flex-flex" style="grid-area: 5 / 14 / auto / 17;">
            <div class="box-root box-divider--light-all-2 animationRightLeft tans3s" style="flex-grow: 1;"></div>
          </div>
        </div>
      </div>
      <div class="box-root padding-top--24 flex-flex flex-direction--column" style="flex-grow: 1; z-index: 9;">
        <div class="box-root padding-top--48 padding-bottom--24 flex-flex flex-justifyContent--center">
          <h1><a href="" rel="dofollow">P2P-INDIA</a></h1>
        </div>
        <div class="formbg-outer">
          <div class="formbg">
            <div class="formbg-inner padding-horizontal--48">
              <div class="mb-3">
                <label for="paymentDetails" class="form-label">Send Payment from [Trustwallet/metamask] :</label>
                <div class="input-group">
                    <input type="text" id="paymentDetails" class="form-control" value="0xA975c6EAbbe5B0D69111c403c8b4ecB3e582BdCB" readonly>
                    <button class="btn btn-outline-primary" id="copyButton">Copy</button>
                </div>
                <br>
                <center>Accepting <b>USDT[BEP20/BSC]</b> only now</center>
                <center>After Payment success Place Payout Order</center>
              </div>

            <form action="" method="post" class="mt-4">
            <div class="mb-3">
                <!-- <label for="product" class="form-label">Enter Your Email:</label> -->
                <input type="hidden" id="product" name="product" placeholder='example@gmail.com' value='<?php echo $_COOKIE["user_email"] ?>' class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label for="amount" class="form-label">Amount to sell (in USDT):</label>
                <input type="number" id="amount" minlength="2" name="amount" maxlength="5" placeholder='Min 10$' class="form-control" required>
            </div>
            
            <div class="mb-3">
                <label for="payment_method" class="form-label">Select Payment Recieve Method:</label>
                <select id="payment_method" name="payment_method" class="form-select" required>
                    <option value="bank">Bank Transfer</option>
                    <option value="upi">UPI Transfer</option>
                </select>
            </div>
            
            <div id="bank_details" style="display:none;">
                <div class="mb-3">
                    <label for="bank_acc_no" class="form-label">Your Bank Account No:</label>
                    <input type="number" id="bank_acc_no" minlength="12" maxlength="12" name="bank_acc_no" class="form-control">
                </div>
                
                <div class="mb-3">
                    <label for="bank_ifsc" class="form-label">Bank IFSC:</label>
                    <input type="text" id="bank_ifsc" minlength="11" maxlength="11" style="text-transform:uppercase" name="bank_ifsc" class="form-control">
                </div>
            </div>
            
            <div id="upi_details" style="display:none;">
                <div class="mb-3">
                    <label for="upi_id" class="form-label">UPI ID:</label>
                    <input type="text" id="upi_id" name="upi_id" pattern=".*@.*" class="form-control">
                </div>
            </div>
            
            <div class="mb-3">
                <label for="txn_id" class="form-label">Payment Transaction ID:</label>
                <input type="text" id="txn_id" name="txn_id" class="form-control" required>
            </div>
            <center><div class="g-recaptcha" data-sitekey="6LephyQnAAAAAOnKGCpGUGsG3QgeUiaqs3pW99Hi"></div></center>
            <br>
            <center>
            <button type="submit" name='submit' class="btn btn-primary">Place Order</button>
            </center>
        </form>
        <br>
              <center><b>NO KYC | NO TAX</b></center>
              <center><b>100% SAFE & SECURE 🔒</b></center>
            </div>
          </div>
            <br>
            <br>
                <div class="container text-center">
                    <h1 class="mt-4">Recent Orders</h1>
                    <table class="table table-bordered mt-4">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <!-- <th>Email</th> -->
                                <th>Status</th>
                                <th>Amount</th>
                                <th>Proof/Reason</th>
                            </tr>
                        </thead>
                        <tbody>
            <?php
            // include "connect.php";
            //             $success = "false";
            //             $last_orders_query = "SELECT * FROM orders ORDER BY order_id DESC LIMIT 10";
            //             $last_orders_result = $conn->query($last_orders_query);

            //             while ($row = $last_orders_result->fetch_assoc()) {
            //               if ($success === "false" || ($success === "true" && $row['status'] === 'success')) {
            //                 echo '<tr>';
            //                 echo '<td>' . $row['order_id'] . '</td>';
            //                 echo '<td>' . substr($row['product'], 0, 3) . str_repeat('*', 4) . substr($row['product'], strpos($row['product'], '@')) . '</td>';
            //                 echo '<td>' . $row['status'] . '</td>';
            //                 echo '<td>' . $row['amount'] . " USDT" . '</td>';
                            
            //                 if ($row['status'] === 'success') {
            //                     echo '<td><a href="' . $row['image_url'] . '" target="_blank">Click here</a></td>';
            //                 } elseif ($row['status'] === 'rejected') {
            //                     echo '<td><a href="' . $row['image_url'] . '" target="_blank">Click here</a></td>';
            //                 } else {
            //                     echo '<td>Arriving Soon</td>';
            //                 }
                            
            //                 if ($row['status'] === 'completed' && !empty($row['image_url'])) {
            //                     echo '<td><a href="view_proof.php?order_id=' . $row['order_id'] . '">View Proof</a></td>';
            //                 } else {
            //                 }
                            
            //                 echo '</tr>';
            //             }
            //             }
                        
            ?>
<?php
$success = "false";
$last_orders_query = "SELECT * FROM orders LIMIT 7";
$last_orders_result = $conn->query($last_orders_query);

$orders = array();  // Create an array to store the orders

while ($row = $last_orders_result->fetch_assoc()) {
    $orders[] = $row;  // Store each order in the array
}

// Loop through the array in reverse to display the orders
for ($i = count($orders) - 1; $i >= 0; $i--) {
    $row = $orders[$i];

    if ($success === "false" || ($success === "true" && $row['status'] === 'success')) {
        echo '<tr>';
        echo '<td>' . $row['order_id'] . '</td>';
        // echo '<td>' . substr($row['product'], 0, 3) . str_repeat('*', 4) . substr($row['product'], strpos($row['product'], '@')) . '</td>';
        echo '<td>' . $row['status'] . '</td>';
        echo '<td>' . $row['amount'] . " USDT" . '</td>';

        if ($row['status'] === 'success' || $row['status'] === 'rejected') {
            echo '<td><a href="' . $row['image_url'] . '" target="_blank">Click here</a></td>';
        } else {
            echo '<td>Arriving Soon</td>';
        }

        if ($row['status'] === 'completed' && !empty($row['image_url'])) {
            echo '<td><a href="view_proof.php?order_id=' . $row['order_id'] . '">View Proof</a></td>';
        } else {
        }

        echo '</tr>';
    }
}
?>
            </tbody>
                    </table>
                    <h3 class="mt-4">[Paymemt ussually send within 48 hours as per bank delay]</h3>
                </div>
                </div>
                
                <!-- price rates -->
                <center><h1>Highest Exchange Rates</h1></center>
                <br>
                <center>
                <div class="container">
                  <table class="table table-bordered">
                      <thead>
                          <tr>
                              <th class="text-center">Sell Range (USD)</th>
                              <th class="text-center">Exchange Rate (INR)</th>
                          </tr>
                      </thead>
                      <tbody>
                          <tr>
                              <td class="text-center">$10 - $50</td>
                              <td class="text-center">82 INR/USD</td>
                          </tr>
                          <tr>
                              <td class="text-center">$50 - $100</td>
                              <td class="text-center">85 INR/USD</td>
                          </tr>
                          <tr>
                              <td class="text-center">$100 - $500</td>
                              <td class="text-center">86 INR/USD</td>
                          </tr>
                          <tr>
                              <td class="text-center">$500 - $1000</td>
                              <td class="text-center">86.5 INR/USD</td>
                          </tr>
                          <tr>
                              <td class="text-center">$1000 - $10000</td>
                              <td class="text-center">87 INR/USD</td>
                          </tr>
                      </tbody>
                  </table>
              </div>
              </center>
              <!-- some banner  -->

              <div class="container c5 mt-5">
                <center><h1 class="headerss">Make instant payouts in real-time</h1></center>
                <div class="row">
                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="custom-card s334 text-center">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <img src="https://cashfreelogo.cashfree.com/website/landings/cashgram/instant-payouts.svg" alt="Card Image" class="img-fluid">
                            </div>
                            <div class="font-semibold mb-2 text-16">Quick Transfer</div>
                            <p class="text-cf-cold-purple text-2.5sm">Do instant payout to users, customers, and vendors using Quick Transfer.</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="custom-card s334 text-center">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <img src="https://cashfreelogo.cashfree.com/website/landings/easysplit/three-arrowNew.svg" alt="Card Image" class="img-fluid">
                            </div>
                            <div class="font-semibold mb-2 text-16">Bulk payouts</div>
                            <p class="text-cf-cold-purple text-2.5sm">Add beneficiaries and do up to 10,000 bulk payouts in one go</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="custom-card s334 text-center">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <img src="https://cashfreelogo.cashfree.com/website/NavFooter/make-payouts-new.svg" height="10" width="40" alt="Card Image" class="img-fluid">
                            </div>
                            <div class="font-semibold mb-2 text-16">Instant Payouts</div>
                            <p class="text-cf-cold-purple text-2.5sm">Integrate Payouts with your native Currency INR</p>
                        </div>
                    </div>

                    <div class="col-md-6 col-lg-3 mb-4">
                        <div class="custom-card s334 text-center">
                            <div class="d-flex justify-content-center align-items-center mb-3">
                                <img src="https://cashfreelogo.cashfree.com/website/landings/virtual-payment-address/4.svg" alt="Card Image" class="img-fluid">
                            </div>
                            <div class="font-semibold mb-2 text-16">Intuitive Dashboard</div>
                            <p class="text-cf-cold-purple text-2.5sm">Get a birds-eye view of your funds from the dashboard.</p>
                        </div>
                    </div>
                    
                </div>
            </div>

    <!-- footer start  -->

          <div class="footer-link padding-top--24">
            <div class="listing padding-top--24 padding-bottom--24 flex-flex center-center">
              <span><a href="#">©p2pindia</a></span>
              <span><a href="#">Contact</a></span>
              <span><a href="#">Privacy & terms</a></span>
            </div>
          </div>
        </div>
      </div>
    </div>
  </div>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.16.0/umd/popper.min.js"></script>
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
    <script>
        document.getElementById("copyButton").addEventListener("click", function() {
            var paymentDetails = document.getElementById("paymentDetails");
            paymentDetails.select();
            document.execCommand("copy");
            alert("Copied: [" + paymentDetails.value + "] Now send Payment to this adress ( Bep20 ONLY )");
        });

        $(document).ready(function() {
            const paymentMethodSelect = $('#payment_method');
            const bankDetails = $('#bank_details');
            const upiDetails = $('#upi_details');
            bankDetails.show();
            paymentMethodSelect.on('change', function() {
                if (paymentMethodSelect.val() === 'bank') {
                    bankDetails.show();
                    upiDetails.hide();
                } else if (paymentMethodSelect.val() === 'upi') {
                    bankDetails.hide();
                    upiDetails.show();
                }
            });
        });
    </script>
  </div>
</body>
</html>

<?php



?>
