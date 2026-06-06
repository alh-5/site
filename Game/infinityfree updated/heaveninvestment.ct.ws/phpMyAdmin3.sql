-- Drop table if exists
DROP TABLE IF EXISTS talent_coins_data;

-- Create the table
CREATE TABLE talent_coins_data (
  user_id VARCHAR(50) NOT NULL PRIMARY KEY,
  talent_coins_json LONGTEXT,
  multiplier_active TINYINT(1) DEFAULT 0,
  multiplier_rate INT DEFAULT 1,
  multiplier_seconds INT DEFAULT 0,
  current_page INT DEFAULT 1,
  last_updated TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- Insert initial empty record for our shared user
INSERT INTO talent_coins_data (user_id, talent_coins_json) 
VALUES ('talent_coins_shared_user', '[]');