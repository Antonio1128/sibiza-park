# Structura Proiect - Parc Auto Manager

```
parc-auto/
│
├── index.php                     # Dashboard principal
├── login.php                     # Autentificare
├── logout.php                    # Deautentificare
│
├── config/
│   └── db.php                    # Conexiune MySQL
│
├── includes/
│   ├── header.php                # Header HTML comun
│   ├── footer.php                # Footer HTML comun
│   ├── nav.php                   # Meniu navigare
│   ├── auth.php                  # Verificare sesiune
│   └── alerts.php                # Logica alerte expirare
│
├── masini/
│   ├── index.php                 # Lista masini
│   ├── add.php                   # Adaugare masina
│   ├── edit.php                  # Editare masina
│   ├── view.php                  # Detalii masina (toate istoricele)
│   └── delete.php                # Stergere masina
│
├── soferi/
│   ├── index.php                 # Lista soferi
│   ├── add.php                   # Adaugare sofer
│   ├── edit.php                  # Editare sofer
│   ├── view.php                  # Detalii sofer
│   └── delete.php                # Stergere sofer
│
├── service/
│   ├── index.php                 # Lista interventii service
│   ├── add.php                   # Adaugare intrare service
│   ├── view.php                  # Detalii intrare service (cu piese)
│   ├── edit.php                  # Editare service
│   └── raport.php                # Raport per masina (Business Analyst)
│
├── asigurari/
│   ├── index.php                 # Lista asigurari
│   ├── add.php                   # Adaugare asigurare
│   ├── edit.php                  # Editare asigurare
│   └── download.php              # Descarca PDF polita
│
├── viniete/
│   ├── index.php                 # Lista viniete/roviniete
│   ├── add.php                   # Adaugare rovinieta
│   └── edit.php                  # Editare rovinieta
│
├── anvelope/
│   ├── index.php                 # Lista seturi anvelope
│   ├── add.php                   # Adaugare set anvelope
│   ├── schimb.php                # Inregistrare schimb anvelope
│   └── istoric.php               # Istoric schimburi per masina
│
├── curse/
│   ├── index.php                 # Lista curse
│   └── add.php                   # Adaugare cursa (km)
│
├── proprietari/
│   ├── index.php                 # Lista proprietari
│   ├── add.php                   # Adaugare proprietar
│   └── edit.php                  # Editare proprietar
│
├── api/
│   ├── get_masini.php            # JSON: lista masini (pentru JS)
│   └── get_alerts.php            # JSON: alerte active
│
├── assets/
│   ├── css/
│   │   └── style.css             # Stiluri principale
│   ├── js/
│   │   ├── main.js               # JS principal
│   │   └── alerts.js             # JS pentru alerte dinamice
│   └── uploads/
│       └── asigurari/            # PDF-uri polite asigurare
│
└── sql/
    └── schema.sql                # Script creare baza de date
```

## Fluxuri Principale

### Adaugare Masina Noua
1. Proprietari/add.php → creare proprietar (dacă e nou)
2. Masini/add.php → completare date masina + selectie proprietar
3. Redirect la masini/view.php?id=X
4. De acolo se pot adauga: sofer, asigurare, rovinieta, anvelope

### Inregistrare Service
1. Service/add.php → selectie masina, data, km, tip
2. Adaugare randuri interventii_service (piese + costuri)
3. Calculare automata cost_total
4. Salvare si redirect la service/view.php

### Gestionare Alerte
- includes/alerts.php ruleaza la fiecare page load
- Verificare DATE diferente: asigurari, viniete, permise
- Afisare banner/notificari in header

## Securitate Minima
- Parole stocate cu password_hash() / password_verify()
- Sesiuni PHP pentru autentificare
- Parametrizare query-uri MySQL (PDO + prepared statements)
- Validare input server-side
