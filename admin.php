<?php
require_once 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['action'])) {
    if($_POST['action'] == 'add_post') {
        $title = trim($_POST['title']);
        $content = trim($_POST['content']);
        
        $image_path = null;
        if(isset($_FILES['image']) && $_FILES['image']['error'] == 0) {
            $allowed = ['jpg', 'jpeg', 'png', 'gif'];
            $ext = strtolower(pathinfo($_FILES['image']['name'], PATHINFO_EXTENSION));
            
            if(in_array($ext, $allowed)) {
                $new_filename = uniqid() . '.' . $ext;
                $upload_path = 'uploads/' . $new_filename;
                
                if(!is_dir('uploads')) {
                    mkdir('uploads', 0777, true);
                }
                
                if(move_uploaded_file($_FILES['image']['tmp_name'], $upload_path)) {
                    $image_path = 'uploads/' . $new_filename;
                }
            }
        }
        
        $stmt = $pdo->prepare("INSERT INTO posts (title, content, image, user_id) VALUES (?, ?, ?, ?)");
        $stmt->execute([$title, $content, $image_path, $_SESSION['user_id']]);
        $message = "Пост успешно добавлен";
    }
}

if(isset($_GET['delete_post'])) {
    $post_id = $_GET['delete_post'];
    
    $stmt = $pdo->prepare("SELECT image FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    $post = $stmt->fetch();
    
    if($post && $post['image'] && file_exists($post['image'])) {
        unlink($post['image']);
    }
    
    $stmt = $pdo->prepare("DELETE FROM posts WHERE id = ?");
    $stmt->execute([$post_id]);
    header('Location: admin.php');
    exit;
}

if(isset($_GET['delete_comment'])) {
    $comment_id = $_GET['delete_comment'];
    $stmt = $pdo->prepare("DELETE FROM comments WHERE id = ?");
    $stmt->execute([$comment_id]);
    header('Location: admin.php');
    exit;
}

$posts = $pdo->query("SELECT p.*, u.username FROM posts p LEFT JOIN users u ON p.user_id = u.id ORDER BY p.created_at DESC")->fetchAll();

$comments = $pdo->query("SELECT c.*, u.username, p.title as post_title FROM comments c LEFT JOIN users u ON c.user_id = u.id LEFT JOIN posts p ON c.post_id = p.id ORDER BY c.created_at DESC LIMIT 20")->fetchAll();

require_once 'header.php';
?>

<h1>Админ-панель</h1>

<?php if(isset($message)): ?>
    <div class="success"><?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<div class="admin-menu">
    <ul>
        <li><a href="#add-post">➕ Добавить пост</a></li>
        <li><a href="#posts">📝 Управление постами</a></li>
        <li><a href="#comments">💬 Управление комментариями</a></li>
    </ul>
</div>

<h2 id="add-post">Добавить новый пост</h2>
<form method="POST" enctype="multipart/form-data">
    <input type="hidden" name="action" value="add_post">
    <div class="form-group">
        <label for="title">Заголовок:</label>
        <input type="text" id="title" name="title" required>
    </div>
    <div class="form-group">
        <label for="content">Текст:</label>
        <textarea id="content" name="content" rows="10" required></textarea>
    </div>
    <div class="form-group">
        <label for="image">Изображение:</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>
    <button type="submit" class="btn">Сохранить пост</button>
</form>

<h2 id="posts">Управление постами</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Заголовок</th>
            <th>Автор</th>
            <th>Дата</th>
            <th>Действия</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($posts as $post): ?>
        <tr>
            <td><?php echo $post['id']; ?></td>
            <td><?php echo htmlspecialchars($post['title']); ?></td>
            <td><?php echo htmlspecialchars($post['username'] ?? 'Удален'); ?></td>
            <td><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></td>
            <td>
                <a href="admin_edit_post.php?id=<?php echo $post['id']; ?>" class="btn-small">✏️ Ред.</a>
                <a href="?delete_post=<?php echo $post['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Удалить пост?')">🗑️ Уд.</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<h2 id="comments">Последние комментарии</h2>
<table class="admin-table">
    <thead>
        <tr>
            <th>ID</th>
            <th>Комментарий</th>
            <th>Автор</th>
            <th>Пост</th>
            <th>Дата</th>
            <th>Действие</th>
        </tr>
    </thead>
    <tbody>
        <?php foreach($comments as $comment): ?>
        <tr>
            <td><?php echo $comment['id']; ?></td>
            <td><?php echo htmlspecialchars(substr($comment['content'], 0, 50)) . '...'; ?></td>
            <td><?php echo htmlspecialchars($comment['username'] ?? 'Удален'); ?></td>
            <td><?php echo htmlspecialchars($comment['post_title'] ?? 'Удален'); ?></td>
            <td><?php echo date('d.m.Y', strtotime($comment['created_at'])); ?></td>
            <td>
                <a href="?delete_comment=<?php echo $comment['id']; ?>" class="btn-small btn-danger" onclick="return confirm('Удалить комментарий?')">🗑️ Удалить</a>
            </td>
        </tr>
        <?php endforeach; ?>
    </tbody>
</table>

<?php require_once 'footer.php'; ?>