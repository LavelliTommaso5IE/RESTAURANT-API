# 📖 Manuale Sviluppatore API - Restaurant System

Benvenuto nella guida completa all'utilizzo del backend per il sistema di gestione ristorazione. Questa documentazione è concepita come riferimento tecnico per lo sviluppo e l'integrazione con client Frontend (Web, Mobile, KDS, POS).

---

## 1. 🏗 Architettura & Autenticazione

Il sistema è basato su **Laravel 11** ed è strutturato come applicazione **SaaS Multi-Tenant**. 
Ogni ristorante rappresenta un "Tenant" con un proprio database separato fisicamente (isolamento dei dati totale). Il tenant viene riconosciuto dinamicamente attraverso il **Dominio** della richiesta HTTP.

### Sicurezza e JWT via Cookie
Per massimizzare la sicurezza contro attacchi XSS (Cross-Site Scripting), i token JWT **NON** devono essere inviati manualmente tramite l'header `Authorization: Bearer <token>`.
Invece, al login il backend rilascia due cookie `HttpOnly`:
1. `Authorization` (Access Token - Scadenza breve, es. 15 minuti)
2. `Refresh` (Refresh Token - Scadenza lunga, es. 7 giorni)

> [!IMPORTANT]
> **Configurazione Frontend (CORS)**: Affinché il browser invii questi cookie automaticamente ad ogni chiamata, è necessario configurare le richieste Axios/Fetch impostando `withCredentials: true` (o `credentials: 'include'`).

### Standardizzazione Risposte
Ogni endpoint dell'applicazione (salvo eccezioni pubbliche minori) restituisce una risposta JSON strutturata sistematicamente con questo formato:
```jsonc
{
  "message": "Descrizione testuale dell'operazione",
  "data": { ... } // L'oggetto o array contenente i dati veri e propri
}
```
*In caso di `DELETE`, il campo `data` varrà `null`.*

---

## 2. 🚀 Guida all'Avvio (Getting Started)

Se stai configurando il progetto partendo da zero, segui questi passaggi:

1. **Requisiti di Sistema**: PHP >= 8.2, Composer, MySQL/MariaDB o SQLite.
2. **Installazione Dipendenze**:
   ```bash
   composer install
   ```
3. **Configurazione Ambiente (.env)**:
   Duplica il file `.env.example` in `.env`.
   Imposta il database centrale:
   ```env
   DB_CONNECTION=mysql # o sqlite
   DB_DATABASE=restaurant_central_db
   ```
   Aggiungi un segreto crittografico per il JWT:
   ```env
   JWT_SECRET="una-stringa-molto-lunga-e-sicura-32-char"
   ```
4. **Generazione Chiavi e Migrazioni**:
   ```bash
   php artisan key:generate
   php artisan migrate:fresh          # Crea le tabelle nel DB Centrale (Tenants, Domains)
   php artisan tenants:migrate        # Crea/Aggiorna le tabelle all'interno di tutti i database dei Tenant
   ```

---


## 3. 📚 Reference Completo Endpoint

> 💡 **Nota Postman:** Tutti gli esempi pratici (inclusi i payload completi) riportati in questa documentazione sono disponibili ed eseguibili direttamente tramite le collezioni salvate nella cartella `POSTMAN REQUESTS` presente nella root del progetto.

Questa sezione elenca tutti gli endpoint disponibili. Per aggiornamenti (`PUT`) o creazioni (`POST`), ricorda di impostare l'header `Content-Type: application/json`.


### 🌐 3.1. Livello Centrale e Accesso (Auth)
Queste rotte gestiscono l'autenticazione all'interno del tenant.

#### Crea Tenant (Scope: Database Centrale)
**Endpoint:** `POST /api/create-tenant`
**Body:**
```jsonc
{
    "name": "Acme Corporation",
    "description": "Leader mondiale in prodotti per catturare Road Runner", // (Facoltativo)
    "admin_name": "Mario",
    "admin_surname": "Rossi",
    "admin_email": "mario.rossi@acme.com",
    "admin_password": "Password1!",
    "admin_password_confirmation": "Password1!"
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Tenant creato con successo",
  "data": {
    "id": "acme-corporation",
    "domain": "acme-corporation.localhost",
    "name": "Acme Corporation"
  }
}
```

