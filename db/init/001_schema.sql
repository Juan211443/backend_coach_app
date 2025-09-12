CREATE DATABASE IF NOT EXISTS coach_app;
USE coach_app;

SET NAMES utf8mb4;
SET time_zone = '+00:00';

CREATE TABLE IF NOT EXISTS users (
  id INT AUTO_INCREMENT PRIMARY KEY,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  role ENUM('jugador','entrenador','admin') NOT NULL DEFAULT 'jugador',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS persons (
  id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name  VARCHAR(80) NOT NULL,
  email      VARCHAR(120),
  phone      VARCHAR(30),
  city       VARCHAR(80),
  birth_date DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_person_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS players (
  id            INT AUTO_INCREMENT PRIMARY KEY,
  person_id     INT NOT NULL,
  shirt_number  INT,
  position_code ENUM('PO','DF','DC','ED','EI','MC','MP','ST','SD'),
  dominant_foot ENUM('DER','IZQ','AMB'),
  height_cm     DECIMAL(5,2),
  weight_kg     DECIMAL(5,2),
  efd_center    VARCHAR(120),
  efd_join_year YEAR,
  guardian_name_1  VARCHAR(120),
  guardian_phone_1 VARCHAR(30),
  guardian_name_2  VARCHAR(120),
  guardian_phone_2 VARCHAR(30),
  CONSTRAINT fk_player_person FOREIGN KEY (person_id) REFERENCES persons(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS categories (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL,
  year_tag YEAR NOT NULL,
  UNIQUE KEY uk_categories_year (year_tag)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS teams (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL,
  category_id INT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_team_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS team_players (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  player_id INT NOT NULL,
  shirt_number INT,
  position_code ENUM('PO','DF','DC','ED','EI','MC','MP','ST','SD'),
  joined_at DATE DEFAULT (CURRENT_DATE),
  left_at   DATE NULL,
  UNIQUE KEY uk_team_player_active (team_id, player_id, joined_at),
  CONSTRAINT fk_tp_team   FOREIGN KEY (team_id)   REFERENCES teams(id)   ON DELETE CASCADE,
  CONSTRAINT fk_tp_player FOREIGN KEY (player_id) REFERENCES players(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS training_sessions (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  session_date DATE NOT NULL,
  start_time TIME NULL,
  end_time   TIME NULL,
  location   VARCHAR(120),
  notes      TEXT,
  UNIQUE KEY uk_team_session (team_id, session_date),
  CONSTRAINT fk_ts_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  player_id  INT NOT NULL,
  status     ENUM('PRESENTE','TARDE','AUSENTE','JUSTIFICADA') NOT NULL DEFAULT 'PRESENTE',
  comment    VARCHAR(255),
  UNIQUE KEY uk_attendance (session_id, player_id),
  CONSTRAINT fk_att_session FOREIGN KEY (session_id) REFERENCES training_sessions(id) ON DELETE CASCADE,
  CONSTRAINT fk_att_player  FOREIGN KEY (player_id)  REFERENCES players(id)           ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE IF NOT EXISTS matches (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  opponent VARCHAR(120) NOT NULL,
  home_away ENUM('LOCAL','VISITANTE') NOT NULL,
  match_date DATETIME NOT NULL,
  location   VARCHAR(120),
  notes      TEXT,
  CONSTRAINT fk_match_team FOREIGN KEY (team_id) REFERENCES teams(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
