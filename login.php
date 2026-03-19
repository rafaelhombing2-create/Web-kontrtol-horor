<?php
// login.php
require_once 'config.php';

$input = json_decode(file_get_contents('php://input'), true);
$credential = $input['credential'];

// Decode JWT dari Google
$payload = json_decode(base64_decode(explode('.', $credential)[1]), true);

$google_id = $payload['sub'];
$name = $payload['name'];
$email = $payload['email'];
$avatar = $payload['picture'];

// Cek user di database
$result = mysqli_query($conn, "SELECT * FROM users WHERE google_id = '$google_id' OR email = '$email'");

if (mysqli_num_rows($result) == 0) {
    // User baru
    $role = 'user'; // Default
    mysqli_query($conn, "INSERT INTO users (google_id, name, email, avatar, role) 
                         VALUES ('$google_id', '$name', '$email', '$avatar', '$role')");
    $user_id = mysqli_insert_id($conn);
} else {
    $user = mysqli_fetch_assoc($result);
    $user_id = $user['id'];
}

$_SESSION['user_id'] = $user_id;

// Kirim notifikasi ke Telegram (user baru)
if (mysqli_num_rows($result) == 0) {
    sendToTelegram("🔔 <b>User Baru</b>\nNama: $name\nEmail: $email");
}

echo json_encode([
    'success' => true,
    'user' => [
        'id' => $user_id,
        'name' => $name,
        'avatar' => $avatar,
        'role' => $role ?? 'user'
    ]
]);
?>