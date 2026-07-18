<?php

require_once __DIR__ . '/database.php';


class DatabaseManager
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

            throw new Exception(
                "Table '{$table}' is not configured."
            );

        }

    }



    public function get(string $table, array $params = []): array
    {

        $results = [];


        foreach ($this->databases as $databaseConfig) {


            $database = new Database($databaseConfig);


            $response = $database->get(
                $table,
                $params
            );


            if (!$response['success']) {

                continue;

            }



            foreach ($response['data'] as $row) {


                $row['_project'] = $databaseConfig['name'];


                $row['_uid'] =
                    $databaseConfig['name']
                    . '_'
                    . ($row['id'] ?? uniqid());


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



    public function patch(
        string $table,
        array $params,
        array $data
    ): array
    {

        return [

            'success' => false,

            'status' => 501,

            'message' => 'Not implemented'

        ];

    }



    public function delete(
        string $table,
        array $params
    ): array
    {

        return [

            'success' => false,

            'status' => 501,

            'message' => 'Not implemented'

        ];

    }


}