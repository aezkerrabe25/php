<?php
session_start();
require_once 'DBManager.php';

if (!isset($_SESSION['akats_kop'])) {
    $_SESSION['akats_kop'] = 0;
}

// Funtzio laguntzaileak
function kalkulatu25($performerArray) {
    $suma = 0;
    $kop  = 0;
    foreach ($performerArray as $perf) {
        if ($perf->adina > 25) {
            $suma += $perf->altuera;
            $kop++;
        }
    }
    if ($kop > 0) {
        echo "<p>25 urtetik gorako interpreteen batez besteko altuera: "
             . round($suma / $kop, 2) . " m</p>";
    } else {
        echo "<p>Ez dago 25 urtetik gorako interpreterik.</p>";
    }
}

function kalkulatuEmakumeAltuak($performerArray) {
    $sumaEma = 0;
    $kopEma  = 0;
    foreach ($performerArray as $perf) {
        if ($perf->generoa === 'emakumezkoa') {
            $sumaEma += $perf->altuera;
            $kopEma++;
        }
    }
    $guztira = count($performerArray);
    if ($guztira > 0 && $kopEma > 0) {
        $emakume_avg = $sumaEma / $kopEma;
        $altuagoak   = 0;
        foreach ($performerArray as $perf) {
            if ($perf->altuera > $emakume_avg) {
                $altuagoak++;
            }
        }
        $ehunekoa = ($altuagoak / $guztira) * 100;
        echo "<p>Emakumeen bataz bestekoa baino altuagoak diren interpreteen ehunekoa: "
             . round($ehunekoa, 2) . " %</p>";
    } else {
        echo "<p>Ez dago emakumezko interpreterik.</p>";
    }
}

// POST ekintza zein den begiratu
$ekintza = $_POST['ekintza'] ?? '';

if ($ekintza === 'gorde') {

    $izena   = trim($_POST['izena']);
    $jaiotze = $_POST['jaiotze_data'];
    $generoa = $_POST['generoa'];
    $adina   = intval($_POST['adina']);
    $altuera = floatval($_POST['altuera']);

    $errorea = false;

    if (strlen($izena) < 2 || strlen($izena) > 15) $errorea = true;

    $jaiotze_dt = new DateTime($jaiotze);
    $min_dt     = new DateTime("1900-01-01");
    if ($jaiotze_dt <= $min_dt) $errorea = true;

    if (!in_array($generoa, ["gizonezkoa", "emakumezkoa", "besteak"])) $errorea = true;
    if ($adina < 1 || $adina > 110) $errorea = true;
    if ($altuera < 0.5 || $altuera > 3) $errorea = true;

    if ($errorea) {
        $_SESSION['akats_kop']++;
        if ($_SESSION['akats_kop'] >= 3) {
            echo "<h2>Zer egiten ari zara?</h2>";
        } else {
            echo "<p>Datu okerrak. Saiatu berriro.</p>";
        }
    } else {
        $_SESSION['akats_kop'] = 0;

        $db = new DBManager();
        $performer = new Performer($izena, $jaiotze, $generoa, $adina, $altuera);
        $db->insertPerformer($performer);

        echo "<p>Interpretea ondo gorde da!</p>";

        $performerArray = $db->getPerformers();
        echo "<ul>";
        foreach ($performerArray as $perf) {
            echo "<li>$perf</li>";
        }
        echo "</ul>";

        kalkulatu25($performerArray);
        kalkulatuEmakumeAltuak($performerArray);
    }

} elseif ($ekintza === '25gora') {

    $db = new DBManager();
    kalkulatu25($db->getPerformers());

} elseif ($ekintza === 'emakumeak') {

    $db = new DBManager();
    kalkulatuEmakumeAltuak($db->getPerformers());

}
?>

<html>
<head>
    <meta charset="UTF-8">
    <title>Aktore berria sortu</title>
</head>
<body>

<h2>Aktore berria sortu</h2>

<form method="post" action="">
    <label>Izena:</label>
    <input type="text" name="izena" minlength="2" maxlength="15" required><br><br>

    <label>Jaiotze data:</label>
    <input type="date" name="jaiotze_data" min="1900-01-01" required><br><br>

    <label>Generoa:</label>
    <select name="generoa" required>
        <option value="">Aukeratu</option>
        <option value="gizonezkoa">Gizonezkoa</option>
        <option value="emakumezkoa">Emakumezkoa</option>
        <option value="besteak">Besteak</option>
    </select><br><br>

    <label>Adina:</label>
    <input type="number" name="adina" min="1" max="110" required><br><br>

    <label>Altuera (m):</label>
    <input type="number" name="altuera" step="0.01" min="0.5" max="3" required><br><br>

    <input type="hidden" name="ekintza" value="gorde">
    <button type="submit">Gorde</button>
</form>

<hr>

<h3>Estatistikak</h3>

<form method="post" action="">
    <input type="hidden" name="ekintza" value="25gora">
    <button type="submit">25 urte gora</button>
</form>

<form method="post" action="">
    <input type="hidden" name="ekintza" value="emakumeak">
    <button type="submit">Emakume altuak</button>
</form>

</body>
</html>