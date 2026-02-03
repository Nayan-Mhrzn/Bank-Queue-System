<?php
$host = 'localhost';
$user = 'root';
$pass = '';
$dbname = 'bank_queue_db';

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}

function ensure_schema() {
    global $conn;

    $required = ['users', 'services', 'counters', 'tokens'];
    $missing = [];
    foreach ($required as $table) {
        $res = $conn->query("SHOW TABLES LIKE '{$table}'");
        if (!$res || $res->num_rows === 0) {
            $missing[] = $table;
        }
    }
    if (count($missing) > 0) {
        $schemaPath = __DIR__ . '/../sql/database.sql';
        if (!file_exists($schemaPath)) {
            die('Schema file missing: ' . $schemaPath);
        }

        $sql = file_get_contents($schemaPath);
        $lines = preg_split("/\\r\\n|\\n|\\r/", $sql);
        $filtered = [];

        foreach ($lines as $line) {
            $trimmed = trim($line);
            if ($trimmed === '' || str_starts_with($trimmed, '--')) {
                continue;
            }
            if (preg_match('/^(CREATE DATABASE|USE|DROP TABLE)/i', $trimmed)) {
                continue;
            }
            $filtered[] = $line;
        }

        $statements = explode(';', implode("\n", $filtered));
        foreach ($statements as $statement) {
            $statement = trim($statement);
            if ($statement === '') {
                continue;
            }
            if (stripos($statement, 'CREATE TABLE') === 0) {
                $statement = preg_replace('/^CREATE TABLE\\s+/i', 'CREATE TABLE IF NOT EXISTS ', $statement);
            }

            if (stripos($statement, 'INSERT INTO services') === 0) {
                $countRes = $conn->query("SELECT COUNT(*) AS c FROM services");
                $countRow = $countRes ? $countRes->fetch_assoc() : null;
                if ($countRow && (int)$countRow['c'] > 0) {
                    continue;
                }
            }

            if (stripos($statement, 'INSERT INTO counters') === 0) {
                $countRes = $conn->query("SELECT COUNT(*) AS c FROM counters");
                $countRow = $countRes ? $countRes->fetch_assoc() : null;
                if ($countRow && (int)$countRow['c'] > 0) {
                    continue;
                }
            }

            if ($conn->query($statement) !== true) {
                die('Schema setup failed: ' . $conn->error);
            }
        }
    }

    $legacyMap = [
        'users' => 'user_id',
        'services' => 'service_id',
        'counters' => 'counter_id',
        'tokens' => 'token_id',
    ];

    foreach ($legacyMap as $table => $legacyColumn) {
        $tRes = $conn->query("SHOW TABLES LIKE '{$table}'");
        if (!$tRes || $tRes->num_rows === 0) {
            continue;
        }

        $hasId = $conn->query("SHOW COLUMNS FROM {$table} LIKE 'id'");
        if ($hasId && $hasId->num_rows > 0) {
            continue;
        }

        $hasLegacy = $conn->query("SHOW COLUMNS FROM {$table} LIKE '{$legacyColumn}'");
        if (!$hasLegacy || $hasLegacy->num_rows === 0) {
            continue;
        }

        if ($conn->query("ALTER TABLE {$table} ADD COLUMN id INT NULL") !== true) {
            die('Schema setup failed: ' . $conn->error);
        }
        if ($conn->query("UPDATE {$table} SET id = {$legacyColumn} WHERE id IS NULL") !== true) {
            die('Schema setup failed: ' . $conn->error);
        }
        $idxRes = $conn->query("SHOW INDEX FROM {$table} WHERE Key_name = 'idx_{$table}_id'");
        if (!$idxRes || $idxRes->num_rows === 0) {
            if ($conn->query("CREATE UNIQUE INDEX idx_{$table}_id ON {$table}(id)") !== true) {
                die('Schema setup failed: ' . $conn->error);
            }
        }
    }

    // Legacy column compatibility for counters table name
    $countersRes = $conn->query("SHOW TABLES LIKE 'counters'");
    if ($countersRes && $countersRes->num_rows > 0) {
        $hasName = $conn->query("SHOW COLUMNS FROM counters LIKE 'name'");
        $hasCounterName = $conn->query("SHOW COLUMNS FROM counters LIKE 'counter_name'");
        if ((!$hasName || $hasName->num_rows === 0) && ($hasCounterName && $hasCounterName->num_rows > 0)) {
            if ($conn->query("ALTER TABLE counters ADD COLUMN name VARCHAR(100) NULL") !== true) {
                die('Schema setup failed: ' . $conn->error);
            }
            if ($conn->query("UPDATE counters SET name = counter_name WHERE name IS NULL") !== true) {
                die('Schema setup failed: ' . $conn->error);
            }
        }
    }

    // Ensure id mirrors legacy primary keys if id exists but is NULL
    foreach ($legacyMap as $table => $legacyColumn) {
        $tRes = $conn->query("SHOW TABLES LIKE '{$table}'");
        if (!$tRes || $tRes->num_rows === 0) {
            continue;
        }
        $hasId = $conn->query("SHOW COLUMNS FROM {$table} LIKE 'id'");
        $hasLegacy = $conn->query("SHOW COLUMNS FROM {$table} LIKE '{$legacyColumn}'");
        if ($hasId && $hasId->num_rows > 0 && $hasLegacy && $hasLegacy->num_rows > 0) {
            if ($conn->query("UPDATE {$table} SET id = {$legacyColumn} WHERE id IS NULL") !== true) {
                die('Schema setup failed: ' . $conn->error);
            }
        }
    }

    // Ensure status column exists in users (Auto-migration)
    $usersRes = $conn->query("SHOW TABLES LIKE 'users'");
    if ($usersRes && $usersRes->num_rows > 0) {
        $hasStatus = $conn->query("SHOW COLUMNS FROM users LIKE 'status'");
        if (!$hasStatus || $hasStatus->num_rows === 0) {
            if ($conn->query("ALTER TABLE users ADD COLUMN status ENUM('ONLINE','BREAK','OFFLINE') DEFAULT 'OFFLINE'") !== true) {
                die('Schema setup failed: ' . $conn->error);
            }
            $conn->query("UPDATE users SET status = 'ONLINE' WHERE is_active = 1");
        }
    }
}

ensure_schema();

function db_query($sql, $params = [], $types = '') {
    global $conn;
    if (empty($params)) {
        return $conn->query($sql);
    }
    if ($types === '') {
        $types = str_repeat('s', count($params));
    }
    $stmt = $conn->prepare($sql);
    if (!$stmt) {
        die('Prepare failed: ' . $conn->error);
    }
    $stmt->bind_param($types, ...$params);
    $stmt->execute();
    return $stmt->get_result();
}
?>
