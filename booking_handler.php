<?php
header('Content-Type: application/json; charset=utf-8');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
    exit;
}

$name = trim($_POST['name'] ?? '');
$email = trim($_POST['email'] ?? '');
$phone = trim($_POST['phone'] ?? '');
$service = trim($_POST['service'] ?? '');
$message = trim($_POST['message'] ?? '');

if ($name === '' || $email === '' || $phone === '' || $service === '') {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Missing required fields']);
    exit;
}

if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid email address']);
    exit;
}

$allowed = ["barnameh", "light", "heavy"];
if (!in_array($service, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['status' => 'error', 'message' => 'Invalid service type']);
    exit;
}

$file = __DIR__ . '/../data/booking_requests.csv';
$timestamp = date('c');

try {
    $dir = dirname($file);
    if (!is_dir($dir)) { mkdir($dir, 0775, true); }
    $fp = fopen($file, 'a');
    if (!$fp) { throw new RuntimeException('Unable to open file'); }
    if (filesize($file) === 0) {
        fputcsv($fp, ['name', 'email', 'phone', 'service', 'message', 'timestamp'], ',', '"', '\\');
    }
    fputcsv($fp, [$name, $email, $phone, $service, $message, $timestamp], ',', '"', '\\');
    fclose($fp);
} catch (Throwable $e) {
    http_response_code(500);
    echo json_encode(['status' => 'error', 'message' => 'خطا در ثبت داده']);
    exit;
}

$to = 'info@barname.publicvm.com';
$mailSubject = 'درخواست رزرو جدید';
$mailBody = "نام: $name\nایمیل: $email\nتلفن: $phone\nخدمت: $service\nپیام:\n$message";
@mail($to, $mailSubject, $mailBody, 'From: ' . $email);

echo json_encode(['status' => 'success', 'message' => 'درخواست رزرو با موفقیت ثبت شد.']);
