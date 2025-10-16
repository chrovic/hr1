<?php
require_once __DIR__ . '/embeddings.php';
require_once __DIR__ . '/../data/db.php';

class RecommendationService {
    private $db;
    private EmbeddingsService $embedder;

    public function __construct() {
        $this->db = getDB();
        $this->embedder = new EmbeddingsService();
    }

    // Compute or fetch an embedding for free text
    private function getTextEmbedding(string $text): ?array {
        return $this->embedder->embedText($text);
    }

    // Get top-N recommended modules for an employee given competency gaps
    public function recommendForEmployee(int $employeeId, int $limit = 10): array {
        // 1) Get employee competency gaps (simple heuristic: low-score competencies)
        $stmt = $this->db->prepare("SELECT c.name, ec.score FROM employee_competencies ec JOIN competencies c ON ec.competency_id = c.id WHERE ec.employee_id = ? ORDER BY ec.score ASC LIMIT 5");
        try { $stmt->execute([$employeeId]); } catch (\Throwable $e) { return []; }
        $rows = $stmt->fetchAll();
        if (!$rows) return [];

        $gapPhrases = array_map(function($r) { return $r['name']; }, $rows);
        $query = 'Training for: ' . implode(', ', $gapPhrases);
        $queryVec = $this->getTextEmbedding($query);
        if (!$queryVec) return [];

        // 2) Fetch module embeddings
        $stmt = $this->db->prepare("SELECT tm.id, tm.title, tm.description, me.embedding_json FROM training_modules tm JOIN module_embeddings me ON me.module_id = tm.id WHERE tm.status = 'active'");
        try { $stmt->execute(); } catch (\Throwable $e) { return []; }
        $modules = $stmt->fetchAll();
        if (!$modules) return [];

        // 3) Score by cosine similarity
        $scored = [];
        foreach ($modules as $m) {
            $vec = json_decode($m['embedding_json'] ?? '[]', true) ?: [];
            if (!$vec) continue;
            $score = $this->embedder->cosineSimilarity($queryVec, $vec);
            $scored[] = [
                'module_id' => (int)$m['id'],
                'title' => $m['title'],
                'description' => $m['description'],
                'similarity' => $score
            ];
        }
        usort($scored, function($a, $b) { return $b['similarity'] <=> $a['similarity']; });
        return array_slice($scored, 0, $limit);
    }

    // Backfill embeddings for all modules
    public function backfillModuleEmbeddings(): int {
        $stmt = $this->db->prepare("SELECT id, title, description FROM training_modules WHERE status = 'active'");
        $stmt->execute();
        $rows = $stmt->fetchAll();
        $insert = $this->db->prepare("REPLACE INTO module_embeddings (module_id, embedding_json, updated_at) VALUES (?, ?, NOW())");

        $count = 0;
        foreach ($rows as $r) {
            $text = trim(($r['title'] ?? '') . ' \n ' . ($r['description'] ?? ''));
            if ($text === '') continue;
            $vec = $this->embedder->embedText($text);
            if (!$vec) continue;
            $insert->execute([(int)$r['id'], json_encode($vec)]);
            $count++;
        }
        return $count;
    }
}
?>


