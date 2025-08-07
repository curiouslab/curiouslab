<?php

function generateERPId($entity, $type) {
    $timestamp = date('YmdHi'); // No seconds
    $random = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);
    $base = "$entity-$type-$timestamp-$random";
    $checksum = strtoupper(substr(dechex(crc32($base)), 0, 4));
    return "$base-$checksum";
}

date_default_timezone_set('Europe/Brussels');
$uploadDir = 'uploads/';

$type = $_POST['type'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$category = $_POST['category'] ?? '';
$comment = $_POST['comment'] ?? '';
$dateInput = $_POST['date'] ?? '';
$date = date('Y-m-d H:i:s', strtotime($dateInput));

$entryTypeCode = strtoupper(substr($type, 0, 3)) === 'EXP' ? 'EXP' : 'INC';

$filename = null;
if (!empty($_FILES['file']['name'])) {
    $originalName = basename($_FILES['file']['name']);
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);
    $targetFile = $uploadDir . $filename;
    move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
}

// Save to SQLite
try {
    $db = new PDO('sqlite:erp.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $stmt = $db->prepare("INSERT INTO finance_entries (id, date, type, amount, category, comment, file)
                          VALUES (:id, :date, :type, :amount, :category, :comment, :file)");

    $stmt->execute([
        ':id' => generateERPId('FIN', $entryTypeCode),
        ':date' => $date,
        ':type' => $type,
        ':amount' => $amount,
        ':category' => $category,
        ':comment' => $comment,
        ':file' => $filename
    ]);

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    exit;
}

header('Location: index.php');
exit;
?>
