<?php
require_once 'includes/config.php';
require_once 'includes/session_manager.php';

$page_title = "Login";

/* =========================
   REDIRECT IF ALREADY LOGGED IN
========================= */
if (is_logged_in()) {
    if (is_admin()) {
        header("Location: admin-dashboard.php");
    } else {
        header("Location: dashboard.php");
    }
    exit;
}

/* =========================
   HANDLE LOGIN SUBMIT
========================= */
if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($email === '' || $password === '') {
        set_flash_message('error', 'Email and password are required.');
    } else {

        // Fetch user from DB
        $stmt = mysqli_prepare(
            $conn,
            "SELECT * FROM users WHERE email = ? LIMIT 1"
        );
        mysqli_stmt_bind_param($stmt, "s", $email);
        mysqli_stmt_execute($stmt);
        $result = mysqli_stmt_get_result($stmt);

        if ($result && mysqli_num_rows($result) === 1) {

            $user = mysqli_fetch_assoc($result);

            if (password_verify($password, $user['password'])) {

                // Set session safely
                set_login_session($user);

                // ✅ ADMIN REDIRECT
                if ($user['email'] === 'admin@tourpakistan.com') {
                    header("Location: admin-dashboard.php");
                } else {
                    header("Location: dashboard.php");
                }
                exit;

            } else {
                set_flash_message('error', 'Invalid email or password.');
            }

        } else {
            set_flash_message('error', 'Invalid email or password.');
        }
    }
}

include 'includes/header.php';
?>

<div class="container mt-5 mb-5">
    <?php echo display_flash_messages(); ?>

    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card shadow-sm">
                <div class="card-header bg-primary text-white">
                    <h4 class="mb-0">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </h4>
                </div>
                <div class="card-body">
                    <form method="POST" action="">
                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-envelope"></i> Email Address
                            </label>
                            <input type="email"
                                   name="email"
                                   class="form-control"
                                   required>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">
                                <i class="fas fa-lock"></i> Password
                            </label>
                            <div class="input-group">
                                <input type="password"
                                       name="password"
                                       id="password"
                                       class="form-control"
                                       required>
                                <button class="btn btn-outline-secondary" type="button" id="togglePassword">
                                    <i class="fas fa-eye" id="eyeIcon"></i>
                                </button>
                            </div>
                        </div>

                        <div class="mb-3 form-check">
                            <input type="checkbox" 
                                   class="form-check-input" 
                                   id="rememberMe" 
                                   name="remember_me">
                            <label class="form-check-label" for="rememberMe">
                                <i class="fas fa-cookie-bite"></i> Remember me for 30 days
                            </label>
                        </div>

                        <button type="submit" class="btn btn-success w-100">
                            <i class="fas fa-sign-in-alt"></i> Login
                        </button>
                    </form>
                    
                    <div class="text-center mt-3">
                        <a href="forgot-password.php">
                            <i class="fas fa-question-circle"></i> Forgot Password?
                        </a>
                    </div>

                    <hr class="my-4">

                    <div class="text-center">
                        <p class="text-muted mb-2">Don't have an account?</p>
                        <a href="register.php" class="btn btn-outline-primary w-100">
                            <i class="fas fa-user-plus"></i> Create Account
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Toggle password visibility
document.getElementById('togglePassword').addEventListener('click', function() {
    const passwordInput = document.getElementById('password');
    const eyeIcon = document.getElementById('eyeIcon');
    
    if (passwordInput.type === 'password') {
        passwordInput.type = 'text';
        eyeIcon.classList.remove('fa-eye');
        eyeIcon.classList.add('fa-eye-slash');
    } else {
        passwordInput.type = 'password';
        eyeIcon.classList.remove('fa-eye-slash');
        eyeIcon.classList.add('fa-eye');
    }
});
</script>

<?php include 'includes/footer.php'; ?>