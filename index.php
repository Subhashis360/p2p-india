<?php
// error_reporting(0);
include "connect.php";
?>

<?php
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

      if ($password === $userData["password"]) {
        if ($verified == "1") {
          echo "<script>window.location.href = 'dashbord.php';</script>";
        } else {
          setcookie("user_email", "", time() - 3600, "/");
          setcookie("user_password", "", time() - 3600, "/");
      } 
      } else {
        setcookie("user_email", "", time() - 3600, "/");
        setcookie("user_password", "", time() - 3600, "/");
      }
    }
} else {
  
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
        <form action='verifyotp.php' method='post' class='mt-4'>
            <center>
                <h1>Register</h1>
            </center>
            <div class='mb-3'>
                <label for='product' class='form-label'>Enter Your Email:</label>
                <input type='email' id='email' name='email' placeholder='example@gmail.com' class='form-control' required>
            </div>
            
            <div class='mb-3'>
                <label for='txn_id' class='form-label'>Enter Pasword:</label>
                <input type='text' id='pass' placeholder='Enter Your Pasword' name='pass' class='form-control' required>
            </div>
            <center>
                <div class='g-recaptcha' data-sitekey='6LephyQnAAAAAOnKGCpGUGsG3QgeUiaqs3pW99Hi'></div>
            </center>
            <br>
            <center>
                <button type='submit' name='sendotp' class='btn btn-primary'>Send OTP</button>
            </center>
        </form>
        <br>
        <center>
                <p>Alredy have account ?</p><a href='login.php'>Login Now</a>
        </center>
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




