<?php

require_once __DIR__ . '/../database_manager.php';

class Supabase
{
    private DatabaseManager $db;
    private string $table;

    public function __construct(string $table)
    {
        $this->db = new DatabaseManager($table);
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