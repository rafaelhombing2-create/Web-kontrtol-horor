<?php
// post.php
require_once 'config.php';

// ===== AMBIL SATU POST =====
if (isset($_GET['id'])) {
    $id = (int)$_GET['id'];
    
    // Update views
    mysqli_query($conn, "UPDATE posts SET views = views + 1 WHERE id = $id");
    
    $result = mysqli_query($conn, "
        SELECT p.*, u.name, u.avatar, u.role 
        FROM posts p 
        JOIN users u ON p.user_id = u.id 
        WHERE p.id = $id
    ");
    
    if (mysqli_num_rows($result) > 0) {
        $post = mysqli_fetch_assoc($result);
        echo json_encode(['success' => true, 'post' => $post]);
    } else {
        echo json_encode(['success' => false]);
    }
    exit;
}

// ===== CREATE POST =====
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    
    if ($_POST['action'] === 'create_post') {
        if (!isset($_SESSION['user_id'])) {
            echo json_encode(['error' => 'Login dulu!']);
            exit;
        }
        
        $user_id = $_SESSION['user_id'];
        $title = mysqli_real_escape_string($conn, $_POST['title']);
        $category = mysqli_real_escape_string($conn, $_POST['category']);
        $content = mysqli_real_escape_string($conn, $_POST['content']);
        
        // Upload gambar (simulasi dulu)
        $image_url = '';
        if (isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $target_dir = "uploads/";
            if (!is_dir($target_dir)) mkdir($target_dir);
            
            $ext = pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION);
            $filename = uniqid() . '.' . $ext;
            move_uploaded_file($_FILES['image']['tmp_name'], $target_dir . $filename);
            $image_url = $target_dir . $filename;
        }
        
        mysqli_query($conn, "INSERT INTO posts (user_id, title, category, content, image_url) 
                            VALUES ($user_id, '$title', '$category', '$content', '$image_url')");
        
        // Notifikasi ke Telegram
        sendToTelegram("📝 <b>Cerita Baru</b>\nJudul: $title\nKategori: $category");
        
        echo json_encode(['success' => true]);
        exit;
    }
    
    // ===== REPORT BUG =====
    if ($_POST['action'] === 'report_bug') {
        $report = json_decode(file_get_contents('php://input'), true);
        $user_id = $_SESSION['user_id'] ?? 0;
        $report_text = mysqli_real_escape_string($conn, $report['report']);
        
        mysqli_query($conn, "INSERT INTO bug_reports (user_id, report) VALUES ($user_id, '$report_text')");
        
        // Kirim ke Telegram
        $message = "🐛 <b>BUG REPORT</b>\n";
        $message .= "User ID: $user_id\n";
        $message .= "Report: $report_text\n";
        $message .= "Time: " . date('Y-m-d H:i:s');
        sendToTelegram($message);
        
        echo json_encode(['success' => true]);
        exit;
    }
}
?>