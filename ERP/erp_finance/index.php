<?php
$data = [];
if (file_exists('data.json')) {
  $json = file_get_contents('data.json');
  $data = json_decode($json, true);
}

// Filters for the table
$filterType = $_GET['type'] ?? 'all';
$filterMonth = $_GET['month'] ?? 'all';
$filterCategory = $_GET['category'] ?? 'all';

// Filters for the chart
$selectedMonths = $_GET['chart_months'] ?? [date('Y-m')];
if (!is_array($selectedMonths)) $selectedMonths = [$selectedMonths];

// Prepare dropdown options
$yearMonthOptions = [];
$categoryOptions = [];
foreach ($data as $entry) {
  $ym = substr($entry['date'], 0, 7);
  $cat = strtolower($entry['category']);
  $yearMonthOptions[$ym] = true;
  $categoryOptions[$cat] = true;
}
$yearMonthOptions = array_keys($yearMonthOptions);
$categoryOptions = array_keys($categoryOptions);
sort($yearMonthOptions);
sort($categoryOptions);

// Search by ERP ID
$searchId = $_GET['search_id'] ?? null;

// Apply table filters + optional ERP ID search
$filteredData = array_filter($data, function($entry) use ($filterType, $filterMonth, $filterCategory, $searchId) {
  $entryMonth = substr($entry['date'], 0, 7);
  $entryCat = strtolower($entry['category']);

  if (!empty($searchId)) {
    return isset($entry['id']) && strcasecmp(trim($entry['id']), trim($searchId)) === 0;
  }

  if ($filterType !== 'all' && $entry['type'] !== $filterType) return false;
  if ($filterMonth !== 'all' && $entryMonth !== $filterMonth) return false;
  if ($filterCategory !== 'all' && strtolower($entryCat) !== $filterCategory) return false;

  return true;
});

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
  <title>Personal Finance Dashboard</title>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/qrcodejs/qrcode.min.js"></script>

</head>
<body class="bg-light">
  <div class="container mt-5">

    <h2 class="mb-4">💰 Finance - Input Entry</h2>

    <?php $currentDateTime = date('Y-m-d\TH:i'); ?>
    <form action="backend.php" method="POST" enctype="multipart/form-data" class="row g-3 mb-5">
      <div class="col-md-3">
        <label for="type" class="form-label">Type</label>
        <select class="form-select" id="type" name="type" required>
          <option value="income">Income</option>
          <option value="expense">Expense</option>
        </select>
      </div>

      <div class="col-md-3">
        <label for="amount" class="form-label">Amount (€)</label>
        <input type="number" min="0.01" step="0.01" class="form-control" id="amount" name="amount" required>
      </div>

      <div class="col-md-3">
        <label for="category" class="form-label">Category</label>
        <input type="text" class="form-control" id="category" name="category" required>
      </div>

      <div class="col-md-3">
        <label for="file" class="form-label">Attach file (optional)</label>
        <input type="file" class="form-control" id="file" name="file">
      </div>

      <div class="col-md-3">
        <label for="date" class="form-label">Date & Time</label>
        <input type="datetime-local" class="form-control" id="date" name="date" value="<?= $currentDateTime ?>" required>
      </div>

      <div class="col-12">
        <label for="comment" class="form-label">Comment</label>
        <textarea class="form-control" id="comment" name="comment" rows="2"></textarea>
      </div>

      <div class="col-12">
        <button type="submit" class="btn btn-primary">💾 Save Entry</button>
      </div>
    </form>

    <!-- Table Filters -->
    <form method="GET" class="row g-3 mb-4">
      <div class="col-md-3">
        <label class="form-label">Filter by Type</label>
        <select class="form-select" name="type">
          <option value="all" <?= $filterType === 'all' ? 'selected' : '' ?>>All</option>
          <option value="income" <?= $filterType === 'income' ? 'selected' : '' ?>>Income</option>
          <option value="expense" <?= $filterType === 'expense' ? 'selected' : '' ?>>Expense</option>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Filter by Month</label>
        <select class="form-select" name="month">
          <option value="all" <?= $filterMonth === 'all' ? 'selected' : '' ?>>All</option>
          <?php foreach ($yearMonthOptions as $ym): ?>
            <option value="<?= $ym ?>" <?= $filterMonth === $ym ? 'selected' : '' ?>><?= $ym ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Filter by Category</label>
        <select class="form-select" name="category">
          <option value="all" <?= $filterCategory === 'all' ? 'selected' : '' ?>>All</option>
          <?php foreach ($categoryOptions as $cat): ?>
            <option value="<?= $cat ?>" <?= $filterCategory === $cat ? 'selected' : '' ?>><?= ucfirst($cat) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3 d-flex align-items-end">
        <button class="btn btn-secondary w-100" type="submit">Apply Filters</button>
      </div>
    </form>