#### Verifica Tenant
**Endpoint:** `GET /api/check-tenant`
**Risposta (200 OK):**
```jsonc
{
  "message": "Tenant trovato",
  "data": {
    "id": "acme-corporation"
  }
}
```

#### Login Staff
**Endpoint:** `POST /api/login`
**Body:**
```jsonc
{
    "email": "mario.rossi@acme.com",
    "password": "Password1!"
}
```
**Risposta (200 OK):**
*(I token vengono iniettati nei Cookie HttpOnly `Authorization` e `Refresh`)*
```jsonc
{
  "message": "Login effettuato con successo",
  "data": {
    "id": 1,
    "name": "Mario",
    "role": "Amministratore"
  }
}
```

#### Utente Loggato (Profilo)
**Endpoint:** `GET /api/me`
**Permesso:** *Auth JWT*
**Risposta (200 OK):**
```jsonc
{
  "message": "Dettagli utente recuperati",
  "data": {
    "id": 1,
    "name": "Mario Rossi",
    "email": "mario.rossi@acme.com",
    "role": {
      "id": 1,
      "name": "Amministratore"
    },
    "permissions": ["view_orders", "edit_tables"]
  }
}
```

#### Logout
**Endpoint:** `GET /api/logout`
**Permesso:** *Auth JWT*
**Risposta (200 OK):**
*(I cookie HttpOnly vengono invalidati e rimossi dal browser)*
```jsonc
{
  "message": "Logout effettuato con successo",
  "data": null
}
```

---

### 👥 3.2. Utenti, Ruoli e Permessi

#### Lista Utenti
**Endpoint:** `GET /api/users`
**Permesso:** `view_users`
**Risposta (200 OK):**
```jsonc
{
  "message": "Lista utenti recuperata",
  "data": [
    {
      "id": 2,
      "name": "Gianni",
      "surname": "Bianchi",
      "email": "gianni.bianchi@acme.com",
      "role": {
        "id": 2,
        "name": "Cameriere"
      }
    }
  ]
}
```

#### Crea Utente
**Endpoint:** `POST /api/users`
**Permesso:** `edit_users`
**Body:**
```jsonc
{
    "name": "Gianni",
    "surname": "Bianchi",
    "email": "gianni.bianchi@acme.com",
    "password": "PasswordSicura123!",
    "password_confirmation": "PasswordSicura123!",
    "role_id": 2 // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Utente creato con successo",
  "data": {
    "id": 2,
    "name": "Gianni",
    "surname": "Bianchi",
    "email": "gianni.bianchi@acme.com",
    "role": null
  }
}
```

#### Modifica Utente
**Endpoint:** `PUT /api/users/{id}`
**Permesso:** `edit_users`
**Body:**
```jsonc
{
    "name" : "Marco"
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Utente aggiornato con successo",
  "data": {
    "id": 4,
    "name": "Marco",
    "surname": "Bianchi"
  }
}
```

#### Elimina Utente
**Endpoint:** `DELETE /api/users/{id}`
**Permesso:** `edit_users`
**Risposta (200 OK):**
```jsonc
{
  "message": "Utente eliminato con successo",
  "data": null
}
```

#### Lista Ruoli
**Endpoint:** `GET /api/roles`
**Permesso:** `view_roles`
**Risposta (200 OK):**
```jsonc
{
  "message": "Lista ruoli recuperata",
  "data": [
    {
      "id": 1,
      "name": "Amministratore"
    }
  ]
}
```

#### Crea Ruolo
**Endpoint:** `POST /api/roles`
**Permesso:** `edit_roles`
**Body:**
```jsonc
{
    "name" : "Ruolo nome",
    "description" : "descrizione ruolo"
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Ruolo creato con successo",
  "data": {
    "id": 3,
    "name": "Ruolo nome",
    "description": "descrizione ruolo"
  }
}
```

#### Modifica Ruolo
**Endpoint:** `PUT /api/roles/{id}`
**Permesso:** `edit_roles`
**Body:**
```jsonc
{
    "name" : "RUOLO_NEW",
    "description" : "ruolo aggiornato descrizione"
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Ruolo aggiornato con successo",
  "data": {
    "id": 3,
    "name": "RUOLO_NEW",
    "description": "ruolo aggiornato descrizione"
  }
}
```

