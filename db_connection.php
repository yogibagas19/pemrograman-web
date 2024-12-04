<?php
function connect_db() {
    $host = 'localhost';
    $dbname = 'tr_game_store';
    $username = 'root';
    $password = '';

    // Buat koneksi
    $conn = new mysqli($host, $username, $password, $dbname);

    // Cek koneksi
    if ($conn->connect_error) {
        die("Koneksi gagal: " . $conn->connect_error);
    }

    return $conn;
}
?>
