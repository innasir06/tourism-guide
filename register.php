<?php
session_start();
include 'includes/config.php';
$page_title = "Register";

// Redirect to home if already logged in
if(isset($_SESSION['user_id'])){
    header("Location: index.php");
    exit;
}

// Handle form submission
if(isset($_POST['register'])){
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $full_name = mysqli_real_escape_string($conn, $_POST['full_name']);
    $phone = mysqli_real_escape_string($conn, $_POST['phone']);
    $address = mysqli_real_escape_string($conn, $_POST['address']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT); // hashed password
    $user_type = 'customer'; // default role
    $created_at = date('Y-m-d H:i:s');

    // Check if email already exists
    $check = mysqli_query($conn, "SELECT * FROM users WHERE email='$email'");
    if(mysqli_num_rows($check) > 0){
        $error = "Email already exists!";
    } else {
        // Insert new user
        $insert = "INSERT INTO users (username,email,password,full_name,phone,address,user_type,created_at)
                   VALUES ('$username','$email','$password','$full_name','$phone','$address','$user_type','$created_at')";
        if(mysqli_query($conn, $insert)){
            // Get the inserted user ID
            $user_id = mysqli_insert_id($conn);
            
            // Set session for logged-in user
            $_SESSION['user_id'] = $user_id;
            $_SESSION['username'] = $username;

            // Redirect to home with success message
            $_SESSION['register_success'] = "Registration successful! Welcome, $username.";
            header("Location: index.php");
            exit;
        } else {
            $error = "Registration failed. Please try again.";
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5">
    <h2>Register</h2>
    <?php if(isset($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Full Name</label>
            <input type="text" name="full_name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Phone</label>
            <input type="text" name="phone" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Address</label>
            <input type="text" name="address" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>
        <button type="submit" name="register" class="btn btn-primary">Register</button>
    </form>
</div>

<?php include 'includes/footer.php'; ?>
