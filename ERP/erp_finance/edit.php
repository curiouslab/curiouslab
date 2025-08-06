<?php
$dataFile = 'data.json';
$uploadDir = 'uploads/';
$data = [];
$entryToEdit = null;

if (file_exists($dataFile)) {
  $json = file_get_contents($dataFile);
  $data = json_decode($json, true);
}

$timestamp = isset($_GET['timestamp']) ? intval($_GET['timestamp']) : 0;

// Find the entry to edit
foreach ($data as $entry) {
  if (strtotime($entry['date']) === $timestamp) {
    $entryToEdit = $entry;
    break;
  }
}

// If form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  $newData = [];
  foreach ($data as $entry) {
    if (strtotime($entry['date']) === $timestamp) {
      // Replace this entry with the updated values
      $entry['type'] = $_POST['type'];
      $entry['amount'] = floatval($_POST['amount']);
      $entry['category'] = $_POST['category'];
      $entry['comment'] = $_POST['comment'];
      $entry['date'] = date('Y-m-d H:i:s', strtotime($_POST['date']));

      // If new file uploaded
      if (!empty($_FILES['file']['name'])) {
        $originalName = basename($_FILES['file']['name']);
        $filename = time() . '_' . preg_replace('/[^a-zA-Z0-9_.-]/', '_', $originalName);
        $targetFile = $uploadDir . $filename;
        move_uploaded_file($_FILES['file']['tmp_name'], $targetFile);
        $entry['file'] = $filename;
      }

      $newData[] = $entry;
    } else {
      $newData[] = $entry;
    }
  }

  file_put_contents($dataFile, json_encode($newData, JSON_PRETTY_PRINT));
  header('Location: index.php');
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
          <p class="mt-1">Current: <a href="uploads/<?= urlencode($entryToEdit['file']) ?>" target="_blank">📎</a></p>
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
