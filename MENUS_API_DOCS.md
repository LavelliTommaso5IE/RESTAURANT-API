# Documentazione API: Menù

Questa documentazione ti aiuta a testare i nuovi endpoint creati per gestire i Menù. 
Abbiamo due sezioni: una **Pubblica** (per i clienti) e una **Privata/Protetta** (per lo staff).

L'URL base dipende dal tenant su cui stai lavorando (es. `http://tuo-tenant.localhost:8000/api`).

---

## 1. Rotte Pubbliche (Clienti)
Queste rotte **NON** richiedono l'autenticazione JWT. Sono pensate per essere consumate da un QR Code, un'app o un frontend clienti. 
Mostrano ESCLUSIVAMENTE i menù che hanno il campo `is_active` impostato a `true`.

### 1.1 Elenco Menù Pubblici
Recupera tutti i menù attivi, inclusi i loro piatti e categorie associati.
- **URL**: `/api/public/menus`
- **Metodo**: `GET`
- **Auth**: Nessuna

### 1.2 Dettaglio Singolo Menù Pubblico
- **URL**: `/api/public/menus/{id}` (es. `/api/public/menus/1`)
- **Metodo**: `GET`
- **Auth**: Nessuna

#### Esempio di Risposta (Public)
```json
{
    "data": {
        "id": 1,
        "name": "Menù di Ferragosto",
        "description": "Speciale menù degustazione per il pranzo di Ferragosto",
        "cover": "https://esempio.com/img/ferragosto.jpg",
        "is_active": true,
        "dishes": [
            {
                "id": 5,
                "name": "Spaghetti alla Carbonara",
                "description": "Pasta con guanciale, pecorino e pepe",
                "price": 12.50,
                "category": {
                    "id": 2,
                    "name": "Primi Piatti"
                }
            }
        ]
    }
}
```
*(Nota: se provi ad accedere a un menù con `is_active = false` da questa rotta, riceverai un errore 404).*

---

## 2. Rotte Private (Staff)
Queste rotte richiedono il **JWT** (Header `Authorization: Bearer <token>`) e i permessi corretti (`view_menus`, `edit_menus`). Permettono di fare CRUD su TUTTI i menù (attivi e non).

### 2.1 Elenco Completo Menù (Staff)
Recupera tutti i menù presenti a DB.
- **URL**: `/api/menus`
- **Metodo**: `GET`
- **Permesso**: `view_menus`

### 2.2 Creazione Menù
Crea un nuovo menù e lo associa a uno o più piatti esistenti.
- **URL**: `/api/menus`
- **Metodo**: `POST`
- **Permesso**: `edit_menus`

#### Body (JSON)
```json
{
    "name": "Menù Cena",
    "description": "I nostri piatti classici per la cena",
    "cover": "https://esempio.com/img/cena.jpg", // Opzionale
    "is_active": true, // Opzionale (default: true)
    "dishes": [1, 5, 12] // Passa qui un array con gli ID dei Piatti da associare
}
```

### 2.3 Aggiornamento Menù
Modifica l'anagrafica del menù o l'elenco dei suoi piatti.
- **URL**: `/api/menus/{id}`
- **Metodo**: `PUT`
- **Permesso**: `edit_menus`

#### Body (JSON)
```json
{
    "is_active": false,  // Spegne il menù e lo nasconde dalle rotte pubbliche
    "dishes": [5, 12]    // Il piatto ID=1 viene rimosso dal menù, rimangono il 5 e il 12
}
```

### 2.4 Eliminazione Menù
Cancella definitivamente un menù dal database. (I piatti al suo interno **non** verranno cancellati, verrà solo eliminata l'associazione).
- **URL**: `/api/menus/{id}`
- **Metodo**: `DELETE`
- **Permesso**: `edit_menus`
