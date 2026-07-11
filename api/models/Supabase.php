<?php

require_once __DIR__ . '/../database.php';

class Supabase
{
    private Database $db;
    private string $table;

    public function __construct(string $table)
    {
        $this->db = new Database();
        $this->table = $table;
    }

    public static function table(string $table): self
    {
        return new self($table);
    }

    public function all(array $params = []): array
    {
        return $this->db->get($this->table, $params);
    }

    public function find(int $id): array
    {
        return $this->db->get($this->table, [
            'id' => 'eq.' . $id,
            'limit' => 1
        ]);
    }

    public function where(array $params): array
    {
        return $this->db->get($this->table, $params);
    }

    public function create(array $data): array
    {
        return $this->db->post($this->table, $data);
    }

    public function update(array $where, array $data): array
    {
        return $this->db->patch($this->table, $where, $data);
    }

    public function delete(array $where): array
    {
        return $this->db->delete($this->table, $where);
    }
}