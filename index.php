<?php
require_once 'config.php';

// Ambil semua postingan
$posts = mysqli_query($conn, "
    SELECT p.*, u.name, u.avatar, u.role 
    FROM posts p 
    JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT 20
");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>👻 HOROR FORUM PREMIUM</title>
    <link rel="stylesheet" href="style.css">
    <!-- Google Login -->
    <script src="https://accounts.google.com/gsi/client" async defer></script>
</head>
<body>
    <!-- Background Horror Effect -->
    <div class="horror-bg"></div>
    
    <!-- Navbar Premium -->
    <nav class="navbar">
        <div class="logo">
            <i class="fas fa-ghost"></i> HOROR FORUM
        </div>
        
        <div class="nav-menu">
            <a href="#" class="active">🏠 Beranda</a>
            <a href="#">📖 Cerita</a>
            <a href="#">🏷️ Kategori</a>
            <a href="#">🔥 Trending</a>
            <a href="#">📞 Kontak</a>
        </div>
        
        <div class="user-section" id="userSection">
            <div id="googleLogin"></div>
            <div id="userInfo" style="display: none;"></div>
        </div>
    </nav>
    
    <!-- Service Box (Floating) -->
    <div class="service-box" id="serviceBox">
        <div class="service-header" onclick="toggleService()">
            <i class="fas fa-headset"></i> DEV SUPPORT
            <span class="toggle">▼</span>
        </div>
        <div class="service-body" id="serviceBody">
            <div class="service-messages" id="serviceMessages">
                <div class="message bot">
                    Halo Developer! Ada bug? Laporkan di sini.
                </div>
            </div>
            <div class="service-input">
                <textarea id="bugReport" placeholder="Jelaskan bug..."></textarea>
                <button onclick="reportBug()" class="btn-send">Kirim</button>
            </div>
        </div>
    </div>
    
    <!-- Hero Section -->
    <div class="hero">
        <h1 class="glitch" data-text="HOROR FORUM">HOROR FORUM</h1>
        <p>Tempat paling angker untuk berbagi cerita horor Nusantara</p>
    </div>
    
    <!-- Filter Kategori -->
    <div class="filters">
        <button class="filter-btn active">Semua</button>
        <button class="filter-btn">👻 Kuntilanak</button>
        <button class="filter-btn">⚰️ Pocong</button>
        <button class="filter-btn">🏙️ Urban Legend</button>
        <button class="filter-btn">👤 Pengalaman Nyata</button>
        <div class="search-box">
            <input type="text" placeholder="🔍 Cari cerita seram...">
        </div>
    </div>
    
    <!-- Form Posting (Muncul kalau login) -->
    <div class="post-form" id="postForm" style="display: none;">
        <h3><i class="fas fa-pen"></i> Bagikan Cerita Horormu</h3>
        <input type="text" id="postTitle" placeholder="Judul Cerita" class="horror-input">
        
        <select id="postCategory" class="horror-select">
            <option value="kuntilanak">👻 Kuntilanak</option>
            <option value="pocong">⚰️ Pocong</option>
            <option value="urban">🏙️ Urban Legend</option>
            <option value="experience">👤 Pengalaman Nyata</option>
            <option value="mystery">🔮 Misteri</option>
        </select>
        
        <textarea id="postContent" placeholder="Tulis cerita horormu di sini..." rows="6" class="horror-textarea"></textarea>
        
        <div class="upload-area" onclick="document.getElementById('fileInput').click()">
            <i class="fas fa-cloud-upload-alt"></i>
            <p>Klik untuk upload foto (opsional)</p>
        </div>
        <input type="file" id="fileInput" accept="image/*" style="display: none;">
        
        <button class="btn-post" onclick="submitPost()">
            <i class="fas fa-paper-plane"></i> Publikasikan Cerita
        </button>
    </div>
    
    <!-- Daftar Cerita -->
    <div class="posts-grid">
        <?php while ($post = mysqli_fetch_assoc($posts)): ?>
        <div class="post-card" onclick="openPost(<?= $post['id'] ?>)">
            <?php if ($post['image_url']): ?>
            <div class="post-image">
                <img src="<?= $post['image_url'] ?>" alt="Post image">
            </div>
            <?php endif; ?>
            <div class="post-content">
                <div class="post-author">
                    <img src="<?= $post['avatar'] ?: 'https://ui-avatars.com/api/?name=' . urlencode($post['name']) ?>" class="author-avatar">
                    <div>
                        <h4><?= htmlspecialchars($post['name']) ?></h4>
                        <small><?= date('d M Y', strtotime($post['created_at'])) ?></small>
                    </div>
                </div>
                <h3 class="post-title"><?= htmlspecialchars($post['title']) ?></h3>
                <p><?= substr(htmlspecialchars($post['content']), 0, 150) ?>...</p>
                <div class="post-stats">
                    <span><i class="fas fa-eye"></i> <?= $post['views'] ?></span>
                    <span><i class="fas fa-heart"></i> <?= $post['likes'] ?></span>
                    <span><i class="fas fa-comment"></i> <?= $post['comments_count'] ?></span>
                </div>
            </div>
        </div>
        <?php endwhile; ?>
    </div>
    
    <!-- Modal Detail Post -->
    <div class="modal" id="postModal">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <div id="modalContent"></div>
            <div class="comments-section" id="commentsSection"></div>
        </div>
    </div>
    
    <script src="https://kit.fontawesome.com/yourcode.js" crossorigin="anonymous"></script>
    <script>
        // ===== GOOGLE LOGIN =====
        window.onload = function() {
            google.accounts.id.initialize({
                client_id: '<?= getenv('GOOGLE_CLIENT_ID') ?>',
                callback: handleCredentialResponse
            });
            
            google.accounts.id.renderButton(
                document.getElementById('googleLogin'),
                { theme: 'filled_black', size: 'large', shape: 'pill' }
            );
        };
        
        function handleCredentialResponse(response) {
            fetch('login.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({credential: response.credential})
            })
            .then(res => res.json())
            .then(data => {
                if(data.success) {
                    document.getElementById('googleLogin').style.display = 'none';
                    document.getElementById('userInfo').style.display = 'flex';
                    document.getElementById('userInfo').innerHTML = `
                        <img src="${data.user.avatar}" class="user-avatar">
                        <span>${data.user.name}</span>
                        <button class="btn-logout" onclick="logout()">Logout</button>
                    `;
                    document.getElementById('postForm').style.display = 'block';
                }
            });
        }
        
        function logout() {
            document.cookie.split(";").forEach(c => document.cookie = c.replace(/^ +/, "").replace(/=.*/, "=;expires=" + new Date().toUTCString() + ";path=/"));
            location.reload();
        }
        
        // ===== SERVICE BOX (LAPORAN BUG) =====
        function toggleService() {
            const body = document.getElementById('serviceBody');
            const toggle = document.querySelector('.toggle');
            if (body.style.display === 'none') {
                body.style.display = 'block';
                toggle.textContent = '▼';
            } else {
                body.style.display = 'none';
                toggle.textContent = '▲';
            }
        }
        
        async function reportBug() {
            const report = document.getElementById('bugReport').value;
            if (!report) return;
            
            const res = await fetch('post.php', {
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify({
                    action: 'report_bug',
                    report: report
                })
            });
            
            const data = await res.json();
            if(data.success) {
                document.getElementById('serviceMessages').innerHTML += `
                    <div class="message user">${report}</div>
                    <div class="message bot">✅ Bug telah dilaporkan ke developer!</div>
                `;
                document.getElementById('bugReport').value = '';
            }
        }
        
        // ===== POST CERITA =====
        async function submitPost() {
            const title = document.getElementById('postTitle').value;
            const category = document.getElementById('postCategory').value;
            const content = document.getElementById('postContent').value;
            
            const formData = new FormData();
            formData.append('action', 'create_post');
            formData.append('title', title);
            formData.append('category', category);
            formData.append('content', content);
            
            const file = document.getElementById('fileInput').files[0];
            if (file) formData.append('image', file);
            
            const res = await fetch('post.php', {
                method: 'POST',
                body: formData
            });
            
            const data = await res.json();
            if(data.success) {
                alert('✅ Cerita berhasil dipublikasikan!');
                location.reload();
            }
        }
        
        // ===== OPEN POST DETAIL =====
        async function openPost(id) {
            const res = await fetch(`post.php?id=${id}`);
            const data = await res.json();
            
            document.getElementById('modalContent').innerHTML = `
                <h2>${data.post.title}</h2>
                <div class="post-meta">
                    <img src="${data.post.avatar}" width="30"> ${data.post.author}
                </div>
                <p>${data.post.content}</p>
                ${data.post.image ? `<img src="${data.post.image}" style="max-width:100%">` : ''}
            `;
            
            document.getElementById('postModal').style.display = 'block';
        }
        
        function closeModal() {
            document.getElementById('postModal').style.display = 'none';
        }
    </script>
</body>
</html>