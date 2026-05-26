# Sibiza Park — Fleet Management Platform

Aplicație web pentru gestiunea unui parc auto: mașini, clienți, curse, service, asigurări, viniete, anvelope și ITP.

## Stack

- **Backend:** PHP 8+ (fără framework)
- **Bază de date:** MySQL 8 / MariaDB
- **Server local:** XAMPP (Apache + MySQL)
- **Frontend:** HTML/CSS/JS vanilla — design dark automotive premium

---

## Setup local

1. Instalează [XAMPP](https://www.apachefriends.org/)
2. Clonează repo-ul în `C:/xampp/htdocs/proiectpers`
3. Pornește Apache + MySQL din XAMPP Control Panel
4. Deschide **phpMyAdmin** → creează baza de date `parc_auto`
5. Importă `database.sql` (Schema + cont admin implicit)
6. Opțional: importă `seed_test.sql` pentru date de test
7. Accesează `http://localhost/proiectpers`

**Cont admin implicit:**
- Username: `admin`
- Parolă: `admin123`

---

## Structură foldere

```
proiectpers/
├── assets/css/style.css      # CSS global (tema dark, variabile, componente)
├── config/db.php             # Conexiune MySQL
├── includes/
│   ├── auth.php              # Verificare sesiune
│   ├── helpers.php           # Funcții globale: icon(), fmt_date(), etc.
│   ├── nav.php               # Sidebar navigație
│   ├── header.php / footer.php
│   └── alerts.php            # Notificări expirări (RCA, ITP, etc.)
├── masini/                   # CRUD mașini
├── clienti/                  # CRUD clienți (înlocuiește modulul soferi)
├── curse/                    # Înregistrare curse
├── servicii/                 # Intrări service
├── asigurari/                # RCA / CASCO
├── viniete/                  # Viniete rutiere
├── anvelope/                 # Seturi anvelope + schimburi
├── itp/                      # Inspecții tehnice
├── rapoarte/                 # Statistici (doar rol analyst)
├── portal/                   # Portal client (vizualizare mașini proprii)
├── suport/                   # Sistem tichete suport
├── utilizatori/              # Gestiune conturi (doar admin)
├── soferi/                   # ⚠️ Nefolosit — a fost înlocuit de clienti/
├── database.sql              # Schema completă + seed admin
├── seed_test.sql             # Date de test
└── index.html                # Preview static design (fără server)
```

---

## Roluri utilizatori

| Rol | Acces |
|-----|-------|
| `admin` | Acces complet la toate modulele |
| `analyst` | Doar vizualizare + rapoarte, fără adăugare/ștergere |
| `client` | Doar portalul propriu (mașini, documente, suport) |

---

## Migrări SQL necesare

Dacă baza de date a fost creată înainte de ultimele modificări, rulează în phpMyAdmin:

```sql
-- 1. Schimbă FK curse.sofer_id să pointeze spre clienti în loc de soferi
ALTER TABLE curse DROP FOREIGN KEY curse_ibfk_2;
ALTER TABLE curse ADD CONSTRAINT curse_ibfk_2
  FOREIGN KEY (sofer_id) REFERENCES clienti(id) ON DELETE CASCADE;
```

---

## Ce mai e de făcut

- [ ] **Șterge sau ascunde modulul `soferi/`** — a fost înlocuit complet de `clienti/`; folderul există dar nu mai e în navigație
- [ ] **Redenumește coloana "Șofer" → "Client"** în header-ul tabelului din `curse/index.php` (linia 29)
- [ ] **`reset_pass.php`** — pagina există dar funcționalitatea nu e implementată
- [ ] **Portal client** — mașinile afișate în portal sunt hardcodate după `client_id`; de verificat că FK-ul `masini.proprietar_id` → `proprietari` e corect legat de `clienti`
- [ ] **Rapoarte** — graficele sunt statice (CSS only); de conectat la date reale din DB
- [ ] **Upload poze mașini** — folderul `uploads/` nu e în `.gitignore`; de adăugat înainte de push

---

## Preview design (fără server)

Deschide `index.html` direct în browser. Nu necesită PHP sau bază de date.
Conține trei view-uri navigabile: **Login**, **Admin**, **Portal Client**.
