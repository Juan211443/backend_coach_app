CREATE DATABASE IF NOT EXISTS coach_app
  DEFAULT CHARACTER SET utf8mb4
  DEFAULT COLLATE utf8mb4_general_ci;
USE coach_app;
SET NAMES utf8mb4;

CREATE TABLE user (
  user_id INT AUTO_INCREMENT PRIMARY KEY,
  email VARCHAR(100) NOT NULL UNIQUE,
  password_hash VARCHAR(255) NOT NULL,
  role ENUM('player','coach') NOT NULL DEFAULT 'player',
  is_active TINYINT(1) NOT NULL DEFAULT 1,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE person (
  person_id INT AUTO_INCREMENT PRIMARY KEY,
  user_id INT NULL,
  first_name VARCHAR(80) NOT NULL,
  last_name  VARCHAR(80) NOT NULL,
  birth_date DATE NOT NULL,
  preferred_foot ENUM('Left','Right','Both') DEFAULT 'Right',
  height_cm DECIMAL(5,2) NULL,
  weight_kg DECIMAL(5,2) NULL,
  phone VARCHAR(20) NULL,
  profile_photo MEDIUMBLOB NULL,
  created_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_person_user
    FOREIGN KEY (user_id) REFERENCES user(user_id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE player_position (
  id TINYINT PRIMARY KEY,
  code VARCHAR(5) UNIQUE,
  name VARCHAR(40) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE sports_academy (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(120) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE category (
  id INT AUTO_INCREMENT PRIMARY KEY,
  name VARCHAR(40) NOT NULL,
  year SMALLINT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE team (
  id INT AUTO_INCREMENT PRIMARY KEY,
  sports_academy_id INT NOT NULL,
  name VARCHAR(80) NOT NULL,
  category_id INT NOT NULL,
  coach_person_id INT NULL,
  CONSTRAINT fk_team_academy
    FOREIGN KEY (sports_academy_id) REFERENCES sports_academy(id),
  CONSTRAINT fk_team_category
    FOREIGN KEY (category_id) REFERENCES category(id),
  CONSTRAINT fk_team_coach
    FOREIGN KEY (coach_person_id) REFERENCES person(person_id)
    ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE player (
  person_id INT PRIMARY KEY,
  jersey_number TINYINT NULL
    CHECK (jersey_number BETWEEN 1 AND 99),
  position_id TINYINT NULL,
  current_category_id INT NULL,
  sports_academy_id INT NULL,
  enrollment_year SMALLINT NULL
    CHECK (enrollment_year BETWEEN 1900 AND YEAR(CURDATE())),
  health_status VARCHAR(120) NULL,
  current_injuries VARCHAR(200) NULL,
  current_team_id INT NULL,
  CONSTRAINT fk_player_person
    FOREIGN KEY (person_id) REFERENCES person(person_id)
    ON DELETE CASCADE,
  CONSTRAINT fk_player_position
    FOREIGN KEY (position_id) REFERENCES player_position(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_player_category
    FOREIGN KEY (current_category_id) REFERENCES category(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_player_academy
    FOREIGN KEY (sports_academy_id) REFERENCES sports_academy(id)
    ON DELETE SET NULL,
  CONSTRAINT fk_player_team
    FOREIGN KEY (current_team_id) REFERENCES team(id)
    ON DELETE SET NULL,
  UNIQUE KEY uq_team_jersey (current_team_id, jersey_number)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE session (
  id INT AUTO_INCREMENT PRIMARY KEY,
  team_id INT NOT NULL,
  type ENUM('training','match') NOT NULL,
  date DATE NOT NULL,
  starts_at TIME NULL,
  ends_at TIME NULL,
  location VARCHAR(120) NULL,
  opponent VARCHAR(120) NULL,
  notes VARCHAR(255) NULL,
  CONSTRAINT fk_session_team
    FOREIGN KEY (team_id) REFERENCES team(id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE attendance (
  id INT AUTO_INCREMENT PRIMARY KEY,
  session_id INT NOT NULL,
  player_id INT NOT NULL,
  status ENUM('present','absent','late','excused') NOT NULL,
  checkin_at DATETIME NULL,
  remarks VARCHAR(255) NULL,
  UNIQUE KEY uq_session_player (session_id, player_id),
  CONSTRAINT fk_attendance_session
    FOREIGN KEY (session_id) REFERENCES session(id)
    ON DELETE CASCADE,
  CONSTRAINT fk_attendance_player
    FOREIGN KEY (player_id) REFERENCES player(person_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

CREATE TABLE player_metric (
  id INT AUTO_INCREMENT PRIMARY KEY,
  player_id INT NOT NULL,
  metric ENUM('weight','height','bmi','speed','shots_accuracy','effective_touches') NOT NULL,
  value DECIMAL(8,2) NOT NULL,
  unit VARCHAR(10) NULL,
  recorded_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
  CONSTRAINT fk_metric_player
    FOREIGN KEY (player_id) REFERENCES player(person_id)
    ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;