<form method="GET" class="mb-3" id="idSearchForm">
  <div class="input-group" style="max-width: 400px;">
    <input type="text" class="form-control" name="search_id" placeholder="Search by ERP ID..." value="<?= htmlspecialchars($_GET['search_id'] ?? '') ?>">
    <button class="btn btn-outline-primary" type="submit">Search</button>
    <?php if (!empty($_GET['search_id'])): ?>
      <a href="index.php" class="btn btn-outline-secondary">Clear</a>
    <?php endif; ?>
  </div>
</form>

<?php if (empty($filteredData)): ?>
  <div class="alert alert-warning">No entry found<?php if (!empty($searchId)): ?> for ID "<?= htmlspecialchars($searchId) ?>"<?php endif; ?>.</div>
<?php endif; ?>


    <!-- Entries Table -->
    <h3>📋 Entries</h3>
<div style="max-height: 300px; overflow-y: auto;">
  <table class="table table-striped table-hover table-bordered">
    <thead class="table-light" style="position: sticky; top: 0; z-index: 1;">
      <tr>
        <th>Date</th>
        <th>Type</th>
        <th>Amount (€)</th>
        <th>Category</th>
        <th>Comment</th>
        <th>File</th>
        <th>Actions</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach (array_reverse($filteredData) as $entry): ?>
      <tr>
        <td><?= htmlspecialchars($entry['date']) ?></td>
        <td><?= htmlspecialchars($entry['type']) ?></td>
        <td><?= number_format($entry['amount'], 2) ?></td>
        <td><?= htmlspecialchars($entry['category']) ?></td>
        <td><?= htmlspecialchars($entry['comment']) ?></td>
        <td>
          <?php if (!empty($entry['file'])): ?>
            <a href="uploads/<?= urlencode($entry['file']) ?>" target="_blank">📎</a>
          <?php endif; ?>
        </td>
        <td class="d-flex gap-1">
          <form action="delete.php" method="POST" onsubmit="return confirm('Delete this entry?');">
            <input type="hidden" name="timestamp" value="<?= strtotime($entry['date']) ?>">
            <button type="submit" class="btn btn-sm btn-danger">🗑️</button>
          </form>
          <form action="edit.php" method="GET">
            <input type="hidden" name="timestamp" value="<?= strtotime($entry['date']) ?>">
            <button type="submit" class="btn btn-sm btn-warning">✏️</button>
          </form>
		  <button type="button"
        class="btn btn-sm btn-success btn-show-qr"
        data-timestamp="<?= strtotime($entry['date']) ?>">
  📎
</button>


		  
        </td>
      </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>


    <!-- Chart Month Selector -->
    <form method="GET" class="mb-4">
      <input type="hidden" name="type" value="<?= htmlspecialchars($filterType) ?>">
      <input type="hidden" name="month" value="<?= htmlspecialchars($filterMonth) ?>">
      <input type="hidden" name="category" value="<?= htmlspecialchars($filterCategory) ?>">

      <label class="form-label">📊 Select months to display in chart:</label>
      <div class="d-flex flex-wrap gap-2 align-items-end">
        <div style="min-width: 250px; max-width: 300px;">
          <select name="chart_months[]" multiple class="form-select" size="6" style="width: 100%; max-height: 180px; overflow-y: auto;">
            <option value="TOTAL" <?= in_array('TOTAL', $selectedMonths) ? 'selected' : '' ?>>TOTAL (all selected)</option>
            <?php foreach ($yearMonthOptions as $month): ?>
              <option value="<?= $month ?>" <?= in_array($month, $selectedMonths) ? 'selected' : '' ?>>
                <?= $month ?>
              </option>
            <?php endforeach; ?>
          </select>
        </div>
        <div>
          <button type="submit" class="btn btn-primary">📊 Show Chart</button>
        </div>
      </div>
    </form>

    <!-- Chart -->
    <div class="container my-5">
      <h3>📈 Monthly Summary</h3>
      <canvas id="financeChart" height="100"></canvas>
    </div>

    <?php
    $monthlyData = [];
    $totalIncome = 0;
    $totalExpense = 0;

    foreach ($data as $entry) {
      $monthKey = substr($entry['date'], 0, 7);

      if (!in_array($monthKey, $selectedMonths) && !in_array('TOTAL', $selectedMonths)) {
        continue;
      }

      $totalIncome += $entry['type'] === 'income' ? $entry['amount'] : 0;
      $totalExpense += $entry['type'] === 'expense' ? $entry['amount'] : 0;

      if (in_array($monthKey, $selectedMonths)) {
        if (!isset($monthlyData[$monthKey])) {
          $monthlyData[$monthKey] = ['income' => 0, 'expense' => 0];
        }
        $monthlyData[$monthKey][$entry['type']] += $entry['amount'];
      }
    }

    if (in_array('TOTAL', $selectedMonths)) {
      $monthlyData['TOTAL'] = ['income' => $totalIncome, 'expense' => $totalExpense];
    }

    ksort($monthlyData);
    $labels = array_keys($monthlyData);
    $incomeData = array_column($monthlyData, 'income');
    $expenseData = array_column($monthlyData, 'expense');
    ?>

    <script>
      const ctx = document.getElementById('financeChart').getContext('2d');
      const financeChart = new Chart(ctx, {
        type: 'bar',
        data: {
          labels: <?= json_encode($labels) ?>,
          datasets: [
            {
              label: 'Income (€)',
              data: <?= json_encode($incomeData) ?>,
              backgroundColor: 'rgba(75, 192, 192, 0.6)'
            },
            {
              label: 'Expense (€)',
              data: <?= json_encode($expenseData) ?>,
              backgroundColor: 'rgba(255, 99, 132, 0.6)'
            }
          ]
        },
        options: {
          responsive: true,
          scales: {
            y: { beginAtZero: true }
          }
        }
      });
    </script>
	
	
