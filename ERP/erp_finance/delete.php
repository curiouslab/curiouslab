<?php
$dataFile = 'data.json';
$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $timestampToDelete = intval($_POST['timestamp'] ?? 0);
  
  if (file_exists($dataFile)) {
    $data = json_decode(file_get_contents($dataFile), true);
    $newData = [];

    foreach ($data as $entry) {
      $entryTimestamp = strtotime($entry['date']);
      if ($entryTimestamp !== $timestampToDelete) {
        $newData[] = $entry;
      } else {
        // Delete file if it exists
        if (!empty($entry['file'])) {
          $filePath = $uploadDir . $entry['file'];
          if (file_exists($filePath)) {
            unlink($filePath);
          }
        }
      }
    }

    file_put_contents($dataFile, json_encode($newData, JSON_PRETTY_PRINT));
  }
}

header('Location: index.php');
exit;
