-- Module Embeddings Table
CREATE TABLE IF NOT EXISTS module_embeddings (
  module_id INT PRIMARY KEY,
  embedding_json JSON NOT NULL,
  updated_at TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  CONSTRAINT fk_module_embeddings_module FOREIGN KEY (module_id) REFERENCES training_modules(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;


