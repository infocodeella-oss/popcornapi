<?php

require_once __DIR__ . '/../database.php';
require_once __DIR__ . '/../config/DatabaseManager.php';

class Supabase
{
    private string $table;

    public function __construct(string $table)
    {
        $this->table = $table;
    }

    public static function table(string $table): self
    {
        return new self($table);
    }

    public function all(array $params = []): array
    {
        $limit = null;
        $offset = 0;
        $order = null;

        if (isset($params['limit'])) {
            $limit = (int)$params['limit'];
            unset($params['limit']);
        }

        if (isset($params['offset'])) {
            $offset = (int)$params['offset'];
            unset($params['offset']);
        }

        if (isset($params['order'])) {
            $order = $params['order'];
            unset($params['order']);
        }

        $merged = [];

        foreach (DatabaseManager::databasesForTable($this->table) as $db) {

            $response = $db->get($this->table, $params);

            if (
                !empty($response['success']) &&
                !empty($response['data'])
            ) {
                $merged = array_merge($merged, $response['data']);
            }
        }

        if ($order) {
            $merged = $this->applySorting($merged, $order);
        }

        $total = count($merged);

        if ($offset > 0 || $limit !== null) {
            $merged = array_slice(
                $merged,
                $offset,
                $limit ?? null
            );
        }

        return [

            'success' => true,
            'status' => 200,

            'total' => $total,

            'count' => count($merged),

            'data' => $merged

        ];
    }

    public function where(array $params): array
    {
        return $this->all($params);
    }

    public function find(int $id): array
    {
        $result = $this->where([
            'id' => 'eq.' . $id
        ]);

        if (!empty($result['data'])) {

            $result['data'] = [
                $result['data'][0]
            ];

            $result['count'] = 1;
            $result['total'] = 1;
        }

        return $result;
    }

    public function create(array $data): array
    {
        $db = DatabaseManager::firstDatabase($this->table);

        if (!$db) {

            return [
                'success' => false,
                'status' => 404,
                'message' => 'Table not found',
                'data' => []
            ];
        }

        return $db->post($this->table, $data);
    }

    public function update(array $where, array $data): array
    {
        $db = DatabaseManager::firstDatabase($this->table);

        if (!$db) {

            return [
                'success' => false,
                'status' => 404,
                'message' => 'Table not found',
                'data' => []
            ];
        }

        return $db->patch($this->table, $where, $data);
    }

    public function delete(array $where): array
    {
        $db = DatabaseManager::firstDatabase($this->table);

        if (!$db) {

            return [
                'success' => false,
                'status' => 404,
                'message' => 'Table not found',
                'data' => []
            ];
        }

        return $db->delete($this->table, $where);
    }

    /**
     * ترتيب النتائج
     */
    private function applySorting(array $rows, string $order): array
    {
        $parts = explode('.', $order);

        $field = $parts[0] ?? 'id';

        $direction = strtolower($parts[1] ?? 'asc');

        usort($rows, function ($a, $b) use ($field, $direction) {

            $v1 = $a[$field] ?? null;
            $v2 = $b[$field] ?? null;

            if ($v1 == $v2) {
                return 0;
            }

            if ($direction === 'desc') {
                return ($v1 < $v2) ? 1 : -1;
            }

            return ($v1 > $v2) ? 1 : -1;

        });

        return $rows;
    }
}