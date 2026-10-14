<?php
require_once 'includes/config.php';
$page_title = "Terms & Conditions";
include 'includes/header.php';
?>

<div class="container my-5">
    <div class="row">
        <div class="col-lg-9 mx-auto">
            <div class="card shadow">
                <div class="card-header bg-primary text-white">
                    <h2 class="mb-0"><i class="fas fa-file-contract"></i> Terms & Conditions</h2>
                    <small>Last updated: <?php echo date('F d, Y'); ?></small>
                </div>
                <div class="card-body p-4">
                    
                    <div class="alert alert-warning">
                        <i class="fas fa-exclamation-triangle"></i> By using Discover Pakistan, you agree to these terms and conditions. Please read carefully.
                    </div>

                    <!-- 1. Acceptance -->
                    <h4 class="text-primary mt-4">1. Acceptance of Terms</h4>
                    <p>By accessing our website and services, you accept these terms. If you disagree with any part, please do not use our services.</p>

                    <!-- 2. User Accounts -->
                    <h4 class="text-primary mt-4">2. User Accounts</h4>
                    <p><strong>Registration:</strong></p>
                    <ul>
                        <li>You must be 18+ years old to create an account</li>
                        <li>Provide accurate and complete information</li>
                        <li>Keep your password secure and confidential</li>
                        <li>Notify us immediately of unauthorized access</li>
                    </ul>
                    <p><strong>Account Termination:</strong> We reserve the right to suspend or terminate accounts that violate these terms.</p>

                    <!-- 3. Booking & Reservations -->
                    <h4 class="text-primary mt-4">3. Bookings and Reservations</h4>
                    <p><strong>Booking Process:</strong></p>
                    <ul>
                        <li>All bookings subject to availability</li>
                        <li>Prices in Pakistani Rupees (PKR)</li>
                        <li>Confirmation sent via email after payment</li>
                        <li>Full payment required to confirm booking</li>
                    </ul>
                    <p><strong>Pricing:</strong></p>
                    <ul>
                        <li>Prices include applicable taxes</li>
                        <li>Prices may change without notice</li>
                        <li>Special offers have specific terms</li>
                    </ul>

                    <!-- 4. Cancellation Policy -->
                    <h4 class="text-primary mt-4">4. Cancellation and Refund Policy</h4>
                    <div class="table-responsive">
                        <table class="table table-bordered table-sm">
                            <thead class="table-light">
                                <tr>
                                    <th>Cancellation Time</th>
                                    <th>Refund</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr><td>30+ days before</td><td>90% refund</td></tr>
                                <tr><td>15-29 days before</td><td>50% refund</td></tr>
                                <tr><td>7-14 days before</td><td>25% refund</td></tr>
                                <tr><td>Less than 7 days</td><td>No refund</td></tr>
                            </tbody>
                        </table>
                    </div>
                    <p><strong>Cancellation by Us:</strong> Full refund if we cancel due to weather, safety, or insufficient bookings.</p>

                    <!-- 5. Travel Requirements -->
                    <h4 class="text-primary mt-4">5. Travel Requirements</h4>
                    <p>You are responsible for:</p>
                    <ul>
                        <li>Valid passport (6+ months validity)</li>
                        <li>Visa requirements (if applicable)</li>
                        <li>Travel insurance (recommended)</li>
                        <li>Health certificates or vaccinations</li>
                        <li>Necessary permits for specific areas</li>
                    </ul>

                    <!-- 6. Liability -->
                    <h4 class="text-primary mt-4">6. Liability and Insurance</h4>
                    <p><strong>Our Liability:</strong></p>
                    <ul>
                        <li>We act as intermediary between you and service providers</li>
                        <li>Not liable for third-party service provider actions</li>
                        <li>Not responsible for loss, damage, or injury during travel</li>
                        <li>Liability limited to booking amount paid</li>
                    </ul>
                    <div class="alert alert-info">
                        <i class="fas fa-shield-alt"></i> <strong>Travel Insurance Recommended:</strong> We strongly advise purchasing comprehensive travel insurance.
                    </div>

                    <!-- 7. User Conduct -->
                    <h4 class="text-primary mt-4">7. User Conduct</h4>
                    <p>You agree NOT to:</p>
                    <ul>
                        <li>Use services for illegal purposes</li>
                        <li>Harass or abuse others</li>
                        <li>Post false information</li>
                        <li>Violate intellectual property rights</li>
                    </ul>

                    <!-- 8. Intellectual Property -->
                    <h4 class="text-primary mt-4">8. Intellectual Property</h4>
                    <p>All website content (text, images, logos) is protected by copyright. You may not reproduce without permission.</p>

                    <!-- 9. Force Majeure -->
                    <h4 class="text-primary mt-4">9. Force Majeure</h4>
                    <p>We are not liable for failure to perform due to natural disasters, war, pandemics, government actions, or technical failures beyond our control.</p>

                    <!-- 10. Dispute Resolution -->
                    <h4 class="text-primary mt-4">10. Dispute Resolution</h4>
                    <p><strong>Governing Law:</strong> These terms are governed by Pakistani law.</p>
                    <p><strong>Complaints:</strong> Contact <a href="mailto:support@tourpakistan.com">support@tourpakistan.com</a> with your booking reference. We respond within 48 hours.</p>

                    <!-- 11. Changes -->
                    <h4 class="text-primary mt-4">11. Changes to Terms</h4>
                    <p>We may modify these terms anytime. Continued use constitutes acceptance of changes.</p>

                    <!-- Contact -->
                    <div class="alert alert-light border mt-4">
                        <h5 class="text-primary"><i class="fas fa-envelope"></i> Contact Us</h5>
                        <p class="mb-1"><strong>Email:</strong> legal@tourpakistan.com</p>
                        <p class="mb-1"><strong>Phone:</strong> +92-300-1234567</p>
                        <p class="mb-0"><strong>Address:</strong> Islamabad, Pakistan</p>
                    </div>

                    <div class="text-center mt-4">
                        <a href="index.php" class="btn btn-primary me-2">
                            <i class="fas fa-home"></i> Back to Home
                        </a>
                        <a href="privacy-policy.php" class="btn btn-outline-primary">
                            <i class="fas fa-shield-alt"></i> Privacy Policy
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php include 'includes/footer.php'; ?>