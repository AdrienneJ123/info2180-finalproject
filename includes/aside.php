<?php if (!isset($_SESSION)) session_start(); ?>
<aside class="aside">
    <link rel="stylesheet" href="assets/css/styles.css">
    <div class="header-left">
        <h1>🐬 Dolphin CRM</h1>
    </div>
    <div class="aside-content">
        <nav class="aside-nav">
            <a href="dashboard.php" class="nav-link">
                <span class="nav-icon">🏠</span> <span>Home</span>
            </a>
            <a href="contacts/new.php" class="nav-link">
                <span class="nav-icon">✚</span> <span>New Contact</span>
            </a>
            <?php if (isAdmin()): ?>
                <a href="users/list.php" class="nav-link">
                    <span class="nav-icon">👥</span> <span>Users</span>
                </a>
            <?php endif; ?>
        </nav>
        
        <div class="user-info">
            <div class="user-avatar">
                <?php 
                $firstInitial = isset($_SESSION['firstname']) ? substr($_SESSION['firstname'], 0, 1) : 'U';
                $lastInitial = isset($_SESSION['lastname']) ? substr($_SESSION['lastname'], 0, 1) : 'S';
                echo $firstInitial . $lastInitial;
                ?>
            </div>
            <div class="user-details">
                <span class="user-name">
                    <?php 
                    if (isset($_SESSION['firstname']) && isset($_SESSION['lastname'])) {
                        echo htmlspecialchars($_SESSION['firstname'] . ' ' . $_SESSION['lastname']);
                    } else {
                        echo 'User Name';
                    }
                    ?>
                </span>
                <a href="logout.php" class="logout-btn">Logout</a>
            </div>
        </div>
    </div>
</aside>