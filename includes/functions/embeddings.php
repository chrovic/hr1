<?php
require_once __DIR__ . '/../../config/huggingface_config.php';
require_once __DIR__ . '/../data/db.php';

class EmbeddingsService {
    private string $apiKey;
    private string $model;

    public function __construct(string $model = 'sentence-transformers/all-MiniLM-L6-v2') {
        $this->apiKey = defined('HUGGINGFACE_API_KEY') ? HUGGINGFACE_API_KEY : '';
        $this->model = $model;
    }

    public function embedText(string $text): ?array {
        $text = trim($text);
        if ($text === '') return null;
        $url = 'https://api-inference.huggingface.co/pipeline/feature-extraction/' . rawurlencode($this->model);
        $payload = json_encode(['inputs' => $text, 'options' => ['wait_for_model' => true]]);

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->apiKey,
                'Content-Type: application/json'
            ],
            CURLOPT_POSTFIELDS => $payload,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT => 30
        ]);
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200 || !$response) {
            error_log('Embedding API error: HTTP ' . $httpCode . ' ' . ($response ?: ''));
            return null;
        }
        $data = json_decode($response, true);
        if (!is_array($data)) return null;
        // HF may return nested arrays; flatten if needed.
        if (isset($data[0]) && is_array($data[0])) {
            // Mean-pool tokens to a single vector
            $sum = [];
            $count = 0;
            foreach ($data as $tok) {
                if (!is_array($tok)) continue;
                $sum = $this->vectorAdd($sum, $tok);
                $count++;
            }
            if ($count > 0) {
                return array_map(function($v) use ($count) { return $v / $count; }, $sum);
            }
        }
        return $data;
    }

    public function cosineSimilarity(array $a, array $b): float {
        $dot = 0.0; $na = 0.0; $nb = 0.0; $n = min(count($a), count($b));
        for ($i = 0; $i < $n; $i++) {
            $dot += $a[$i] * $b[$i];
            $na += $a[$i] * $a[$i];
            $nb += $b[$i] * $b[$i];
        }
        if ($na == 0 || $nb == 0) return 0.0;
        return $dot / (sqrt($na) * sqrt($nb));
    }

    private function vectorAdd(array $a, array $b): array {
        $len = max(count($a), count($b));
        $out = [];
        for ($i = 0; $i < $len; $i++) {
            $out[$i] = ($a[$i] ?? 0.0) + ($b[$i] ?? 0.0);
        }
        return $out;
    }
}
?>


