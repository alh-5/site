CREATE TABLE IF NOT EXISTS talent_coins_data (
  user_id VARCHAR(50) NOT NULL PRIMARY KEY,
  talent_coins_json TEXT,
  multiplier_active TINYINT(1) DEFAULT 0,
  multiplier_rate INT DEFAULT 1,
  multiplier_seconds INT DEFAULT 0,
  current_page INT DEFAULT 1,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;