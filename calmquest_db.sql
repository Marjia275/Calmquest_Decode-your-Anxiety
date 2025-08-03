-- Drop old tables if they exist
DROP TABLE IF EXISTS user_achievements;
DROP TABLE IF EXISTS achievements;
DROP TABLE IF EXISTS user_streaks;
DROP TABLE IF EXISTS task_moods;
DROP TABLE IF EXISTS user_challenges;
DROP TABLE IF EXISTS challenges;
DROP TABLE IF EXISTS user_tasks;
DROP TABLE IF EXISTS tasks;
DROP TABLE IF EXISTS user_progress;
DROP TABLE IF EXISTS stress_logs;


-- Create and use the database
CREATE DATABASE IF NOT EXISTS calmquest_db;
USE calmquest_db;

-- Create 'users' table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    username VARCHAR(100) NOT NULL,                      
    password VARCHAR(255) NOT NULL,           -- Hashed password using bcrypt
    password_key CHAR(64) NOT NULL,           -- SHA-256 of raw password for uniqueness check
    
    -- Ensure a user cannot register with same name + same password
    UNIQUE KEY unique_name_password (name, password_key)
);

-- Stress logs table
CREATE TABLE IF NOT EXISTS stress_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    day INT,                                  -- Used in stress_quiz.php
    stress_level INT DEFAULT 0,               -- Used in stress_quiz.php, suggestion.php, summary.php
    suggestion TEXT,                          -- Used in stress_quiz.php, suggestion.php
    feedback TEXT,                            -- Used in feedback.php
    date DATE DEFAULT (CURRENT_DATE),         -- Used in summary.php
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

CREATE TABLE user_progress (
      id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    log_date DATE NOT NULL,
    type ENUM('meditation', 'yoga') NOT NULL,
    duration INT NOT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);

DROP TABLE IF EXISTS user_daily_tasks;

CREATE TABLE IF NOT EXISTS user_daily_tasks (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    task_name VARCHAR(100) NOT NULL,
    duration INT NOT NULL, -- in minutes
    date DATE NOT NULL DEFAULT CURRENT_DATE,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
);


