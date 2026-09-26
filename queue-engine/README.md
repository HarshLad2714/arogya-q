# ArogyaQ Queue Engine

Framework-free PHP service for the live token board. It shares Laravel's database and speaks JSON.

```
GET  /health
GET  /queue/status?doctor_id=1&date=2026-09-26
POST /queue/next   { "doctor_id": 1, "date": "2026-09-26" }
POST /queue/sync   { "doctor_id": 1, "clinic_id": 1, "date": "2026-09-26", "total_booked": 8, "avg_consultation_minutes": 12, "room": "Room 1", "doctor_name": "Meera Shah" }
POST /queue/reset  { "date": "2026-09-26" }
```

POST routes require header `X-Queue-Secret`.

```bash
php -S 0.0.0.0:8081 -t queue-engine/public queue-engine/public/index.php
php queue-engine/websocket/server.php
```
