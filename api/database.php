<?php

class Database
{
    private array $databases = [];

    public function __construct(string $table)
    {
        foreach (SUPABASE_DATABASES as $database) {

            if (in_array($table, $database['tables'])) {
                $this->databases[] = $database;
            }

        }

        if (empty($this->databases)) {
            throw new Exception("Table '{$table}' is not configured.");
        }
    }

    public function get(string $table, array $params = []): array
    {
        $results = [];

        foreach ($this->databases as $database) {

            $response = $this->request(
                $database,
                'GET',
                $table,
                $params
            );

            if (!$response['success']) {
                continue;
            }

            foreach ($response['data'] as $row) {

                $row['_project'] = $database['name'];
                $row['_uid'] = $database['name'] . '_' . ($row['id'] ?? uniqid());    
            
                $results[] = $row;

            }

        }

        return [

            'success' => true,
            'status' => 200,
            'data' => $results

        ];
    }

    public function post(string $table, array $data): array
    {
        return [
            'success' => false,
            'status' => 501,
            'message' => 'Not implemented'
        ];
    }

    public function patch(string $table, array $params, array $data): array
    {
        return [
            'success' => false,
            'status' => 501,
            'message' => 'Not implemented'
        ];
    }

    public function delete(string $table, array $params): array
    {
        return [
            'success' => false,
            'status' => 501,
            'message' => 'Not implemented'
        ];
    }

    private function request(
        array $database,
        string $method,
        string $table,
        array $params = [],
        array $body = []
    ): array {

        $url = rtrim($database['url'], '/') . '/' . $table;

        if (!empty($params)) {

            $query = [];

            foreach ($params as $key => $value) {
                $query[] = $key . '=' . urlencode($value);
            }

            $url .= '?' . implode('&', $query);

        }

        $ch = curl_init($url);

        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => $method,

            CURLOPT_HTTPHEADER => [

                'apikey: ' . $database['key'],
                'Authorization: Bearer ' . $database['key'],
                'Content-Type: application/json',
                'Prefer: return=representation'

            ]

        ]);

        if (!empty($body)) {
            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode($body, JSON_UNESCAPED_UNICODE)
            );
        }

        $response = curl_exec($ch);

        $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

        $error = curl_error($ch);

        curl_close($ch);

        if ($error) {

            return [
                'success' => false,
                'status' => 500,
                'data' => []
            ];

        }

        return [

            'success' => $status >= 200 && $status < 300,
            'status' => $status,
            'data' => json_decode($response, true) ?: []

        ];

    }
}