#### Assegna Permessi a un Ruolo
**Endpoint:** `PUT /api/roles/{id}/permissions`
**Permesso:** `edit_roles`
**Body:**
```jsonc
{
    "permission_ids" : [4,5]
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Permessi aggiornati con successo",
  "data": {
    "id": 2,
    "name": "Cameriere",
    "permissions": [
      { "id": 4, "name": "edit_orders" },
      { "id": 5, "name": "view_tables" }
    ]
  }
}
```

#### Lista Permessi di Sistema
**Endpoint:** `GET /api/permissions`
**Permesso:** `view_permissions`
**Risposta (200 OK):**
```jsonc
{
  "message": "Lista permessi recuperata",
  "data": [
    {
      "id": 1,
      "name": "view_users",
      "description": "Visualizza utenti"
    }
  ]
}
```

---

### 🥘 3.3. Catalogo: Categorie, Piatti e Menù

#### Lista Categorie
**Endpoint:** `GET /api/categories`
**Permesso:** `view_categories`
**Risposta (200 OK):**
```jsonc
{
  "message": "Lista categorie recuperata",
  "data": [
    {
      "id": 1,
      "name": "categoria_test",
      "description": null
    }
  ]
}
```

#### Dettaglio Categoria (Con piatti)
**Endpoint:** `GET /api/categories/{id}`
**Permesso:** `view_categories`
**Risposta (200 OK):**
```jsonc
{
  "message": "Dettaglio categoria recuperato",
  "data": {
    "category": {
      "id": 1,
      "name": "categoria_test",
      "description": null
    },
    "dishes": []
  }
}
```

#### Crea Categoria
**Endpoint:** `POST /api/categories`
**Permesso:** `edit_categories`
**Body:**
```jsonc
{
    "name": "categoria_test"
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Categoria creata con successo",
  "data": {
    "id": 1,
    "name": "categoria_test"
  }
}
```

#### Modifica Categoria
**Endpoint:** `PUT /api/categories/{id}`
**Permesso:** `edit_categories`
**Body:**
```jsonc
{
    "name": "categoria_test_edited"
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Categoria aggiornata con successo",
  "data": {
    "id": 1,
    "name": "categoria_test_edited"
  }
}
```

#### Lista Piatti
**Endpoint:** `GET /api/dishes`
**Permesso:** `view_dishes`
**Risposta (200 OK):**
```jsonc
{
  "message": "Lista piatti recuperata",
  "data": [
    {
      "id": 1,
      "name": "Spaghetti alla Carbonara",
      "price": 12.00,
      "category": {
        "id": 1,
        "name": "Primi Piatti"
      },
      "is_orderable": true
    }
  ]
}
```

#### Crea Piatto
**Endpoint:** `POST /api/dishes`
**Permesso:** `edit_dishes`
**Body:**
```jsonc
{
    "name": "Spaghetti alla Carbonara",
    "description": "Pasta con guanciale, pecorino e pepe", // (Facoltativo)
    "price": 12.00,
    "category_id": 1,
    "is_orderable": true, // (Facoltativo)
    "products": [ // (Facoltativo, array materie prime)
        {
            "id": 1,
            "quantity": 0.1,
            "tolerance_percentage": 5.0 // (Facoltativo)
        },
        {
            "id": 2,
            "quantity": 0.05,
            "tolerance_percentage": 0.0 // (Facoltativo)
        }
    ]
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Piatto creato con successo",
  "data": {
    "id": 1,
    "name": "Spaghetti alla Carbonara",
    "price": 12.00,
    "category_id": 1,
    "is_orderable": true, // (Facoltativo)
    "is_orderable": true,
    "products": [ // (Facoltativo, array materie prime) ... ]
  }
}
```

#### Modifica Piatto
**Endpoint:** `PUT /api/dishes/{id}`
**Permesso:** `edit_dishes`
**Body:**
```jsonc
{
    "price": 7.50,
    "description": "Dolce tipico italiano con savoiardi artigianali"
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Piatto aggiornato con successo",
  "data": {
    "id": 1,
    "name": "Tiramisù",
    "price": 7.50,
    "description": "Dolce tipico italiano con savoiardi artigianali"
  }
}
```

