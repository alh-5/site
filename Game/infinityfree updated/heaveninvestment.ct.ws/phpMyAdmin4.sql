-- Drop table if exists (if you want to start fresh)
DROP TABLE IF EXISTS talent_coins_data;

-- Create the table with correct structure
CREATE TABLE talent_coins_data (
  user_id VARCHAR(50) NOT NULL PRIMARY KEY,
  talent_coins_json LONGTEXT,
  multiplier_active TINYINT(1) DEFAULT 0,
  multiplier_rate INT DEFAULT 1,
  multiplier_seconds INT DEFAULT 0,
  current_page INT DEFAULT 1,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial record for shared user
INSERT INTO talent_coins_data (user_id, talent_coins_json, multiplier_active, multiplier_rate, multiplier_seconds, current_page) 
VALUES ('talent_coins_shared_user', '[]', 0, 1, 0, 1);