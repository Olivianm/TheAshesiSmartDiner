<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Sanitize and validate input data
    $name = htmlspecialchars($_POST['payer_name']);
    $txn = htmlspecialchars($_POST['transaction_id']);
    $amount = htmlspecialchars($_POST['amount']);
    $network = htmlspecialchars($_POST['network']);
    $timestamp = date("Y-m-d H:i:s");

    // Validate that all required fields are filled
    if (empty($name) || empty($txn) || empty($amount) || empty($network)) {
        echo "All fields are required!";
        exit();
    }

    // Send email to admin notifying about the new payment
    $admin_email = "admin@yourdomain.com";  // Change to your admin email
    $subject = "New Mobile Money Payment Received";
    $message = "Payment Details:\n\n"
             . "Name: $name\n"
             . "Transaction ID: $txn\n"
             . "Amount: GHS $amount\n"
             . "Network: $network\n"
             . "Time: $timestamp";
    $headers = "From: noreply@yourdomain.com";  // Change to your domain

    // Sending the email to admin
    if (mail($admin_email, $subject, $message, $headers)) {
        // Log the payment information to a file (optional)
        file_put_contents("payments_log.txt", "$timestamp | $name | $txn | $amount | $network\n", FILE_APPEND);

        // Optional: Redirect to a thank you page
        header("Location: thank_you.html");
        exit();
    } else {
        // If mail fails
        echo "Error sending email to admin.";
    }
}
?>
