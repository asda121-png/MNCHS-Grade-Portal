<!-- Header Component -->
<header class="header">
    <h1>MNCHS Grade Portal</h1>
    <div class="user-info">
        <a href="#" class="notification-bell">
            <i class="fas fa-bell"></i>
            <span class="notification-badge">3</span>
        </a>
        <span>Welcome, <?php echo htmlspecialchars($_SESSION['user_id'] ?? 'User'); ?></span>
    </div>
</header>
