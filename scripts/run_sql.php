<?php
require_once __DIR__ . '/../includes/data/db.php';

if (php_sapi_name() !== 'cli') {
	echo "Run from CLI" . PHP_EOL;
	exit(1);
}

$path = $argv[1] ?? '';
if (!$path || !file_exists($path)) {
	echo "Usage: php scripts/run_sql.php <path-to-sql>" . PHP_EOL;
	echo "File not found: $path" . PHP_EOL;
	exit(1);
}

try {
	$db = getDB();
	$sql = file_get_contents($path);
	$db->exec($sql);
	echo "Executed SQL: $path" . PHP_EOL;
	exit(0);
} catch (Throwable $e) {
	echo "Error executing SQL: " . $e->getMessage() . PHP_EOL;
	exit(2);
}
