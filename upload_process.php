<?php
session_start();

if (!isset($_SESSION['username'])) {
    header("Location: login.php");
    exit();
}

$targetDir = "uploads/";
if (!is_dir($targetDir)) {
    mkdir($targetDir);
}

$filename = $_FILES["profile_pic"]["name"];
$targetFile = $targetDir . basename($filename);
$imageFileType = strtolower(pathinfo($targetFile, PATHINFO_EXTENSION));

// Validate image
$check = getimagesize($_FILES["profile_pic"]["tmp_name"]);
if ($check === false) {
    die("File is not an image.");
}

// Move file
if (move_uploaded_file($_FILES["profile_pic"]["tmp_name"], $targetFile)) {
    $_SESSION['profile_pic'] = $targetFile;
    echo "Profile picture uploaded successfully.<br>";
    echo "<img src='$targetFile' width='200'><br><br>";
    echo "<a href='index.php'>Back to Home</a>";
} else {
    echo "Upload failed.";
}
?>
