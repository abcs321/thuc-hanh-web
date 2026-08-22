-- database.sql
-- Tạo CSDL và bảng cho form "Liên hệ"

CREATE DATABASE IF NOT EXISTS lien_he_db
    CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;

USE lien_he_db;

CREATE TABLE IF NOT EXISTS lien_he (
    id INT AUTO_INCREMENT PRIMARY KEY,
    ho_ten VARCHAR(100) NOT NULL,
    email VARCHAR(150) NOT NULL,
    chu_de ENUM('Hỗ trợ kỹ thuật', 'Góp ý', 'Khiếu nại', 'Khác') NOT NULL,
    noi_dung VARCHAR(500) NOT NULL,
    anh_dai_dien VARCHAR(255) DEFAULT NULL,
    ngay_gui DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;
