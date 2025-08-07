<?php
$uploadDir = 'uploads/';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $idToDelete = $_POST['id'] ?? '';

    if ($idToDelete) {
        try {
            $db = new PDO('sqlite:erp.db');
            $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

            // First get the file name to delete it
            $stmt = $db->prepare("SELECT file FROM finance_entries WHERE id = :id");
            $stmt->execute([':id' => $idToDelete]);
            $entry = $stmt->fetch(PDO::FETCH_ASSOC);

            if ($entry && !empty($entry['file'])) {
                $filePath = $uploadDir . $entry['file'];
                if (file_exists($filePath)) {
                    unlink($filePath);
                }
            }

            // Then delete the entry
            $stmt = $db->prepare("DELETE FROM finance_entries WHERE id = :id");
            $stmt->execute([':id' => $idToDelete]);

        } catch (PDOException $e) {
            echo "❌ Error: " . $e->getMessage();
            exit;
        }
    }
}

header('Location: index.php');
exit;
?>
