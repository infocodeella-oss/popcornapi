<?php

class Database
{
    private string $baseUrl;
    private string $apiKey;

    public function __construct(array $config)
    {
        $this->baseUrl = rtrim($config['url'], '/');
        $this->apiKey = $config['key'];
    }


    public function get(string $table, array $params = []): array
    {
        return $this->request('GET', $table, $params);
    }


    public function post(string $table, array $data): array
    {
        return $this->request('POST', $table, [], $data);
    }


    public function patch(string $table, array $params, array $data): array
    {
        return $this->request('PATCH', $table, $params, $data);
    }


    public function delete(string $table, array $params): array
    {
        return $this->request('DELETE', $table, $params);
    }


    private function request(
        string $method,
        string $table,
        array $params = [],
        array $body = []
    ): array {


        $url = $this->baseUrl . '/' . $table;


        if (!empty($params)) {

            $query = [];

            foreach ($params as $key => $value) {

                $query[] = $key . '=' . $value;

            }

            $url .= '?' . implode('&', $query);

        }


        $ch = curl_init($url);


        curl_setopt_array($ch, [

            CURLOPT_RETURNTRANSFER => true,

            CURLOPT_CUSTOMREQUEST => $method,

            CURLOPT_HTTPHEADER => [

                'apikey: ' . $this->apiKey,

                'Authorization: Bearer ' . $this->apiKey,

                'Content-Type: application/json',

                'Prefer: return=representation'

            ]

        ]);


        if (!empty($body)) {

            curl_setopt(
                $ch,
                CURLOPT_POSTFIELDS,
                json_encode(
                    $body,
                    JSON_UNESCAPED_UNICODE
                )
            );

        }


        $response = curl_exec($ch);

        $status = curl_getinfo(
            $ch,
            CURLINFO_HTTP_CODE
        );

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