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
                // Clear the barcode input and reset focus
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
    // Redirect to index.php after updating the item
    window.location.href = 'index.php';
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
