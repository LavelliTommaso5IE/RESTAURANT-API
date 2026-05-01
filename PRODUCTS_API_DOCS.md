# Documentazione API: Prodotti e Integrazione Piatti-Ingredienti

Questa documentazione ti aiuta a testare i nuovi endpoint creati per gestire il magazzino (Prodotti) e spiega come associare gli ingredienti (Prodotti) ai Piatti.

Tutte le rotte sono protette da **JWT**. È necessario passare l'header `Authorization: Bearer <token_jwt>`. I permessi necessari sono `view_products` e `edit_products` per i Prodotti, e `view_dishes`/`edit_dishes` per i Piatti.

---

## PARTE 1: Gestione Magazzino (Prodotti)

### 1. Elenco di tutti i Prodotti
Recupera l'anagrafica del magazzino.
- **URL**: `/api/products`
- **Metodo**: `GET`
- **Permesso**: `view_products`

### 2. Creazione di un Prodotto
Aggiunge un ingrediente al magazzino.
- **URL**: `/api/products`
- **Metodo**: `POST`
- **Permesso**: `edit_products`

#### Body (JSON)
```json
{
    "name": "Guanciale",
    "description": "Guanciale stagionato per Carbonara",
    "quantity": 5.5,    // Quantità totale a magazzino
    "unit": "kg"        // Unità di misura (es. kg, g, l, pz)
}
```

### 3. Aggiornamento di un Prodotto
Modifica l'anagrafica o le scorte.
- **URL**: `/api/products/{id}` (es. `/api/products/1`)
- **Metodo**: `PUT`
- **Permesso**: `edit_products`

#### Body (JSON)
```json
{
    "quantity": 10.0
}
```

### 4. Eliminazione di un Prodotto
- **URL**: `/api/products/{id}`
- **Metodo**: `DELETE`
- **Permesso**: `edit_products`

---

## PARTE 2: Associazione Piatti e Ingredienti (Spiegazione Pivot)

Quando crei o modifichi un **Piatto (Dish)**, ora puoi inviare anche l'elenco degli ingredienti necessari (la ricetta).

> **Spiegazione dei campi Pivot (Il tuo `TODO: da chiarire`)**:
> Dato che un Prodotto ha una sua anagrafica (es. Guanciale, unit="kg"), quando lo leghiamo a un Piatto (es. Carbonara), dobbiamo specificare due cose per QUEL piatto specifico (campi Pivot):
> 1. **`quantity`**: Quanti "kg" o "g" di guanciale servono *per una porzione* di Carbonara? (es. `0.1` se sono 100 grammi).
> 2. **`tolerance_percentage`**: Quanto margine di tolleranza o scarto c'è su questo ingrediente per questo piatto? Ad esempio, lo chef può inserire uno scarto del 5% perché in cottura si perde un po' di peso, così quando scala il magazzino considererà un consumo reale del +5%. 

### 1. Creare/Modificare un Piatto con la sua Ricetta
- **URL**: `/api/dishes` (POST) oppure `/api/dishes/{id}` (PUT)
- **Metodo**: `POST` / `PUT`
- **Permesso**: `edit_dishes`

#### Body (JSON)
```json
{
    "name": "Spaghetti alla Carbonara",
    "description": "Pasta con guanciale, pecorino e pepe",
    "price": 12.00,
    "category_id": 2,
    "products": [
        {
            "id": 1,                     // ID del prodotto (es. Guanciale)
            "quantity": 0.1,             // Serve 0.1 kg per piatto
            "tolerance_percentage": 5.0  // Tolleranza / scarto del 5%
        },
        {
            "id": 2,                     // ID del prodotto (es. Pecorino)
            "quantity": 0.05,            // Servono 0.05 kg
            "tolerance_percentage": 0.0  // Nessuna tolleranza
        }
    ]
}
```
*(Nota: L'array `products` sovrascriverà eventuali ingredienti precedenti tramite il metodo `sync` di Laravel, eliminando automaticamente le vecchie associazioni e creando le nuove).*

---

## PARTE 3: Recupero di un Singolo Piatto

Ho creato un nuovo endpoint che restituisce un singolo piatto, caricando automaticamente tutti i suoi ingredienti con le rispettive quantità "Pivot".

- **URL**: `/api/dishes/{id}` (es. `/api/dishes/5`)
- **Metodo**: `GET`
- **Permesso**: `view_dishes`

#### Esempio di Risposta
```json
{
    "data": {
        "id": 5,
        "name": "Spaghetti alla Carbonara",
        "description": "Pasta con guanciale, pecorino e pepe",
        "image": null,
        "price": 12,
        "category": {
            "id": 2,
            "name": "Primi Piatti"
        },
        "products": [
            {
                "id": 1,
                "name": "Guanciale",
                "unit": "kg",
                "quantity": 0.1,                 // Quantità della ricetta (Pivot)
                "tolerance_percentage": 5        // Tolleranza della ricetta (Pivot)
            },
            {
                "id": 2,
                "name": "Pecorino",
                "unit": "kg",
                "quantity": 0.05,
                "tolerance_percentage": 0
            }
        ],
        "created_at": "2026-05-01T10:00:00.000000Z",
        "updated_at": "2026-05-01T10:00:00.000000Z"
    }
}
```
