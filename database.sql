CREATE DATABASE IF NOT EXISTS horor_forum;
USE horor_forum;

-- ===== TABEL USERS =====
CREATE TABLE users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    google_id VARCHAR(100) UNIQUE,
    name VARCHAR(100),
    email VARCHAR(100) UNIQUE,
    avatar TEXT,
    role ENUM('user', 'creator', 'developer') DEFAULT 'user',
    bio TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- ===== TABEL POSTS =====
CREATE TABLE posts (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    title VARCHAR(255),
    category VARCHAR(50),
    content TEXT,
    image_url TEXT,
    views INT DEFAULT 0,
    likes INT DEFAULT 0,
    comments_count INT DEFAULT 0,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===== TABEL COMMENTS =====
CREATE TABLE comments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    post_id INT,
    user_id INT,
    content TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (post_id) REFERENCES posts(id) ON DELETE CASCADE,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===== TABEL BUG_REPORTS (SERVICE BOX) =====
CREATE TABLE bug_reports (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    report TEXT,
    status ENUM('pending', 'fixed', 'rejected') DEFAULT 'pending',
    fix_code TEXT,
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    fixed_at DATETIME NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

-- ===== TABEL BOT_SETTINGS =====
CREATE TABLE bot_settings (
    id INT AUTO_INCREMENT PRIMARY KEY,
    bot_token VARCHAR(255),
    chat_id VARCHAR(50),
    last_update DATETIME
);