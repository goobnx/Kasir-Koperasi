<?php
include 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $code = $_POST['code'];
    $name = $_POST['name'];
    $price = $_POST['price'];
    $stock = $_POST['stock'];

    // Periksa apakah kode barang sudah ada
    $sql = "SELECT * FROM items WHERE code='$code'";
    $result = $conn->query($sql);

    if ($result->num_rows > 0) {
        // Jika kode barang sudah ada, tambahkan stok lama
        $row = $result->fetch_assoc();
        $new_stock = $row['stock'] + $stock;
        $sql = "UPDATE items SET name='$name', price='$price', stock='$new_stock' WHERE code='$code'";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                alert('Stok barang berhasil diperbarui.');
                window.location.href = 'tambah_barang.php';
              </script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    } else {
        // Jika kode barang tidak ada, tambahkan barang baru
        $sql = "INSERT INTO items (code, name, price, stock) VALUES ('$code', '$name', '$price', '$stock')";
        if ($conn->query($sql) === TRUE) {
            echo "<script>
                alert('Barang berhasil ditambahkan.');
                window.location.href = 'tambah_barang.php';
              </script>";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}
?>
