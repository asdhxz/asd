<?php
require_once 'db.php';

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username']);
    $email = trim($_POST['email']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];
    
    $errors = [];
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Введите корректный email адрес (например: name@domain.ru)';
    }
    
    if (strlen($email) < 5 || strlen($email) > 100) {
        $errors[] = 'Email должен быть от 5 до 100 символов';
    }
    
    $parts = explode('@', $email);
    if (count($parts) == 2 && strpos($parts[1], '.') === false) {
        $errors[] = 'Email должен содержать точку в домене (например: mail.ru, gmail.com)';
    }
    
    if(strlen($password) < 6) {
        $errors[] = 'Пароль должен быть не менее 6 символов';
    }
    
    if($password !== $confirm_password) {
        $errors[] = 'Пароли не совпадают';
    }
    
    if(strlen($username) < 2) {
        $errors[] = 'Имя пользователя должно быть не менее 2 символов';
    }
    
    if(!preg_match('/^[a-zA-Z0-9_]+$/', $username)) {
        $errors[] = 'Имя пользователя может содержать только буквы латинского алфавита, цифры и знак подчеркивания';
    }
    
    $stmt = $pdo->prepare("SELECT id FROM users WHERE username = ? OR email = ?");
    $stmt->execute([$username, $email]);
    if($stmt->fetch()) {
        $errors[] = 'Пользователь с таким именем или email уже существует';
    }
    
    if(empty($errors)) {
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);
        $stmt = $pdo->prepare("INSERT INTO users (username, email, password) VALUES (?, ?, ?)");
        if($stmt->execute([$username, $email, $hashed_password])) {
            $_SESSION['success'] = 'Регистрация успешна! Теперь вы можете войти.';
            header('Location: login.php');
            exit;
        }
    }
}

require_once 'header.php';
?>

<div class="auth-form">
    <h2>Регистрация</h2>
    
    <?php if(!empty($errors)): ?>
        <div class="errors">
            <?php foreach($errors as $error): ?>
                <p class="error"><?php echo htmlspecialchars($error); ?></p>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
    
    <form method="POST" action="" id="register-form">
        <div class="form-group">
            <label for="username">Имя пользователя:</label>
            <input type="text" id="username" name="username" required 
                   pattern="[a-zA-Z0-9_]+" 
                   title="Только латинские буквы, цифры и знак подчеркивания"
                   value="<?php echo htmlspecialchars($username ?? ''); ?>">
            <small>Только латинские буквы, цифры и знак подчеркивания</small>
        </div>
        
        <div class="form-group">
            <label for="email">Email:</label>
            <input type="email" id="email" name="email" required 
                   pattern="[a-z0-9._%+-]+@[a-z0-9.-]+\.[a-z]{2,}$"
                   title="Введите корректный email адрес (например: name@domain.ru)"
                   value="<?php echo htmlspecialchars($email ?? ''); ?>">
            <small>Введите корректный email (пример: user@mail.ru)</small>
        </div>
        
        <div class="form-group">
            <label for="password">Пароль:</label>
            <input type="password" id="password" name="password" required 
                   minlength="6"
                   title="Пароль должен быть не менее 6 символов">
            <small>Минимум 6 символов</small>
        </div>
        
        <div class="form-group">
            <label for="confirm_password">Подтверждение пароля:</label>
            <input type="password" id="confirm_password" name="confirm_password" required>
        </div>
        
        <button type="submit" class="btn">Зарегистрироваться</button>
    </form>
    
    <p>Уже есть аккаунт? <a href="login.php">Войдите</a></p>
</div>

<script>
document.getElementById('register-form').addEventListener('submit', function(e) {
    const email = document.getElementById('email').value;
    const password = document.getElementById('password').value;
    const confirm = document.getElementById('confirm_password').value;
    const username = document.getElementById('username').value;
    let errors = [];
    
    const emailPattern = /^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/;
    if (!emailPattern.test(email)) {
        errors.push('Email должен быть корректным (например: name@domain.ru)');
    }
    
    if (password.length < 6) {
        errors.push('Пароль должен быть не менее 6 символов');
    }
    
    if (password !== confirm) {
        errors.push('Пароли не совпадают');
    }
    
    const usernamePattern = /^[a-zA-Z0-9_]+$/;
    if (!usernamePattern.test(username)) {
        errors.push('Имя пользователя может содержать только латинские буквы, цифры и знак подчеркивания');
    }
    
    if (errors.length > 0) {
        e.preventDefault();
        alert('Ошибки:\n- ' + errors.join('\n- '));
    }
});
</script>

<?php require_once 'footer.php'; ?>