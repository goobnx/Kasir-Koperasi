<?php
include 'koneksi.php';
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $transaction_code = $_POST['transaction_code'];
    $cashier = $_POST['cashier'];
    $date = $_POST['date'];
    $total_amount = $_POST['total_amount'];
    $paid_amount = $_POST['paid_amount'];
    $change_amount = $_POST['change_amount'];
    $items = json_decode($_POST['items'], true);

    if (empty($transaction_code) || empty($cashier) || empty($date) || empty($total_amount) || empty($paid_amount) || empty($change_amount) || empty($items)) {
        echo "Data tidak lengkap.";
        exit;
    }

    // Fungsi untuk menyimpan transaksi dan menangani duplikasi kode transaksi
    function saveTransaction($conn, $transaction_code, $cashier, $date, $total_amount, $paid_amount, $change_amount, $items) {
        try {
            $sql = "INSERT INTO transactions (transaction_code, cashier, date, total_amount, paid_amount, change_amount)
                    VALUES ('$transaction_code', '$cashier', '$date', '$total_amount', '$paid_amount', '$change_amount')";
            if ($conn->query($sql) === TRUE) {
                $transaction_id = $conn->insert_id;
                foreach ($items as $item) {
                    $item_id = $item['id'];
                    $quantity = $item['quantity'];
                    $subtotal = $item['subtotal'];
                    $sql = "INSERT INTO transaction_items (transaction_id, item_id, quantity, subtotal)
                            VALUES ('$transaction_id', '$item_id', '$quantity', '$subtotal')";
                    $conn->query($sql);
                }

                // Simpan data transaksi ke sesi
                $_SESSION['transaction'] = [
                    'transaction_code' => $transaction_code,
                    'cashier' => $cashier,
                    'date' => $date,
                    'total_amount' => $total_amount,
                    'paid_amount' => $paid_amount,
                    'change_amount' => $change_amount,
                    'items' => $items
                ];

                header('Location: receipt.php');
                exit();
            } else {
                throw new Exception($conn->error);
            }
        } catch (mysqli_sql_exception $e) {
            if ($e->getCode() == 1062) { // Kode kesalahan untuk duplikasi entri
                // Coba lagi dengan kode transaksi yang baru
                $new_transaction_code = uniqid('TRX', true);
                saveTransaction($conn, $new_transaction_code, $cashier, $date, $total_amount, $paid_amount, $change_amount, $items);
            } else {
                echo "Error: " . $sql . "<br>" . $conn->error;
            }
        }
    }

    // Panggil fungsi untuk menyimpan transaksi
    saveTransaction($conn, $transaction_code, $cashier, $date, $total_amount, $paid_amount, $change_amount, $items);
}
?>
