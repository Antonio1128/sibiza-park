# Schema Baza de Date - Parc Auto Manager

## Tabele

### proprietari
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID proprietar |
| nume | VARCHAR(100) | Nume complet / firmă |
| telefon | VARCHAR(20) | Telefon contact |
| email | VARCHAR(100) | Email |
| adresa | TEXT | Adresă |
| created_at | TIMESTAMP | Data înregistrare |

### masini
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID mașină |
| proprietar_id | INT FK | Referință proprietar |
| nr_inmatriculare | VARCHAR(20) | Număr înmatriculare |
| marca | VARCHAR(50) | Marcă (Dacia, VW, etc.) |
| model | VARCHAR(50) | Model |
| an_fabricatie | YEAR | Anul fabricației |
| capacitate_cmc | INT | Capacitate cilindrică (cmc) |
| tip_motor | VARCHAR(30) | Benzină/Diesel/Electric/Hibrid |
| nr_locuri | TINYINT | Număr locuri |
| tonaj | DECIMAL(5,2) | Tonaj (tone) |
| culoare | VARCHAR(30) | Culoare |
| serie_sasiu | VARCHAR(50) | Serie șasiu (VIN) |
| km_actuali | INT | Kilometraj curent |
| created_at | TIMESTAMP | Data adăugare |

### soferi
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID șofer |
| nume | VARCHAR(100) | Nume complet |
| telefon | VARCHAR(20) | Telefon |
| email | VARCHAR(100) | Email |
| nr_permis | VARCHAR(30) | Număr permis |
| categorie_permis | VARCHAR(20) | Categorii (B, C, D, etc.) |
| data_expirare_permis | DATE | Data expirare permis |
| created_at | TIMESTAMP | Data înregistrare |

### masina_sofer (asociere)
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID |
| masina_id | INT FK | Referință mașină |
| sofer_id | INT FK | Referință șofer |
| data_start | DATE | De când conduce |
| data_sfarsit | DATE NULL | Până când (NULL = activ) |
| km_la_preluare | INT | Km la preluare mașină |

### curse (kilometraj per cursă)
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID cursă |
| masina_id | INT FK | Referință mașină |
| sofer_id | INT FK | Referință șofer |
| data | DATE | Data cursei |
| km_start | INT | Km la plecare |
| km_final | INT | Km la sosire |
| destinatie | VARCHAR(200) | Destinație |
| observatii | TEXT | Observații |

### servicii (înregistrări service)
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID service |
| masina_id | INT FK | Referință mașină |
| data | DATE | Data intrare service |
| km_la_intrare | INT | Km la momentul intrării |
| tip | ENUM | revizie/reparatie/accident/vopsitorie/altele |
| descriere | TEXT | Descriere generală |
| cost_total | DECIMAL(10,2) | Cost total (calculat) |
| service_extern | VARCHAR(100) | Numele service-ului extern |
| created_at | TIMESTAMP | Data înregistrare |

### interventii_service (detalii piese/manoperă)
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID interventie |
| service_id | INT FK | Referință service |
| denumire | VARCHAR(200) | Denumire piesă/manoperă |
| cantitate | DECIMAL(8,2) | Cantitate |
| pret_unitar | DECIMAL(10,2) | Preț per unitate |
| total | DECIMAL(10,2) | Total (calculat) |

### asigurari
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID asigurare |
| masina_id | INT FK | Referință mașină |
| tip | ENUM | RCA/CASCO |
| companie | VARCHAR(100) | Companie asiguratoare |
| nr_polita | VARCHAR(50) | Număr poliță |
| data_start | DATE | Data începere |
| data_expirare | DATE | Data expirare |
| pret | DECIMAL(10,2) | Preț (RON) |
| fisier_pdf | VARCHAR(255) NULL | Cale fișier PDF |
| created_at | TIMESTAMP | Data înregistrare |

### viniete
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID rovinieta |
| masina_id | INT FK | Referință mașină |
| tara | VARCHAR(50) | Țară |
| tip | VARCHAR(30) | Tip (lunar, trimestrial, anual) |
| data_start | DATE | Data începere |
| data_expirare | DATE | Data expirare |
| cost | DECIMAL(8,2) | Cost (RON) |
| created_at | TIMESTAMP | Data înregistrare |

### seturi_anvelope
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID set |
| masina_id | INT FK | Referință mașină |
| tip_sezon | ENUM | vara/iarna/all_season |
| marca | VARCHAR(50) | Marcă anvelope |
| dimensiune | VARCHAR(30) | ex: 205/55 R16 |
| tip_set | ENUM | anvelope/roti_complete |
| stare | ENUM | montate/depozitate |
| nr_bucati | TINYINT | Număr bucăți |
| created_at | TIMESTAMP | Data înregistrare |

### schimburi_anvelope
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID schimb |
| masina_id | INT FK | Referință mașină |
| set_montat_id | INT FK | Set montat |
| set_demontat_id | INT FK NULL | Set demontat |
| data | DATE | Data schimbului |
| km_la_schimb | INT | Km la momentul schimbului |
| observatii | TEXT | Observații |

### utilizatori
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID utilizator |
| username | VARCHAR(50) | Username |
| password | VARCHAR(255) | Parolă (hash) |
| email | VARCHAR(100) | Email |
| rol | ENUM | admin/analyst/operator |
| created_at | TIMESTAMP | Data creare cont |
