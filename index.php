<?php
require_once 'db.php';

$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$limit = 5;
$offset = ($page - 1) * $limit;

$totalStmt = $pdo->query("SELECT COUNT(*) FROM posts");
$totalPosts = $totalStmt->fetchColumn();
$totalPages = ceil($totalPosts / $limit);

$stmt = $pdo->prepare("
    SELECT p.*, u.username 
    FROM posts p 
    LEFT JOIN users u ON p.user_id = u.id 
    ORDER BY p.created_at DESC 
    LIMIT :limit OFFSET :offset
");
$stmt->bindParam(':limit', $limit, PDO::PARAM_INT);
$stmt->bindParam(':offset', $offset, PDO::PARAM_INT);
$stmt->execute();
$posts = $stmt->fetchAll();

require_once 'header.php';
?>

<h1>Последние статьи</h1>

<div class="posts">
    <?php foreach($posts as $post): ?>
        <article class="post-card">
            <h2><a href="post.php?id=<?php echo $post['id']; ?>"><?php echo htmlspecialchars($post['title']); ?></a></h2>
            <div class="post-meta">
                <span class="author">Автор: <?php echo htmlspecialchars($post['username'] ?? 'Удален'); ?></span>
                <span class="date"><?php echo date('d.m.Y', strtotime($post['created_at'])); ?></span>
            </div>
            <?php if($post['image']): ?>
                <img src="<?php echo htmlspecialchars($post['image']); ?>" alt="Post image" class="post-image">
            <?php endif; ?>
            <p class="post-excerpt"><?php echo htmlspecialchars(substr($post['content'], 0, 200)) . '...'; ?></p>
            <a href="post.php?id=<?php echo $post['id']; ?>" class="read-more">Читать далее</a>
        </article>
    <?php endforeach; ?>
</div>

<?php if($totalPages > 1): ?>
    <div class="pagination">
        <?php if($page > 1): ?>
            <a href="?page=<?php echo $page-1; ?>">← Предыдущая</a>
        <?php endif; ?>
        
        <?php for($i = 1; $i <= $totalPages; $i++): ?>
            <a href="?page=<?php echo $i; ?>" class="<?php echo $i == $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
        <?php endfor; ?>
        
        <?php if($page < $totalPages): ?>
            <a href="?page=<?php echo $page+1; ?>">Следующая →</a>
        <?php endif; ?>
    </div>
<?php endif; ?>

<?php require_once 'footer.php'; ?>