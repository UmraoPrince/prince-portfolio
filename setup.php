<?php
/**
 * ============================================
 * ONE-CLICK SETUP SCRIPT
 * Run this file once to initialize everything.
 * DELETE THIS FILE AFTER SETUP IS COMPLETE.
 * ============================================
 */

 $messages = [];
 $success = true;

// --- Step 1: Read Config ---
require_once __DIR__ . '/config.php';

// --- Step 2: Connect without database ---
try {
    $pdo = new PDO(
        "mysql:host=$DB_HOST;charset=utf8mb4",
        $DB_USER,
        $DB_PASS,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]
    );
} catch (PDOException $e) {
    die("<h2>Connection Failed</h2><p>Could not connect to MySQL. Please check config.php credentials.<br><small>" . htmlspecialchars($e->getMessage()) . "</small></p>");
}

// --- Step 3: Create Database ---
try {
    $pdo->exec("CREATE DATABASE IF NOT EXISTS `$DB_NAME` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
    $messages[] = ['success', "Database '$DB_NAME' created (or already exists)."];
} catch (Exception $e) {
    $messages[] = ['error', "Failed to create database: " . $e->getMessage()];
    $success = false;
}

// --- Step 4: Select Database ---
 $pdo->exec("USE `$DB_NAME`");

// --- Step 5: Create Tables ---
 $queries = [
    "CREATE TABLE IF NOT EXISTS certificates (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cert_id VARCHAR(50) NOT NULL UNIQUE,
        name VARCHAR(255) NOT NULL,
        course VARCHAR(255) NOT NULL,
        issuer VARCHAR(255) NOT NULL,
        date DATE NOT NULL,
        file_path VARCHAR(500) NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci",
    "CREATE TABLE IF NOT EXISTS admin (
        id INT AUTO_INCREMENT PRIMARY KEY,
        username VARCHAR(100) NOT NULL UNIQUE,
        password VARCHAR(255) NOT NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci"
];

foreach ($queries as $sql) {
    try {
        $pdo->exec($sql);
        $messages[] = ['success', 'Table created successfully.'];
    } catch (Exception $e) {
        $messages[] = ['error', 'Table creation error: ' . $e->getMessage()];
        $success = false;
    }
}

// --- Step 6: Insert Admin User ---
 $hashedPassword = password_hash($ADMIN_PASSWORD, PASSWORD_DEFAULT);
try {
    $stmt = $pdo->prepare("SELECT id FROM admin WHERE username = :u");
    $stmt->execute([':u' => $ADMIN_USERNAME]);
    if ($stmt->rowCount() === 0) {
        $insert = $pdo->prepare("INSERT INTO admin (username, password) VALUES (:u, :p)");
        $insert->execute([':u' => $ADMIN_USERNAME, ':p' => $hashedPassword]);
        $messages[] = ['success', "Admin user '$ADMIN_USERNAME' created with hashed password."];
    } else {
        $messages[] = ['success', "Admin user '$ADMIN_USERNAME' already exists. Skipping."];
    }
} catch (Exception $e) {
    $messages[] = ['error', 'Admin user creation error: ' . $e->getMessage()];
    $success = false;
}

// --- Step 7: Create Upload Directory ---
 $uploadDir = __DIR__ . '/certificates/';
if (!is_dir($uploadDir)) {
    if (mkdir($uploadDir, 0755, true)) {
        $messages[] = ['success', 'Upload directory /certificates/ created.'];
    } else {
        $messages[] = ['error', 'Failed to create /certificates/ directory. Create it manually.'];
        $success = false;
    }
} else {
    $messages[] = ['success', 'Upload directory /certificates/ already exists.'];
}

// --- Step 8: Create .htaccess in certificates folder ---
 $htaccess = $uploadDir . '.htaccess';
if (!file_exists($htaccess)) {
    file_put_contents($htaccess, "Options -Indexes\nRemoveHandler .php\n");
    $messages[] = ['success', 'Security .htaccess added to /certificates/.'];
}

// --- Step 9: Create assets directory ---
 $assetsDir = __DIR__ . '/assets/';
if (!is_dir($assetsDir)) {
    mkdir($assetsDir, 0755, true);
    $messages[] = ['success', 'Directory /assets/ created.'];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup - Prince Portfolio</title>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Poppins', sans-serif;
            background: #080e17;
            color: #f0f4f8;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 24px;
        }
        .setup-card {
            background: rgba(255,255,255,0.04);
            border: 1px solid rgba(255,255,255,0.1);
            border-radius: 20px;
            backdrop-filter: blur(16px);
            padding: 48px;
            max-width: 560px;
            width: 100%;
        }
        .setup-card h1 {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 8px;
        }
        .setup-card .subtitle {
            color: rgba(240,244,248,0.5);
            font-size: 14px;
            margin-bottom: 32px;
        }
        .msg {
            padding: 12px 16px;
            border-radius: 10px;
            font-size: 13px;
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        .msg.ok {
            background: rgba(0,230,118,0.08);
            border: 1px solid rgba(0,230,118,0.15);
            color: #00e676;
        }
        .msg.err {
            background: rgba(255,82,82,0.08);
            border: 1px solid rgba(255,82,82,0.15);
            color: #ff5252;
        }
        .final {
            margin-top: 24px;
            padding: 20px;
            border-radius: 14px;
            text-align: center;
            font-weight: 600;
        }
        .final.ok {
            background: rgba(0,230,118,0.1);
            border: 1px solid rgba(0,230,118,0.2);
            color: #00e676;
        }
        .final.err {
            background: rgba(255,82,82,0.1);
            border: 1px solid rgba(255,82,82,0.2);
            color: #ff5252;
        }
        .creds {
            margin-top: 20px;
            padding: 16px;
            border-radius: 10px;
            background: rgba(0,0,0,0.3);
            font-size: 13px;
            line-height: 2;
        }
        .creds code {
            background: rgba(0,210,255,0.1);
            color: #00d2ff;
            padding: 2px 8px;
            border-radius: 4px;
            font-family: monospace;
        }
        a.btn {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            margin-top: 20px;
            padding: 12px 28px;
            border-radius: 25px;
            background: linear-gradient(135deg, #6a11cb, #2575fc);
            color: #fff;
            text-decoration: none;
            font-weight: 600;
            font-size: 14px;
            transition: 0.3s;
        }
        a.btn:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(106,17,203,0.3); }
        .warning {
            margin-top: 16px;
            padding: 14px;
            border-radius: 10px;
            background: rgba(255,193,7,0.08);
            border: 1px solid rgba(255,193,7,0.15);
            color: #ffc107;
            font-size: 13px;
        }
        .warning i { margin-right: 6px; }
    </style>
</head>
<body>
    <div class="setup-card">
        <h1><i class="fas fa-cog" style="color:#00d2ff;"></i> Setup Complete</h1>
        <p class="subtitle">Prince Verified Portfolio - Initialization Report</p>

        <?php foreach ($messages as $m): ?>
            <div class="msg <?= $m[0] === 'success' ? 'ok' : 'err' ?>">
                <i class="fas fa-<?= $m[0] === 'success' ? 'check-circle' : 'times-circle' ?>"></i>
                <?= htmlspecialchars($m[1]) ?>
            </div>
        <?php endforeach; ?>

        <?php if ($success): ?>
            <div class="final ok">
                <i class="fas fa-check-circle"></i> All systems ready!
            </div>
            <div class="creds">
                <strong>Admin Login Credentials:</strong><br>
                Username: <code><?= htmlspecialchars($ADMIN_USERNAME) ?></code><br>
                Password: <code><?= htmlspecialchars($ADMIN_PASSWORD) ?></code><br>
                URL: <code>admin/login.php</code>
            </div>
            <div class="warning">
                <i class="fas fa-exclamation-triangle"></i>
                Delete setup.php after setup for security.
            </div>
            <a href="index.php" class="btn">
                <i class="fas fa-arrow-right"></i> Go to Portfolio
            </a>
        <?php else: ?>
            <div class="final err">
                <i class="fas fa-times-circle"></i> Setup encountered errors. Please fix and re-run.
            </div>
        <?php endif; ?>
    </div>
</body>
</html>