#### Crea Menù
**Endpoint:** `POST /api/menus`
**Permesso:** `edit_menus`
**Body:**
```jsonc
{
    "name": "Menù Cena",
    "description": "I nostri piatti classici per la cena", // (Facoltativo)
    "cover": "https://esempio.com/img/cena.jpg", // (Facoltativo)
    "is_active": true,
    "dishes": [1] // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Menù creato con successo",
  "data": {
    "id": 1,
    "name": "Menù Cena",
    "is_active": true,
    "dishes": [
      { "id": 1, "name": "Spaghetti alla Carbonara" }
    ]
  }
}
```

#### Modifica Menù
**Endpoint:** `PUT /api/menus/{id}`
**Permesso:** `edit_menus`
**Body:**
```jsonc
{
    "is_active": true,
    "dishes": [1, 2] // (Facoltativo)
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Menù aggiornato con successo",
  "data": {
    "id": 2,
    "is_active": true,
    "dishes": [
      { "id": 1, "name": "Spaghetti alla Carbonara" },
      { "id": 2, "name": "Pizza" }
    ]
  }
}
```

#### Menù Pubblico (Digital Menu per i clienti)
**Endpoint:** `GET /api/public/menus`
**Permesso:** *Nessuno (Pubblico)*
**Risposta (200 OK):**
*Ritorna solo i menù attivi e i piatti con `is_orderable = true`*
```jsonc
{
  "message": "Menu pubblici recuperati",
  "data": [
    {
      "id": 1,
      "name": "Menù Cena",
      "dishes": [ ... ]
    }
  ]
}
```

---

### 📦 3.4. Magazzino (Inventario)

#### Lista Prodotti
**Endpoint:** `GET /api/products`
**Permesso:** `view_products`
**Risposta (200 OK):**
```jsonc
{
  "message": "Lista prodotti recuperata",
  "data": [
    {
      "id": 1,
      "name": "Guanciale",
      "unit": "kg",
      "quantity_in_stock": 5.5,
      "alert_threshold": 0.0
    }
  ]
}
```

#### Crea Prodotto
**Endpoint:** `POST /api/products`
**Permesso:** `edit_products`
**Body:**
```jsonc
{
    "name": "Guanciale",
    "description": "Guanciale stagionato per Carbonara", // (Facoltativo)
    "quantity": 5.5,
    "unit": "kg"
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Prodotto creato con successo",
  "data": {
    "id": 1,
    "name": "Guanciale",
    "unit": "kg",
    "quantity_in_stock": 5.5
  }
}
```

#### Modifica Prodotto
**Endpoint:** `PUT /api/products/{id}`
**Permesso:** `edit_products`
**Body:**
```jsonc
{
    "quantity": 10.0
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Prodotto aggiornato con successo",
  "data": {
    "id": 1,
    "name": "Guanciale",
    "quantity_in_stock": 10.0
  }
}
```

---

### 🪑 3.5. Sala e Tavoli

#### Lista Tavoli
**Endpoint:** `GET /api/tables`
**Permesso:** `view_tables`
**Risposta (200 OK):**
```jsonc
{
  "message": "Mappa tavoli recuperata",
  "data": [
    {
      "id": 1,
      "name": "Tavolo 1",
      "seats": 2,
      "status": "free" // (Facoltativo),
      "pin": null,
      "parent_id": null,
      "children": []
    }
  ]
}
```

#### Crea Tavolo
**Endpoint:** `POST /api/tables`
**Permesso:** `edit_tables`
**Body:**
```jsonc
{
    "name": "Tavolo 1",
    "seats": 2,
    "status": "free" // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Tavolo creato con successo",
  "data": {
    "id": 1,
    "name": "Tavolo 1",
    "seats": 2,
    "status": "free" // (Facoltativo)
  }
}
```

