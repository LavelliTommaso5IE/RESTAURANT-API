# 🍽️ ANH Restaurant - API Backend
### *Il tuo ristorante a portata di mano*  

**Autore:** Lavelli Tommaso  

---

## 📖 Descrizione

**ANH Restaurant** è un’applicazione gestionale per **ristoranti, bar e pizzerie**, progettata per semplificare e velocizzare ogni aspetto del servizio.  
Questo repository contiene il **Backend API SaaS (Multi-Tenant)** che permette di gestire in completo isolamento logico i **tavoli, le ordinazioni, i menù e i conti** per diversi ristoranti. Offre logiche avanzate per **calcolare automaticamente i totali**, **dividere i conti (split-bill)**, applicare **Gift Card e Sconti**, ed elaborare le comande tra Sala e Cucina.

> 📚 **Sviluppatori**: Consulta la guida tecnica completa [DEVELOPER_GUIDE.md](./DEVELOPER_GUIDE.md) per scoprire come installare il progetto e consultare la documentazione di tutti gli oltre 60 endpoint RESTful disponibili.

---

## 👥 Ruoli e Permessi Gestiti dall'API

Il sistema implementa un completo sistema RBAC (Role-Based Access Control) tramite JWT. I permessi sono dinamicamente assegnabili ai vari ruoli:

### 🧑‍💼 Amministratore (Manager)
**Permessi Base:** Completi  
**Funzionalità Esposte:**
- Gestione utenti e ruoli (RBAC).
- Configurazione e modifica del magazzino e del menù.
- Gestione della mappa e configurazione dei tavoli.
- Impostazione di Gift Card, Sconti e Coupon.
- Accesso ai log, andamenti economici storici e controllo dello stock.

### 🧑‍🍽️ Cameriere / Operatore di Sala
**Permessi Base:** Limitati alla gestione operativa  
**Funzionalità Esposte:**
- Visualizzazione mappa tavoli e stato (libero/occupato).
- Creazione, modifica e annullamento ordinazioni.
- Aggiunta note alle singole comande (es. varianti piatto).
- Invio comande e notifica a cucina/bar.
- Accorpamento tavoli o separazione per gruppi numerosi.
- Generazione PIN temporaneo del tavolo (per abilitare il self-ordering).
- Richiesta scontrino/conto per il cliente.

### 👨‍🍳 Personale di Cucina / Bar (KDS)
**Permessi Base:** Sola visualizzazione ordini e aggiornamento comande  
**Funzionalità Esposte:**
- Interrogazione real-time delle comande in attesa (Kitchen Display System).
- Aggiornamento stato di preparazione (es. *In preparazione*, *Pronto*, *Servito*).
- Gestione flussi per smaltimento rapido delle code in cucina.

### 💰 Cassiere
**Permessi Base:** Gestione conti e pagamenti  
**Funzionalità Esposte:**
- Accesso a tutti gli ordini aperti.
- Split-bill parziale o pagamenti totali.
- Controllo validità coupon e addebito automatico su Gift Card ricaricabili.
- Chiusura dell'ordine con svuotamento e pulizia virtuale del tavolo associato.

### 📱 Cliente (Self-Ordering Pubblico)
**Accesso:** Tramite **QR Code** al tavolo  
**Funzionalità Esposte (Endpoint senza JWT Auth):**
- Consultazione del menù digitale aggiornato in tempo reale (solo piatti disponibili/ordinabili).

---

## 🎯 Target
**ANH Restaurant** è un prodotto B2B e B2C pensato per:  
- Proprietari e gestori di ristoranti, pizzerie, bar e bistrot.
- Team di sala per migliorare i tempi di servizio.
- Staff di cucina per digitalizzare le classiche comande di carta.
- Responsabili di cassa per evitare colli di bottiglia durante i pagamenti di gruppo.

---

## ⚖️ Competitors
- TheFork Manager  
- iPratico POS  
- Scloby  
- Tilby  
- Ristomanager  

---

## 💻 Stack Tecnologico (Backend)
- **Framework:** Laravel 11 (PHP 8.2+)  
- **Architettura:** REST API Multi-Tenant (SaaS) con `stancl/tenancy`
- **Autenticazione:** JWT Custom con Cookie HttpOnly (Anti-XSS)
- **Database:** MySQL / SQLite
- **Testing:** PHPUnit (Suite di test mock/stubbed)

---

## 🌐 Link Utili
👉 **App Pubblica (Demo):** [anh-restaurant.alpinenode.it](https://anh-restaurant.alpinenode.it)  
🎨 **Mockup UI Frontend:** [my-new-web-project.lovable.app](https://my-new-web-project.lovable.app/)

---

## 📜 Licenza
Questo progetto è di proprietà di **Lavelli Tommaso**.  
Tutti i diritti riservati.  
