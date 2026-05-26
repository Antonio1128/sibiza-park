# Schema Baza de Date

## Tabele

### CarMakes
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID marca |
| name | VARCHAR(100) | Numele marcii |

### Car
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID masina |
| make | INT FK | Referinta CarMakes |
| model | VARCHAR(100) | Modelul masinii |
| year | YEAR | Anul fabricatiei |
| type | VARCHAR(50) | Tipul masinii |
| engine | VARCHAR(50) | Motorul |
| license | VARCHAR(20) | Numarul de inmatriculare |
| firstRegistration | DATE | Data primei inmatriculari |

### ServiceEntry
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID intrare service |
| carID | INT FK | Referinta Car |
| mileage | INT | Kilometraj la intrare |

### ServiceActions
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID actiune |
| action | VARCHAR(200) | Descrierea actiunii |
| price | DECIMAL(10,2) | Pretul actiunii |

### ServiceEntryActions
| Coloana | Tip | Descriere |
|---|---|---|
| id | INT PK AUTO | ID |
| serviceEntryID | INT FK | Referinta ServiceEntry |
| serviceActionsID | INT FK | Referinta ServiceActions |