#### Modifica Tavolo
**Endpoint:** `PUT /api/tables/{id}`
**Permesso:** `edit_tables`
**Body:**
```jsonc
{
    "name": "Tavolo 1A",
    "seats": 6,
    "status": "reserved"
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Tavolo aggiornato con successo",
  "data": {
    "id": 2,
    "name": "Tavolo 1A",
    "seats": 6,
    "status": "reserved"
  }
}
```

#### Accorpa Tavoli
**Endpoint:** `POST /api/tables/{id}/join`
**Permesso:** `edit_tables`
**Descrizione:** Accorpa il tavolo `child_id` (Tavolo 2) dentro il tavolo principale specificato nell'URL `{id}` (Tavolo 3).
**Body:**
```jsonc
{
    "parent_id": 2
}
```
*(Nota: L'ID del tavolo figlio va nell'URL, il parent_id nel body, in base alla logica del controller)*
**Risposta (200 OK):**
```jsonc
{
  "message": "Tavoli accorpati con successo",
  "data": {
    "id": 2,
    "children": [
      {
        "id": 3
      }
    ]
  }
}
```

#### Genera PIN Tavolo (Self Ordering)
**Endpoint:** `POST /api/tables/{id}/generate-pin`
**Permesso:** `edit_tables`
**Body:** `{}` (Vuoto)
**Risposta (200 OK):**
```jsonc
{
  "message": "PIN generato con successo",
  "data": {
    "id": 3,
    "pin": "8542"
  }
}
```

#### Libera Tavolo (Clear)
**Endpoint:** `POST /api/tables/{id}/clear`
**Permesso:** `edit_tables`
**Descrizione:** Libera lo stato del tavolo a `free`, scollega eventuali ordini aperti e distrugge il PIN.
**Body:** `{}` (Vuoto)
**Risposta (200 OK):**
```jsonc
{
  "message": "Tavolo liberato con successo",
  "data": {
    "id": 3,
    "status": "free" // (Facoltativo),
    "pin": null
  }
}
```

---

### 📅 3.6. Clienti e Prenotazioni

#### Crea Cliente
**Endpoint:** `POST /api/customers`
**Permesso:** `edit_customers`
**Body:**
```jsonc
{
    "first_name": "Luca",
    "last_name": "Bianchi",
    "phone": "+39 333 1234567", // (Facoltativo)
    "email": "luca.bianchi@email.it", // (Facoltativo)
    "vat_number": "IT12345678901", // (Facoltativo)
    "tax_code": "AAABBB80A01H501U", // (Facoltativo)
    "address": "Via Roma 66, Milano", // (Facoltativo)
    "notes": "Cliente abituale, preferisce tavoli all'aperto." // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Cliente creato con successo",
  "data": {
    "id": 1,
    "first_name": "Luca",
    "last_name": "Bianchi"
  }
}
```

#### Crea Prenotazione
**Endpoint:** `POST /api/reservations`
**Permesso:** `edit_reservations`
**Body:**
```jsonc
{
    "customer_id": 1,
    "table_id": 2,
    "reservation_date": "2026-05-15",
    "reservation_time": "20:30",
    "people_count": 4,
    "status": "confirmed", // (Facoltativo)
    "notes": "Richiesto seggiolone" // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Prenotazione creata con successo",
  "data": {
    "id": 1,
    "reservation_date": "2026-05-15",
    "reservation_time": "20:30:00",
    "status": "confirmed"
  }
}
```

---

### 🧾 3.7. Ordini e Comande (KDS)

#### Crea Ordine
**Endpoint:** `POST /api/orders`
**Permesso:** `edit_orders`
**Body:**
```jsonc
{
    "table_id": 1,
    "customer_id": 1, // (Facoltativo)
    "notes": "Tavolo vicino alla finestra" // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Ordine creato con successo",
  "data": {
    "id": 1,
    "table_id": 1,
    "customer_id": 1, // (Facoltativo)
    "status": "open",
    "total_amount": 0.00
  }
}
```

#### Associa Cliente a Ordine
**Endpoint:** `POST /api/orders/{id}/customer`
**Permesso:** `edit_orders`
**Body:**
```jsonc
{
    "customer_id": 1
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Cliente associato con successo",
  "data": {
    "id": 1,
    "customer_id": 1
  }
}
```

