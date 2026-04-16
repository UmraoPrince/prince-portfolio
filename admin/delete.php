<?php
ob_start();
session_start();
require_once __DIR__ . '/../db.php';

if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    header('Location: login.php');
    exit;
}

 $id = isset($_GET['id']) ? (int)$_GET['id'] : 0;

if ($id <= 0) {
    $_SESSION['flash_msg']  = 'Invalid certificate ID.';
    $_SESSION['flash_type'] = 'error';
    header('Location: dashboard.php');
    exit;
}

 $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = :id LIMIT 1");
 $stmt->execute([':id' => $id]);
 $cert = $stmt->fetch();

if (!$cert) {
    $_SESSION['flash_msg']  = 'Certificate not found.';
    $_SESSION['flash_type'] = 'error';
    header('Location: dashboard.php');
    exit;
}

 $filePath = __DIR__ . '/../' . $cert['file_path'];
if (file_exists($filePath)) {
    unlink($filePath);
}

 $stmt = $pdo->prepare("DELETE FROM certificates WHERE id = :id");
 $stmt->execute([':id' => $id]);

 $_SESSION['flash_msg']  = "Certificate {$cert['cert_id']} has been deleted.";
 $_SESSION['flash_type'] = 'success';
header('Location: dashboard.php');
exit;