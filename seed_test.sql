USE parc_auto;

-- ITP table (missing from original schema)
CREATE TABLE IF NOT EXISTS itp (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  data_efectuare DATE,
  data_expirare DATE NOT NULL,
  statie VARCHAR(100),
  cost DECIMAL(8,2) DEFAULT 0,
  observatii TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Proprietar pentru masini de test
INSERT IGNORE INTO proprietari (nume, telefon, email, adresa) VALUES
('SC Transport Test SRL', '0721000001', 'office@transporttest.ro', 'Str. Garajului 1, București');

SET @prop_id = (SELECT id FROM proprietari WHERE email = 'office@transporttest.ro' LIMIT 1);

-- 5 Soferi
INSERT IGNORE INTO soferi (nume, telefon, email, nr_permis, categorie_permis, data_expirare_permis) VALUES
('Ion Popescu',         '0740111001', 'ion.popescu@test.ro',     'RO-123456', 'B,C',   '2028-06-15'),
('Maria Ionescu',       '0740111002', 'maria.ionescu@test.ro',   'RO-234567', 'B',     '2027-03-20'),
('Gheorghe Dumitrescu', '0740111003', 'gh.dumitrescu@test.ro',   'RO-345678', 'B,C,E', '2029-09-10'),
('Elena Constantin',    '0740111004', 'elena.constantin@test.ro','RO-456789', 'B',     '2026-11-05'),
('Mihai Andreescu',     '0740111005', 'mihai.andreescu@test.ro', 'RO-567890', 'B,C',   '2028-01-30');

-- 5 Masini
INSERT IGNORE INTO masini (proprietar_id, nr_inmatriculare, marca, model, an_fabricatie, capacitate_cmc, tip_motor, nr_locuri, culoare, serie_sasiu, km_actuali) VALUES
(@prop_id, 'B-123-ABC',  'Dacia',      'Logan',   2019, 1598, 'Benzina', 5, 'Alb',  'UU1LSDA71KA000001', 85000),
(@prop_id, 'B-456-DEF',  'Volkswagen', 'Passat',  2020, 1968, 'Diesel',  5, 'Gri',  'WVWZZZ3CZLE000002', 62000),
(@prop_id, 'B-789-GHI',  'Ford',       'Transit', 2018, 2198, 'Diesel',  9, 'Alb',  'WF0XXXTTGXJB000003', 130000),
(@prop_id, 'CJ-100-XYZ', 'Renault',    'Megane',  2021, 1332, 'Benzina', 5, 'Rosu', 'VF1BA0B0H65000004', 41000),
(@prop_id, 'TM-55-POW',  'Toyota',     'Corolla', 2022, 1798, 'Hibrid',  5, 'Negru','NMTBA3BE80R000005', 28000);

-- ID-uri masini
SET @m1 = (SELECT id FROM masini WHERE nr_inmatriculare = 'B-123-ABC'  LIMIT 1);
SET @m2 = (SELECT id FROM masini WHERE nr_inmatriculare = 'B-456-DEF'  LIMIT 1);
SET @m3 = (SELECT id FROM masini WHERE nr_inmatriculare = 'B-789-GHI'  LIMIT 1);
SET @m4 = (SELECT id FROM masini WHERE nr_inmatriculare = 'CJ-100-XYZ' LIMIT 1);
SET @m5 = (SELECT id FROM masini WHERE nr_inmatriculare = 'TM-55-POW'  LIMIT 1);

-- ITP pentru fiecare masina (INSERT IGNORE evita duplicate)
INSERT IGNORE INTO itp (masina_id, data_efectuare, data_expirare, statie, cost, observatii) VALUES
(@m1, '2024-05-10', '2026-05-10', 'RAR București Sector 1', 250.00, 'Fara defecte'),
(@m2, '2025-01-15', '2027-01-15', 'RAR București Sector 2', 280.00, 'Fara defecte'),
(@m3, '2024-03-20', '2026-06-05', 'RAR Ilfov',              310.00, 'Schimb placute frana recomandat'),
(@m4, '2025-04-01', '2025-05-28', 'RAR Cluj-Napoca',        260.00, 'Expira curand - de reinnoit'),
(@m5, '2024-11-05', '2024-11-05', 'RAR Timisoara',          270.00, 'ITP expirat - necesita reinoire');

-- Migrare coloana poza in masini (daca nu exista deja)
ALTER TABLE masini ADD COLUMN IF NOT EXISTS poza VARCHAR(255) NULL;

-- Migrare coloana sofer_id in utilizatori (daca nu exista deja)
ALTER TABLE utilizatori
  ADD COLUMN IF NOT EXISTS sofer_id  INT NULL,
  ADD COLUMN IF NOT EXISTS client_id INT NULL,
  MODIFY COLUMN rol ENUM('admin','analyst','operator','client') NOT NULL DEFAULT 'operator';

-- ID-uri soferi
SET @s1 = (SELECT id FROM soferi WHERE nr_permis = 'RO-123456' LIMIT 1);
SET @s2 = (SELECT id FROM soferi WHERE nr_permis = 'RO-234567' LIMIT 1);
SET @s3 = (SELECT id FROM soferi WHERE nr_permis = 'RO-345678' LIMIT 1);
SET @s4 = (SELECT id FROM soferi WHERE nr_permis = 'RO-456789' LIMIT 1);
SET @s5 = (SELECT id FROM soferi WHERE nr_permis = 'RO-567890' LIMIT 1);

-- Asociere masina-sofer (INSERT IGNORE evita duplicate)
INSERT IGNORE INTO masina_sofer (masina_id, sofer_id, data_start, km_la_preluare) VALUES
(@m1, @s1, '2024-01-01', 70000),
(@m2, @s2, '2024-02-01', 50000),
(@m3, @s3, '2024-01-15', 110000),
(@m4, @s4, '2024-03-01', 35000),
(@m5, @s5, '2024-04-01', 20000);

-- 5 conturi operator (parola: password) legate de soferi
-- hash bcrypt pentru 'password' (schimba din interfata dupa import)
SET @hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi';
INSERT IGNORE INTO utilizatori (username, password, email, rol, sofer_id) VALUES
('ion.popescu',      @hash, 'ion.popescu@test.ro',     'operator', @s1),
('maria.ionescu',    @hash, 'maria.ionescu@test.ro',   'operator', @s2),
('gh.dumitrescu',    @hash, 'gh.dumitrescu@test.ro',   'operator', @s3),
('elena.constantin', @hash, 'elena.constantin@test.ro','operator', @s4),
('mihai.andreescu',  @hash, 'mihai.andreescu@test.ro', 'operator', @s5);
