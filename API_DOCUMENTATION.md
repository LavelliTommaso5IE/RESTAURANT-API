# 📘 Manuale Tecnico API - Restaurant System (69 Endpoint)

Questa documentazione associa ogni chiamata al suo esatto formato di risposta JSON.

---

## 🔐 1. SISTEMA E AUTENTICAZIONE (5)

| Metodo | Endpoint | Descrizione |
| :--- | :--- | :--- |
| `POST` | `/api/login` | Login staff. |
| `GET` | `/api/me` | Profilo utente loggato. |
| `GET` | `/api/check-tenant` | Info base del ristorante. |
| `POST` | `/api/create-tenant` | Crea nuovo ristorante (Centrale). |
| `GET` | `/api/logout` | Logout. |

**Esempio Risposta `/api/me`:**
```json
{
  "data": {
    "id": 1,
    "name": "Mario Rossi",
    "email": "mario@example.com",
    "role": { "id": 1, "name": "admin" },
    "permissions": ["view_orders", "edit_tables"]
  }
}
```

---

## 👥 2. STAFF E RUOLI (11)

| Metodo | Endpoint | Risposta JSON |
| :--- | :--- | :--- |
| `GET` | `/api/users` | Lista Utenti. |
| `POST` | `/api/users` | Singolo Utente (Creato). |
| `GET` | `/api/roles` | Lista Ruoli. |
| `PUT` | `/api/roles/{id}/permissions` | Ruolo con permessi aggiornati. |

**Esempio Risposta `/api/users`:**
```json
{
  "data": [
    {
      "id": 1,
      "name": "Luigi",
      "surname": "Verdi",
      "email": "luigi@example.com",
      "stato": "attivo",
      "role": { "id": 2, "name": "cameriere" }
    }
  ]
}
```

---

## 📦 3. CATALOGO E PRODOTTI (22)
*(Include Categorie, Prodotti, Piatti e Menù)*

| Metodo | Endpoint | Risposta JSON |
| :--- | :--- | :--- |
| `GET` | `/api/dishes` | Lista Piatti. |
| `GET` | `/api/categories` | Lista Categorie. |
| `GET` | `/api/products` | Lista Prodotti (Magazzino). |
| `GET` | `/api/public/menus` | Menù per sito pubblico. |

**Esempio Risposta `/api/dishes`:**
```json
{
  "data": {
    "id": 42,
    "name": "Pizza Margherita",
    "price": 8.50,
    "description": "Pomodoro, mozzarella, basilico",
    "category": { "id": 2, "name": "Pizze" },
    "is_orderable": true
  }
}
```

---

## 🪑 4. SALA E TAVOLI (9)

| Metodo | Endpoint | Risposta JSON |
| :--- | :--- | :--- |
| `GET` | `/api/tables` | Mappa dei tavoli. |
| `POST` | `/api/tables/{id}/join` | Tavolo unito. |
| `POST` | `/api/tables/{id}/generate-pin` | PIN di accesso. |

**Esempio Risposta `/api/tables`:**
```json
{
  "data": [
    {
      "id": 5,
      "name": "Tavolo 5",
      "seats": 4,
      "status": "occupied", 
      "pin": "1234",
      "parent": null,
      "children": [] 
    }
  ]
}
```

---

## 📅 5. CLIENTI E PRENOTAZIONI (8)

| Metodo | Endpoint | Risposta JSON |
| :--- | :--- | :--- |
| `GET` | `/api/reservations` | Lista Prenotazioni. |
| `POST` | `/api/customers` | Nuovo Cliente. |

**Esempio Risposta `/api/reservations`:**
```json
{
  "data": {
    "id": 10,
    "reservation_date": "2024-05-15",
    "reservation_time": "20:30",
    "people_count": 4,
    "customer": { "id": 1, "first_name": "Anna", "phone": "333..." },
    "table": { "id": 5, "name": "Tavolo 5" }
  }
}
```

---

## 🧾 6. ORDINI E CASSA (14)

| Metodo | Endpoint | Risposta JSON |
| :--- | :--- | :--- |
| `GET` | `/api/orders/{id}` | Dettaglio Ordine. |
| `POST` | `/api/payments/order/{id}` | Ricevuta Pagamento. |
| `GET` | `/api/discounts/code/{code}` | Validità Coupon. |

**Esempio Risposta `/api/orders/{id}`:**
```json
{
  "data": {
    "id": 100,
    "status": "active",
    "total_amount": 50.00,
    "final_amount": 45.00,
    "paid_amount": 20.00,
    "remaining_amount": 25.00,
    "items": [
      { "id": 1, "dish_name": "Pasta", "quantity": 1, "status": "preparing" }
    ],
    "payments": [
      { "amount": 20.00, "method": "card" }
    ]
  }
}
```

---

## ⚠️ 7. ERRORI DI VALIDAZIONE (422)
Quando invii dati errati (es. email già esistente o campo mancante):
```json
{
  "message": "The given data was invalid.",
  "errors": {
    "email": ["L'indirizzo email è già in uso."],
    "password": ["La password deve avere almeno 8 caratteri."]
  }
}
```
