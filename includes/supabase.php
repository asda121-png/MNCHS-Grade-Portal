<?php
class Supabase {

    private $url;
    private $key;

    public function __construct() 
    {
        // Your Supabase URL and JWT key
        $this->url = "https://sunleqpmzpmhjvufhdco.supabase.co";
        $this->key = "eyJhbGciOiJIUzI1NiIsInR5cCI6IkpXVCJ9.eyJpc3MiOiJzdXBhYmFzZSIsInJlZiI6InN1bmxlcXBtenBtaGp2dWZoZGNvIiwicm9sZSI6InNlcnZpY2Vfcm9sZSIsImlhdCI6MTc2NTEwODYxMSwiZXhwIjoyMDgwNjg0NjExfQ.F4lvgEGIwb_M_M5u8OHzkMfIU7uj4IeZjWOceIpS9ks"; 
    }

    private function request($method, $table, $filters = [], $body = null)
    {
        $endpoint = $this->url . "/rest/v1/" . $table;

        if (!empty($filters)) {
            $endpoint .= "?" . http_build_query($filters);
        }

        $curl = curl_init($endpoint);

        $headers = [
            "apikey: {$this->key}",
            "Authorization: Bearer {$this->key}",
            "Content-Type: application/json",
            "Prefer: return=representation"
        ];

        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($curl, CURLOPT_CUSTOMREQUEST, $method);

        if ($body !== null) {
            curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($body));
        }

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }

    // SELECT
    public function select($table, $filters = [])
    {
        return $this->request("GET", $table, $filters);
    }

    // INSERT
    public function insert($table, $data)
    {
        return $this->request("POST", $table, [], $data);
    }

    // UPDATE
    public function update($table, $filters, $data)
    {
        return $this->request("PATCH", $table, $filters, $data);
    }

    // DELETE
    public function delete($table, $filters)
    {
        return $this->request("DELETE", $table, $filters);
    }
    
   
    private $apiKey;


    public function fetchUser($usernameOrEmail) {
        $endpoint = $this->url . "/rest/v1/users?select=*&or=(username.eq.$usernameOrEmail,email.eq.$usernameOrEmail)";

        $curl = curl_init($endpoint);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_HTTPHEADER, [
            "apikey: {$this->apiKey}",
            "Authorization: Bearer {$this->apiKey}"
        ]);

        $response = curl_exec($curl);
        curl_close($curl);

        return json_decode($response, true);
    }
}
