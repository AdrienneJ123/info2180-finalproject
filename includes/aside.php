<?php if (!isset($_SESSION)) session_start(); ?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Dolphin CRM</title>
    <link rel="stylesheet" href="assets/css/styles.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">

</head>
<body>

<nav class="top-nav">
    <h1>🐬 Dolphin CRM</h1>
</nav>
<div class="layout">
    <aside class="aside">
        <nav class="aside-nav">
            <a href="dashboard.php" class="nav-link">
                <i class="bi bi-house navi"></i> <span>Home</span>
            </a>

            <a href="contacts/new.php" class="nav-link">
                <i class="bi bi-person-circle navi"></i><span>New Contact</span>
            </a>

         
                <a href="users/list.php" id="nav-id" class="nav-link">
                    <i class="bi bi-people navi"></i><span>Users</span>
                </a>
                <hr class="divider">
                 <a href="logout.php" class="nav-link">
                    <i class="bi bi-box-arrow-left navi" class=></i> <span>Logout</span>
                 </a>

        </nav>
    
            </div>
        </div>
    </aside>
    <main class="main-content">
    </main>
</div>

</body>
</html>