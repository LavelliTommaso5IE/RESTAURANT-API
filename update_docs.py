import re

with open("DEVELOPER_GUIDE.md", "r") as f:
    content = f.read()

# Add Postman note
postman_note = """
## 3. 📚 Reference Completo Endpoint

> 💡 **Nota Postman:** Tutti gli esempi pratici (inclusi i payload completi) riportati in questa documentazione sono disponibili ed eseguibili direttamente tramite le collezioni salvate nella cartella `POSTMAN REQUESTS` presente nella root del progetto.

Questa sezione elenca tutti gli endpoint disponibili. Per aggiornamenti (`PUT`) o creazioni (`POST`), ricorda di impostare l'header `Content-Type: application/json`.
"""
content = content.replace("## 3. 📚 Reference Completo Endpoint\n\nQuesta sezione elenca tutti gli endpoint disponibili. Per aggiornamenti (`PUT`) o creazioni (`POST`), ricorda di impostare l'header `Content-Type: application/json`.", postman_note)

# Fix code blocks from json to jsonc to support comments
content = content.replace("```json\n", "```jsonc\n")

# Replacements for payloads
replacements = [
    # Tenant
    (
        '"description": "Leader mondiale in prodotti per catturare Road Runner",',
        '"description": "Leader mondiale in prodotti per catturare Road Runner", // (Facoltativo)'
    ),
    (
        '"domain": "acme-corporation.localhost"',
        '"domain": "acme-corporation.localhost",\n    "name": "Acme Corporation"'
    ),
    # User
    (
        '"password_confirmation": "PasswordSicura123!"',
        '"password_confirmation": "PasswordSicura123!",\n    "role_id": 2 // (Facoltativo)'
    ),
    # Customer
    (
        '"phone": "+39 333 1234567",',
        '"phone": "+39 333 1234567", // (Facoltativo)'
    ),
    (
        '"email": "luca.bianchi@email.it",',
        '"email": "luca.bianchi@email.it", // (Facoltativo)'
    ),
    (
        '"vat_number": "IT12345678901",',
        '"vat_number": "IT12345678901", // (Facoltativo)'
    ),
    (
        '"tax_code": "AAABBB80A01H501U",',
        '"tax_code": "AAABBB80A01H501U", // (Facoltativo)'
    ),
    (
        '"address": "Via Roma 66, Milano",',
        '"address": "Via Roma 66, Milano", // (Facoltativo)'
    ),
    (
        '"notes": "Cliente abituale, preferisce tavoli all\'aperto."',
        '"notes": "Cliente abituale, preferisce tavoli all\'aperto." // (Facoltativo)'
    ),
    # Reservation
    (
        '"people_count": 4,',
        '"people_count": 4,\n    "status": "confirmed", // (Facoltativo)'
    ),
    (
        '"notes": "Richiesto seggiolone"',
        '"notes": "Richiesto seggiolone" // (Facoltativo)'
    ),
    # Product
    (
        '"description": "Guanciale stagionato per Carbonara",',
        '"description": "Guanciale stagionato per Carbonara", // (Facoltativo)'
    ),
    # Dish
    (
        '"description": "Pasta con guanciale, pecorino e pepe",',
        '"description": "Pasta con guanciale, pecorino e pepe", // (Facoltativo)'
    ),
    (
        '"category_id": 1,',
        '"category_id": 1,\n    "is_orderable": true, // (Facoltativo)'
    ),
    (
        '"products": [',
        '"products": [ // (Facoltativo, array materie prime)'
    ),
    (
        '"tolerance_percentage": 5.0',
        '"tolerance_percentage": 5.0 // (Facoltativo)'
    ),
    (
        '"tolerance_percentage": 0.0',
        '"tolerance_percentage": 0.0 // (Facoltativo)'
    ),
    # Menu
    (
        '"description": "I nostri piatti classici per la cena",',
        '"description": "I nostri piatti classici per la cena", // (Facoltativo)'
    ),
    (
        '"cover": "https://esempio.com/img/cena.jpg",',
        '"cover": "https://esempio.com/img/cena.jpg", // (Facoltativo)'
    ),
    (
        '"dishes": [1]',
        '"dishes": [1] // (Facoltativo)'
    ),
    (
        '"dishes": [1, 2]',
        '"dishes": [1, 2] // (Facoltativo)'
    ),
    # Table
    (
        '"status": "free"',
        '"status": "free" // (Facoltativo)'
    ),
    # Discount
    (
        '"code": "SUMMER5",',
        '"code": "SUMMER5", // (Facoltativo, autogenerato se omesso)'
    ),
    (
        '"min_order_value": 0.00,',
        '"min_order_value": 0.00, // (Facoltativo)'
    ),
    (
        '"valid_until": "2026-12-31 23:59:59"',
        '"valid_until": "2026-12-31 23:59:59" // (Facoltativo)'
    ),
    # Order
    (
        '"notes": "Tavolo vicino alla finestra"',
        '"notes": "Tavolo vicino alla finestra" // (Facoltativo)'
    ),
    (
        '"table_id": 1,',
        '"table_id": 1,\n    "customer_id": 1, // (Facoltativo)'
    ),
    # Order Item
    (
        '"notes": "Senza pepe"',
        '"notes": "Senza pepe" // (Facoltativo)'
    ),
    # Payment
    (
        '"notes": "Pagato da Mario"',
        '"notes": "Pagato da Mario" // (Facoltativo)'
    ),
    (
        '"notes": "Uso parziale del buono"',
        '"notes": "Uso parziale del buono" // (Facoltativo)'
    )
]

for old, new in replacements:
    content = content.replace(old, new)

with open("DEVELOPER_GUIDE.md", "w") as f:
    f.write(content)

print("DEVELOPER_GUIDE.md updated successfully")
