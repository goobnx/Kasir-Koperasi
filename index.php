<?php
session_start();
include 'koneksi.php';

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Check if the login success flag is set
$loginSuccess = false;
if (isset($_SESSION['login_success'])) {
    $loginSuccess = true;
    unset($_SESSION['login_success']); // Unset the login success flag
}

$nickname = $_SESSION['nickname'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Transaksi Penjualan</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.css">
    <style>
        input[type="number"] {
            width: 50%;
            padding: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
    <h1>Transaksi Penjualan</h1>
    <button onclick="confirmLogout()">Logout</button>
    <form method="POST" action="process_transaction.php" id="transactionForm">
        <label for="date">Tanggal:</label>
        <input type="date" name="date" id="date" value="<?php echo date('Y-m-d'); ?>"><br>
        <label for="cashier">Kasir:</label>
        <input type="text" name="cashier" id="cashier" value="<?php echo htmlspecialchars($nickname); ?>" readonly><br>
        <label for="transaction_code">Kode Transaksi:</label>
        <input type="text" name="transaction_code" id="transaction_code" value="<?php echo uniqid('TRX', false); ?>" readonly><br>
        <table id="transactionTable">
            <thead>
                <tr>
                    <th>No.</th>
                    <th>Nama Barang</th>
                    <th>Harga Satuan</th>
                    <th>Banyak</th>
                    <th>Subtotal</th>
                    <th>Aksi</th>
                </tr>
            </thead>
            <tbody id="transactionItems">
                <!-- Diisi secara dinamis dengan JavaScript -->
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="4" style="text-align:right;">Total:</td>
                    <td colspan="2"><input type="text" name="total_amount" id="total_amount" readonly></td>
                </tr>
            </tfoot>
        </table>
        <label for="barcodeInput">Kode Barang:</label>
        <input type="text" id="barcodeInput" required><br>
        <label for="paid_amount">Dibayar:</label>
        <input type="number" name="paid_amount" id="paid_amount" required><br>
        <label for="change_amount">Kembali:</label>
        <input type="number" name="change_amount" id="change_amount" readonly><br>
        <input type="hidden" name="items" id="items">
        <button type="submit">Simpan Transaksi</button>
    </form>

    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/toastr.js/latest/toastr.min.js"></script>
    <script>
        <?php if ($loginSuccess): ?>
            toastr.success('Selamat Datang <?=$nickname;?>');
        <?php endif; ?>

        document.getElementById('transactionForm').addEventListener('submit', function(e) {
            var items = [];
            var transactionItems = document.getElementById('transactionItems').children;
            for (var i = 0; i < transactionItems.length; i++) {
                var item = {
                    id: transactionItems[i].dataset.id,
                    name: transactionItems[i].querySelector('.name').textContent,
                    price: transactionItems[i].querySelector('.price').textContent,
                    quantity: transactionItems[i].querySelector('.quantity').value,
                    subtotal: transactionItems[i].querySelector('.subtotal').textContent
                };
                items.push(item);
            }
            document.getElementById('items').value = JSON.stringify(items);
        });

        document.getElementById('barcodeInput').addEventListener('keypress', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                addItem();
            }
        });

        document.getElementById('barcodeInput').addEventListener('change', addItem);

        function addItem() {
            var code = document.getElementById('barcodeInput').value;
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'get_item.php?code=' + code, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    var item = JSON.parse(this.responseText);
                    if (item.error) {
                        alert(item.error);
                    } else {
                        addItemToTransaction(item);
                        document.getElementById('barcodeInput').value = '';
                        document.getElementById('barcodeInput').focus();
                    }
                }
            };
            xhr.send();
        }

        function addItemToTransaction(item) {
            var transactionItems = document.getElementById('transactionItems');
            var existingItem = document.getElementById('item-' + item.id);

            if (existingItem) {
                var quantityInput = existingItem.querySelector('.quantity');
                quantityInput.value = parseInt(quantityInput.value) + 1;
                var subtotal = existingItem.querySelector('.subtotal');
                subtotal.textContent = parseInt(quantityInput.value) * item.price;
            } else {
                var rowCount = transactionItems.children.length + 1;
                var itemRow = document.createElement('tr');
                itemRow.id = 'item-' + item.id;
                itemRow.dataset.id = item.id;
                itemRow.innerHTML = `
                    <td>${rowCount}</td>
                    <td class="name">${item.name}</td>
                    <td class="price">${item.price}</td>
                    <td><input type="number" class="quantity" value="1" readonly></td>
                    <td class="subtotal">${item.price}</td>
                    <td><button type="button" onclick="removeItem(${item.id})">Hapus</button></td>
                `;
                transactionItems.appendChild(itemRow);
            }
            updateTotal();
        }

        function removeItem(itemId) {
            var itemRow = document.getElementById('item-' + itemId);
            itemRow.parentNode.removeChild(itemRow);
            updateTotal();
        }

        function updateTotal() {
            var transactionItems = document.getElementById('transactionItems').children;
            var total = 0;
            for (var i = 0; i < transactionItems.length; i++) {
                var subtotal = parseFloat(transactionItems[i].querySelector('.subtotal').textContent);
                total += subtotal;
            }
            document.getElementById('total_amount').value = total;
            updateChange();
        }

        document.getElementById('paid_amount').addEventListener('input', updateChange);

        function updateChange() {
            var total = parseFloat(document.getElementById('total_amount').value);
            var paid = parseFloat(document.getElementById('paid_amount').value);
            document.getElementById('change_amount').value = paid - total;
        }

        function confirmLogout() {
            if (confirm('Apakah Anda yakin ingin logout?')) {
                window.location.href = 'logout.php'; // Redirect to logout page
            }
        }
    </script>
</body>
</html>
