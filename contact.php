<?php
$page_title = "Contact Us";
include 'includes/config.php';
include 'includes/header.php';

// Handle form submission
if(isset($_POST['submit'])){
    $name = mysqli_real_escape_string($conn, $_POST['name']);
    $email = mysqli_real_escape_string($conn, $_POST['email']);
    $subject = mysqli_real_escape_string($conn, $_POST['subject']);
    $message = mysqli_real_escape_string($conn, $_POST['message']);
    $status = 'pending';
    $created_at = date('Y-m-d H:i:s');

    $insert = "INSERT INTO contact_messages (name, email, subject, message, status, created_at) 
               VALUES ('$name', '$email', '$subject', '$message', '$status', '$created_at')";

    if(mysqli_query($conn, $insert)){
        $success = "Message sent successfully!";
    } else {
        $error = "Failed to send message. Please try again.";
    }
}
?>

<div class="container mt-5">
    <h2>Contact Us</h2>
    <?php if(isset($success)) echo '<div class="alert alert-success">'.$success.'</div>'; ?>
    <?php if(isset($error)) echo '<div class="alert alert-danger">'.$error.'</div>'; ?>

    <form method="post" action="">
        <div class="mb-3">
            <label>Name</label>
            <input type="text" name="name" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Email</label>
            <input type="email" name="email" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Subject</label>
            <input type="text" name="subject" class="form-control" required>
        </div>
        <div class="mb-3">
            <label>Message</label>
            <textarea name="message" class="form-control" rows="5" required></textarea>
        </div>
        <button type="submit" name="submit" class="btn btn-primary">Send Message</button>
    </form>
</div>

<?php
include 'includes/footer.php';
?>