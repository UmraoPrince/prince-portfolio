<?php
 $pageTitle = 'Prince Umrao - Verified Portfolio';
require_once 'db.php';
?>
<?php require_once 'header.php'; ?>

<div class="page-content">

    <!-- ===== HERO SECTION ===== -->
    <section class="hero-section">
        <div class="hero-badge fade-up">
            <span class="dot"></span>
            Available for Opportunities
        </div>
        <h1 class="hero-name fade-up">
            <span class="gradient-text">Prince Umrao</span>
        </h1>
        <div class="hero-typing fade-up" aria-label="Full Stack Developer"></div>
        <p class="hero-desc fade-up">
            A dedicated professional with verified credentials.
            Every certificate listed below is cryptographically verifiable through our validation system.
        </p>
        <div class="hero-actions fade-up">
            <a href="#certificates" class="btn btn-primary">
                <i class="fas fa-award"></i> View Certificates
            </a>
            <a href="#verify" class="btn btn-outline">
                <i class="fas fa-search"></i> Verify Certificate
            </a>
        </div>
        <div class="scroll-indicator">
            <span>Scroll</span>
            <i class="fas fa-chevron-down"></i>
        </div>
    </section>

    <!-- ===== CERTIFICATES SECTION ===== -->
    <section class="section" id="certificates">
        <div class="section-header fade-up">
            <span class="section-tag">Achievements</span>
            <h2 class="section-title">Verified Certificates</h2>
            <p class="section-subtitle">
                Each certificate is uniquely identifiable and can be independently verified by anyone.
            </p>
        </div>

        <?php
        $stmt = $pdo->query("SELECT * FROM certificates ORDER BY date DESC");
        if ($stmt->rowCount() > 0):
        ?>
            <div class="cert-grid">
                <?php while ($cert = $stmt->fetch()): ?>
                    <div class="cert-card glass">
                        <div class="cert-card-icon">
                            <i class="fas fa-certificate"></i>
                        </div>
                        <h3 class="cert-card-title"><?= e($cert['course']) ?></h3>
                        <div class="cert-card-meta">
                            <span><i class="fas fa-user"></i> <?= e($cert['name']) ?></span>
                            <span><i class="fas fa-building"></i> <?= e($cert['issuer']) ?></span>
                            <span><i class="fas fa-calendar"></i> <?= e(date('M d, Y', strtotime($cert['date']))) ?></span>
                        </div>
                        <div class="cert-card-footer">
                            <span class="cert-id-badge"><?= e($cert['cert_id']) ?></span>
                            <a href="verify.php?cert_id=<?= urlencode($cert['cert_id']) ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-shield-halved"></i> Verify
                            </a>
                        </div>
                    </div>
                <?php endwhile; ?>
            </div>
        <?php else: ?>
            <div class="empty-state glass">
                <i class="fas fa-folder-open"></i>
                <p>No certificates have been added yet. Check back soon.</p>
            </div>
        <?php endif; ?>
    </section>

    <!-- ===== VERIFY SECTION ===== -->
    <section class="verify-section" id="verify">
        <div class="verify-form glass fade-up">
            <h3>Verify a Certificate</h3>
            <p>Enter a Certificate ID to validate its authenticity</p>
            <form action="verify.php" method="POST">
                <div class="input-group">
                    <input
                        type="text"
                        name="cert_id"
                        placeholder="e.g. PRINCE-001"
                        pattern="[A-Za-z0-9\-]+"
                        required
                        autocomplete="off"
                    >
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-search"></i> Verify
                    </button>
                </div>
            </form>
            <p class="verify-hint">
                Try: <code>PRINCE-001</code>
            </p>
        </div>
    </section>

</div>

<?php require_once 'footer.php'; ?>