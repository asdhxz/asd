<?php
require_once 'db.php';

header('Content-Type: application/json');

if(!isset($_SESSION['user_id'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Не авторизован']);
    exit;
}

if($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Метод не поддерживается']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if(!isset($data['post_id']) || !isset($data['content']) || empty(trim($data['content']))) {
    http_response_code(400);
    echo json_encode(['error' => 'Не все поля заполнены']);
    exit;
}

$post_id = (int)$data['post_id'];
$content = trim($data['content']);
$user_id = $_SESSION['user_id'];

$stmt = $pdo->prepare("SELECT id FROM posts WHERE id = ?");
$stmt->execute([$post_id]);
if(!$stmt->fetch()) {
    http_response_code(404);
    echo json_encode(['error' => 'Пост не найден']);
    exit;
}

$stmt = $pdo->prepare("INSERT INTO comments (content, user_id, post_id) VALUES (?, ?, ?)");
if($stmt->execute([$content, $user_id, $post_id])) {
    $comment_id = $pdo->lastInsertId();
    
    $stmt = $pdo->prepare("
        SELECT c.*, u.username 
        FROM comments c 
        LEFT JOIN users u ON c.user_id = u.id 
        WHERE c.id = ?
    ");
    $stmt->execute([$comment_id]);
    $comment = $stmt->fetch();
    
    echo json_encode([
        'success' => true,
        'comment' => [
            'id' => $comment['id'],
            'content' => htmlspecialchars($comment['content']),
            'username' => htmlspecialchars($comment['username'] ?? 'Удален'),
            'created_at' => date('d.m.Y H:i', strtotime($comment['created_at']))
        ]
    ]);
} else {
    http_response_code(500);
    echo json_encode(['error' => 'Ошибка при добавлении комментария']);
}
?>