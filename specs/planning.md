# Planificare Proiect: Parc Auto Manager

## Descriere Generală
Aplicație web pentru firme care gestionează mașini pentru mai mulți proprietari.
Firmele preiau tot ce ține de mașină: asigurare, rovinieta, service, anvelope.

## Tehnologii
- **Frontend:** HTML5, CSS3, JavaScript (Vanilla)
- **Backend:** PHP 8+
- **Baza de date:** MySQL 8+
- **Protocol:** HTTP
- **Server:** Apache (XAMPP/LAMP)

## Module Principale

### 1. Dashboard
- Rezumat general: nr. mașini, șoferi, alerte active
- Alerte expirare: asigurări, viniete, permise șoferi
- Ultimele intervenții service

### 2. Gestiune Mașini
- Adăugare/editare/ștergere mașini
- Detalii complete: capacitate cilindrică, motor, nr. locuri, tonaj, nr. înmatriculare, an fabricație, marcă, model
- Istoric km la fiecare cursă
- Asociere proprietar

### 3. Gestiune Șoferi
- Date personale șofer
- Nr. permis, categorie, dată expirare permis
- Alertă expirare permis
- Asociere mașini conduse

### 4. Management Service
- Evidență completă intervenții
- Data intervenției, km la momentul respectiv
- Lista piese înlocuite + costuri
- Tipuri: revizie, reparație, accident, vopsitorie
- Rapoarte per mașină (rol Business Analyst)

### 5. Asigurări
- Tip: RCA / CASCO
- Dată start, dată expirare
- Preț, companie asiguratoare
- Alertă expirare
- Descărcare PDF

### 6. Viniete (Roviniete)
- Țară, tip, validitate
- Cost
- Dată expirare
- Alertă expirare

### 7. Anvelope
- Seturi: vară / iarnă / all-season
- Dimensiuni, marcă
- Tip: doar anvelope sau roți complete (jante + anvelope)
- Istoric montare/demontare: dată + km
- Stare curentă (montate/depozitate)

## Roluri Utilizatori
- **Admin:** acces complet
- **Business Analyst:** acces rapoarte service
- **Operator:** adăugare/editare date zilnice

## Alerte Sistem
- Expirare asigurare (30, 15, 7 zile înainte)
- Expirare rovinieta (15, 7, 1 zi înainte)
- Expirare permis șofer (60, 30 zile înainte)
- Interval service recomandat (km sau timp)
