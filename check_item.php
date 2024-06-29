<?php
include 'koneksi.php';

if (isset($_GET['code'])) {
    $code = $_GET['code'];
    $sql = "SELECT * FROM items WHERE code='$code'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        echo json_encode([
            'exists' => true,
            'name' => $row['name'],
            'price' => $row['price'],
            'stock' => $row['stock']
        ]);
    } else {
        echo json_encode(['exists' => false]);
    }
}
?>
