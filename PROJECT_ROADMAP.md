# Roadmap Progetto: RESTAURANT API BACKEND

Questa è la lista delle attività (Task) rimanenti per completare l'intera architettura del backend del ristorante, basata sul diagramma Entità-Relazione (ER) che hai fornito.

Abbiamo già completato:
- [x] Autenticazione (Login, JWT, Tenants)
- [x] Gestione Utenti, Ruoli e Permessi
- [x] Gestione Categorie
- [x] Gestione Piatti (con Immagini e paginazione ottimizzata)

---

## Task da Completare

### 1. Gestione Prodotti (Magazzino / Ingredienti)
- [x] Creazione Migration e Model `Product`.
- [x] Relazione: Collegamento tra `Dish` e `Product` (probabilmente una tabella pivot `dish_product` per indicare gli ingredienti/ricetta di un piatto).
- [x] Creazione Requests (`StoreProductRequest`, `UpdateProductRequest`).
- [x] Creazione `ProductResource` e `ProductController`.
- [x] Rotte API in `tenant.php`.

### 2. Gestione Menù
I clienti potranno visualizzare i menù (pubblicamente).
- [x] Creazione Migration e Model `Menu` (es. Menù Pranzo, Menù Cena, Carta dei Vini).
- [x] Relazione: Tabella pivot `dish_menu` per associare i piatti ai vari menù.
- [x] Creazione Controller, Resource e Requests per lo staff (CRUD).
- [x] Endpoint pubblico `GET /api/public/menus` per far visualizzare il menù ai clienti senza autenticazione.

### 3. Gestione Tavoli (Tables)
- [x] Creazione Migration e Model `Table` (campi: numero, posti_max, stato).
- [x] Logica unione tavoli (potenziale self-referencing o tabella d'appoggio per i "Tavoli Uniti").
- [x] Endpoint per cambiare stato (Libero, Occupato, Da Sparecchiare).
- [x] Generazione/Assegnazione dinamica di un QRCode (o token temporaneo) per permettere ai clienti del tavolo di ordinare dal telefono.
- [x] Creazione Controller, Resource e Requests.
- [x] Rotte API.

### 4. Gestione Clienti (Anagrafica)
Essendo per lo più per fatturazione o prenotazioni (senza login).
- [ ] Creazione Migration e Model `Customer` (nome, telefono, email, note).
- [ ] Creazione Controller, Resource e Requests.
- [ ] Rotte API.

### 5. Gestione Prenotazioni (Reservations)
- [ ] Creazione Migration e Model `Reservation` (data, ora, numero_persone, stato).
- [ ] Relazioni: Appartiene a un `Customer` e a un `Table`.
- [ ] Creazione Controller, Resource e Requests.
- [ ] Rotte API per la gestione da parte dei camerieri/admin.

### 6. Gestione Sconti (Discounts)
- [ ] Creazione Migration e Model `Discount` (nome, percentuale o importo fisso, validità).
- [ ] Creazione Controller, Resource e Requests.
- [ ] Rotte API.

### 7. Gestione Ordini (Orders / Conti)
Il cuore del sistema di cassa.
- [ ] Creazione Migration e Model `Order` (stato_pagamento, totale, data).
- [ ] Relazioni: Appartiene a un `Table`, eventualmente a un `Customer`, e può avere uno `Discount`.
- [ ] Creazione Controller, Resource e Requests.
- [ ] Rotte API (creazione ordine, applicazione sconto, chiusura conto).

### 8. Gestione Comande (Cucina / Order Items)
Il collegamento tra l'Ordine e la Cucina.
- [ ] Creazione Migration e Model `Comanda` (quantità, note_cucina, stato [es: inviato, in_preparazione, pronto, consegnato]).
- [ ] Relazioni: Appartiene a un `Order` e fa riferimento a un `Dish`.
- [ ] Creazione Controller, Resource e Requests.
- [ ] Endpoint specifici per la cucina: `PUT /api/comande/{id}/status` per far avanzare lo stato.

### 9. Reportistica (Dashboard)
- [ ] Creazione di un `ReportController`.
- [ ] Endpoint per visualizzare incassi giornalieri/mensili.
- [ ] Endpoint per visualizzare i piatti più venduti.

---
