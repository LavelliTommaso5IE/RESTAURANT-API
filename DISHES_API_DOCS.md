# Documentazione API: Piatti (Dishes)

Questa documentazione ti aiuta a testare i nuovi endpoint creati per gestire i Piatti. 
Ricorda che tutte queste rotte sono protette da **JWT** e dai relativi **permessi**, quindi devi passare l'header `Authorization: Bearer <tuo_token_jwt>` in ogni richiesta.

L'URL base dipende dal tenant su cui stai lavorando (es. `http://tuo-tenant.localhost:8000/api`).

---

## 1. Elenco di tutti i Piatti
Recupera la lista di tutti i piatti presenti nel menu del tenant.
- **URL**: `/api/dishes`
- **Metodo**: `GET`
- **Permesso richiesto**: `view_dishes`

### Esempio di Risposta (200 OK)
```json
{
    "data": [
        {
            "id": 1,
            "name": "Spaghetti alla Carbonara",
            "description": "Pasta con uovo, guanciale, pecorino e pepe",
            "image": "https://esempio.com/immagini/carbonara.jpg",
            "price": 12.50,
            "category_id": 2,
            "category": {
                "id": 2,
                "name": "Primi Piatti"
            },
            "created_at": "2026-05-01T10:00:00.000000Z",
            "updated_at": "2026-05-01T10:00:00.000000Z"
        }
    ]
}
```

---

## 2. Creazione di un Piatto
Crea un nuovo piatto assegnandolo a una categoria esistente.
- **URL**: `/api/dishes`
- **Metodo**: `POST`
- **Permesso richiesto**: `edit_dishes`

### Body (JSON)
```json
{
    "name": "Tiramisù",
    "description": "Dolce tipico italiano",
    "image": "https://esempio.com/immagini/tiramisu.jpg",
    "price": 6.00,
    "category_id": 3
}
```

### Esempio di Risposta (201 Created)
Ritorna l'oggetto appena creato formattato.

---

## 3. Aggiornamento di un Piatto
Modifica i dati di un piatto esistente. Puoi inviare anche solo i campi che vuoi aggiornare.
- **URL**: `/api/dishes/{id}` (es. `/api/dishes/1`)
- **Metodo**: `PUT`
- **Permesso richiesto**: `edit_dishes`

### Body (JSON)
```json
{
    "price": 7.50,
    "description": "Dolce tipico italiano con savoiardi artigianali"
}
```

### Esempio di Risposta (200 OK)
Ritorna l'oggetto aggiornato formattato.

---

## 4. Eliminazione di un Piatto
Elimina un piatto dal database.
- **URL**: `/api/dishes/{id}` (es. `/api/dishes/1`)
- **Metodo**: `DELETE`
- **Permesso richiesto**: `edit_dishes`

### Esempio di Risposta (200 OK)
```json
{
    "message": "Piatto eliminato"
}
```

---

## Come Testarli (Flusso Consigliato su Postman / Insomnia)
1. Esegui il login con l'Admin (`POST /api/login`) o con un utente che abbia i permessi `view_dishes` e `edit_dishes`.
2. Salva il Token JWT ricevuto in risposta.
3. Se non l'hai già fatto, crea una Categoria in `/api/categories` per ottenere un `category_id` valido.
4. Usa il Token per testare prima la creazione (`POST`), poi l'elenco (`GET`), l'aggiornamento (`PUT`) e l'eliminazione (`DELETE`).
