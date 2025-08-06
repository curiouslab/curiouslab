<?php

function generateERPId($entity, $type) {
    $timestamp = date('YmdHi'); // No seconds
    $random = substr(str_shuffle('ABCDEFGHJKLMNPQRSTUVWXYZ23456789'), 0, 4);
    
    $base = "$entity-$type-$timestamp-$random";

    // Checksum: take CRC32 hash of base and reduce to 4 chars
    $checksum = strtoupper(substr(dechex(crc32($base)), 0, 4));

    return "$base-$checksum";
}

date_default_timezone_set('Europe/Brussels'); // Set your local timezone

$dataFile = 'data.json';
$uploadDir = 'uploads/';

// Get form data
$type = $_POST['type'] ?? '';
$amount = floatval($_POST['amount'] ?? 0);
$category = $_POST['category'] ?? '';
$comment = $_POST['comment'] ?? '';
$dateInput = $_POST['date'] ?? '';
$date = date('Y-m-d H:i:s', strtotime($dateInput));

// Determine sub-type (EXP or INC)
$entryTypeCode = strtoupper(substr($type, 0, 3)) === 'EXP' ? 'EXP' : 'INC';

// Handle file upload
$filename = null;
if (!empty($_FILES['file']['name'])) {
    $originalName = basename($_FILES['file']['name']);
    $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);
    $targetFile = $uploadDir . $filename;
    move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
}

// Load existing data
$data = [];
if (file_exists($dataFile)) {
    $json = file_get_contents($dataFile);
    $data = json_decode($json, true) ?? [];
}

// Append new entry with unique ERP ID
$data[] = [
    'id' => generateERPId('FIN', $entryTypeCode), // ✅ Add the unique ID
    'date' => $date,
    'type' => $type,
    'amount' => $amount,
    'category' => $category,
    'comment' => $comment,
    'file' => $filename
];

// Save back to JSON
file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT));

// Redirect back to form
header('Location: index.php');
exit;
?>
