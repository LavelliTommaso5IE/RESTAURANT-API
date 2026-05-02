# API Documentation: Ordini, Comande e Pagamenti

Questo modulo gestisce il ciclo di vita del consumo al tavolo: dall'apertura del tavolo (Ordine), all'ordinazione dei piatti (Comande), fino al pagamento finale (anche diviso) e chiusura del conto.

---

## 1. Gestione Ordini (Il Conto)

### 1.1 Apri un Ordine (Occupa Tavolo)
Crea una sessione di consumo per un tavolo. Il tavolo passerà automaticamente in stato `occupied`.
- **URL**: `/api/orders`
- **Metodo**: `POST`
- **Permesso**: `edit_orders`
- **Body**:
```json
{
    "table_id": 1,
    "notes": "Tavolo vicino alla finestra"
}
```

### 1.2 Lista Ordini
- **URL**: `/api/orders`
- **Metodo**: `GET`
- **Permesso**: `view_orders`

### 1.3 Dettaglio Ordine (Con Piatti e Pagamenti)
- **URL**: `/api/orders/{id}`
- **Metodo**: `GET`

### 1.4 Associa Cliente all'Ordine
Associa una ricevuta nominativa all'ordine per lo storico cliente.
- **URL**: `/api/orders/{id}/customer`
- **Metodo**: `POST`
- **Body**: `{ "customer_id": 5 }`

### 1.5 Applica Sconto
Calcola il totale e applica una riduzione basata su uno sconto esistente o Gift Card. Puoi passare l'ID interno o il codice alfanumerico.
- **URL**: `/api/orders/{id}/discount`
- **Metodo**: `POST`
- **Body (Opzione 1 - ID)**:
```json
{ "discount_id": 2 }
```
- **Body (Opzione 2 - Codice)**:
```json
{ "discount_code": "GIFT-MARIO-2026" }
```

### 1.5.1 Rimuovi Sconto (Sgancia)
Rimuove lo sconto o la gift card attualmente associata all'ordine.
- **URL**: `/api/orders/{id}/discount`
- **Metodo**: `DELETE`
- **Permesso**: `edit_orders`
- *Nota: Se sono già stati effettuati pagamenti con Gift Card, dovrai prima eliminare quei pagamenti.*
Chiude l'ordine se il totale è stato coperto dai pagamenti. Il tavolo passa in stato `cleaning`.
- **URL**: `/api/orders/{id}/close`
- **Metodo**: `POST`

---

## 2. Gestione Comande (Piatti in Cucina)

### 2.1 Aggiungi Piatto all'Ordine
- **URL**: `/api/order-items/order/{order_id}`
- **Metodo**: `POST`
- **Body**:
```json
{
    "dish_id": 10,
    "quantity": 2,
    "notes": "Senza pepe"
}
```

### 2.2 Aggiorna Stato Comanda (Per Cucina/Bar)
Cambia lo stato di preparazione di un piatto.
- **URL**: `/api/order-items/{id}/status`
- **Metodo**: `PUT`
- **Body**:
```json
{
    "status": "ready" // Valori: pending, preparing, ready, served, cancelled
}
```

---

## 3. Pagamenti (Conti Divisi)

### 3.1 Registra Pagamento
Registra una tranche di pagamento per un ordine aperto.
- **URL**: `/api/payments/order/{order_id}`
- **Metodo**: `POST`
- **Body (Contanti/Carta)**:
```json
{
    "amount": 25.50,
    "payment_method": "card", 
    "notes": "Pagato da Mario"
}
```
- **Body (Gift Card)**:
```json
{
    "amount": 10.00,
    "payment_method": "gift_card",
    "discount_code": "GIFT-123-ABC",
    "notes": "Uso parziale del buono"
}
```
*Nota: Ora puoi registrare molteplici pagamenti con Gift Card diverse sullo stesso ordine. Il sistema scalerà il saldo da ogni specifica carta indicata.*

### 3.2 Elimina Pagamento (Storno)
Elimina un pagamento registrato per errore.
- **URL**: `/api/payments/{id}`
- **Metodo**: `DELETE`
- **Permesso**: `edit_payments`
- *Nota: Se il pagamento era stato fatto con Gift Card, l'importo verrà rimborsato automaticamente sul saldo della carta.*

---

## Flusso Esempio (Scenario Reale)

Ecco come si sviluppa una sessione tipo all'interno del ristorante utilizzando le API:

1. **Arrivo Clienti**: Il cameriere fa accomodare i clienti al Tavolo 5.
   - `POST /api/orders` con `table_id: 5`.
   - *Risultato*: Viene creato l'Ordine #100. Il Tavolo 5 diventa `occupied`.

2. **Ordinazione**: Il cameriere prende l'ordine.
   - `POST /api/order-items/order/100` per 2 Pizze Margherita.
   - `POST /api/order-items/order/100` per 2 Birre.
   - *Risultato*: Gli item sono in stato `pending`. La cucina li vede sul monitor.

3. **In Cucina**: Lo chef inizia a preparare.
   - `PUT /api/order-items/{pizza_id}/status` con `status: preparing`.
   - Una volta pronte: `PUT /api/order-items/{pizza_id}/status` con `status: ready`.

4. **Servizio**: Il cameriere vede che le pizze sono "al pass" e le porta al tavolo.
   - `PUT /api/order-items/{pizza_id}/status` con `status: served`.

5. **Chiusura Conto (Split Payment)**: I clienti chiedono il conto e vogliono dividere. Totale: 50€.
   - **Associazione Cliente**: `POST /api/orders/100/customer` con `customer_id: 1` (per lo storico).
   - **Sconto**: `POST /api/orders/100/discount` con l'ID di un coupon del 10%. Il totale diventa 45€.
   - **Pagamento 1**: `POST /api/payments/order/100` con `amount: 20.00` e `method: gift_card`. (Il saldo della carta viene scalato).
   - **Pagamento 2**: `POST /api/payments/order/100` con `amount: 25.00` e `method: cash`.
   - **Fine**: `POST /api/orders/100/close`.
   - *Risultato*: L'ordine è `closed`. Il Tavolo 5 torna `cleaning`.