#### Dettaglio Ordine
**Endpoint:** `GET /api/orders/{id}`
**Permesso:** `view_orders`
**Risposta (200 OK):**
```jsonc
{
  "message": "Dettaglio ordine recuperato",
  "data": {
    "id": 1,
    "status": "open",
    "total_amount": 20.50,
    "discount_amount": 0.00,
    "final_amount": 20.50,
    "paid_amount": 0.00,
    "remaining_amount": 20.50,
    "items": [
      {
        "id": 1,
        "dish_name": "Spaghetti alla Carbonara",
        "quantity": 1,
        "unit_price": 12.00,
        "status": "pending"
      }
    ],
    "payments": []
  }
}
```

#### Aggiungi Piatto (Comanda)
**Endpoint:** `POST /api/order-items/order/{id}`
**Permesso:** `edit_orders`
**Descrizione:** Inserisce un piatto in un ordine e salva uno **snapshot** del prezzo.
**Body:**
```jsonc
{
    "dish_id": 1,
    "quantity": 2,
    "notes": "Senza pepe" // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Elemento aggiunto all'ordine con successo",
  "data": {
    "id": 1,
    "dish_id": 1,
    "quantity": 2,
    "unit_price": 12.00,
    "status": "pending",
    "notes": "Senza pepe" // (Facoltativo)
  }
}
```

#### Aggiorna Stato Piatto (Kitchen Display System)
**Endpoint:** `PUT /api/order-items/{id}/status`
**Permesso:** `edit_comande`
**Descrizione:** Stati possibili: `pending`, `preparing`, `ready`, `served`.
**Body:**
```jsonc
{
    "status": "ready"
}
```
**Risposta (200 OK):**
```jsonc
{
  "message": "Stato elemento ordine aggiornato con successo",
  "data": {
    "id": 1,
    "status": "ready"
  }
}
```

#### Chiudi Ordine
**Endpoint:** `POST /api/orders/{id}/close`
**Permesso:** `edit_orders`
**Descrizione:** Cambia lo stato dell'ordine in `closed` e libera il tavolo (`free`) ad esso collegato.
**Body:** `{}` (Vuoto)
**Risposta (200 OK):**
```jsonc
{
  "message": "Ordine chiuso con successo",
  "data": {
    "id": 1,
    "status": "closed"
  }
}
```

---

### 💳 3.8. Sconti e Pagamenti (Gift Card & Split-Bill)

#### Crea Sconto o Gift Card
**Endpoint:** `POST /api/discounts`
**Permesso:** `edit_discounts`
**Descrizione:** Può creare sconti in percentuale (`percentage`), importo fisso (`fixed`) o carte regalo (`gift_card`). Per le `gift_card` il codice viene auto-generato a 12 cifre se non fornito.
**Body:**
```jsonc
{
    "name": "Gift Card Compleanno Mario",
    "code": "SUMMER5", // (Facoltativo, autogenerato se omesso)
    "type": "percentage",
    "value": 5,
    "min_order_value": 0.00, // (Facoltativo)
    "is_active": true,
    "valid_until": "2026-12-31 23:59:59" // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Sconto creato con successo",
  "data": {
    "id": 1,
    "name": "Gift Card Compleanno Mario",
    "type": "percentage",
    "value": 5.00,
    "code": "SUMMER5", // (Facoltativo, autogenerato se omesso)
    "is_active": true
  }
}
```

#### Registra Pagamento
**Endpoint:** `POST /api/payments/order/{id}`
**Permesso:** `edit_payments`
**Descrizione:** Genera la transazione e riduce il conto da pagare `remaining_amount`. **Non consente di pagare più del saldo residuo (Overpayment)**.
**Body (Pagamento Standard):**
```jsonc
{
    "amount": 7.8,
    "payment_method": "cash",
    "notes": "Pagato da Mario" // (Facoltativo)
}
```
**Body (Pagamento con Gift Card):**
```jsonc
{
    "amount": 15,
    "payment_method": "gift_card",
    "discount_code": "MARIO",
    "notes": "Uso parziale del buono" // (Facoltativo)
}
```
**Risposta (201 Created):**
```jsonc
{
  "message": "Pagamento registrato con successo",
  "data": {
    "id": 2,
    "amount": 15.00,
    "payment_method": "gift_card",
    "discount_id": 5
  }
}
```

