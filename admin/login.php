<?php
ob_start();
session_start();

 $pageTitle = 'Admin Login';

if (isset($_SESSION['admin_logged_in']) && $_SESSION['admin_logged_in'] === true) {
    header('Location: dashboard.php');
    exit;
}

 $error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';

    if ($username === '' || $password === '') {
        $error = 'Please enter both username and password.';
    } else {
        require_once __DIR__ . '/../db.php';
        $stmt = $pdo->prepare("SELECT * FROM admin WHERE username = :u LIMIT 1");
        $stmt->execute([':u' => $username]);
        $admin = $stmt->fetch();

        if ($admin && password_verify($password, $admin['password'])) {
            session_regenerate_id(true);
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id']        = $admin['id'];
            $_SESSION['admin_username']  = $admin['username'];
            header('Location: dashboard.php');
            exit;
        } else {
            $error = 'Invalid username or password.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Poppins:wght@400;500;600;700&family=Space+Grotesk:wght@500;600;700&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="../style.css">
</head>
<body>

<canvas id="particleCanvas"></canvas>

<div class="login-wrapper">
    <div class="login-card glass">
        <div class="login-icon">
            <i class="fas fa-shield-halved"></i>
        </div>
        <h2>Admin Login</h2>
        <p class="login-subtitle">Access the certificate management panel</p>

        <?php if ($error !== ''): ?>
            <div class="login-error">
                <i class="fas fa-exclamation-circle"></i>
                <?= htmlspecialchars($error) ?>
            </div>
        <?php endif; ?>

        <form method="POST" action="">
            <div class="form-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" class="form-control" placeholder="Enter username" required autocomplete="username" autofocus>
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" class="form-control" placeholder="Enter password" required autocomplete="current-password">
            </div>
            <button type="submit" class="btn btn-primary" style="width:100%; justify-content:center; margin-top:8px;">
                <i class="fas fa-sign-in-alt"></i> Sign In
            </button>
        </form>

        <p style="margin-top:20px; font-size:13px; color:var(--text-muted);">
            <a href="../index.php" style="color:var(--text-secondary);">
                <i class="fas fa-arrow-left"></i> Back to Portfolio
            </a>
        </p>
    </div>
</div>

<script>
(function(){
    var c = document.getElementById('particleCanvas');
    if (!c) return;
    var ctx = c.getContext('2d');
    function resize(){ c.width = window.innerWidth; c.height = window.innerHeight; }
    resize();
    window.addEventListener('resize', resize);
    var pts = [];
    for(var i=0;i<50;i++){
        pts.push({x:Math.random()*c.width, y:Math.random()*c.height, vx:(Math.random()-0.5)*0.4, vy:(Math.random()-0.5)*0.4, r:Math.random()*1.5+0.5, o:Math.random()*0.4+0.15});
    }
    function draw(){
        ctx.clearRect(0,0,c.width,c.height);
        for(var i=0;i<pts.length;i++){
            var p=pts[i];
            p.x+=p.vx; p.y+=p.vy;
            if(p.x<0)p.x=c.width; if(p.x>c.width)p.x=0;
            if(p.y<0)p.y=c.height; if(p.y>c.height)p.y=0;
            ctx.beginPath(); ctx.arc(p.x,p.y,Math.max(0.3,p.r),0,Math.PI*2);
            ctx.fillStyle='rgba(0,210,255,'+p.o+')'; ctx.fill();
            for(var j=i+1;j<pts.length;j++){
                var dx=pts[i].x-pts[j].x, dy=pts[i].y-pts[j].y, d=Math.sqrt(dx*dx+dy*dy);
                if(d<120){
                    ctx.beginPath(); ctx.moveTo(pts[i].x,pts[i].y); ctx.lineTo(pts[j].x,pts[j].y);
                    ctx.strokeStyle='rgba(106,17,203,'+(1-d/120)*0.12+')'; ctx.lineWidth=0.5; ctx.stroke();
                }
            }
        }
        requestAnimationFrame(draw);
    }
    draw();
})();
</script>

</body>
</html>
<?php ob_end_flush(); ?>