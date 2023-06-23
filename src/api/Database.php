<?php

class Database
{
    private static $instance;
    private $pdo;

    private function __construct()
    {
        // MySQL database configuration
        $host = 'db';
        $dbname = 'webproject';
        $username = 'root';
        $password = 'parola';
        $port = '3306';

        $dsn = "mysql:dbname=$dbname;host=$host;port=$port";

        // Create a PDO instance
        $this->pdo = new PDO($dsn, $username, $password);

        // Set PDO error mode to exception
        $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    }

    public static function getInstance()
    {
        if (!self::$instance) {
            self::$instance = new self();
        }

        return self::$instance;
    }

    public function getConnection()
    {
        return $this->pdo;
    }

    public function insert($table, $data)
    {
        $columns = implode(', ', array_keys($data));
        $placeholders = implode(', ', array_fill(0, count($data), '?'));

        $values = array_values($data);

        $sql = "INSERT INTO $table ($columns) VALUES ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($values);

        return $this->pdo->lastInsertId();
    }

    public function executeRawQuery($sql, $selectOne = false){
        $stmt = $this->pdo->prepare($sql);
        $stmt->execute();

        if($selectOne){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function select($table, $columns = '*', $where = '', $params = [], $selectOne = false, $limit = null, $offset = null)
    {
        $sql = "SELECT $columns FROM $table";

        if (!empty($where)) {
            $sql .= " WHERE $where";
        }

        if(isset($limit) && isset($offset)){
            $sql .= " LIMIT $limit OFFSET $offset";
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        if($selectOne){
            return $stmt->fetch(PDO::FETCH_ASSOC);
        }
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function update($table, $data, $where, $params = [])
    {
        $set = '';
        foreach ($data as $column => $value) {
            $set .= "$column = ?, ";
            $params[] = $value;
        }
        $set = rtrim($set, ', ');

        $sql = "UPDATE $table SET $set WHERE $where";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    public function delete($table, $where, $params = [])
    {
        $sql = "DELETE FROM $table WHERE $where";

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return $stmt->rowCount();
    }

    // Other database-related methods can be added here
}