---

## 4. 🛠 Troubleshooting (Risoluzione Problemi Frequenti)

| Errore Comune | Causa Probabile | Soluzione |
| :--- | :--- | :--- |
| **`401 Unauthorized`** (Sembra che il login non funzioni) | Il Client non sta inviando i Cookie HttpOnly. | Verifica che le tue chiamate API (`axios` o `fetch`) abbiano il flag `withCredentials: true` abilitato (oppure `credentials: 'include'`). |
| **`403 Forbidden`** su una rotta | L'utente loggato non ha il permesso richiesto nel suo Ruolo. | Usa l'endpoint `/api/me` per ispezionare l'array `permissions` dell'utente. Aggiungi il permesso tramite `PUT /api/roles/{id}/permissions`. |
| **`Database db_tenant_X non esiste`** | Richiesta arrivata su dominio corretto, ma mancata esecuzione migrazioni. | Esegui nel terminale server: `php artisan tenants:migrate`. |
| **`422 Unprocessable Entity` (Messaggio Overpayment)** | Stai cercando di pagare es. 50€ su un conto che ha un `remaining_amount` di soli 30€. | Il frontend deve suggerire come importo massimo pagabile il valore residuo, per prevenire errori in cassa. |
| **`SQLSTATE[HY000]: General error: 1 cannot VACUUM`** | Problema tipico nei Test PHPUnit di Laravel SQLite quando si usa il trait `RefreshDatabase` assieme a transazioni/tenant. | Evita di usare `RefreshDatabase` nei test che gestiscono dinamicamente la creazione di DB SQLite; usa test "Unit" isolati con mock/stub. |

---

## 5. 📝 Implementazioni Future (TODO List Sviluppatore)

Il seguente elenco riporta le attività lasciate in sospeso come commenti `TODO:` all'interno del codice per la finalizzazione completa dell'applicativo:

### ✉️ Modulo Notifiche (Email)
- [ ] **Email di Registrazione**: (`UserController@createUser`). Aggiungere l'invio (tramite Job/Queue) di una email di benvenuto al dipendente.
- [ ] **Email Recupero Password**: (`AuthController`). Implementare logica di *Forgot Password* per ripristinare credenziali.
- [ ] **Email Soglia Magazzino**: Quando le giacenze scendono sotto la `alert_threshold`, notificare gli Amministratori automaticamente tramite un comando batch o evento `saved`.

### 📦 Consumo e Integrità Dati
- [ ] **Consumo Scorte Dinamico**: In `OrderItemController@updateStatus`, aggiungere l'algoritmo che scala le materie prime dalla tabella `products` quando una comanda passa allo stato `preparing`, basandosi sulla ricetta del piatto.
- [ ] **Snapshot Storico Prenotazioni**: (`ReservationController@store`). Aggiungere campi testuali (es. `snapshot_customer_name`) per registrare il nome cliente e numero tavolo *al momento della prenotazione*. Questo eviterà che modifiche o cancellazioni anagrafiche future alterino lo storico contabile/passato.
- [ ] **Logica Assegnazione Ruoli Frontend**: Includere nella UI una schermata per la selezione/deselezione dei permessi ai ruoli collegata all'endpoint `RoleController`.

### 🛡️ Miglioramenti Architetturali
- [ ] **Transazioni SQL (`DB::transaction`)**: Aggiungere le transazioni SQL in endpoint critici (come la Creazione degli Ordini complessi o il Pagamento con Gift Card in `PaymentController`). Questo assicura che in caso di errore (es. riga di rete caduta o bug), la deduzione dalla Gift Card e la registrazione del pagamento vengano effettuati o bloccati in blocco, preservando l'integrità atomica dei saldi.
- [ ] **Sistema di Licenze e Limiti (Tenant Plans)**: Aggiungere a livello Centrale una logica per gestire i piani in abbonamento (Es. Free, Premium, Enterprise) che imponga dei limiti hardware/software sui Tenant (es. Massimo 5 Utenti Staff per il piano Free, o massimo 10 Tavoli). Controllo da attuare nei Service Layer o tramite Middleware globale.
