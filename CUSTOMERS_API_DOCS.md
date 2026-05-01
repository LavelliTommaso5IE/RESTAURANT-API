# Documentazione API: Anagrafica Clienti (Customers)

Questa documentazione illustra gli endpoint per gestire l'anagrafica dei clienti del ristorante. Utile per prenotazioni, ordini da asporto, consegne a domicilio (delivery) o per la fatturazione rapida a fine pasto.
Tutte le rotte sono protette da **JWT** e richiedono i permessi `view_customers` (per leggere) o `edit_customers` (per modificare).

L'URL base dipende dal tenant su cui stai lavorando (es. `http://tuo-tenant.localhost:8000/api`).

---

## 1. Elenco Clienti
Recupera l'elenco di tutti i clienti salvati nel database del ristorante.
- **URL**: `/api/customers`
- **Metodo**: `GET`
- **Permesso**: `view_customers`

## 2. Dettaglio Singolo Cliente
Recupera l'anagrafica completa di un cliente specifico tramite il suo ID.
- **URL**: `/api/customers/{id}` (es. `/api/customers/1`)
- **Metodo**: `GET`
- **Permesso**: `view_customers`

## 3. Creazione Cliente
Crea un nuovo cliente. Solo nome (`first_name`) e cognome (`last_name`) sono obbligatori, il resto è facoltativo per velocizzare il lavoro in cassa.
- **URL**: `/api/customers`
- **Metodo**: `POST`
- **Permesso**: `edit_customers`

#### Body (JSON)
```json
{
    "first_name": "Mario",
    "last_name": "Rossi",
    "phone": "+39 333 1234567",
    "email": "mario.rossi@email.it",
    "vat_number": "IT12345678901",
    "tax_code": "RSSMRA80A01H501U",
    "address": "Via Roma 1, Milano",
    "notes": "Cliente abituale, preferisce tavoli all'aperto."
}
```

## 4. Modifica Cliente
Aggiorna i dati di un cliente esistente. Si possono passare anche solo i campi che si desidera modificare.
- **URL**: `/api/customers/{id}` (es. `/api/customers/1`)
- **Metodo**: `PUT`
- **Permesso**: `edit_customers`

#### Body (JSON)
```json
{
    "address": "Via Milano 2, Roma",
    "notes": "Si è trasferito."
}
```

## 5. Eliminazione Cliente
Cancella un'anagrafica cliente dal sistema.
- **URL**: `/api/customers/{id}` (es. `/api/customers/1`)
- **Metodo**: `DELETE`
- **Permesso**: `edit_customers`
