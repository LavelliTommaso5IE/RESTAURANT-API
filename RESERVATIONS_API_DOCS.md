# Documentazione API: Prenotazioni (Reservations)

Questa documentazione illustra gli endpoint per gestire le prenotazioni, associando un cliente a un tavolo specifico in una determinata data e ora.
Tutte le rotte sono protette da **JWT** e richiedono i permessi `view_reservations` (per leggere) o `edit_reservations` (per modificare).

L'URL base dipende dal tenant su cui stai lavorando (es. `http://tuo-tenant.localhost:8000/api`).

---

## 1. Elenco Prenotazioni
Recupera l'elenco di tutte le prenotazioni. Puoi filtrare per data specifica aggiungendo il parametro `date`.
- **URL**: `/api/reservations` (es. `/api/reservations?date=2026-05-15`)
- **Metodo**: `GET`
- **Permesso**: `view_reservations`

## 2. Dettaglio Singola Prenotazione
Recupera i dettagli di una singola prenotazione.
- **URL**: `/api/reservations/{id}` (es. `/api/reservations/1`)
- **Metodo**: `GET`
- **Permesso**: `view_reservations`

## 3. Creazione Prenotazione
Crea una nuova prenotazione collegando il cliente (`customer_id`) al tavolo (`table_id`).
- **URL**: `/api/reservations`
- **Metodo**: `POST`
- **Permesso**: `edit_reservations`

#### Body (JSON)
```json
{
    "customer_id": 1,
    "table_id": 5,
    "reservation_date": "2026-05-15",
    "reservation_time": "20:30",
    "people_count": 4,
    "notes": "Richiesto seggiolone"
}
```

## 4. Modifica Prenotazione
Aggiorna i dati di una prenotazione esistente (es. per spostare l'orario o cambiare tavolo).
- **URL**: `/api/reservations/{id}` (es. `/api/reservations/1`)
- **Metodo**: `PUT`
- **Permesso**: `edit_reservations`

#### Body (JSON)
```json
{
    "reservation_time": "21:00",
    "people_count": 5
}
```

## 5. Eliminazione Prenotazione
Cancella una prenotazione dal sistema.
- **URL**: `/api/reservations/{id}` (es. `/api/reservations/1`)
- **Metodo**: `DELETE`
- **Permesso**: `edit_reservations`

---

## 6. Storico Prenotazioni di un Cliente (Paginate per Anno)
Questo endpoint permette di visualizzare lo storico delle prenotazioni di uno specifico cliente. Le prenotazioni vengono restituite **paginate** (15 per pagina) e filtrate per anno. Di default viene usato l'anno corrente, ma puoi passare l'anno desiderato in query string.
- **URL**: `/api/customers/{customer_id}/reservations` (es. `/api/customers/1/reservations?year=2025&page=2`)
- **Metodo**: `GET`
- **Permesso**: `view_reservations` (Nota: questa rotta è associata al controller `CustomerController`, ma il permesso logico richiesto per vedere i dati è `view_reservations`).
