<?php

class QueueRepository
{
    public function __construct(private PDO $pdo) {}

    public function status(int $doctorId, string $date): ?array
    {
        $statement = $this->pdo->prepare('SELECT * FROM queue_states WHERE doctor_id = :doctor AND service_date = :date LIMIT 1');
        $statement->execute(['doctor' => $doctorId, 'date' => $date]);
        $row = $statement->fetch();

        return $row ? $this->shape($row) : null;
    }

    public function next(int $doctorId, string $date): array
    {
        $current = $this->status($doctorId, $date);

        if (! $current) {
            throw new RuntimeException('Queue not initialised');
        }

        $statement = $this->pdo->prepare('UPDATE queue_states SET current_token = current_token + 1, updated_at = :updated WHERE doctor_id = :doctor AND service_date = :date');
        $statement->execute([
            'updated' => date('Y-m-d H:i:s'),
            'doctor' => $doctorId,
            'date' => $date,
        ]);

        return $this->status($doctorId, $date);
    }

    public function sync(array $payload): array
    {
        $now = date('Y-m-d H:i:s');
        $driver = $this->pdo->getAttribute(PDO::ATTR_DRIVER_NAME);

        if ($driver === 'sqlite') {
            $sql = 'INSERT INTO queue_states (doctor_id, clinic_id, service_date, current_token, total_booked, avg_consultation_minutes, room, doctor_name, created_at, updated_at)
                VALUES (:doctor_id, :clinic_id, :date, 0, :total, :avg, :room, :doctor_name, :created, :updated)
                ON CONFLICT(doctor_id, service_date) DO UPDATE SET
                    total_booked = excluded.total_booked,
                    avg_consultation_minutes = excluded.avg_consultation_minutes,
                    room = excluded.room,
                    doctor_name = excluded.doctor_name,
                    clinic_id = excluded.clinic_id,
                    updated_at = excluded.updated_at';
        } else {
            $sql = 'INSERT INTO queue_states (doctor_id, clinic_id, service_date, current_token, total_booked, avg_consultation_minutes, room, doctor_name, created_at, updated_at)
                VALUES (:doctor_id, :clinic_id, :date, 0, :total, :avg, :room, :doctor_name, :created, :updated)
                ON DUPLICATE KEY UPDATE
                    total_booked = VALUES(total_booked),
                    avg_consultation_minutes = VALUES(avg_consultation_minutes),
                    room = VALUES(room),
                    doctor_name = VALUES(doctor_name),
                    clinic_id = VALUES(clinic_id),
                    updated_at = VALUES(updated_at)';
        }

        $statement = $this->pdo->prepare($sql);
        $statement->execute([
            'doctor_id' => (int) $payload['doctor_id'],
            'clinic_id' => (int) $payload['clinic_id'],
            'date' => $payload['date'],
            'total' => (int) $payload['total_booked'],
            'avg' => (int) $payload['avg_consultation_minutes'],
            'room' => $payload['room'] ?? null,
            'doctor_name' => $payload['doctor_name'] ?? null,
            'created' => $now,
            'updated' => $now,
        ]);

        return $this->status((int) $payload['doctor_id'], (string) $payload['date']);
    }

    public function reset(string $date): int
    {
        $statement = $this->pdo->prepare('UPDATE queue_states SET current_token = 0, updated_at = :updated WHERE service_date = :date');
        $statement->execute(['updated' => date('Y-m-d H:i:s'), 'date' => $date]);

        return $statement->rowCount();
    }

    public function snapshot(string $date): array
    {
        $statement = $this->pdo->prepare('SELECT * FROM queue_states WHERE service_date = :date');
        $statement->execute(['date' => $date]);

        return array_map(fn (array $row) => $this->shape($row), $statement->fetchAll());
    }

    private function shape(array $row): array
    {
        return shape_row($row, (int) $row['doctor_id'], (string) $row['service_date']);
    }
}
