<?php
include 'koneksi.php';

if (!isset($_GET['transaction_id'])) {
    echo "Transaction ID tidak ditemukan.";
    exit;
}

$transaction_id = $_GET['transaction_id'];

// Ambil data transaksi
$sql = "SELECT * FROM transactions WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $transaction_id);
$stmt->execute();
$result = $stmt->get_result();
$transaction = $result->fetch_assoc();

if (!$transaction) {
    echo "Transaksi tidak ditemukan.";
    exit;
}

// Ambil item transaksi
$sql = "SELECT ti.*, i.name, i.price FROM transaction_items ti JOIN items i ON ti.item_id = i.id WHERE ti.transaction_id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param('i', $transaction_id);
$stmt->execute();
$items = $stmt->get_result();

?>

<!DOCTYPE html>
<html>
<head>
    <title>Struk Transaksi</title>
    <style>
        body {
            font-family: 'Courier New', Courier, monospace;
            margin: 0;
            padding: 0;
            background-color: #f7f7f7;
        }
        .receipt {
            width: 300px;
            margin: 20px auto;
            padding: 10px;
            background: #fff;
            border: 1px solid #ccc;
            box-shadow: 0 0 10px rgba(0, 0, 0, 0.1);
        }
        .receipt h2 {
            text-align: center;
            margin: 0;
        }
        .receipt .details {
            margin: 20px 0;
            text-align: center;
        }
        .receipt .details p {
            margin: 5px 0;
        }
        .receipt table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .receipt table, .receipt table th, .receipt table td {
            border: none;
        }
        .receipt table th, .receipt table td {
            padding: 5px 0;
            text-align: left;
        }
        .receipt table th {
            font-weight: bold;
        }
        .receipt table td:last-child {
            text-align: right;
        }
        .receipt .totals table {
            width: 100%;
        }
        .receipt .totals table td {
            padding: 5px 0;
        }
        .receipt .totals table td:first-child {
            text-align: left;
        }
        .receipt .totals table td:last-child {
            text-align: right;
        }
        .receipt .thanks {
            text-align: center;
            margin-top: 20px;
            font-size: 12px;
        }
        .dashed-line-1 {
            border-top: 1.5px dashed #000;
            margin: 10px 0;
        }
        .dashed-line-2 {
            border-top: 1.5px dashed #000;
            margin: 10px 0;
            width: 75%;
            float: right;
        }
    </style>
</head>
<body>
    <div class="receipt">
        <h2>KPRS - KOPERASI SKAGA</h2>
        <div class="dashed-line-1"></div>
        <div class="details">
            <p>Lingkungan SMKN 3 Jember</p>
            <p>Jl. dr. Soebandi No. 31</p>
            <p>Telp: (0341) 427457</p>
        </div>
        <div class="dashed-line-1"></div>
        <p>Nota: <?php echo htmlspecialchars($transaction['transaction_code']); ?></p>
        <p>Kasir: <?php echo htmlspecialchars($transaction['cashier']); ?></p>
        <p>Tanggal: <?php echo $transaction['date']; ?></p>
        <table>
            <thead>
                <tr>
                    <th>Item</th>
                    <th>Qty</th>
                    <th>Harga</th>
                    <th>Subtotal</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($item = $items->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($item['name']); ?></td>
                    <td><?php echo $item['quantity']; ?></td>
                    <td><?php echo number_format($item['price'], 0, ',', '.'); ?></td>
                    <td><?php echo number_format($item['quantity'] * $item['price'], 0, ',', '.'); ?></td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <div class="totals"></div>
        <div class="dashed-line-2"></div>
        <div>
            <table>
                <tr>
                    <td>TOTAL:</td>
                    <td><?php echo number_format($transaction['total_amount'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>BAYAR:</td>
                    <td><?php echo number_format($transaction['paid_amount'], 0, ',', '.'); ?></td>
                </tr>
                <tr>
                    <td>KEMBALI:</td>
                    <td><?php echo number_format($transaction['change_amount'], 0, ',', '.'); ?></td>
                </tr>
            </table>
        </div>
        <div class="thanks">
            <p>* Terima kasih atas kunjungan Anda *</p>
        </div>
    </div>
</body>
</html>

<?php
unset($_SESSION['transaction']);
?>
