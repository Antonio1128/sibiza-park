-- Parc Auto Manager - Schema completa
CREATE DATABASE IF NOT EXISTS parc_auto CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE parc_auto;

SET FOREIGN_KEY_CHECKS = 0;
DROP TABLE IF EXISTS schimburi_anvelope;
DROP TABLE IF EXISTS seturi_anvelope;
DROP TABLE IF EXISTS viniete;
DROP TABLE IF EXISTS asigurari;
DROP TABLE IF EXISTS interventii_service;
DROP TABLE IF EXISTS servicii;
DROP TABLE IF EXISTS curse;
DROP TABLE IF EXISTS masina_sofer;
DROP TABLE IF EXISTS soferi;
DROP TABLE IF EXISTS masini;
DROP TABLE IF EXISTS proprietari;
DROP TABLE IF EXISTS utilizatori;
-- sterge si tabelele vechi daca exista
DROP TABLE IF EXISTS ServiceEntryActions;
DROP TABLE IF EXISTS ServiceEntry;
DROP TABLE IF EXISTS ServiceActions;
DROP TABLE IF EXISTS Car;
DROP TABLE IF EXISTS CarMakes;
SET FOREIGN_KEY_CHECKS = 1;

CREATE TABLE utilizatori (
  id INT PRIMARY KEY AUTO_INCREMENT,
  username VARCHAR(50) NOT NULL UNIQUE,
  password VARCHAR(255) NOT NULL,
  email VARCHAR(100),
  rol ENUM('admin','analyst','client') NOT NULL DEFAULT 'admin',
  sofer_id INT NULL,
  client_id INT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE proprietari (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nume VARCHAR(100) NOT NULL,
  telefon VARCHAR(20),
  email VARCHAR(100),
  adresa TEXT,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE masini (
  id INT PRIMARY KEY AUTO_INCREMENT,
  proprietar_id INT NOT NULL,
  nr_inmatriculare VARCHAR(20) NOT NULL UNIQUE,
  marca VARCHAR(50),
  model VARCHAR(50),
  an_fabricatie YEAR,
  capacitate_cmc INT,
  tip_motor VARCHAR(30),
  nr_locuri TINYINT,
  tonaj DECIMAL(5,2),
  culoare VARCHAR(30),
  serie_sasiu VARCHAR(50),
  km_actuali INT DEFAULT 0,
  poza VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (proprietar_id) REFERENCES proprietari(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE soferi (
  id INT PRIMARY KEY AUTO_INCREMENT,
  nume VARCHAR(100) NOT NULL,
  telefon VARCHAR(20),
  email VARCHAR(100),
  nr_permis VARCHAR(30) UNIQUE,
  categorie_permis VARCHAR(20),
  data_expirare_permis DATE,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE masina_sofer (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  sofer_id INT NOT NULL,
  data_start DATE,
  data_sfarsit DATE NULL,
  km_la_preluare INT,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE,
  FOREIGN KEY (sofer_id) REFERENCES soferi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE curse (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  sofer_id INT NOT NULL,
  data DATE NOT NULL,
  km_start INT,
  km_final INT,
  destinatie VARCHAR(200),
  observatii TEXT,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE,
  FOREIGN KEY (sofer_id) REFERENCES soferi(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE servicii (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  data DATE NOT NULL,
  km_la_intrare INT,
  tip ENUM('revizie','reparatie','accident','vopsitorie','altele') NOT NULL DEFAULT 'revizie',
  descriere TEXT,
  cost_total DECIMAL(10,2) DEFAULT 0,
  service_extern VARCHAR(100),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE interventii_service (
  id INT PRIMARY KEY AUTO_INCREMENT,
  service_id INT NOT NULL,
  denumire VARCHAR(200) NOT NULL,
  cantitate DECIMAL(8,2) DEFAULT 1,
  pret_unitar DECIMAL(10,2) NOT NULL,
  total DECIMAL(10,2) NOT NULL,
  FOREIGN KEY (service_id) REFERENCES servicii(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE asigurari (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  tip ENUM('RCA','CASCO') NOT NULL,
  companie VARCHAR(100),
  nr_polita VARCHAR(50),
  data_start DATE,
  data_expirare DATE,
  pret DECIMAL(10,2),
  fisier_pdf VARCHAR(255) NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE viniete (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  tara VARCHAR(50),
  tip VARCHAR(30),
  data_start DATE,
  data_expirare DATE,
  cost DECIMAL(8,2),
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE seturi_anvelope (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  tip_sezon ENUM('vara','iarna','all_season') NOT NULL,
  marca VARCHAR(50),
  dimensiune VARCHAR(30),
  tip_set ENUM('anvelope','roti_complete') NOT NULL DEFAULT 'anvelope',
  stare ENUM('montate','depozitate') NOT NULL DEFAULT 'montate',
  nr_bucati TINYINT DEFAULT 4,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE schimburi_anvelope (
  id INT PRIMARY KEY AUTO_INCREMENT,
  masina_id INT NOT NULL,
  set_montat_id INT NOT NULL,
  set_demontat_id INT NULL,
  data DATE NOT NULL,
  km_la_schimb INT,
  observatii TEXT,
  FOREIGN KEY (masina_id) REFERENCES masini(id) ON DELETE CASCADE,
  FOREIGN KEY (set_montat_id) REFERENCES seturi_anvelope(id) ON DELETE RESTRICT,
  FOREIGN KEY (set_demontat_id) REFERENCES seturi_anvelope(id) ON DELETE SET NULL
) ENGINE=InnoDB;

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

CREATE TABLE IF NOT EXISTS tichete (
  id INT PRIMARY KEY AUTO_INCREMENT,
  user_id INT NOT NULL,
  tip ENUM('cerere_masina','feedback','ajutor') NOT NULL,
  subiect VARCHAR(200) NOT NULL,
  mesaj TEXT NOT NULL,
  stele TINYINT NULL DEFAULT NULL,
  status ENUM('nou','in_lucru','rezolvat') NOT NULL DEFAULT 'nou',
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (user_id) REFERENCES utilizatori(id) ON DELETE CASCADE
) ENGINE=InnoDB;

ALTER TABLE tichete ADD COLUMN IF NOT EXISTS stele TINYINT NULL DEFAULT NULL;

CREATE TABLE IF NOT EXISTS tichete_raspunsuri (
  id INT PRIMARY KEY AUTO_INCREMENT,
  tichet_id INT NOT NULL,
  user_id INT NOT NULL,
  mesaj TEXT NOT NULL,
  created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (tichet_id) REFERENCES tichete(id) ON DELETE CASCADE,
  FOREIGN KEY (user_id) REFERENCES utilizatori(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Cont admin implicit (parola: admin123)
INSERT INTO utilizatori (username, password, email, rol)
VALUES ('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin@parcauto.ro', 'admin');
