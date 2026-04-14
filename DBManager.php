<?php
// DBManager.php
class Performer {
    public $izena, $jaiotze_data, $generoa, $adina, $altuera;

    public function __construct($izena, $jaiotze_data, $generoa, $adina, $altuera) {
        $this->izena = $izena;
        $this->jaiotze_data = $jaiotze_data;
        $this->generoa = $generoa;
        $this->adina = $adina;
        $this->altuera = $altuera;
    }

    public function __toString() {
        return "$this->izena - $this->generoa - $this->adina urte - $this->altuera m";
    }
}

class DBManager {
    private $mysqli;

    public function __construct() {
        $this->mysqli = new mysqli("localhost", "root", "", "exam");
    }

    public function insertPerformer(Performer $p) {
        $stmt = $this->mysqli->prepare(
            "INSERT INTO interpretea (izena, jaiotze_data, generoa, adina, altuera) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param("sssid", $p->izena, $p->jaiotze_data, $p->generoa, $p->adina, $p->altuera);
        $stmt->execute();
    }

    public function getPerformers() {
        $result = $this->mysqli->query("SELECT * FROM interpretea");
        $performers = [];
        while ($row = $result->fetch_assoc()) {
            $performers[] = new Performer(
                $row['izena'], $row['jaiotze_data'], $row['generoa'],
                $row['adina'], $row['altuera']
            );
        }
        return $performers;
    }
}