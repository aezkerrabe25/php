<?php
class DBManager {
    private $conn;

    public function __construct($host, $user, $pass, $db) {
        $this->conn = new mysqli($host, $user, $pass, $db);
        if ($this->conn->connect_error) {
            die("Error conexión: " . $this->conn->connect_error);
        }
    }

    public function insert($sql) {
        return $this->conn->query($sql);
    }

    public function select($sql) {
        $result = $this->conn->query($sql);
        $rows = [];
        while ($row = $result->fetch_assoc()) {
            $rows[] = $row;
        }
        return $rows;
    }

    public function escape($value) {
        return $this->conn->real_escape_string($value);
    }
}