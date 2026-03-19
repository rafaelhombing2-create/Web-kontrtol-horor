<?php
// ============================================
// HOROR FORUM PREMIUM - CONFIG
// ============================================

// Load .env
$env = parse_ini_file(__DIR__ . '/.env');
foreach ($env as $key => $value) {
    putenv("$key=$value");
}

// Database
$host = getenv('DB_HOST');
$user = getenv('DB_USER');
$pass = getenv('DB_PASS');
$db = getenv('DB_NAME');

$conn = mysqli_connect($host, $user, $pass, $db);
if (!$conn) die("Database error");

// Session
session_start();

// Telegram Config
define('BOT_TOKEN', getenv('TELEGRAM_BOT_TOKEN'));
define('CHAT_ID', getenv('TELEGRAM_CHAT_ID'));

// ===== FUNGSI KIRIM KE TELEGRAM =====
function sendToTelegram($message) {
    $url = "https://api.telegram.org/bot" . BOT_TOKEN . "/sendMessage";
    $data = [
        'chat_id' => CHAT_ID,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_exec($ch);
    curl_close($ch);
}

// ===== FUNGSI LOG ERROR KE TELEGRAM =====
function logError($error, $file, $line) {
    $message = "❌ <b>ERROR DETEKSI</b>\n";
    $message .= "File: $file\n";
    $message .= "Line: $line\n";
    $message .= "Error: $error\n";
    $message .= "Time: " . date('Y-m-d H:i:s');
    
    sendToTelegram($message);
}

// ===== FUNGSI AUTO FIX =====
function autoFix($bug_id, $fix_code) {
    $file = __DIR__ . '/fix_' . $bug_id . '.php';
    file_put_contents($file, $fix_code);
    
    $message = "✅ <b>BUG TELAH DIFIX</b>\n";
    $message .= "Bug ID: $bug_id\n";
    $message .= "Fix file: fix_$bug_id.php\n";
    $message .= "Silakan jalankan file tersebut.";
    
    sendToTelegram($message);
}

// Error handler
set_error_handler(function($errno, $errstr, $errfile, $errline) {
    logError($errstr, $errfile, $errline);
});
?>