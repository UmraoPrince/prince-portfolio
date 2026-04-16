<?php
 $pageTitle = 'Certificate Verification';
require_once 'db.php';

 $cert = null;
 $valid = false;
 $error = null;
 $certIdInput = '';

// Support both GET (URL param) and POST (form)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cert_id'])) {
    $certIdInput = trim($_POST['cert_id']);
} elseif (isset($_GET['cert_id'])) {
    $certIdInput = trim($_GET['cert_id']);
}

if ($certIdInput !== '') {
    // Validate input format
    if (preg_match('/^[A-Za-z0-9\-]+$/', $certIdInput)) {
        $stmt = $pdo->prepare("SELECT * FROM certificates WHERE cert_id = :cert_id LIMIT 1");
        $stmt->execute([':cert_id' => $certIdInput]);
        $cert = $stmt->fetch();

        if ($cert) {
            $valid = true;
        }
    } else {
        $error = 'Invalid certificate ID format.';
    }
}
?>
<?php require_once 'header.php'; ?>

<div class="page-content">
    <div class="verify-page">

        <a href="index.php" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to Portfolio
        </a>

        <?php if ($error): ?>
            <!-- Format Error -->
            <div class="verify-result glass">
                <div class="verify-status invalid">
                    <i class="fas fa-exclamation-triangle"></i> Error
                </div>
                <p style="color:var(--text-secondary); margin-bottom:24px;"><?= e($error) ?></p>
                <a href="index.php#verify" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Try Again
                </a>
            </div>

        <?php elseif ($certIdInput === ''): ?>
            <!-- No ID provided -->
            <div class="verify-result glass">
                <div class="verify-status" style="color:var(--warning); background:rgba(255,193,7,0.1); border:1px solid rgba(255,193,7,0.2);">
                    <i class="fas fa-info-circle"></i> No Certificate ID
                </div>
                <p style="color:var(--text-secondary); margin-bottom:24px;">
                    Please provide a Certificate ID to verify. You can search from the portfolio or enter it directly.
                </p>
                <a href="index.php#verify" class="btn btn-primary">
                    <i class="fas fa-search"></i> Go to Verify
                </a>
            </div>

        <?php elseif ($valid): ?>
            <!-- VERIFIED -->
            <div class="verify-result glass fade-up">
                <div class="verify-status valid">
                    <i class="fas fa-check-circle"></i> Certificate Verified
                </div>
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:28px;">
                    This certificate has been validated and is authentic.
                </p>

                <div class="verify-details glass" style="background:rgba(0,0,0,0.15);">
                    <div class="verify-detail-row">
                        <span class="verify-detail-label">Certificate ID</span>
                        <span class="verify-detail-value table-cert-id"><?= e($cert['cert_id']) ?></span>
                    </div>
                    <div class="verify-detail-row">
                        <span class="verify-detail-label">Name</span>
                        <span class="verify-detail-value"><?= e($cert['name']) ?></span>
                    </div>
                    <div class="verify-detail-row">
                        <span class="verify-detail-label">Course</span>
                        <span class="verify-detail-value"><?= e($cert['course']) ?></span>
                    </div>
                    <div class="verify-detail-row">
                        <span class="verify-detail-label">Issuer</span>
                        <span class="verify-detail-value"><?= e($cert['issuer']) ?></span>
                    </div>
                    <div class="verify-detail-row">
                        <span class="verify-detail-label">Issue Date</span>
                        <span class="verify-detail-value"><?= e(date('F d, Y', strtotime($cert['date']))) ?></span>
                    </div>
                </div>

                <!-- Certificate Preview -->
                <?php
                $filePath = $cert['file_path'];
                $ext = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                $fullPath = $filePath;
                $fileExists = file_exists($fullPath);
                ?>
                <?php if ($fileExists): ?>
                    <div class="cert-preview">
                        <?php if (in_array($ext, ['jpg', 'jpeg', 'png'])): ?>
                            <img src="<?= e($fullPath) ?>" alt="Certificate - <?= e($cert['course']) ?>">
                        <?php elseif ($ext === 'pdf'): ?>
                            <iframe src="<?= e($fullPath) ?>" title="Certificate PDF"></iframe>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Action Buttons -->
                <div class="verify-actions">
                    <?php if ($fileExists): ?>
                        <a href="<?= e($fullPath) ?>" download class="btn btn-primary">
                            <i class="fas fa-download"></i> Download Certificate
                        </a>
                        <a href="<?= e($fullPath) ?>" target="_blank" class="btn btn-outline">
                            <i class="fas fa-external-link-alt"></i> View Full
                        </a>
                    <?php endif; ?>
                    <button
                        class="btn btn-outline"
                        data-copy="<?= e('https://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . '/verify.php?cert_id=' . urlencode($cert['cert_id'])) ?>"
                        onclick=""
                    >
                        <i class="fas fa-link"></i> Copy Verification Link
                    </button>
                </div>

                <!-- QR Code -->
                <div class="qr-container">
                    <div id="qrcode"></div>
                    <span class="qr-label">Scan to Verify</span>
                </div>

                <script>
                    (function() {
                        var qrUrl = window.location.href;
                        var el = document.getElementById('qrcode');
                        if (el && typeof QRCode !== 'undefined') {
                            new QRCode(el, {
                                text: qrUrl,
                                width: 140,
                                height: 140,
                                colorDark: '#00d2ff',
                                colorLight: '#0f2027',
                                correctLevel: QRCode.CorrectLevel.M
                            });
                        }
                    })();
                </script>
            </div>

        <?php else: ?>
            <!-- INVALID -->
            <div class="verify-result glass fade-up">
                <div class="verify-status invalid">
                    <i class="fas fa-times-circle"></i> Invalid Certificate
                </div>
                <p style="color:var(--text-secondary); margin-bottom:10px;">
                    The Certificate ID <strong style="color:var(--accent-cyan);"><?= e($certIdInput) ?></strong> was not found in our system.
                </p>
                <p style="color:var(--text-muted); font-size:14px; margin-bottom:28px;">
                    This could mean the ID is incorrect, the certificate does not exist, or it has been removed.
                    Please verify the ID and try again.
                </p>
                <a href="index.php#verify" class="btn btn-outline">
                    <i class="fas fa-arrow-left"></i> Try Again
                </a>
            </div>
        <?php endif; ?>

    </div>
</div>

<?php require_once 'footer.php'; ?>