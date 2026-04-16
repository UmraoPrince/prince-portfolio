<?php
ob_start();
require_once __DIR__ . '/../db.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

 $searchQuery = '';
if (isset($_GET['q']) && trim($_GET['q']) !== '') {
    $search = '%' . trim($_GET['q']) . '%';
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE cert_id LIKE :s OR name LIKE :s OR course LIKE :s OR issuer LIKE :s ORDER BY date DESC");
    $stmt->execute([':s' => $search]);
    $searchQuery = trim($_GET['q']);
} else {
    $stmt = $pdo->query("SELECT * FROM certificates ORDER BY date DESC");
}
 $certificates = $stmt->fetchAll();
 $total = count($certificates);

 $flash = null;
if (isset($_SESSION['flash_msg'])) {
    $flash = ['message' => $_SESSION['flash_msg'], 'type' => $_SESSION['flash_type']];
    unset($_SESSION['flash_msg'], $_SESSION['flash_type']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<div class="admin-wrapper">
    <header class="admin-topbar">
        <div class="admin-topbar-inner">
            <div class="admin-topbar-title">
                <i class="fas fa-shield-halved"></i> Admin Panel
            </div>
            <nav class="admin-topbar-nav">
                <a href="dashboard.php" class="active"><i class="fas fa-th-large"></i> Dashboard</a>
                <a href="upload.php"><i class="fas fa-plus-circle"></i> Upload</a>
                <a href="../index.php"><i class="fas fa-globe"></i> View Site</a>
            </nav>
            <div class="admin-topbar-user">
                <div class="admin-avatar"><?= strtoupper(substr($_SESSION['admin_username'], 0, 1)) ?></div>
                <a href="logout.php" class="btn btn-sm btn-danger"><i class="fas fa-sign-out-alt"></i> Logout</a>
            </div>
        </div>
    </header>

    <main class="admin-body">

        <?php if ($flash): ?>
            <?php
            $bg = $flash['type'] === 'success' ? 'rgba(0,230,118,0.15)' : 'rgba(255,82,82,0.15)';
            $border = $flash['type'] === 'success' ? '#00e676' : '#ff5252';
            $color = $flash['type'] === 'success' ? '#00e676' : '#ff5252';
            ?>
            <div style="background:<?= $bg ?>; border:1px solid <?= $border ?>; color:<?= $color ?>; padding:14px 22px; border-radius:12px; margin-bottom:24px; backdrop-filter:blur(8px); font-size:15px;">
                <i class="fas fa-<?= $flash['type'] === 'success' ? 'check-circle' : 'exclamation-circle' ?>"></i>
                <?= htmlspecialchars($flash['message']) ?>
            </div>
        <?php endif; ?>

        <div style="display:grid; grid-template-columns:repeat(auto-fit, minmax(200px,1fr)); gap:16px; margin-bottom:32px;">
            <div class="glass" style="padding:22px; display:flex; align-items:center; gap:16px;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,rgba(0,210,255,0.15),rgba(37,117,252,0.15));display:flex;align-items:center;justify-content:center;color:#00d2ff;font-size:20px;">
                    <i class="fas fa-certificate"></i>
                </div>
                <div>
                    <div style="font-size:26px;font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= $total ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Total Certificates</div>
                </div>
            </div>
            <div class="glass" style="padding:22px; display:flex; align-items:center; gap:16px;">
                <div style="width:46px;height:46px;border-radius:12px;background:linear-gradient(135deg,rgba(106,17,203,0.15),rgba(106,17,203,0.05));display:flex;align-items:center;justify-content:center;color:#6a11cb;font-size:20px;">
                    <i class="fas fa-search"></i>
                </div>
                <div>
                    <div style="font-size:26px;font-weight:700;font-family:'Space Grotesk',sans-serif;"><?= $searchQuery !== '' ? $total : '--' ?></div>
                    <div style="font-size:12px;color:var(--text-muted);">Search Results</div>
                </div>
            </div>
        </div>

        <div class="admin-toolbar">
            <div class="admin-search">
                <i class="fas fa-search"></i>
                <form method="GET" action="" style="display:flex;gap:8px;width:100%;">
                    <input type="text" name="q" placeholder="Search by ID, name, course, issuer..." value="<?= htmlspecialchars($searchQuery) ?>" style="flex:1;padding:10px 14px 10px 40px;border-radius:25px;border:1px solid var(--glass-border);background:var(--glass-bg);color:var(--text-primary);font-family:var(--font-main);font-size:14px;outline:none;">
                    <?php if ($searchQuery !== ''): ?>
                        <a href="dashboard.php" class="btn btn-sm btn-outline" style="white-space:nowrap;"><i class="fas fa-times"></i> Clear</a>
                    <?php endif; ?>
                </form>
            </div>
            <a href="upload.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Certificate</a>
        </div>

        <?php if ($total > 0): ?>
            <div class="table-wrapper glass">
                <table class="admin-table">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Certificate ID</th>
                            <th>Name</th>
                            <th>Course</th>
                            <th>Issuer</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php $i = 1; foreach ($certificates as $c): ?>
                            <tr>
                                <td><?= $i++ ?></td>
                                <td><span class="table-cert-id"><?= htmlspecialchars($c['cert_id']) ?></span></td>
                                <td><?= htmlspecialchars($c['name']) ?></td>
                                <td><?= htmlspecialchars($c['course']) ?></td>
                                <td><?= htmlspecialchars($c['issuer']) ?></td>
                                <td><?= htmlspecialchars(date('M d, Y', strtotime($c['date']))) ?></td>
                                <td>
                                    <div class="table-actions">
                                        <a href="edit.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-success"><i class="fas fa-pen"></i> Edit</a>
                                        <a href="delete.php?id=<?= $c['id'] ?>" class="btn btn-sm btn-danger" onclick="return confirm('Delete <?= htmlspecialchars(addslashes($c['cert_id'])) ?>? This cannot be undone.');"><i class="fas fa-trash"></i> Delete</a>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php else: ?>
            <div class="empty-state glass">
                <i class="fas fa-inbox"></i>
                <p><?= $searchQuery !== '' ? 'No certificates match your search.' : 'No certificates added yet.' ?></p>
                <?php if ($searchQuery === ''): ?>
                    <a href="upload.php" class="btn btn-primary" style="margin-top:20px;"><i class="fas fa-plus"></i> Upload First Certificate</a>
                <?php endif; ?>
            </div>
        <?php endif; ?>

    </main>
</div>

<script src="../script.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>