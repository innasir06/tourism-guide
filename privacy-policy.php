<?php
require_once 'includes/config.php';
$page_title = "Privacy Policy";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-9 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-shield-alt"></i> Privacy Policy</h2>
                    <small>Last updated: <?php echo date('F d, Y'); ?></small>
                </div>
                <div class="card-body p-4">
                    
                    <div class="alert alert-info">
                        <i class="fas fa-info-circle"></i> Your privacy is important to us. This policy explains how we collect, use, and protect your personal information.
                    </div>

                    <!-- 1. Information Collection -->
                    <h4 class="text-primary mt-4">1. Information We Collect</h4>
                    <p><strong>Personal Information:</strong></p>
                    <ul>
                        <li>Name, email, phone number, and address</li>
                        <li>Payment information (processed securely)</li>
                        <li>Travel preferences and booking history</li>
                    </ul>
                    <p><strong>Automatic Information:</strong></p>
                    <ul>
                        <li>IP address, browser type, and device information</li>
                        <li>Cookies and usage data</li>
                    </ul>

                    <!-- 2. How We Use Information -->
                    <h4 class="text-primary mt-4">2. How We Use Your Information</h4>
                    <ul>
                        <li>Process bookings and reservations</li>
                        <li>Send booking confirmations and updates</li>
                        <li>Improve our services and user experience</li>
                        <li>Send promotional offers (you can opt-out anytime)</li>
                        <li>Prevent fraud and ensure security</li>
                    </ul>

                    <!-- 3. Information Sharing -->
                    <h4 class="text-primary mt-4">3. Information Sharing</h4>
                    <p>We do not sell your personal information. We may share information with:</p>
                    <ul>
                        <li><strong>Service Providers:</strong> Hotels, tour operators, payment processors</li>
                        <li><strong>Legal Requirements:</strong> When required by law</li>
                        <li><strong>Business Partners:</strong> With your explicit consent</li>
                    </ul>

                    <!-- 4. Data Security -->
                    <h4 class="text-primary mt-4">4. Data Security</h4>
                    <p>We implement security measures including:</p>
                    <ul>
                        <li>SSL encryption for data transmission</li>
                        <li>Secure password hashing</li>
                        <li>Regular security audits</li>
                        <li>Restricted access to personal data</li>
                    </ul>

                    <!-- 5. Cookies -->
                    <h4 class="text-primary mt-4">5. Cookies and Tracking</h4>
                    <p>We use cookies to:</p>
                    <ul>
                        <li>Remember your login session</li>
                        <li>Store preferences (language, currency)</li>
                        <li>Analyze website usage</li>
                    </ul>
                    <p>You can control cookies through your browser settings.</p>

                    <!-- 6. Your Rights -->
                    <h4 class="text-primary mt-4">6. Your Rights</h4>
                    <p>You have the right to:</p>
                    <ul>
                        <li><strong>Access:</strong> Request a copy of your data</li>
                        <li><strong>Correction:</strong> Update inaccurate information</li>
                        <li><strong>Deletion:</strong> Request account deletion</li>
                        <li><strong>Opt-out:</strong> Unsubscribe from marketing emails</li>
                    </ul>
                    <p>Contact us at <a href="mailto:privacy@tourpakistan.com">privacy@tourpakistan.com</a> to exercise these rights.</p>

                    <!-- 7. Children's Privacy -->
                    <h4 class="text-primary mt-4">7. Children's Privacy</h4>
                    <p>Our services are not intended for children under 13. We do not knowingly collect information from children.</p>

                    <!-- 8. Changes to Policy -->
                    <h4 class="text-primary mt-4">8. Policy Updates</h4>
                    <p>We may update this policy periodically. Continued use of our services after changes constitutes acceptance of the updated policy.</p>

                    <!-- Contact -->
                    <div class="alert alert-light border mt-4">
                        <h5 class="text-primary"><i class="fas fa-envelope"></i> Contact Us</h5>
                        <p class="mb-1"><strong>Email:</strong> privacy@tourpakistan.com</p>
                        <p class="mb-1"><strong>Phone:</strong> +92-300-1234567</p>
                        <p class="mb-0"><strong>Address:</strong> Islamabad, Pakistan</p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary me-2">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                        <a href="terms-and-conditions.php" class="btn btn-outline-primary">
                            <i class="fas fa-file-contract"></i> Terms & Conditions
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>