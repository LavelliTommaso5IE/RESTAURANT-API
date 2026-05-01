# Documentazione API: Tavoli (Tables)

Questa documentazione ti aiuta a testare i nuovi endpoint creati per gestire i Tavoli e la loro logica di unione/separazione.
Tutte le rotte sono protette da **JWT**. I permessi richiesti sono `view_tables` e `edit_tables`.

L'URL base dipende dal tenant su cui stai lavorando (es. `http://tuo-tenant.localhost:8000/api`).

---

## 1. CRUD Standard (Gestione Anagrafica Tavoli)

### 1.1 Elenco di tutti i Tavoli
Recupera l'elenco dei tavoli, includendo eventuali parentele (tavolo principale a cui sono uniti o tavoli secondari uniti a loro).
- **URL**: `/api/tables`
- **Metodo**: `GET`
- **Permesso**: `view_tables`

### 1.1.1 Dettaglio Singolo Tavolo
Recupera i dettagli di un singolo tavolo tramite il suo ID.
- **URL**: `/api/tables/{id}` (es. `/api/tables/1`)
- **Metodo**: `GET`
- **Permesso**: `view_tables`

### 1.2 Creazione di un Tavolo
- **URL**: `/api/tables`
- **Metodo**: `POST`
- **Permesso**: `edit_tables`

#### Body (JSON)
```json
{
    "name": "Tavolo 1",
    "seats": 4,
    "status": "free" // Opzionale. Accetta: free, occupied, reserved, cleaning
}
```

### 1.3 Modifica di un Tavolo (Generica)
Modifica nome, posti o stato manualmente.
- **URL**: `/api/tables/{id}`
- **Metodo**: `PUT`
- **Permesso**: `edit_tables`

#### Body (JSON)
```json
{
    "name": "Tavolo 1A",
    "seats": 6,
    "status": "reserved"
}
```

### 1.4 Eliminazione di un Tavolo
- **URL**: `/api/tables/{id}`
- **Metodo**: `DELETE`
- **Permesso**: `edit_tables`

---

## 2. Metodi Custom (Flusso Camerieri)

Per evitare logiche complesse sul frontend (es. passare parametri nulli con le PUT), ho predisposto questi endpoint rapidi per le azioni più frequenti durante il servizio:

### 2.1 Unione Tavoli (Join)
Unisce il tavolo specificato nell'URL a un altro tavolo "padre". Molto utile per creare lunghe tavolate.
- **URL**: `/api/tables/{id}/join` (es. `/api/tables/2/join`)
- **Metodo**: `POST`
- **Permesso**: `edit_tables`

#### Body (JSON)
```json
{
    "parent_id": 1 // Il Tavolo 2 diventerà "figlio" del Tavolo 1
}
```

### 2.2 Separazione Tavolo (Separate)
Rimuove il tavolo dalla tavolata (imposta il suo `parent_id` a null).
- **URL**: `/api/tables/{id}/separate`
- **Metodo**: `POST`
- **Permesso**: `edit_tables`

### 2.3 Generazione PIN (per il QR Code)
Genera e restituisce una stringa random alfanumerica di 64 caratteri per questo tavolo. Utilissimo quando il cliente si siede e il cameriere gli fornisce l'accesso all'ordine tramite smartphone.
*Se il tavolo ha già un PIN attivo, questo endpoint non ne genererà uno nuovo ma restituirà quello attualmente esistente (per evitare invalidazioni accidentali del QR Code).*
- **URL**: `/api/tables/{id}/generate-pin`
- **Metodo**: `POST`
- **Permesso**: `edit_tables`

#### Risposta (200 OK)
```json
{
    "pin": "b5XgP...9kLz" // Stringa di 64 caratteri
}
```

### 2.4 Pulisci / Inizializza Tavolo (Toggle)
Questo endpoint fa due cose a seconda dello stato attuale del tavolo:
- Se il tavolo è occupato/prenotato: azzera il PIN (invalidando il QR) e imposta lo stato a `cleaning`.
- Se il tavolo è GIA' in `cleaning`: lo rimette a `free` (libero).
- **URL**: `/api/tables/{id}/clear`
- **Metodo**: `POST`
- **Permesso**: `edit_tables`
