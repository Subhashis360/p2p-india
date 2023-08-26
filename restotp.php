<?php
include "connect.php";
// error_reporting(0);
?>
<?php

function sendOTPEmail($to, $otp) {
    $from = "order@p2pindia.in";
    $headers = "MIME-Version: 1.0" . "\r\n";
    $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
    $headers .= "From:" . $from;
    $subject = "Hurray!! Your OTP For Password Reset in P2pIndia is : $otp";
  
    $message = '<!DOCTYPE html>
    <html lang="en">
    
    <head>
      <meta charset="UTF-8">
      <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <title>' . $subject . '</title>
      <!-- Include Bootstrap CSS -->
      <link href="https://maxcdn.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
      <style>
        /* Custom styles */
        body {
          font-family: Arial, sans-serif;
          background-color: #f5f5f5;
          margin: 0;
          padding: 0;
        }
    
        .container {
          background-color: #ffffff;
          border-radius: 10px;
          box-shadow: 0 2px 6px rgba(0, 0, 0, 0.1);
          padding: 30px;
          margin: 20px auto;
          max-width: 600px;
        }
    
        .success-message {
          text-align: center;
          color: #28a745;
          font-size: 24px;
          margin-bottom: 20px;
        }
    
        .cards-container {
          display: flex;
          flex-direction: column;
          align-items: center;
        }
    
        .card {
          background-color: #f8f9fa;
          border: none;
          border-radius: 10px;
          padding: 15px;
          margin: 10px;
          text-align: center;
          box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
          width: 100%;
        }
    
        .card img {
          max-width: 100%;
          height: auto;
        }
      </style>
    </head>
    
    <body>
    <center>
      <div class="container">
        <div class="cards-container">
          <div class="card"> 
            <h1 class="success-message">Your OTP For Password Reset is : ' . $otp . '</h1>
            <p>Enter Your OTP in Verify OTP to Complete The Process</p>
            <p>Your OTP is: <span style="font-weight: bold;">' . $otp . '</span></p>
            <p>Have a Good day</p>
          </div>
        </div>
      </div>
    </center>
    </body>
    </html>';
    mail($to, $subject, $message, $headers);
  }

function generateRandomOTP() {
    $numbers = '0123456789';
    $id = '';
    for ($i = 0; $i < 6; $i++) {
        $id .= $numbers[rand(0, strlen($numbers) - 1)];
    }
    $id = str_shuffle($id);
    return $id;
}

$pass = $_POST['pass'];
$email = $_POST['email'];

if (!$email) {
    echo "<script>window.location.href = 'index.php';</script>";
}

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

        $checkUserQuery = "SELECT email, verified, tries FROM users WHERE email = ?";
        $stmt = $conn->prepare($checkUserQuery);
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $userData = $result->fetch_assoc();
            $verified = $userData["verified"];
            $tries = $userData["tries"];
            if ($verified == "1") {
                if ($tries < "5") {
                    $otp = generateRandomOTP();
                    $updateUserQuery = "UPDATE users SET otp = ?, tries = tries + 1 WHERE email = ?";
                    $stmt = $conn->prepare($updateUserQuery);
                    $stmt->bind_param("ss", $otp, $email);
                    $stmt->execute();
                    sendOTPEmail($email, $otp);
                } else {
                echo "<script>alert('OTP Limit Over Try 24 Hours Later'); window.location.href = 'index.php';</script>";
                }
        } else {
            echo "<script>alert('Email is Not Registred Please try Register Again'); window.location.href = 'index.php';</script>";
            }
    } else {
        echo "<script>alert('Fill captha again cause its Expired'); window.location.href = 'index.php';</script>";
    }
    }




if(isset($_POST['verify'])){
    $pass = $_POST['pass'];
    $email = $_POST['email'];
    $otp = $_POST['otps'];

    $getOTPQuery = "SELECT otp, tries, verified, password FROM users WHERE email = ?";
    $stmt = $conn->prepare($getOTPQuery);
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();
    $otpInfo = $result->fetch_assoc();

    if ($otpInfo["verified"] == "1") {
        $userOTP = strval($otp);
        if ($userOTP === $otpInfo["otp"]){
            $updateUserQuery = "UPDATE users SET password = ?, verified = 1 WHERE email = ?";
            $stmt = $conn->prepare($updateUserQuery);
            $stmt->bind_param("ss", $pass2, $email2);
            $stmt->execute();
            echo "<script>alert('Pssword Reset Sucess Your New Password is : $pass pelase Login'); window.location.href = 'login.php';</script>";
        } else {
        echo "<script>alert('Wrong OTP. Please enter correct OTP or resend it by refreshing the page.');</script>";
        }
    } else { 
        echo "<script>alert('OTP Not Verified Please Register First'); window.location.href = 'index.php';</script>";
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

<?php
    echo "<div class='formbg-outer'>
            <div class='formbg'>
                <div class='formbg-inner padding-horizontal--48'>
                <div class='mb-3'>
                <form action='' method='post' class='mt-4'>
                    <div class='mb-3'>
                        <center>OTP Send To <b>$email</b> Email check in spam folder</center>
                        <input type='hidden' id='email' name='email' value='$email' class='form-control' required>
                        <input type='hidden' id='pass' name='pass' value='$pass' class='form-control' required>
                    </div>
                    <div class='mb-3'>
                        <label for='txn_id' class='form-label'>Enter OTP:</label>
                        <input type='text' id='otps' placeholder='Enter Your 6 diit OTP' name='otps' class='form-control' required>
                    </div>
                    <br>
                    <center>
                        <button type='submit' name='verify' class='btn btn-primary'>Verify OTP</button>
                    </center>
                </form>
                <br>
            </div>
            </div>";

?>
          <div class="footer-link padding-top--24">
            <div class="listing padding-top--24 padding-bottom--24 flex-flex center-center">
              <span><a href="#">©p2pindia</a></span>
              <span><a href="https://telegram.dog/p2pindia1">Contact</a></span>
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
  </div>
</body>
</html>
