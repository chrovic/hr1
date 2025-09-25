<?php
require_once 'includes/data/db.php';

try {
    $db = getDB();
    
    // Check training catalog
    $stmt = $db->query('SELECT COUNT(*) as count FROM training_catalog');
    $catalogCount = $stmt->fetch()['count'];
    echo "Training Catalog Entries: $catalogCount\n";
    
    // Check training sessions
    $stmt = $db->query('SELECT COUNT(*) as count FROM training_sessions');
    $sessionsCount = $stmt->fetch()['count'];
    echo "Training Sessions: $sessionsCount\n";
    
    // Check training enrollments
    $stmt = $db->query('SELECT COUNT(*) as count FROM training_enrollments');
    $enrollmentsCount = $stmt->fetch()['count'];
    echo "Training Enrollments: $enrollmentsCount\n";
    
    // Show sample data if exists
    if ($catalogCount > 0) {
        echo "\nSample Training Catalog:\n";
        $stmt = $db->query('SELECT id, title, category, type FROM training_catalog LIMIT 3');
        while ($row = $stmt->fetch()) {
            echo "- ID: {$row['id']}, Title: {$row['title']}, Category: {$row['category']}, Type: {$row['type']}\n";
        }
    }
    
} catch (Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
?>




