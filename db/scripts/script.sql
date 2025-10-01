-- USE coach_app_dev;

INSERT INTO player_position (id, code, name) VALUES
  (1,'GK','Goalkeeper'),
  (2,'LB','Left Back'),
  (3,'CB','Center Back'),
  (4,'RB','Right Back'),
  (5,'DM','Defensive Mid'),
  (6,'CM','Central Mid'),
  (7,'AM','Attacking Mid'),
  (8,'ST','Striker')
ON DUPLICATE KEY UPDATE code=VALUES(code), name=VALUES(name);

INSERT INTO sports_academy (id, name) VALUES
  (1,'Leones FC Academy'),
  (2,'Tigres Academy')
ON DUPLICATE KEY UPDATE name=VALUES(name);

INSERT INTO category (id, name, year) VALUES
  (1, '2014', 2014),
  (2, '2013', 2013),
  (3, '2012', 2012)
ON DUPLICATE KEY UPDATE name=VALUES(name), year=VALUES(year);

INSERT INTO team (id, sports_academy_id, name, category_id, coach_person_id) VALUES
  (1, 1, 'Leones A', 1, NULL),
  (2, 1, 'Leones B', 1, NULL),
  (3, 2, 'Tigres A',  2, NULL)
ON DUPLICATE KEY UPDATE name=VALUES(name), category_id=VALUES(category_id);

INSERT INTO person
  (person_id, user_id, first_name, last_name, birth_date, preferred_foot, height_cm, weight_kg, phone, profile_photo_url)
VALUES
  (1, NULL, 'Camilo',     'Andrés',   '2014-03-12', 'Right', 145.0, 38.0, '3001111111', NULL),
  (2, NULL, 'Mateo',      'Ríos',     '2013-07-21', 'Left',  152.0, 40.0, '3002222222', NULL),
  (3, NULL, 'Brayan',     'Steven',   '2012-09-30', 'Both',  156.0, 44.0, '3003333333', NULL),
  (4, NULL, 'Juan',       'Pérez',    '2014-01-18', 'Right', 147.0, 39.0, '3004444444', NULL),
  (5, NULL, 'Santiago',   'López',    '2013-10-05', 'Right', 151.0, 41.0, '3005555555', NULL),
  (6, NULL, 'Andrés',     'Suárez',   '2013-06-14', 'Left',  153.0, 42.0, '3006666666', NULL),
  (7, NULL, 'Luis',       'Martínez', '2012-12-22', 'Right', 158.0, 45.0, '3007777777', NULL),
  (8, NULL, 'Diego',      'Ramírez',  '2014-04-03', 'Both',  146.0, 37.0, '3008888888', NULL)
ON DUPLICATE KEY UPDATE first_name=VALUES(first_name), last_name=VALUES(last_name);

INSERT INTO player
  (person_id, jersey_number, position_id, current_category_id, sports_academy_id, enrollment_year, health_status, current_injuries, current_team_id)
VALUES
  (1, 7,  8, 1, 1, 2022, 'Healthy', NULL,             1),
  (2, 5,  6, 1, 1, 2021, 'Healthy', NULL,             2),
  (3, 10, 7, 3, 2, 2020, 'Healthy', 'Sprained ankle', 3),
  (4, 1,  1, 1, 1, 2022, 'Healthy', NULL,             1), 
  (5, 8,  3, 2, 2, 2021, 'Healthy', NULL,             3),
  (6, 6,  5, 1, 1, 2021, 'Healthy', NULL,             2),
  (7, 9,  8, 3, 2, 2020, 'Healthy', NULL,             3), 
  (8, 2,  4, 1, 1, 2022, 'Healthy', NULL,             1)  
ON DUPLICATE KEY UPDATE jersey_number=VALUES(jersey_number), position_id=VALUES(position_id),
  current_category_id=VALUES(current_category_id), current_team_id=VALUES(current_team_id);

INSERT INTO session (id, team_id, type, date, starts_at, ends_at, location, opponent, notes) VALUES
  (1, 1, 'training', '2025-09-05', '17:00:00', '18:30:00', 'Cancha Norte', NULL, 'Técnica individual'),
  (2, 1, 'match',    '2025-09-12', '10:00:00', '11:00:00', 'Estadio A',    'Tiburones', 'Amistoso'),
  (3, 2, 'training', '2025-09-08', '17:00:00', '18:30:00', 'Cancha Sur',   NULL, 'Salida con balón'),
  (4, 3, 'training', '2025-09-07', '16:30:00', '18:00:00', 'Campo 3',      NULL, 'Pressing alto'),
  (5, 3, 'match',    '2025-09-14', '09:00:00', '10:30:00', 'Estadio B',    'Pumas', 'Liga'),
  (6, 1, 'training', '2025-09-19', '17:00:00', '18:30:00', 'Cancha Norte', NULL, 'Definición')
ON DUPLICATE KEY UPDATE team_id=VALUES(team_id), type=VALUES(type), date=VALUES(date);

INSERT INTO attendance (session_id, player_id, status, checkin_at, remarks) VALUES
  (1, 1, 'present', '2025-09-05 16:55:00', NULL),
  (1, 4, 'present', '2025-09-05 16:58:00', NULL),
  (1, 8, 'late',    '2025-09-05 17:10:00', 'Tráfico'),
  (2, 1, 'present', '2025-09-12 09:45:00', NULL),
  (2, 4, 'present', '2025-09-12 09:47:00', NULL),
  (2, 8, 'present', '2025-09-12 09:50:00', NULL),
  (3, 2, 'present', '2025-09-08 16:55:00', NULL),
  (3, 6, 'excused', NULL, 'Terapia'),
  (4, 3, 'late',    '2025-09-07 16:40:00', 'Bus'),
  (4, 5, 'present', '2025-09-07 16:32:00', NULL),
  (4, 7, 'present', '2025-09-07 16:31:00', NULL),
  (5, 3, 'present', '2025-09-14 08:45:00', NULL),
  (5, 5, 'present', '2025-09-14 08:49:00', NULL),
  (5, 7, 'absent',  NULL, 'Enfermedad'),
  (6, 1, 'present', '2025-09-19 16:58:00', NULL),
  (6, 4, 'present', '2025-09-19 16:59:00', NULL),
  (6, 8, 'present', '2025-09-19 17:00:00', NULL)
ON DUPLICATE KEY UPDATE status=VALUES(status), checkin_at=VALUES(checkin_at), remarks=VALUES(remarks);

INSERT INTO player_metric (id, player_id, metric, value, unit, recorded_at) VALUES
  (1, 1, 'speed', 24.5, 'km/h', '2025-09-10 10:00:00'),
  (2, 3, 'shots_accuracy', 68.0, '%',    '2025-09-10 10:00:00'),
  (3, 5, 'height', 151.0, 'cm',  '2025-09-10 10:00:00'),
  (4, 7, 'weight', 45.0,  'kg',  '2025-09-10 10:00:00')
ON DUPLICATE KEY UPDATE value=VALUES(value), unit=VALUES(unit), recorded_at=VALUES(recorded_at);
