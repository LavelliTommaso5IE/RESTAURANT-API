# Documentazione API: Sconti e Gift Cards (Discounts)

Questa documentazione descrive gli endpoint per gestire gli sconti applicabili agli ordini e le carte regalo (Gift Cards) con saldo a scalare.
Tutte le rotte sono protette da **JWT** e richiedono i permessi `view_discounts` o `edit_discounts`.

L'URL base dipende dal tenant su cui stai lavorando (es. `http://tuo-tenant.localhost:8000/api`).

---

## 1. Elenco Sconti
Recupera l'elenco di tutti gli sconti attivi. Nota: gli sconti eliminati (soft delete) non appariranno in questa lista.
- **URL**: `/api/discounts`
- **Metodo**: `GET`
- **Permesso**: `view_discounts`

## 2. Dettaglio Singolo Sconto (Tramite ID)
Recupera i dettagli di uno sconto specifico tramite il suo ID numerico interno.
- **URL**: `/api/discounts/{id}`
- **Metodo**: `GET`
- **Permesso**: `view_discounts`

## 2.1 Recupero Sconto (Tramite Codice)
Recupera i dettagli di uno sconto o gift card tramite il suo codice alfanumerico (quello che possiede il cliente). Questo endpoint è fondamentale per verificare se un coupon è valido prima di applicarlo a un ordine.
L'endpoint restituisce lo sconto solo se:
- Esiste un record con quel codice.
- Lo sconto è attivo (`is_active: true`).
- Non è scaduto (`valid_until` nullo o nel futuro).
- **URL**: `/api/discounts/code/{code}` (es. `/api/discounts/code/GIFT-MARIO-2026`)
- **Metodo**: `GET`
- **Permesso**: `view_discounts`

## 3. Creazione Sconto / Gift Card
Crea un nuovo sconto o una nuova carta regalo.
- **URL**: `/api/discounts`
- **Metodo**: `POST`
- **Permesso**: `edit_discounts`

#### Body (JSON)
```json
{
    "name": "Gift Card Compleanno Mario",
    "code": "GIFT-MARIO-2026", // Facoltativo. Se vuoto, viene generato un codice casuale di 12 caratteri.
    "type": "gift_card", // Valori ammessi: percentage, fixed, gift_card
    "value": 50.00,
    "min_order_value": 0.00,
    "is_active": true,
    "valid_until": "2026-12-31 23:59:59"
}
```
*Nota: Se il codice (`code`) non viene passato, il sistema ne genererà uno casuale alfanumerico (es. `X7Y2Z9W1K4P8`).*

*Nota: Se il tipo è `gift_card`, il sistema inizializzerà automaticamente il `current_balance` al valore indicato in `value`.*

## 4. Modifica Sconto
Aggiorna i dati di uno sconto esistente.
- **URL**: `/api/discounts/{id}`
- **Metodo**: `PUT`
- **Permesso**: `edit_discounts`

## 5. Eliminazione Sconto (Soft Delete)
Cancella logicamente uno sconto. Lo sconto non verrà rimosso fisicamente dal database per preservare l'integrità degli ordini passati, ma non sarà più selezionabile per nuovi ordini.
- **URL**: `/api/discounts/{id}`
- **Metodo**: `DELETE`
- **Permesso**: `edit_discounts`

---

## Tipi di Sconto Supportati
1. **Percentage**: Applica una riduzione percentuale sul totale (es. 10%).
2. **Fixed**: Sottrae un importo fisso dal totale (es. 5€).
3. **Gift Card**: Funziona come un borsellino elettronico. Quando viene usata, il saldo residuo (`current_balance`) viene scalato. Può essere utilizzata in più ordini finché il saldo non arriva a zero.

---

## Logica di Funzionamento (Workflow)

### Creazione e Inizializzazione
Quando crei uno sconto di tipo `gift_card` inviando un valore (es. `value: 100`), il sistema imposta automaticamente il `current_balance` a `100`. Per gli altri tipi (`percentage`, `fixed`), il `current_balance` rimane nullo poiché lo sconto è riutilizzabile o non basato su un saldo a scalare.

### Utilizzo negli Ordini (Anteprima)
Sebbene l'applicazione effettiva avvenga nel modulo **Ordini**, la logica prevista è:
- **Percentage/Fixed**: Lo sconto viene applicato al totale dell'ordine se la spesa minima (`min_order_value`) è soddisfatta.
- **Gift Card**: Il sistema verifica il `current_balance`. L'utente potrà scegliere se scalare l'intero totale dell'ordine dal saldo della carta o solo una parte. Il `current_balance` verrà aggiornato nel tempo finché non raggiunge lo zero.

### Integrità e "Soft Delete"
Per evitare che l'eliminazione di uno sconto modifichi gli scontrini/ordini passati:
1. Usiamo il **Soft Delete**: il comando `DELETE` non rimuove la riga, ma imposta una data in `deleted_at`.
2. Gli ordini passati continueranno a vedere lo sconto poiché il record esiste ancora fisicamente.
3. Le liste pubbliche e la ricerca degli sconti attivi ignoreranno automaticamente i record "soft-deleted".

### Validazioni di Sicurezza
- Non è possibile creare sconti percentuali superiori al **100%**.
- La data `valid_until` (se presente) impedisce l'uso dello sconto se la data attuale è successiva alla scadenza.
- Il campo `is_active` permette allo staff di disabilitare istantaneamente un coupon (es. in caso di abuso) senza cancellarlo.
