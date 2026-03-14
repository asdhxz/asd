<?php
require_once 'db.php';

if(!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: login.php');
    exit;
}

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: admin.php');
    exit;
}

$post_id = $_GET['id'];

$stmt = $pdo->prepare("SELECT * FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post) {
    header('Location: admin.php');
    exit;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $title = trim($_POST['title']);
    $content = trim($_POST['content']);
    
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
                if($post['image'] && file_exists($post['image'])) {
                    unlink($post['image']);
                }
                $image_path = 'uploads/' . $new_filename;
                
                $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ?, image = ? WHERE id = ?");
                $stmt->execute([$title, $content, $image_path, $post_id]);
            }
        }
    } else {
        $stmt = $pdo->prepare("UPDATE posts SET title = ?, content = ? WHERE id = ?");
        $stmt->execute([$title, $content, $post_id]);
    }
    
    header('Location: admin.php');
    exit;
}

require_once 'header.php';
?>

<h1>Редактировать пост</h1>

<form method="POST" enctype="multipart/form-data">
    <div class="form-group">
        <label for="title">Заголовок:</label>
        <input type="text" id="title" name="title" value="<?php echo htmlspecialchars($post['title']); ?>" required>
    </div>
    <div class="form-group">
        <label for="content">Текст:</label>
        <textarea id="content" name="content" rows="10" required><?php echo htmlspecialchars($post['content']); ?></textarea>
    </div>
    <?php if($post['image']): ?>
        <div class="form-group">
            <label>Текущее изображение:</label>
            <img src="<?php echo htmlspecialchars($post['image']); ?>" style="max-width: 200px; display: block; margin: 10px 0;">
        </div>
    <?php endif; ?>
    <div class="form-group">
        <label for="image">Новое изображение (оставьте пустым, чтобы не менять):</label>
        <input type="file" id="image" name="image" accept="image/*">
    </div>
    <button type="submit" class="btn">Сохранить изменения</button>
    <a href="admin.php" class="btn">Отмена</a>
</form>

<?php require_once 'footer.php'; ?>