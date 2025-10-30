-- Initialize MySQL database for on-prem app
CREATE DATABASE IF NOT EXISTS onprem_kontakami CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

GRANT ALL PRIVILEGES ON onprem_kontakami.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;
