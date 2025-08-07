<?php
$uploadDir = 'uploads/';
$data = [];
$entryToEdit = null;

try {
    $db = new PDO('sqlite:erp.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $id = $_GET['id'] ?? '';

	// Handle delete file action
if (isset($_GET['action']) && $_GET['action'] === 'delete_file' && $id) {
    // Get filename
    $stmt = $db->prepare("SELECT file FROM finance_entries WHERE id = :id");
    $stmt->execute([':id' => $id]);
    $entry = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($entry && !empty($entry['file'])) {
        $filePath = $uploadDir . $entry['file'];
        if (file_exists($filePath)) {
            unlink($filePath);
        }

        // Remove file reference from database
        $stmt = $db->prepare("UPDATE finance_entries SET file = NULL WHERE id = :id");
        $stmt->execute([':id' => $id]);
    }

    // Redirect to edit page again (to clean URL)
    header("Location: edit.php?id=" . urlencode($id));
    exit;
}


    // Load entry to edit
    if ($id) {
        $stmt = $db->prepare("SELECT * FROM finance_entries WHERE id = :id");
        $stmt->execute([':id' => $id]);
        $entryToEdit = $stmt->fetch(PDO::FETCH_ASSOC);
    }

    // Handle form submission
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && $entryToEdit) {
        $stmt = $db->prepare("UPDATE finance_entries SET 
            type = :type,
            amount = :amount,
            category = :category,
            comment = :comment,
            date = :date,
            file = :file
            WHERE id = :id");

        $filename = $entryToEdit['file']; // Keep old file unless a new one is uploaded

        if (!empty($_FILES['file']['name'])) {
            $originalName = basename($_FILES['file']['name']);
            $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);
            $targetFile = $uploadDir . $filename;
            move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
        }

        $stmt->execute([
            ':type' => $_POST['type'],
            ':amount' => floatval($_POST['amount']),
            ':category' => $_POST['category'],
            ':comment' => $_POST['comment'],
            ':date' => date('Y-m-d H:i:s', strtotime($_POST['date'])),
            ':file' => $filename,
            ':id' => $_POST['id']
        ]);

        header('Location: index.php');
        exit;
    }

} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
    exit;
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>Edit Entry</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
</head>
<body class="bg-light">
  <div class="container mt-5">
    <h2 class="mb-4">✏️ Edit Entry</h2>

    <?php if ($entryToEdit): ?>
    <form method="POST" enctype="multipart/form-data" class="row g-3">
		<input type="hidden" name="id" value="<?= htmlspecialchars($entryToEdit['id']) ?>">

      <div class="col-md-3">
        <label for="type" class="form-label">Type</label>
        <select class="form-select" name="type" id="type" required>
          <option value="income" <?= $entryToEdit['type'] === 'income' ? 'selected' : '' ?>>Income</option>
          <option value="expense" <?= $entryToEdit['type'] === 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>
      </div>

      <div class="col-md-3">
        <label for="amount" class="form-label">Amount (€)</label>
        <input type="number" min="0.01" step="0.01" class="form-control" name="amount" value="<?= $entryToEdit['amount'] ?>" required>
      </div>

      <div class="col-md-3">
        <label for="category" class="form-label">Category</label>
        <input type="text" class="form-control" name="category" value="<?= htmlspecialchars($entryToEdit['category']) ?>" required>
      </div>

      <div class="col-md-3">
        <label for="file" class="form-label">Replace File (optional)</label>
        <input type="file" class="form-control" name="file">
        <?php if (!empty($entryToEdit['file'])): ?>
  <div class="mt-1 d-flex gap-2 align-items-center">
    <span>Current: <a href="uploads/<?= urlencode($entryToEdit['file']) ?>" target="_blank">📎</a></span>
    <a href="edit.php?action=delete_file&id=<?= urlencode($entryToEdit['id']) ?>"
       class="btn btn-sm btn-outline-danger"
       onclick="return confirm('Are you sure you want to delete this file?');">
      ❌ Delete File
    </a>
  </div>
<?php endif; ?>

      </div>

      <div class="col-md-3">
        <label for="date" class="form-label">Date & Time</label>
        <input type="datetime-local" class="form-control" name="date"
               value="<?= date('Y-m-d\TH:i', strtotime($entryToEdit['date'])) ?>" required>
      </div>

      <div class="col-12">
        <label for="comment" class="form-label">Comment</label>
        <textarea class="form-control" name="comment" rows="2"><?= htmlspecialchars($entryToEdit['comment']) ?></textarea>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-success">💾 Save Changes</button>
        <a href="index.php" class="btn btn-secondary">← Cancel</a>
      </div>
    </form>

    <?php else: ?>
      <div class="alert alert-danger">Entry not found.</div>
      <a href="index.php" class="btn btn-secondary">← Back</a>
    <?php endif; ?>
  </div>
</body>
</html>
