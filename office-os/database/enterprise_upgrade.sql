-- Security and audit enhancements
ALTER TABLE users
    ADD COLUMN role VARCHAR(50) DEFAULT 'staff',
    ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN company_id INT;

ALTER TABLE approvals
    ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN updated_at DATETIME NULL,
    ADD COLUMN company_id INT;

ALTER TABLE rooms
    ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP,
    ADD COLUMN company_id INT;

ALTER TABLE visitors
    ADD COLUMN created_at DATETIME DEFAULT CURRENT_TIMESTAMP;

CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT,
    action VARCHAR(255),
    ip_address VARCHAR(45),
    created_at DATETIME DEFAULT CURRENT_TIMESTAMP
);

-- Store hashed passwords only
-- Example: INSERT INTO users (email, password) VALUES ('admin@corp.com', '$2y$10$...');
