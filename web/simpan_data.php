<?php
// simpan_data.php
$host = "localhost";
$user = "root";
$pass = "";
$db   = "iot_project";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Koneksi gagal: " . $conn->connect_error);
}

if (isset($_POST['suhu']) && isset($_POST['kelembaban'])) {
    $suhu = $_POST['suhu'];
    $hum  = $_POST['kelembaban'];

    $sql = "INSERT INTO sensor_data (suhu, kelembaban) VALUES ('$suhu', '$hum')";
    
    if ($conn->query($sql) === TRUE) {
        echo "Sukses simpan";
    } else {
        echo "Error: " . $sql . "<br>" . $conn->error;
    }
}
$conn->close();
?>