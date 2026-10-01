<?php
declare(strict_types=1);

final class Field extends Model
{
    public function all(array $filters = []): array
    {
        $where = ['f.is_active = 1'];
        $params = [];
        if (!empty($filters['search'])) {
            $where[] = '(f.name LIKE :name_search OR f.location LIKE :location_search OR f.city LIKE :city_search)';
            $search = '%' . trim($filters['search']) . '%';
            $params[':name_search'] = $search;
            $params[':location_search'] = $search;
            $params[':city_search'] = $search;
        }
        if (!empty($filters['city'])) {
            $where[] = 'f.city = :city';
            $params[':city'] = $filters['city'];
        }
        $sql = 'SELECT f.*, u.name AS owner_name
                FROM fields f JOIN users u ON u.id = f.owner_id
                WHERE ' . implode(' AND ', $where) . ' ORDER BY f.created_at DESC';
        $statement = $this->db->prepare($sql);
        $statement->execute($params);
        return $statement->fetchAll();
    }

    public function allForAdmin(): array
    {
        return $this->db->query(
            'SELECT f.*, u.name AS owner_name,
                (SELECT COUNT(*) FROM bookings b WHERE b.field_id = f.id AND b.status <> "cancelled") AS booking_count
             FROM fields f JOIN users u ON u.id = f.owner_id ORDER BY f.created_at DESC'
        )->fetchAll();
    }

    public function find(int $id): ?array
    {
        $statement = $this->db->prepare(
            'SELECT f.*, u.name AS owner_name, u.phone AS owner_phone
             FROM fields f JOIN users u ON u.id = f.owner_id WHERE f.id = ? LIMIT 1'
        );
        $statement->execute([$id]);
        return $statement->fetch() ?: null;
    }

    public function create(array $data, int $ownerId): int
    {
        $statement = $this->db->prepare(
            'INSERT INTO fields (owner_id, name, description, location, city, price_per_hour, open_time, close_time, amenities, image_url)
             VALUES (:owner_id, :name, :description, :location, :city, :price, :open, :close, :amenities, :image)'
        );
        $statement->execute([
            ':owner_id' => $ownerId,
            ':name' => trim($data['name']),
            ':description' => trim($data['description'] ?? ''),
            ':location' => trim($data['location']),
            ':city' => trim($data['city']),
            ':price' => (int) $data['price_per_hour'],
            ':open' => $data['open_time'],
            ':close' => $data['close_time'],
            ':amenities' => trim($data['amenities'] ?? ''),
            ':image' => trim($data['image_url'] ?? ''),
        ]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): void
    {
        $statement = $this->db->prepare(
            'UPDATE fields SET name=:name, description=:description, location=:location, city=:city,
             price_per_hour=:price, open_time=:open, close_time=:close, amenities=:amenities, image_url=:image, is_active=:active
             WHERE id=:id'
        );
        $statement->execute([
            ':id' => $id,
            ':name' => trim($data['name']),
            ':description' => trim($data['description'] ?? ''),
            ':location' => trim($data['location']),
            ':city' => trim($data['city']),
            ':price' => (int) $data['price_per_hour'],
            ':open' => $data['open_time'],
            ':close' => $data['close_time'],
            ':amenities' => trim($data['amenities'] ?? ''),
            ':image' => trim($data['image_url'] ?? ''),
            ':active' => isset($data['is_active']) ? 1 : 0,
        ]);
    }

    public function delete(int $id): void
    {
        $statement = $this->db->prepare('UPDATE fields SET is_active = 0 WHERE id = ?');
        $statement->execute([$id]);
    }

    public function cities(): array
    {
        return $this->db->query('SELECT DISTINCT city FROM fields WHERE is_active = 1 ORDER BY city')->fetchAll(PDO::FETCH_COLUMN);
    }
}