<?php
include 'koneksi.php';

$code = $_GET['code'];
$sql = "SELECT * FROM items WHERE code='$code'";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    echo json_encode($result->fetch_assoc());
} else {
    echo json_encode(array("error" => "Barang tidak ditemukan"));
}
?>