<script>
function crc32(str) {
  let crc = 0 ^ (-1);
  for (let i = 0; i < str.length; i++) {
    let byte = str.charCodeAt(i);
    crc = (crc >>> 8) ^ crc32Table[(crc ^ byte) & 0xFF];
  }
  return (crc ^ (-1)) >>> 0;
}

const crc32Table = (() => {
  let c, table = [];
  for (let n = 0; n < 256; n++) {
    c = n;
    for (let k = 0; k < 8; k++) {
      c = (c & 1) ? (0xEDB88320 ^ (c >>> 1)) : (c >>> 1);
    }
    table[n] = c;
  }
  return table;
})();
</script>


<script>
document.addEventListener('DOMContentLoaded', function () {
  const qrButtons = document.querySelectorAll('.btn-show-qr');
  const qrContainer = document.getElementById('qrContainer');

  qrButtons.forEach(button => {
    button.addEventListener('click', function () {
      const timestamp = this.dataset.timestamp;
      const entryData = <?= json_encode($filteredData) ?>;
      const entry = entryData.find(e => new Date(e.date).getTime() / 1000 === parseInt(timestamp));

      if (entry) {
        // Clear previous QR
        qrContainer.innerHTML = '';

        // Generate new QR
        // Extract and validate ID before generating QR
const fullId = entry.id;
const idParts = fullId.split('-');

if (idParts.length !== 5) {
  alert("Invalid ERP ID format.");
  return;
}

const [entity, type, timestamp, rand, checksum] = idParts;
const baseId = `${entity}-${type}-${timestamp}-${rand}`;

// Compute checksum (same as PHP: CRC32 hex, 4 chars)
const computedChecksum = crc32(baseId).toString(16).toUpperCase().slice(0, 4);

// Validate checksum
if (computedChecksum !== checksum) {
  alert(`Invalid checksum.\nExpected: ${computedChecksum}\nFound: ${checksum}`);
  return;
}

// If valid, generate QR
qrContainer.innerHTML = '';
new QRCode(qrContainer, {
  text: `ERP-ID:${fullId}`,
  width: 200,
  height: 200,
});

// Show ERP ID under the QR
document.getElementById('qrIdText').textContent = entry.id;



        // Show modal
        const qrModal = new bootstrap.Modal(document.getElementById('qrModal'));
        qrModal.show();
      } else {
        alert("Entry not found.");
      }
    });
  });
});
</script>





  </div>
  
 <!-- QR Modal -->
<div class="modal fade" id="qrModal" tabindex="-1" aria-labelledby="qrModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-sm modal-dialog-centered">
    <div class="modal-content text-center">
      <div class="modal-header">
        <h5 class="modal-title" id="qrModalLabel">📎 QR Code</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
      </div>
      <div class="modal-body text-center">
  <div id="qrContainer"></div>
  <div id="qrIdText" class="mt-3 text-muted" style="font-family: monospace; font-size: 0.9em;"></div>
</div>

    </div>
  </div>
</div>


  
</body>
</html>
