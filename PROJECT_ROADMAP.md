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
- [x] Creazione Migration e Model `Customer` (campi: nome, cognome, telefono, email, p_iva/cf facoltativi, indirizzo e note).
- [x] CRUD base per lo staff per gestire l'anagrafica.
- [x] Rotte API.

### 5. Gestione Prenotazioni (Reservations)
- [x] Creazione Migration e Model `Reservation` (data, ora, numero_persone, stato).
- [x] Relazioni: Appartiene a un `Customer` e a un `Table`.
- [x] Creazione Controller, Resource e Requests.
- [x] Rotte API per la gestione da parte dei camerieri/admin (incluso storico annuale del cliente).

### 6. Gestione Sconti (Discounts)
- [x] Creazione Migration e Model `Discount` (nome, percentuale o importo fisso, validità, gift card).
- [x] Creazione Controller, Resource e Requests.
- [x] Rotte API.

### 7. Gestione Ordini (Orders / Conti)
- [x] Creazione Migration e Model `Order` (collegamento a Table, Customer, Discount, totale, stato pagamento).
- [x] Creazione Migration e Model `Payment` (gestione conti divisi e metodi di pagamento).
- [x] Logica per chiusura conto, associazione cliente e applicazione sconti/gift cards.

### 8. Gestione Comande (OrderItem)
- [x] Creazione Migration e Model `OrderItem` (quantità, snapshot prezzo, stato preparazione).
- [x] Endpoint per cucina/bar: avanzamento stato (pending, preparing, ready, served).

### 9. Reportistica (Dashboard)
- [ ] Creazione di un `ReportController`.
- [ ] Endpoint per visualizzare incassi giornalieri/mensili.
- [ ] Endpoint per visualizzare i piatti più venduti.

---
