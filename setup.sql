-- BDrive MySQL Database Setup
-- Run this script to create the database and table

CREATE DATABASE IF NOT EXISTS bdrive;
USE bdrive;

CREATE TABLE IF NOT EXISTS files (
    id INT AUTO_INCREMENT PRIMARY KEY,
    filename VARCHAR(255) NOT NULL,
    filepath VARCHAR(500) NOT NULL UNIQUE,
    filetype VARCHAR(50),
    tags VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Create index on filepath for faster lookups
CREATE INDEX idx_filepath ON files(filepath);

-- Create index on tags for faster searching
CREATE INDEX idx_tags ON files(tags);
