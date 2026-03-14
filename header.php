<?php
if (session_status() === PHP_SESSION_NONE) session_start();
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Блог</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif; line-height: 1.6; color: #333; min-height: 100vh; display: flex; flex-direction: column; }
        .container { max-width: 1200px; margin: 0 auto; padding: 0 20px; width: 100%; }
        
        .navbar { background: #2c3e50; color: white; padding: 1rem 0; }
        .nav-container { max-width: 1200px; margin: 0 auto; padding: 0 20px; display: flex; justify-content: space-between; align-items: center; }
        .logo { color: white; text-decoration: none; font-size: 1.5rem; font-weight: bold; }
        .nav-menu { display: flex; list-style: none; gap: 2rem; }
        .nav-menu a { color: white; text-decoration: none; }
        .nav-menu a:hover { color: #3498db; }
        .mobile-menu-btn { display: none; background: none; border: none; color: white; font-size: 1.5rem; cursor: pointer; }
        
        @media (max-width: 768px) {
            .mobile-menu-btn { display: block; }
            .nav-menu { display: none; position: absolute; top: 60px; left: 0; right: 0; background: #2c3e50; flex-direction: column; padding: 1rem; gap: 1rem; }
            .nav-menu.active { display: flex; }
        }
        
        main { flex: 1; padding: 2rem 0; }
        
        .post-card { background: #f9f9f9; border: 1px solid #ddd; border-radius: 8px; padding: 1.5rem; margin-bottom: 2rem; }
        .post-card h2 { margin-bottom: 0.5rem; }
        .post-card h2 a { color: #2c3e50; text-decoration: none; }
        .post-card h2 a:hover { color: #3498db; }
        .post-meta { color: #666; font-size: 0.9rem; margin-bottom: 1rem; }
        .post-meta span { margin-right: 1rem; }
        .post-image { max-width: 100%; max-height: 300px; object-fit: cover; border-radius: 4px; margin: 1rem 0; }
        .post-excerpt { margin-bottom: 1rem; }
        .read-more { display: inline-block; background: #3498db; color: white; padding: 0.5rem 1rem; text-decoration: none; border-radius: 4px; }
        .read-more:hover { background: #2980b9; }
        
        .pagination { display: flex; justify-content: center; gap: 0.5rem; margin-top: 2rem; flex-wrap: wrap; }
        .pagination a { padding: 0.5rem 1rem; background: #f9f9f9; border: 1px solid #ddd; color: #333; text-decoration: none; border-radius: 4px; }
        .pagination a.active { background: #3498db; color: white; border-color: #3498db; }
        .pagination a:hover:not(.active) { background: #eee; }
        
        .auth-form { max-width: 400px; margin: 0 auto; padding: 2rem; background: #f9f9f9; border-radius: 8px; border: 1px solid #ddd; }
        .auth-form h2 { margin-bottom: 1.5rem; text-align: center; }
        .form-group { margin-bottom: 1rem; }
        .form-group label { display: block; margin-bottom: 0.3rem; font-weight: 500; }
        .form-group input, .form-group textarea, .form-group select { width: 100%; padding: 0.5rem; border: 1px solid #ddd; border-radius: 4px; }
        .form-group textarea { resize: vertical; }
        .btn { display: inline-block; background: #3498db; color: white; padding: 0.5rem 1rem; border: none; border-radius: 4px; cursor: pointer; font-size: 1rem; text-decoration: none; }
        .btn:hover { background: #2980b9; }
        .btn-small { display: inline-block; background: #3498db; color: white; padding: 0.25rem 0.5rem; border-radius: 3px; font-size: 0.875rem; text-decoration: none; margin-right: 0.25rem; }
        .btn-danger { background: #e74c3c; }
        .btn-danger:hover { background: #c0392b; }
        
        .error, .errors { background: #f8d7da; color: #721c24; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; border: 1px solid #f5c6cb; }
        .success { background: #d4edda; color: #155724; padding: 0.75rem; border-radius: 4px; margin-bottom: 1rem; border: 1px solid #c3e6cb; }
        
        .comments { margin-top: 3rem; padding-top: 2rem; border-top: 2px solid #eee; }
        .comment { background: #f9f9f9; border: 1px solid #ddd; border-radius: 4px; padding: 1rem; margin-bottom: 1rem; }
        .comment-meta { color: #666; font-size: 0.9rem; margin-bottom: 0.5rem; }
        .comment-author { font-weight: bold; color: #2c3e50; }
        .comment-date { margin-left: 1rem; }
        .login-message { text-align: center; padding: 1rem; background: #f9f9f9; border-radius: 4px; }
        .like-btn { background: none; border: 1px solid #ddd; padding: 0.25rem 0.5rem; cursor: pointer; border-radius: 4px; }
        .like-btn:hover { background: #f9f9f9; }
        
        .admin-menu { margin: 2rem 0; }
        .admin-menu ul { list-style: none; display: flex; gap: 1rem; }
        .admin-menu a { display: block; padding: 1rem 2rem; background: #f9f9f9; border: 1px solid #ddd; text-decoration: none; color: #333; border-radius: 4px; }
        .admin-menu a:hover { background: #e9e9e9; }
        .admin-table { width: 100%; border-collapse: collapse; margin: 2rem 0; }
        .admin-table th, .admin-table td { padding: 0.75rem; border: 1px solid #ddd; text-align: left; }
        .admin-table th { background: #f9f9f9; }
        .admin-table tr:hover { background: #f5f5f5; }
        
        footer { background: #2c3e50; color: white; text-align: center; padding: 1rem 0; margin-top: auto; }
    </style>
</head>
<body>
    <header>
        <nav class="navbar">
            <div class="nav-container">
                <a href="index.php" class="logo">Блог</a>
                <button class="mobile-menu-btn" onclick="document.querySelector('.nav-menu').classList.toggle('active')">☰</button>
                <ul class="nav-menu">
                    <li><a href="index.php">Главная</a></li>
                    <?php if(isset($_SESSION['user_id'])): ?>
                        <?php if($_SESSION['user_role'] === 'admin'): ?>
                            <li><a href="admin.php">Админ-панель</a></li>
                        <?php endif; ?>
                        <li><a href="logout.php">Выйти (<?php echo htmlspecialchars($_SESSION['username']); ?>)</a></li>
                    <?php else: ?>
                        <li><a href="login.php">Войти</a></li>
                        <li><a href="register.php">Регистрация</a></li>
                    <?php endif; ?>
                </ul>
            </div>
        </nav>
    </header>
    <main class="container">