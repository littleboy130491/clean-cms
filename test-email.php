<?php

// Simple test script to verify email notification works
require_once 'vendor/autoload.php';

use App\Models\Submission;
use App\Mail\FormSubmissionNotification;
use Illuminate\Support\Facades\Mail;

// Create a test submission
$testSubmission = new Submission([
    'fields' => [
        'name' => 'Test User',
        'email' => 'test@example.com',
        'phone' => '+62812345678',
        'subject' => 'Test Form Submission',
        'message' => 'This is a test message to verify the email notification system is working correctly.',
        'submitted_at' => now()->toISOString(),
        'ip_address' => '127.0.0.1',
        'user_agent' => 'Test Browser',
    ]
]);

// Set a test ID
$testSubmission->id = 999;

// Send test email
$adminEmail = env('MAIL_ADMIN_EMAIL', 'admin@example.com');
echo "Sending test email to: " . $adminEmail . "\n";

try {
    Mail::to($adminEmail)->send(new FormSubmissionNotification($testSubmission));
    echo "✅ Test email sent successfully!\n";
    echo "Check your email inbox for the notification.\n";
} catch (Exception $e) {
    echo "❌ Error sending email: " . $e->getMessage() . "\n";
}