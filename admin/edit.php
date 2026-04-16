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

 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) {
    $_SESSION['flash_msg'] = 'Invalid certificate ID.';
    $_SESSION['flash_type'] = 'error';
    header('Location: dashboard.php');
    exit;
}

 $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = :id LIMIT 1");
 $stmt->execute([':id' => $id]);
 $cert = $stmt->fetch();

if (!$cert) {
    $_SESSION['flash_msg'] = 'Certificate not found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: dashboard.php');
    exit;
}

 $formData = ['name' => $cert['name'], 'course' => $cert['course'], 'issuer' => $cert['issuer'], 'date' => $cert['date']];
 $errors = [];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name   = trim($_POST['name'] ?? '');
    $course = trim($_POST['course'] ?? '');
    $issuer = trim($_POST['issuer'] ?? '');
    $date   = trim($_POST['date'] ?? '');

    if ($name === '')   $errors[] = 'Name is required.';
    if ($course === '') $errors[] = 'Course is required.';
    if ($issuer === '') $errors[] = 'Issuer is required.';
    if ($date === '' || !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) $errors[] = 'Valid date is required.';

    $filePath = $cert['file_path'];

    if (isset($_FILES['file']) && $_FILES['file']['error'] !== UPLOAD_ERR_NO_FILE) {
        $upload = handleFileUpload($_FILES['file'], $cert['cert_id']);
        if (!$upload['success']) {
            $errors[] = $upload['error'];
        } else {
            $oldFile = __DIR__ . '/../' . $cert['file_path'];
            if (file_exists($oldFile)) { unlink($oldFile); }
            $filePath = $upload['path'];
        }
    }

    if (empty($errors)) {
        try {
            $stmt = $pdo->prepare("UPDATE certificates SET name=:n, course=:c, issuer=:i, date=:d, file_path=:fp WHERE id=:id");
            $stmt->execute([':n' => $name, ':c' => $course, ':i' => $issuer, ':d' => $date, ':fp' => $filePath, ':id' => $id]);
            $_SESSION['flash_msg']  = "Certificate {$cert['cert_id']} updated successfully.";
            $_SESSION['flash_type'] = 'success';
            header('Location: dashboard.php');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Database error. Please try again.';
        }
    }
    $formData = ['name' => $name, 'course' => $course, 'issuer' => $issuer, 'date' => $date];
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Certificate</title>
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
            <div class="admin-topbar-title"><i class="fas fa-shield-halved"></i> Admin Panel</div>
            <nav class="admin-topbar-nav">
                <a href="dashboard.php"><i class="fas fa-th-large"></i> Dashboard</a>
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
        <a href="dashboard.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Dashboard</a>

        <?php if (!empty($errors)): ?>
            <div style="background:var(--danger-bg);border:1px solid rgba(255,82,82,0.2);color:var(--danger);padding:16px 20px;border-radius:var(--radius-md);margin-bottom:24px;font-size:14px;">
                <strong><i class="fas fa-exclamation-circle"></i> Errors:</strong>
                <ul style="margin:8px 0 0 20px;"><?php foreach ($errors as $err): ?><li><?= htmlspecialchars($err) ?></li><?php endforeach; ?></ul>
            </div>
        <?php endif; ?>

        <div class="glass form-card" style="max-width:600px;">
            <h3><i class="fas fa-pen" style="color:var(--accent-cyan);margin-right:8px;"></i> Edit Certificate</h3>
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label>Certificate ID</label>
                    <div class="cert-id-display"><?= htmlspecialchars($cert['cert_id']) ?></div>
                </div>
                <div class="form-group">
                    <label for="name">Recipient Name</label>
                    <input type="text" id="name" name="name" class="form-control" value="<?= htmlspecialchars($formData['name']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="course">Course Name</label>
                    <input type="text" id="course" name="course" class="form-control" value="<?= htmlspecialchars($formData['course']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="issuer">Issuing Organization</label>
                    <input type="text" id="issuer" name="issuer" class="form-control" value="<?= htmlspecialchars($formData['issuer']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="date">Issue Date</label>
                    <input type="date" id="date" name="date" class="form-control" value="<?= htmlspecialchars($formData['date']) ?>" required>
                </div>
                <div class="form-group">
                    <label for="file">Replace Certificate File (Optional)</label>
                    <div class="file-input-wrapper">
                        <input type="file" id="file" name="file" accept=".pdf,.jpg,.jpeg,.png">
                    </div>
                    <p class="file-hint">Leave empty to keep current file. Accepted: PDF, JPG, PNG &bull; Max: 5MB</p>
                    <?php
                    $currentFile = __DIR__ . '/../' . $cert['file_path'];
                    if (file_exists($currentFile)):
                        $ext = strtolower(pathinfo($cert['file_path'], PATHINFO_EXTENSION));
                    ?>
                        <p style="margin-top:8px;font-size:12px;color:var(--text-muted);">
                            Current: <span style="color:var(--accent-cyan);"><?= htmlspecialchars(basename($cert['file_path'])) ?></span> (<?= strtoupper($ext) ?>)
                        </p>
                    <?php endif; ?>
                </div>
                <div style="display:flex;gap:12px;margin-top:8px;">
                    <button type="submit" class="btn btn-primary" style="flex:1;justify-content:center;"><i class="fas fa-save"></i> Save Changes</button>
                    <a href="dashboard.php" class="btn btn-outline">Cancel</a>
                </div>
            </form>
        </div>
    </main>
</div>

<script src="../script.js"></script>
</body>
</html>
<?php ob_end_flush(); ?>