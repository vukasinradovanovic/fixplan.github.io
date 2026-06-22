# Link do sajta
https://fixplan.infinityfree.io/ 

# FixPlan Platforma - Lokalno Pokretanje Aplikacije

Ovaj vodič sadrži detaljne korake za uspešno postavljanje i pokretanje aplikacije na Vašem lokalnom razvojnom okruženju koristeći **XAMPP** paket.

---

## 📋 Preduslovi

Pre nego što započnete instalaciju, uverite se da imate instalirano sledeće:
* **XAMPP** (sa omogućenim PHP i MySQL servisima)
* Web čitač (Chrome, Firefox, Edge, itd.)
* Bilo koji editor koda (VS Code, Sublime Text, itd.)

---

##  Koraci za Instalaciju

### Korak 1: Pozicioniranje Projekta (XAMPP htdocs)

Aplikacija se obavezno mora nalaziti unutar glavnog direktorijuma za XAMPP web server kako bi skripte radile ispravno.

1. Kopirajte folder sa kompletnim izvornim kodom aplikacije.
2. Prebacite folder na sledeću lokaciju na Vašem računaru:
   ```text
   C:\xampp\htdocs\fixplan-app
### Korak 2: Konfiguracija .env fajla

Unutar config foldera se nalazi `.env` fajl koji je generički. U njemu se nalazi konfiguracija baze podataka i mailera.
Ovde je primer kako on izgleda ukoliko je neophodno da se opet napravi ili je došlo do greške:

```ini
# Konfiguracija baze podataka
DB_SERVER=127.0.0.1
DB_PORT=3306 
DB_DATABASE=fixplan_db
DB_USERNAME=root
DB_PASSWORD=

# Mailtrap SMTP konfiguracija (Slanje verifikacionih email-ova)
MAIL_HOST="sandbox.smtp.mailtrap.io"
MAIL_PORT=2525
MAIL_USERNAME="TVOJ_MAILTRAP_USERNAME"
MAIL_PASSWORD="TVOJ_MAILTRAP_PASSWORD"
MAIL_ENCRYPTION="tls"
MAIL_FROM_ADDRESS="no-reply@fixplan.com"
MAIL_FROM_NAME="FixPlan Platform"

### Korak 3: Pokretanje xampp-a

Neophodno je pokrenuti apache i mysql servise unutar xampp-a

### Korak 4: Uvoz baze podatka u phpmyadmin

U paketu sa aplikacijom dostavljen je .sql fajl (eksport baze) koji sadrži strukturu tabela i početne podatke neophodne za rad sistema.

1. Otvorite Vaš web čitač i idite na adresu: http://localhost/phpmyadmin

2. Sa leve strane kliknite na opciju New kako biste kreirali novu bazu.

3. U polje "Database name" unesite tačan naziv koji ste definisali u .env fajlu: fixplan_db.

4. Kliknite na dugme Create.

5. Kada se otvori novo-kreirana prazna baza, u gornjem meniju kliknite na tab Import.

6. Kliknite na dugme Choose File (Izaberi datoteku) i locirajte .sql fajl koji se nalazi u folderu aplikacije.

7. Skrolujte na dno stranice i kliknite na dugme Import (ili Go). Sačekajte potvrdu da su svi upiti uspešno izvršeni.


## 🔐 Test Korisnički Nalozi

Nakon uvoza baze podataka, sistem je unapred konfigurisan sa tri nivoa pristupa. Možete koristiti sledeće pristupne podatke za testiranje različitih uloga na platformi:

| Uloga (Role) | Email Adresa | Lozinka |
| :--- | :--- | :--- |
| **Administrator** | `admin@example.com` | `admin123` |
| **Moderator (Radnik)** | `radnik@example.com` | `user123` |
| **Korisnik (Klijent)** | `user@example.com` | `user123` |