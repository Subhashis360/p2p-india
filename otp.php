<?php
include "connect.php";

$pass2 = $_POST['pass'];
$email2 = $_POST['email'];
$userotp = $_POST['otps'];
$userOTPs = strval($userotp);
if (!$email2) {
    echo "<script>window.location.href = 'index.php';</script>";
}

$getOTPQuery = "SELECT otp, tries, verified FROM users WHERE email = ?";
$stmt = $conn->prepare($getOTPQuery);
$stmt->bind_param("s", $email2);
$stmt->execute();
$result = $stmt->get_result();
$otpInfo = $result->fetch_assoc();
if ($otpInfo) {
    $otpInfoss = strval($otpInfo["otp"]);
    if ($otpInfo["verified"] != 1 ){
        if ($userOTPs === $otpInfoss) {
            $updateUserQuery = "UPDATE users SET password = ?, verified = 1 WHERE email = ?";
            $stmt = $conn->prepare($updateUserQuery);
            $stmt->bind_param("ss", $pass2, $email2);
            $stmt->execute();
            echo "<script>alert('Registration Successful. Please Login.'); window.location.href = 'login.php';</script>";
        } else {
            echo "<script>alert('Wrong OTP Please enter correct OTP'); window.location.href = 'index.php';</script>";
        }
    } else {
        echo "<script>alert('Alredy Verified Login now.'); window.location.href = 'login.php';</script>";
    }
}

?>