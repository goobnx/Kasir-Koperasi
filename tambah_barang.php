
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Barang</title>
    <link rel="stylesheet" type="text/css" href="style.css">
    <style>
        input[type="number"] {
            width: 50%;
            padding: 5px;
            margin-top: 5px;
        }
    </style>
</head>
<body>
<body>
    <h1>Tambah Barang</h1>
    <form method="POST" action="add_item.php">
        <label for="code">Kode Barang:</label>
        <input type="text" name="code" id="code" required><br>
        <label for="name">Nama Barang:</label>
        <input type="text" name="name" id="name" required><br>
        <label for="price">Harga Satuan:</label>
        <input type="number" name="price" id="price" min="1" required><br>
        <label for="stock">Stok:</label>
        <input type="number" name="stock" id="stock" min="1" required><br>
        <button type="submit">Tambah Barang</button>
    </form>

    <script>
        document.getElementById('code').addEventListener('input', function() {
            var code = this.value;
            if (code.trim() === '') {
                clearForm();
                return;
            }
            var xhr = new XMLHttpRequest();
            xhr.open('GET', 'check_item.php?code=' + code, true);
            xhr.onload = function() {
                if (this.status == 200) {
                    var response = JSON.parse(this.responseText);
                    if (response.exists) {
                        document.getElementById('name').value = response.name;
                        document.getElementById('price').value = response.price;
                        document.getElementById('stock').value = response.stock;
                    } else {
                        clearForm();
                    }
                }
            };
            xhr.send();
        });

        function clearForm() {
            document.getElementById('name').value = '';
            document.getElementById('price').value = '';
            document.getElementById('stock').value = '';
        }
    </script>
</body>
</html>
