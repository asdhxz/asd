<?php
require_once 'db.php';

if(!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$post_id = $_GET['id'];

$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM posts p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.id = ?
");
$stmt->execute([$post_id]);
$post = $stmt->fetch();

if(!$post) {
    header('Location: index.php');
    exit;
}

$stmt = $pdo->prepare("
    SELECT c.*, u.username 
    FROM comments c 
    LEFT JOIN users u ON c.user_id = u.id 
    WHERE c.post_id = ? 
    ORDER BY c.created_at DESC
");
$stmt->execute([$post_id]);
$comments = $stmt->fetchAll();

require_once 'header.php';
?>

<article class="post-full">
    <h1><?php echo htmlspecialchars($post['title']); ?></h1>
    
    <div class="post-meta">
        <span class="author">Автор: <?php echo htmlspecialchars($post['username'] ?? 'Удален'); ?></span>
        <span class="date"><?php echo date('d.m.Y H:i', strtotime($post['created_at'])); ?></span>
    </div>
    
    <?php if($post['image']): ?>
        <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post image" class="post-full-image">
    <?php endif; ?>
    
    <div class="post-content">
        <?php echo nl2br(htmlspecialchars($post['content'])); ?>
    </div>
</article>

<section class="comments">
    <h2>Комментарии (<?php echo count($comments); ?>)</h2>
    
    <div id="comments-list">
        <?php foreach($comments as $comment): ?>
            <div class="comment" data-comment-id="<?php echo $comment['id']; ?>">
                <div class="comment-meta">
                    <span class="comment-author"><?php echo htmlspecialchars($comment['username'] ?? 'Удален'); ?></span>
                    <span class="comment-date"><?php echo date('d.m.Y H:i', strtotime($comment['created_at'])); ?></span>
                </div>
                <div class="comment-content">
                    <?php echo nl2br(htmlspecialchars($comment['content'])); ?>
                </div>
                <button class="like-btn" onclick="likeComment(this)" data-id="<?php echo $comment['id']; ?>">❤️ <span>0</span></button>
            </div>
        <?php endforeach; ?>
    </div>
    
    <?php if(isset($_SESSION['user_id'])): ?>
        <form id="comment-form" class="comment-form" data-post-id="<?php echo $post_id; ?>">
            <h3>Оставить комментарий</h3>
            <div class="form-group">
                <textarea name="content" id="comment-content" rows="4" required placeholder="Ваш комментарий..."></textarea>
            </div>
            <button type="submit" class="btn">Отправить</button>
        </form>
    <?php else: ?>
        <p class="login-message">Чтобы оставить комментарий, <a href="login.php">войдите</a>.</p>
    <?php endif; ?>
</section>

<?php require_once 'footer.php'; ?>