<?php
include 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $date = $_POST['date'];
    $cashier = $_POST['cashier'];
    $transaction_code = $_POST['transaction_code'];
    $total_amount = $_POST['total_amount'];
    $paid_amount = $_POST['paid_amount'];
    $change_amount = $_POST['change_amount'];
    $items = json_decode($_POST['items'], true);

    // Mulai transaksi
    $conn->begin_transaction();

    try {
        // Insert data transaksi ke tabel transaksi
        $sql = "INSERT INTO transactions (date, cashier, transaction_code, total_amount, paid_amount, change_amount) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param('sssdds', $date, $cashier, $transaction_code, $total_amount, $paid_amount, $change_amount);
        $stmt->execute();

        // Dapatkan ID transaksi yang baru saja dimasukkan
        $transaction_id = $conn->insert_id;

        // Loop melalui setiap item dan masukkan ke tabel transaction_items, lalu perbarui stok barang
        foreach ($items as $item) {
            $item_id = $item['id'];
            $quantity = $item['quantity'];
            $subtotal = $item['subtotal'];

            // Insert data ke tabel transaction_items
            $sql = "INSERT INTO transaction_items (transaction_id, item_id, quantity, subtotal) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('iiid', $transaction_id, $item_id, $quantity, $subtotal);
            $stmt->execute();

            // Kurangi stok barang
            $sql = "UPDATE items SET stock = stock - ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param('ii', $quantity, $item_id);
            $stmt->execute();
        }

        // Commit transaksi
        $conn->commit();

        // Redirect ke receipt.php dengan transaction_id
        header("Location: receipt.php?transaction_id=$transaction_id");
        exit();
    } catch (Exception $e) {
        // Rollback transaksi jika terjadi kesalahan
        $conn->rollback();
        echo "Error: " . $e->getMessage();
    }
}
?>
