<?php
try {
    $db = new PDO('sqlite:erp.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $db->exec("
        CREATE TABLE IF NOT EXISTS finance_entries (
            id TEXT PRIMARY KEY,
            date TEXT,
            type TEXT,
            amount REAL,
            category TEXT,
            comment TEXT,
            file TEXT
        )
    ");

    echo "✅ Database initialized successfully.";
} catch (PDOException $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
