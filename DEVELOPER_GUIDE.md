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
```json
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

Questa sezione elenca tutti gli endpoint disponibili. Per aggiornamenti (`PUT`) o creazioni (`POST`), ricorda di impostare l'header `Content-Type: application/json`.

### 🌐 3.1. Livello Centrale e Accesso (Auth)
Queste rotte gestiscono l'autenticazione all'interno del tenant.

#### Crea Tenant (Scope: Database Centrale)
**Endpoint:** `POST /api/create-tenant`
**Body:**
```json
{
  "id": "ristorante1",
  "domain": "ristorante1.localhost"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Tenant creato con successo",
  "data": {
    "id": "ristorante1",
    "domain": "ristorante1.localhost"
  }
}
```

#### Verifica Tenant
**Endpoint:** `GET /api/check-tenant`
**Risposta (200 OK):**
```json
{
  "message": "Tenant trovato",
  "data": {
    "id": "ristorante1"
  }
}
```

#### Login Staff
**Endpoint:** `POST /api/login`
**Body:**
```json
{
  "email": "admin@example.com",
  "password": "password123"
}
```
**Risposta (200 OK):**
*(I token vengono iniettati nei Cookie HttpOnly `Authorization` e `Refresh`)*
```json
{
  "message": "Login effettuato con successo",
  "data": {
    "id": 1,
    "name": "Admin",
    "role": "Amministratore"
  }
}
```

#### Utente Loggato (Profilo)
**Endpoint:** `GET /api/me`
**Permesso:** *Auth JWT*
**Risposta (200 OK):**
```json
{
  "message": "Dettagli utente recuperati",
  "data": {
    "id": 1,
    "name": "Mario Rossi",
    "email": "mario@example.com",
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
```json
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
```json
{
  "message": "Lista utenti recuperata",
  "data": [
    {
      "id": 1,
      "name": "Luigi",
      "surname": "Verdi",
      "email": "luigi@example.com",
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
```json
{
  "name": "Anna",
  "surname": "Neri",
  "email": "anna@example.com",
  "password": "SecretPassword1!",
  "role_id": 2
}
```
**Risposta (201 Created):**
```json
{
  "message": "Utente creato con successo",
  "data": {
    "id": 2,
    "name": "Anna",
    "surname": "Neri",
    "email": "anna@example.com",
    "role": {
      "id": 2,
      "name": "Cameriere"
    }
  }
}
```

#### Modifica Utente
**Endpoint:** `PUT /api/users/{id}`
**Permesso:** `edit_users`
**Body:**
```json
{
  "name": "Anna Maria"
}
```
**Risposta (200 OK):**
```json
{
  "message": "Utente aggiornato con successo",
  "data": {
    "id": 2,
    "name": "Anna Maria",
    "surname": "Neri"
  }
}
```

#### Elimina Utente
**Endpoint:** `DELETE /api/users/{id}`
**Permesso:** `edit_users`
**Risposta (200 OK):**
```json
{
  "message": "Utente eliminato con successo",
  "data": null
}
```

#### Lista Ruoli
**Endpoint:** `GET /api/roles`
**Permesso:** `view_roles`
**Risposta (200 OK):**
```json
{
  "message": "Lista ruoli recuperata",
  "data": [
    {
      "id": 1,
      "name": "Amministratore"
    },
    {
      "id": 2,
      "name": "Cameriere"
    }
  ]
}
```

#### Crea Ruolo
**Endpoint:** `POST /api/roles`
**Permesso:** `edit_roles`
**Body:**
```json
{
  "name": "Cuoco"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Ruolo creato con successo",
  "data": {
    "id": 3,
    "name": "Cuoco"
  }
}
```

#### Assegna Permessi a un Ruolo
**Endpoint:** `PUT /api/roles/{id}/permissions`
**Permesso:** `edit_roles`
**Body:**
```json
{
  "permissions": [1, 2, 5, 8]
}
```
*L'array contiene gli ID numerici dei permessi dalla tabella permissions.*
**Risposta (200 OK):**
```json
{
  "message": "Permessi aggiornati con successo",
  "data": {
    "id": 3,
    "name": "Cuoco",
    "permissions": [
      { "id": 1, "name": "view_orders" },
      { "id": 2, "name": "edit_orders" }
    ]
  }
}
```

#### Lista Permessi di Sistema
**Endpoint:** `GET /api/permissions`
**Permesso:** `view_permissions`
**Risposta (200 OK):**
```json
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
```json
{
  "message": "Lista categorie recuperata",
  "data": [
    {
      "id": 1,
      "name": "Primi Piatti",
      "description": "Pasta e risotti"
    }
  ]
}
```

#### Dettaglio Categoria (Con piatti)
**Endpoint:** `GET /api/categories/{id}`
**Permesso:** `view_categories`
**Risposta (200 OK):**
```json
{
  "message": "Dettaglio categoria recuperato",
  "data": {
    "category": {
      "id": 1,
      "name": "Primi Piatti",
      "description": "Pasta e risotti"
    },
    "dishes": [
      {
        "id": 10,
        "name": "Spaghetti alla Carbonara",
        "price": 12.00,
        "is_orderable": true
      }
    ]
  }
}
```

#### Crea Categoria
**Endpoint:** `POST /api/categories`
**Permesso:** `edit_categories`
**Body:**
```json
{
  "name": "Pizze",
  "description": "Pizze cotte nel forno a legna"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Categoria creata con successo",
  "data": {
    "id": 2,
    "name": "Pizze",
    "description": "Pizze cotte nel forno a legna"
  }
}
```

#### Lista Piatti
**Endpoint:** `GET /api/dishes`
**Permesso:** `view_dishes`
**Risposta (200 OK):**
```json
{
  "message": "Lista piatti recuperata",
  "data": [
    {
      "id": 10,
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
```json
{
  "name": "Pizza Margherita",
  "description": "Pomodoro, mozzarella, basilico",
  "price": 8.50,
  "category_id": 2,
  "is_orderable": true
}
```
**Risposta (201 Created):**
```json
{
  "message": "Piatto creato con successo",
  "data": {
    "id": 11,
    "name": "Pizza Margherita",
    "price": 8.50,
    "category_id": 2,
    "is_orderable": true
  }
}
```

#### Crea Menù
**Endpoint:** `POST /api/menus`
**Permesso:** `edit_menus`
**Body:**
```json
{
  "name": "Menù Cena",
  "description": "Menù serale del weekend",
  "is_active": true,
  "dishes": [10, 11] 
}
```
*L'array `dishes` contiene gli ID dei piatti da includere nel menù.*
**Risposta (201 Created):**
```json
{
  "message": "Menù creato con successo",
  "data": {
    "id": 1,
    "name": "Menù Cena",
    "is_active": true,
    "dishes": [
      { "id": 10, "name": "Spaghetti alla Carbonara" },
      { "id": 11, "name": "Pizza Margherita" }
    ]
  }
}
```

#### Menù Pubblico (Digital Menu per i clienti)
**Endpoint:** `GET /api/public/menus`
**Permesso:** *Nessuno (Pubblico)*
**Risposta (200 OK):**
*Ritorna solo i menù attivi e i piatti con `is_orderable = true`*
```json
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
```json
{
  "message": "Lista prodotti recuperata",
  "data": [
    {
      "id": 1,
      "name": "Farina 00",
      "unit": "kg",
      "quantity_in_stock": 50.5,
      "alert_threshold": 10.0
    }
  ]
}
```

#### Crea Prodotto
**Endpoint:** `POST /api/products`
**Permesso:** `edit_products`
**Body:**
```json
{
  "name": "Pomodoro Pelato",
  "unit": "kg",
  "quantity_in_stock": 30.0,
  "alert_threshold": 5.0
}
```
**Risposta (201 Created):**
```json
{
  "message": "Prodotto creato con successo",
  "data": {
    "id": 2,
    "name": "Pomodoro Pelato",
    "unit": "kg",
    "quantity_in_stock": 30.0,
    "alert_threshold": 5.0
  }
}
```

---

### 🪑 3.5. Sala e Tavoli

#### Lista Tavoli
**Endpoint:** `GET /api/tables`
**Permesso:** `view_tables`
**Risposta (200 OK):**
```json
{
  "message": "Mappa tavoli recuperata",
  "data": [
    {
      "id": 1,
      "name": "Tavolo 1",
      "seats": 4,
      "status": "free",
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
```json
{
  "name": "Tavolo 2",
  "seats": 2,
  "status": "free"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Tavolo creato con successo",
  "data": {
    "id": 2,
    "name": "Tavolo 2",
    "seats": 2,
    "status": "free"
  }
}
```

#### Accorpa Tavoli
**Endpoint:** `POST /api/tables/{id}/join`
**Permesso:** `edit_tables`
**Descrizione:** Accorpa il tavolo `child_id` (Tavolo 2) dentro il tavolo principale specificato nell'URL `{id}` (Tavolo 1).
**Body:**
```json
{
  "child_id": 2
}
```
**Risposta (200 OK):**
```json
{
  "message": "Tavoli accorpati con successo",
  "data": {
    "id": 1,
    "name": "Tavolo 1",
    "children": [
      {
        "id": 2,
        "name": "Tavolo 2"
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
```json
{
  "message": "PIN generato con successo",
  "data": {
    "id": 1,
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
```json
{
  "message": "Tavolo liberato con successo",
  "data": {
    "id": 1,
    "status": "free",
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
```json
{
  "first_name": "Giulia",
  "last_name": "Bianchi",
  "phone": "3331234567",
  "email": "giulia@example.com"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Cliente creato con successo",
  "data": {
    "id": 1,
    "first_name": "Giulia",
    "last_name": "Bianchi",
    "phone": "3331234567"
  }
}
```

#### Crea Prenotazione
**Endpoint:** `POST /api/reservations`
**Permesso:** `edit_reservations`
**Body:**
```json
{
  "reservation_date": "2026-06-15",
  "reservation_time": "20:30:00",
  "people_count": 4,
  "customer_id": 1,
  "table_id": 1,
  "status": "confirmed",
  "notes": "Richiesto seggiolone"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Prenotazione creata con successo",
  "data": {
    "id": 10,
    "reservation_date": "2026-06-15",
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
```json
{
  "table_id": 1,
  "customer_id": 1,
  "notes": "Cliente abituale"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Ordine creato con successo",
  "data": {
    "id": 100,
    "table_id": 1,
    "status": "open",
    "total_amount": 0.00
  }
}
```

#### Dettaglio Ordine
**Endpoint:** `GET /api/orders/{id}`
**Permesso:** `view_orders`
**Risposta (200 OK):**
```json
{
  "message": "Dettaglio ordine recuperato",
  "data": {
    "id": 100,
    "status": "open",
    "total_amount": 20.50,
    "discount_amount": 0.00,
    "final_amount": 20.50,
    "paid_amount": 0.00,
    "remaining_amount": 20.50,
    "items": [
      {
        "id": 50,
        "dish_name": "Pizza Margherita",
        "quantity": 1,
        "unit_price": 8.50,
        "status": "pending",
        "notes": "Ben cotta"
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
```json
{
  "dish_id": 11,
  "quantity": 2,
  "notes": "Ben cotta"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Elemento aggiunto all'ordine con successo",
  "data": {
    "id": 51,
    "dish_id": 11,
    "quantity": 2,
    "unit_price": 8.50,
    "status": "pending",
    "notes": "Ben cotta"
  }
}
```

#### Aggiorna Stato Piatto (Kitchen Display System)
**Endpoint:** `PUT /api/order-items/{id}/status`
**Permesso:** `edit_comande`
**Descrizione:** Stati possibili: `pending`, `preparing`, `ready`, `served`.
**Body:**
```json
{
  "status": "preparing"
}
```
**Risposta (200 OK):**
```json
{
  "message": "Stato elemento ordine aggiornato con successo",
  "data": {
    "id": 51,
    "status": "preparing"
  }
}
```

#### Chiudi Ordine
**Endpoint:** `POST /api/orders/{id}/close`
**Permesso:** `edit_orders`
**Descrizione:** Cambia lo stato dell'ordine in `closed` e libera il tavolo (`free`) ad esso collegato.
**Body:** `{}` (Vuoto)
**Risposta (200 OK):**
```json
{
  "message": "Ordine chiuso con successo",
  "data": {
    "id": 100,
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
**Body (Gift Card):**
```json
{
  "name": "Gift Card 50 Euro",
  "type": "gift_card",
  "value": 50.00,
  "is_active": true
}
```
**Risposta (201 Created):**
```json
{
  "message": "Sconto creato con successo",
  "data": {
    "id": 5,
    "name": "Gift Card 50 Euro",
    "type": "gift_card",
    "value": 50.00,
    "current_balance": 50.00,
    "code": "X9F2M1L0P5K8",
    "is_active": true
  }
}
```

#### Verifica Codice Sconto / Gift Card
**Endpoint:** `GET /api/discounts/code/{code}`
**Permesso:** `view_discounts`
**Risposta (200 OK):**
```json
{
  "message": "Sconto recuperato con successo",
  "data": {
    "id": 5,
    "name": "Gift Card 50 Euro",
    "current_balance": 50.00,
    "is_active": true
  }
}
```

#### Registra Pagamento
**Endpoint:** `POST /api/payments/order/{id}`
**Permesso:** `edit_payments`
**Descrizione:** Genera la transazione e riduce il conto da pagare `remaining_amount`. **Non consente di pagare più del saldo residuo (Overpayment)**.
**Body (Pagamento Standard):**
```json
{
  "amount": 20.00,
  "payment_method": "cash"
}
```
**Body (Pagamento con Gift Card):**
```json
{
  "amount": 20.00,
  "payment_method": "gift_card",
  "discount_code": "X9F2M1L0P5K8"
}
```
**Risposta (201 Created):**
```json
{
  "message": "Pagamento registrato con successo",
  "data": {
    "id": 20,
    "amount": 20.00,
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
