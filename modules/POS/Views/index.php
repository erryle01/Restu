<?php
$e = static fn($value): string => htmlspecialchars((string) ($value ?? ''), ENT_QUOTES, 'UTF-8');
$products = $products ?? [];
$tables = $tables ?? [];
$taxRate = (float) ($taxRate ?? 0);
?>
<style>
    .pos-screen {
        --pos-ink: #17221f;
        --pos-muted: #687873;
        --pos-line: #dce5e1;
        --pos-paper: #f4f7f5;
        color: var(--pos-ink);
    }

    .pos-screen .pos-heading {
        font-weight: 750;
    }

    .pos-screen .pos-product-card {
        border-color: var(--pos-line) !important;
        background: #fff;
        transition: transform .14s ease, box-shadow .14s ease, border-color .14s ease;
    }

    .pos-screen .pos-product-card:hover,
    .pos-screen .pos-product-card:focus-visible {
        transform: translateY(-2px);
        border-color: #42a37f !important;
        box-shadow: 0 8px 22px rgba(23, 55, 43, .1);
    }

    .pos-screen .pos-product-card,
    .pos-screen .pos-table-card {
        min-height: 48px;
        color: var(--pos-ink);
    }

    .pos-screen .pos-table-card {
        min-width: 104px;
        border: 1px solid var(--pos-line);
        background: #fff;
        text-align: left;
    }

    .pos-screen .pos-table-card.is-selected {
        border-color: #16815d;
        box-shadow: inset 0 0 0 1px #16815d;
        background: #e9f6ef;
    }

    .pos-screen .pos-table-card:disabled {
        opacity: .45;
        cursor: not-allowed;
    }

    .pos-search-wrap {
        position: relative;
        z-index: 5;
    }

    .pos-search-suggestions {
        position: absolute;
        top: 100%;
        left: 0;
        right: 0;
        max-height: 280px;
        overflow-y: auto;
        background: #fff;
        border: 1px solid #cbd5d1;
        border-top: 0;
        box-shadow: 0 10px 24px rgba(16, 32, 26, .16);
    }

    .pos-search-suggestion {
        display: flex;
        justify-content: space-between;
        width: 100%;
        padding: 10px 12px;
        border: 0;
        background: #fff;
        text-align: left;
    }

    .pos-search-suggestion[aria-selected="true"],
    .pos-search-suggestion:hover {
        background: #e8f3ee;
    }

    .pos-quantity-dialog {
        width: min(420px, calc(100vw - 32px));
        border: 0;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .28);
    }

    .pos-quantity-dialog::backdrop {
        background: rgba(8, 15, 12, .68);
    }

    .pos-table-dialog {
        width: min(760px, calc(100vw - 32px));
        max-width: 760px;
        border: 0;
        border-radius: 8px;
        padding: 0;
        box-shadow: 0 24px 80px rgba(0, 0, 0, .28);
    }

    .pos-table-dialog::backdrop {
        background: rgba(8, 15, 12, .68);
    }

    .pos-table-dialog .pos-table-options {
        display: grid;
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
        gap: 10px;
        max-height: min(55vh, 440px);
        overflow-y: auto;
        padding: 2px;
    }

    .pos-shortcut-bar {
        position: sticky;
        bottom: 0;
        z-index: 4;
        display: flex;
        flex-wrap: wrap;
        gap: 6px 12px;
        margin: 16px -24px -24px;
        padding: 10px 20px;
        background: #111;
        color: #f5f5f5;
        font-size: 11px;
    }

    .pos-shortcut {
        display: inline-flex;
        align-items: center;
        gap: 5px;
        white-space: nowrap;
    }

    .pos-shortcut kbd {
        min-width: 25px;
        padding: 2px 5px;
        border: 1px solid #666;
        border-radius: 4px;
        background: #292929;
        color: #fff;
        text-align: center;
        font-size: 10px;
    }

    .pos-screen .pos-cart-panel {
        position: sticky;
        top: 80px;
        border: 1px solid var(--pos-line) !important;
    }

    .pos-screen .pos-cart-items {
        max-height: min(42vh, 420px);
        overflow: auto;
    }

    .pos-screen:fullscreen {
        position: fixed;
        inset: 0;
        width: 100vw;
        height: 100vh;
        min-height: 100vh;
        overflow: auto;
        padding: 16px 24px 48px !important;
        background: var(--pos-paper);
        color: var(--pos-ink);
        z-index: 2147483647;
    }

    .pos-screen:fullscreen::backdrop {
        background: var(--pos-paper);
    }

    .pos-immersive {
        position: fixed;
        inset: 0;
        z-index: 2147483647;
        overflow: auto;
        padding: 16px 24px 54px;
        background: #f4f7f5;
        color: #17221f;
    }

    .pos-screen:fullscreen .pos-shortcut-bar,
    .pos-immersive .pos-shortcut-bar {
        position: fixed;
        left: 0;
        right: 0;
        bottom: 0;
        margin: 0;
    }

    .pos-screen:fullscreen .pos-cart-panel,
    .pos-immersive .pos-cart-panel {
        top: 12px;
    }

    @media (max-width: 1199.98px) {
        .pos-screen .pos-cart-panel { position: static; }
    }
</style>
<div class="container-fluid py-3 pos-screen" data-tax-rate="<?= $taxRate ?>">
    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div><h1 class="h3 mb-1 pos-heading">Restaurant POS</h1><p class="text-muted mb-0">Fast ordering for dine-in and take-out.</p><time class="small text-muted" id="posLiveClock" aria-live="off"></time></div>
        <div class="d-flex flex-wrap gap-2">
            <button type="button" class="btn btn-outline-primary" data-pos-select-table title="Select a dine-in table (Alt+T)"><i data-lucide="armchair"></i> Choose table <kbd>Alt+T</kbd></button>
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/tables/create" data-add-table-link title="Add restaurant table (Alt+N)"><i data-lucide="plus"></i> Add table <kbd>Alt+N</kbd></a>
            <a class="btn btn-outline-secondary" href="<?= BASE_URL ?>/tables" data-manage-tables-link title="Manage tables (Alt+M)"><i data-lucide="layout-grid"></i> Manage tables <kbd>Alt+M</kbd></a>
        </div>
    </div>
    <?php if (!empty($success)): ?><div class="alert alert-success"><?= $e($success) ?></div><?php endif; ?>
    <?php if (!empty($error)): ?><div class="alert alert-danger"><?= $e($error) ?></div><?php endif; ?>
    <form method="POST" action="<?= BASE_URL ?>/pos/checkout" id="posCheckoutForm">
        <input type="hidden" name="cart" id="posCart">
        <input type="hidden" name="tax_rate" value="<?= $taxRate ?>">
        <div class="row g-3">
            <div class="col-xl-8">
                <div class="d-flex flex-wrap gap-2 mb-3" role="group" aria-label="Order type">
                    <button type="button" class="btn btn-primary pos-order-type" data-order-type="take_out">Take-out <kbd>F1</kbd></button>
                    <button type="button" class="btn btn-outline-primary pos-order-type" data-order-type="dine_in">Dine-in <kbd>F2</kbd></button>
                    <input type="hidden" name="order_type" id="posOrderType" value="take_out">
                    <input type="hidden" name="table_id" id="posTable" value="">
                </div>
                <dialog id="posTableDialog" class="pos-table-dialog" aria-labelledby="posTableDialogTitle">
                    <div class="p-4">
                        <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                            <div>
                                <h2 class="h5 mb-1" id="posTableDialogTitle">Choose a table</h2>
                                <p class="text-muted small mb-0">Select an available table for this dine-in order.</p>
                            </div>
                            <button type="button" class="btn-close" data-close-table-dialog aria-label="Close table selection"></button>
                        </div>
                        <div class="pos-table-options" id="posTables" aria-label="Available dine-in tables">
                    <?php foreach ($tables as $table): ?>
                        <?php if ($table['status'] !== 'inactive'): ?>
                            <button type="button" class="btn pos-table-card <?= $table['status'] === 'available' ? '' : 'text-muted' ?>" data-table-id="<?= (int) $table['id'] ?>" data-table-available="<?= $table['status'] === 'available' ? '1' : '0' ?>" <?= $table['status'] !== 'available' ? 'disabled' : '' ?> aria-pressed="false">
                                <span class="d-block fw-semibold"><?= $e($table['table_number']) ?></span>
                                <span class="d-block small"><?= (int) $table['capacity'] ?> seats · <?= $e(ucfirst($table['status'])) ?></span>
                            </button>
                        <?php endif; ?>
                    <?php endforeach; ?>
                        </div>
                        <div class="d-flex justify-content-end mt-3">
                            <button type="button" class="btn btn-outline-secondary" data-close-table-dialog>Cancel <kbd>Esc</kbd></button>
                        </div>
                    </div>
                </dialog>
                <div class="input-group mb-0 pos-search-wrap"><span class="input-group-text"><i data-lucide="search"></i></span><input id="posProductSearch" class="form-control" placeholder="Search products by name or SKU..." autocomplete="off" role="combobox" aria-autocomplete="list" aria-expanded="false" aria-controls="posSearchSuggestions"><span class="input-group-text"><kbd>F3</kbd></span><div id="posSearchSuggestions" class="pos-search-suggestions" role="listbox" hidden></div></div>
                <div class="row row-cols-2 row-cols-md-3 row-cols-lg-4 g-2" id="posProducts">
                    <?php foreach ($products as $product): ?><div class="col pos-product" data-name="<?= $e(strtolower($product['name'] . ' ' . $product['sku'] . ' ' . ($product['category'] ?? ''))) ?>"><button type="button" class="btn pos-product-card w-100 text-start p-2 h-100" data-product-id="<?= (int) $product['id'] ?>" data-product-name="<?= $e($product['name']) ?>" data-product-price="<?= (float) $product['price'] ?>" data-product-stock="<?= (float) $product['stock_quantity'] ?>"><?php if (!empty($product['image_path'])): ?><img src="<?= BASE_URL . '/' . $e($product['image_path']) ?>" alt="" class="w-100 rounded mb-2" style="height:110px;object-fit:cover"><?php endif; ?><span class="d-block fw-semibold"><?= $e($product['name']) ?></span><span class="d-block text-muted small"><?= $e($product['sku']) ?></span><span class="d-block text-success mt-2"><?= number_format((float) $product['price'], 2) ?></span><span class="d-block small text-muted">Stock: <?= $e($product['stock_quantity']) ?></span></button></div><?php endforeach; ?>
                </div>
            </div>
            <div class="col-xl-4"><div class="card border-0 shadow-sm pos-cart-panel"><div class="card-header bg-white d-flex justify-content-between"><span class="fw-semibold">Current order</span><button type="button" class="btn btn-sm btn-link text-danger" data-pos-clear>Clear</button></div><div class="card-body"><div id="posCartItems" class="pos-cart-items mb-3"><p class="text-muted text-center py-4">Select products to start.</p></div><div class="border-top pt-3"><div class="d-flex justify-content-between"><span>Subtotal</span><strong id="posSubtotal">0.00</strong></div><div class="input-group input-group-sm my-2"><span class="input-group-text">Discount</span><input type="number" min="0" step="0.01" class="form-control" name="discount" id="posDiscount" value="0"><span class="input-group-text"><?= $e($currency ?? 'PHP') ?></span></div><div class="d-flex justify-content-between"><span>Tax (<?= $taxRate ?>%)</span><strong id="posTax">0.00</strong></div><div class="d-flex justify-content-between fs-4 mt-2"><span>Total</span><strong id="posTotal">0.00</strong></div><div class="row g-2 mt-2"><div class="col-7"><select class="form-select" name="payment_method"><option value="cash">Cash</option><option value="card">Card</option><option value="gcash">GCash</option><option value="other">Other</option></select></div><div class="col-5"><input class="form-control" type="number" min="0" step="0.01" name="amount_tendered" id="posTendered" placeholder="Tendered" required></div></div><div class="d-flex justify-content-between small mt-2"><span>Change</span><strong id="posChange">0.00</strong></div><button type="submit" class="btn btn-success btn-lg w-100 mt-3"><i data-lucide="check-circle"></i> Complete order <kbd>F9</kbd></button></div></div></div></div>
        </div>
    </form>
    <div class="pos-shortcut-bar" aria-label="POS keyboard shortcuts">
        <span class="pos-shortcut"><kbd>F1</kbd> Take-out</span><span class="pos-shortcut"><kbd>F2</kbd> Dine-in</span><span class="pos-shortcut"><kbd>F3</kbd> Search</span><span class="pos-shortcut"><kbd>F4</kbd> Toggle fullscreen</span><span class="pos-shortcut"><kbd>F5</kbd> Clear</span><span class="pos-shortcut"><kbd>F6</kbd> Cash</span><span class="pos-shortcut"><kbd>F7</kbd> Card</span><span class="pos-shortcut"><kbd>F8</kbd> GCash</span><span class="pos-shortcut"><kbd>F9</kbd> Checkout</span><span class="pos-shortcut"><kbd>F10</kbd> Discount</span><span class="pos-shortcut"><kbd>F11</kbd> Exit fullscreen</span><span class="pos-shortcut"><kbd>F12</kbd> Tendered</span><span class="pos-shortcut"><kbd>Alt+T</kbd> Choose table</span><span class="pos-shortcut"><kbd>Alt+N</kbd> Add table</span><span class="pos-shortcut"><kbd>Alt+M</kbd> Manage tables</span><span class="pos-shortcut"><kbd>Esc</kbd> Dialog only</span><span class="pos-shortcut"><kbd>← ↑ ↓ →</kbd> Navigate</span><span class="pos-shortcut"><kbd>Enter</kbd> Choose / confirm</span><span class="pos-shortcut"><kbd>↑ ↓</kbd> Quantity</span>
    </div>
    <dialog class="pos-quantity-dialog" id="posQuantityDialog" aria-labelledby="posQuantityTitle">
        <div class="p-4">
            <div class="d-flex justify-content-between align-items-start gap-3 mb-3">
                <div>
                    <h2 class="h5 mb-1" id="posQuantityTitle">Add to order</h2>
                    <div class="text-muted" id="posQuantityProduct"></div>
                </div>
                <strong class="text-success" id="posQuantityPrice"></strong>
            </div>
            <label for="posQuantityInput" class="form-label">Quantity</label>
            <div class="input-group input-group-lg">
                <button type="button" class="btn btn-outline-secondary" data-quantity-down aria-label="Decrease quantity">−</button>
                <input type="number" class="form-control text-center fw-bold" id="posQuantityInput" min="1" step="1" value="1" inputmode="numeric">
                <button type="button" class="btn btn-outline-secondary" data-quantity-up aria-label="Increase quantity">+</button>
            </div>
            <div class="form-text mt-2" id="posQuantityStock"></div>
            <div class="d-flex justify-content-end gap-2 mt-4">
                <button type="button" class="btn btn-outline-secondary" data-quantity-cancel>Cancel <kbd>Esc</kbd></button>
                <button type="button" class="btn btn-success" data-quantity-confirm>Add item <kbd>Enter</kbd></button>
            </div>
        </div>
    </dialog>
</div>
<script>
document.addEventListener('DOMContentLoaded', function () {
    const root = document.querySelector('.pos-screen');
    if (!root) return;
    const cart = {};
    const taxRate = Number(root.dataset.taxRate || 0);
    const table = document.getElementById('posTable');
    const tableCards = Array.from(root.querySelectorAll('.pos-table-card:not(:disabled)'));
    const orderType = document.getElementById('posOrderType');
    const cartField = document.getElementById('posCart');
    const search = document.getElementById('posProductSearch');
    const suggestions = document.getElementById('posSearchSuggestions');
    const tableDialog = document.getElementById('posTableDialog');
    const discount = document.getElementById('posDiscount');
    const tendered = document.getElementById('posTendered');
    const payment = root.querySelector('[name="payment_method"]');
    const quantityDialog = document.getElementById('posQuantityDialog');
    const quantityInput = document.getElementById('posQuantityInput');
    let pendingProductButton = null;
    let returnToSearchAfterQuantity = false;
    let suggestionIndex = -1;

    function money(value) { return Number(value).toFixed(2); }

    function updateLiveClock() {
        const clock = root.querySelector('#posLiveClock');
        if (!clock) return;
        const now = new Date();
        const dateText = new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila',
            weekday: 'short',
            year: 'numeric',
            month: 'short',
            day: '2-digit'
        }).format(now);
        const timeText = new Intl.DateTimeFormat('en-PH', {
            timeZone: 'Asia/Manila',
            hour: '2-digit',
            minute: '2-digit',
            second: '2-digit',
            hour12: true
        }).format(now);
        clock.textContent = dateText + ' · ' + timeText + ' PHT';
    }

    updateLiveClock();
    window.setInterval(updateLiveClock, 1000);

    function availableProducts() {
        return Array.from(root.querySelectorAll('.pos-product:not([hidden]) [data-product-id]'));
    }

    function selectTable(card) {
        tableCards.forEach(function (item) {
            const selected = item === card;
            item.classList.toggle('is-selected', selected);
            item.setAttribute('aria-pressed', String(selected));
        });
        table.value = card ? card.dataset.tableId : '';
    }

    function setOrderType(type) {
        root.querySelectorAll('.pos-order-type').forEach(function (button) {
            const selected = button.dataset.orderType === type;
            button.classList.toggle('btn-primary', selected);
            button.classList.toggle('btn-outline-primary', !selected);
        });
        orderType.value = type;
        root.querySelector('#posTables').hidden = type !== 'dine_in';
        if (type === 'dine_in' && !table.value && tableCards.length) {
            selectTable(tableCards[0]);
            tableCards[0].focus();
        }
        if (type === 'take_out') selectTable(null);
    }

    function openTablePicker() {
        setOrderType('dine_in');
        if (!tableDialog.open) tableDialog.showModal();
        const selected = tableCards.find(card => card.dataset.tableId === table.value) || tableCards[0];
        if (selected) {
            selected.focus();
            selected.scrollIntoView({ block: 'nearest' });
        }
    }

    function addProduct(button, requestedQuantity) {
        if (!button) return;
        const productId = button.dataset.productId;
        const stock = Number(button.dataset.productStock);
        if (!cart[productId]) {
            cart[productId] = {
                id: productId,
                name: button.dataset.productName,
                price: Number(button.dataset.productPrice),
                quantity: 0,
                stock: stock
            };
        }
        const quantity = Math.max(1, Number(requestedQuantity) || 1);
        cart[productId].quantity = Math.min(cart[productId].quantity + quantity, cart[productId].stock);
        render();
    }

    function openQuantityDialog(button, returnToSearch) {
        if (!button) return;
        const productId = button.dataset.productId;
        const alreadyAdded = cart[productId] ? cart[productId].quantity : 0;
        const remaining = Number(button.dataset.productStock) - alreadyAdded;
        if (remaining < 1) return;

        pendingProductButton = button;
        returnToSearchAfterQuantity = returnToSearch;
        document.getElementById('posQuantityProduct').textContent = button.dataset.productName;
        document.getElementById('posQuantityPrice').textContent = money(button.dataset.productPrice);
        document.getElementById('posQuantityStock').textContent = remaining + ' available';
        quantityInput.max = String(remaining);
        quantityInput.value = '1';
        quantityDialog.showModal();
        quantityInput.focus();
        quantityInput.select();
    }

    function confirmQuantity() {
        if (!pendingProductButton) return;
        const maximum = Number(quantityInput.max || 1);
        const amount = Math.min(maximum, Math.max(1, Math.floor(Number(quantityInput.value) || 1)));
        const selectedProduct = pendingProductButton;
        quantityDialog.close();
        addProduct(selectedProduct, amount);
        pendingProductButton = null;
        closeSuggestions();

        if (returnToSearchAfterQuantity) {
            search.value = '';
            root.querySelectorAll('.pos-product').forEach(productCard => productCard.hidden = false);
            search.focus();
        } else {
            selectedProduct.focus();
        }
        returnToSearchAfterQuantity = false;
    }

    function closeSuggestions() {
        suggestions.hidden = true;
        suggestions.innerHTML = '';
        search.setAttribute('aria-expanded', 'false');
        search.removeAttribute('aria-activedescendant');
        suggestionIndex = -1;
    }

    function renderSuggestions() {
        const query = search.value.trim().toLowerCase();
        const matchingCards = query
            ? Array.from(root.querySelectorAll('.pos-product')).filter(function (productCard) {
                return productCard.dataset.name.includes(query);
            }).slice(0, 8)
            : [];

        root.querySelectorAll('.pos-product').forEach(function (productCard) {
            productCard.hidden = Boolean(query) && !productCard.dataset.name.includes(query);
        });

        if (!matchingCards.length) {
            closeSuggestions();
            return;
        }

        suggestions.innerHTML = matchingCards.map(function (productCard, index) {
            const productButton = productCard.querySelector('[data-product-id]');
            return '<button type="button" class="pos-search-suggestion" role="option" id="pos-suggestion-' + index + '" aria-selected="false" data-suggestion-index="' + index + '" data-product-id="' + productButton.dataset.productId + '"><span>' + productButton.dataset.productName + '<small class="d-block text-muted">' + productButton.dataset.productStock + ' in stock</small></span><strong>' + money(productButton.dataset.productPrice) + '</strong></button>';
        }).join('');
        suggestions.hidden = false;
        search.setAttribute('aria-expanded', 'true');
        suggestionIndex = -1;
    }

    function focusSuggestion(index) {
        const options = Array.from(suggestions.querySelectorAll('[role="option"]'));
        if (!options.length) return;
        suggestionIndex = (index + options.length) % options.length;
        options.forEach(function (option, optionIndex) {
            const selected = optionIndex === suggestionIndex;
            option.setAttribute('aria-selected', String(selected));
            if (selected) {
                search.setAttribute('aria-activedescendant', option.id);
                option.scrollIntoView({ block: 'nearest' });
            }
        });
    }

    function render() {
        const items = Object.values(cart);
        const subtotal = items.reduce((sum, item) => sum + item.price * item.quantity, 0);
        const discountValue = Math.min(subtotal, Math.max(0, Number(discount.value || 0)));
        const tax = (subtotal - discountValue) * taxRate / 100;
        const total = subtotal - discountValue + tax;
        document.getElementById('posCartItems').innerHTML = items.length ? items.map(item => '<div class="d-flex justify-content-between align-items-center border-bottom py-2"><div><div class="fw-semibold">' + item.name + '</div><small>' + money(item.price) + ' x ' + item.quantity + '</small></div><div class="btn-group btn-group-sm"><button type="button" class="btn btn-outline-secondary" aria-label="Decrease ' + item.name + ' quantity" data-minus="' + item.id + '">-</button><button type="button" class="btn btn-light" disabled>' + item.quantity + '</button><button type="button" class="btn btn-outline-secondary" aria-label="Increase ' + item.name + ' quantity" data-plus="' + item.id + '">+</button></div></div>').join('') : '<p class="text-muted text-center py-4">Select products to start.</p>';
        document.getElementById('posSubtotal').textContent = money(subtotal);
        document.getElementById('posTax').textContent = money(tax);
        document.getElementById('posTotal').textContent = money(total);
        document.getElementById('posChange').textContent = money(Math.max(0, Number(tendered.value || 0) - total));
        cartField.value = JSON.stringify(items.map(item => ({ id: item.id, quantity: item.quantity })));
    }
    root.querySelectorAll('.pos-product-card[data-product-id]').forEach(button => button.addEventListener('click', function () { openQuantityDialog(this, false); }));
    document.getElementById('posCartItems').addEventListener('click', function (event) { const button = event.target.closest('[data-plus], [data-minus]'); if (!button) return; const productId = button.dataset.plus || button.dataset.minus; if (!productId || !cart[productId]) return; if (button.dataset.plus && cart[productId].quantity < cart[productId].stock) cart[productId].quantity++; if (button.dataset.minus) cart[productId].quantity--; if (cart[productId].quantity <= 0) delete cart[productId]; render(); });
    root.querySelectorAll('.pos-order-type').forEach(button => button.addEventListener('click', function () { setOrderType(this.dataset.orderType); }));
    tableCards.forEach(function (card) {
        card.addEventListener('click', function () {
            setOrderType('dine_in');
            selectTable(card);
            if (tableDialog.open) tableDialog.close();
        });
    });
    root.querySelectorAll('[data-close-table-dialog]').forEach(function (button) {
        button.addEventListener('click', function () { tableDialog.close(); });
    });
    root.querySelector('[data-pos-clear]').addEventListener('click', function () { Object.keys(cart).forEach(id => delete cart[id]); render(); });
    search.addEventListener('input', renderSuggestions);
    search.addEventListener('keydown', function (event) {
        if (event.key !== 'Enter') return;

        event.preventDefault();
        event.stopPropagation();

        if (!suggestions.hidden) {
            const option = suggestions.querySelector('[aria-selected="true"]') ||
                suggestions.querySelector('[role="option"]');

            if (option) option.click();

            return;
        }

        const firstMatch = root.querySelector('.pos-product:not([hidden]) [data-product-id]');

        if (firstMatch && search.value.trim() !== '') openQuantityDialog(firstMatch, true);
    });
    suggestions.addEventListener('click', function (event) {
        const option = event.target.closest('[data-suggestion-index]');
        if (!option) return;
        const productButton = root.querySelector('.pos-product-card[data-product-id="' + option.dataset.productId + '"]');
        if (productButton) openQuantityDialog(productButton, true);
    });
    root.querySelector('[data-quantity-up]').addEventListener('click', function () {
        quantityInput.value = String(Math.min(Number(quantityInput.max), Number(quantityInput.value || 1) + 1));
    });
    root.querySelector('[data-quantity-down]').addEventListener('click', function () {
        quantityInput.value = String(Math.max(1, Number(quantityInput.value || 1) - 1));
    });
    root.querySelector('[data-quantity-confirm]').addEventListener('click', confirmQuantity);
    root.querySelector('[data-quantity-cancel]').addEventListener('click', function () {
        quantityDialog.close();
        pendingProductButton = null;
        if (returnToSearchAfterQuantity) search.focus();
        returnToSearchAfterQuantity = false;
    });
    quantityDialog.addEventListener('keydown', function (event) {
        if (event.key === 'ArrowUp' || event.key === 'ArrowDown') {
            event.preventDefault();
            event.stopPropagation();
            const direction = event.key === 'ArrowUp' ? 1 : -1;
            quantityInput.value = String(Math.min(Number(quantityInput.max), Math.max(1, Number(quantityInput.value || 1) + direction)));
        }
        if (event.key === 'Enter') {
            event.preventDefault();
            event.stopImmediatePropagation();
            confirmQuantity();
        }
    });
    quantityInput.addEventListener('keydown', function (event) {
        if (event.key === 'Enter') event.preventDefault();
    });
    quantityDialog.addEventListener('cancel', function () {
        pendingProductButton = null;
        if (returnToSearchAfterQuantity) search.focus();
        returnToSearchAfterQuantity = false;
    });
    discount.addEventListener('input', render); tendered.addEventListener('input', render);
    function exitFullscreenMode() {
        root.dataset.keepImmersive = 'false';
        root.classList.remove('pos-immersive');
        if (document.fullscreenElement) document.exitFullscreen();
    }

    function toggleFullscreen() {
        if (document.fullscreenElement || root.classList.contains('pos-immersive')) {
            exitFullscreenMode();
            return;
        }
        if (typeof root.requestFullscreen !== 'function') {
            root.dataset.keepImmersive = 'true';
            root.classList.add('pos-immersive');
            return;
        }
        root.dataset.keepImmersive = 'true';
        root.requestFullscreen().then(function () {
            root.classList.remove('pos-immersive');
        }).catch(function () { root.classList.add('pos-immersive'); });
    }
    root.querySelector('[data-pos-select-table]').addEventListener('click', openTablePicker);

    document.addEventListener('submit', function (event) {
        if (event.target.id !== 'posCheckoutForm') return;

        const items = Object.values(cart);
        if (!items.length) {
            event.preventDefault();
            event.stopImmediatePropagation();
            showCheckoutMessage('Add at least one product before checkout.', false);
            search.focus();
            return;
        }
        if (orderType.value === 'dine_in' && !table.value) {
            event.preventDefault();
            event.stopImmediatePropagation();
            showCheckoutMessage('Choose an available table for this dine-in order.', false);
            openTablePicker();
            return;
        }
    }, true);

    function showCheckoutMessage(message, success) {
        let notice = root.querySelector('[data-pos-checkout-notice]');
        if (!notice) {
            notice = document.createElement('div');
            notice.setAttribute('data-pos-checkout-notice', 'true');
            notice.setAttribute('role', success ? 'status' : 'alert');
            root.querySelector('#posCheckoutForm').before(notice);
        }
        notice.className = success ? 'alert alert-success' : 'alert alert-danger';
        notice.textContent = message;
        window.clearTimeout(notice.dismissTimer);
        notice.dismissTimer = window.setTimeout(function () { notice.remove(); }, 5000);
    }

    document.addEventListener('pos:checkout-error', function (event) {
        showCheckoutMessage(event.detail.message || 'Checkout failed. Please check the order and try again.', false);
    });

    document.addEventListener('pos:checkout-complete', function (event) {
        const usedTableId = table.value;

        if (orderType.value === 'dine_in' && usedTableId) {
            const usedCard = tableCards.find(card => card.dataset.tableId === usedTableId);
            if (usedCard) {
                usedCard.disabled = true;
                usedCard.dataset.tableAvailable = '0';
                usedCard.classList.remove('is-selected');
                usedCard.classList.add('text-muted');
                usedCard.setAttribute('aria-pressed', 'false');
                const statusText = usedCard.querySelector('.small');
                if (statusText) statusText.textContent = statusText.textContent.replace(/Available/i, 'Occupied');
            }
        }

        Object.keys(cart).forEach(id => delete cart[id]);
        render();
        tendered.value = '';
        selectTable(null);
        setOrderType('take_out');
        search.value = '';
        root.querySelectorAll('.pos-product').forEach(card => card.hidden = false);
        closeSuggestions();

        showCheckoutMessage(event.detail.message || 'Order completed successfully.', true);
        search.focus();
    });

    document.addEventListener('fullscreenchange', function () {
        if (document.fullscreenElement === root) {
            root.classList.remove('pos-immersive');
        } else if (root.dataset.keepImmersive === 'true' && root.isConnected) {
            root.classList.add('pos-immersive');
        }
    });

    function moveFocus(current, collection, key) {
        const items = collection.filter(function (item) {
            return !item.disabled && !item.closest('[hidden]');
        });
        if (!items.length) return;
        const currentRect = current && current.getBoundingClientRect();
        let next = null;
        if (currentRect) {
            const candidates = items.filter(item => item !== current).map(function (item) {
                const rect = item.getBoundingClientRect();
                const dx = rect.left + rect.width / 2 - (currentRect.left + currentRect.width / 2);
                const dy = rect.top + rect.height / 2 - (currentRect.top + currentRect.height / 2);
                const valid = key === 'ArrowLeft' ? dx < -4 : key === 'ArrowRight' ? dx > 4 : key === 'ArrowUp' ? dy < -4 : dy > 4;
                const primary = key === 'ArrowLeft' || key === 'ArrowRight' ? Math.abs(dx) : Math.abs(dy);
                const cross = key === 'ArrowLeft' || key === 'ArrowRight' ? Math.abs(dy) : Math.abs(dx);
                return { item: item, valid: valid, score: primary + cross * 2 };
            }).filter(item => item.valid).sort((first, second) => first.score - second.score);
            next = candidates[0] && candidates[0].item;
        }
        if (!next) {
            const currentIndex = Math.max(0, items.indexOf(current));
            const backwards = key === 'ArrowLeft' || key === 'ArrowUp';
            next = items[(currentIndex + (backwards ? items.length - 1 : 1)) % items.length];
        }
        next.focus();
        next.scrollIntoView({ block: 'nearest', inline: 'nearest' });
    }

    document.addEventListener('keydown', function (event) {
        if (!root.isConnected) return;

        const dialogIsOpen = tableDialog.open || quantityDialog.open;
        const posIsFullscreen = document.fullscreenElement === root || root.classList.contains('pos-immersive');
        const editingShortcut = (event.ctrlKey || event.metaKey)
            && ['a', 'c', 'v', 'x', 'z', 'y'].includes(event.key.toLowerCase());
        const browserModifierShortcut = (event.ctrlKey || event.metaKey) && !editingShortcut;
        const allowedPosAltShortcut = event.altKey
            && !event.ctrlKey
            && !event.metaKey
            && ['t', 'n', 'm', 'Alt'].includes(event.key);
        const browserAltShortcut = event.altKey && !allowedPosAltShortcut;

        if (/^F(?:[1-9]|1[0-2])$/.test(event.key)) {
            event.preventDefault();
        }

        if ((browserModifierShortcut || browserAltShortcut) && !dialogIsOpen) {
            event.preventDefault();
            event.stopPropagation();
        }

        if (event.key === 'Escape' && posIsFullscreen && !dialogIsOpen) {
            event.preventDefault();
        }
    }, true);

    document.addEventListener('keydown', function (event) {
        if (!root.isConnected) return;
        const active = document.activeElement;
        const isTable = active.classList && active.classList.contains('pos-table-card');
        const isProduct = active.hasAttribute && active.hasAttribute('data-product-id');

        if (tableDialog.open || quantityDialog.open) return;

        if (event.key === 'Escape' && (tableDialog.open || quantityDialog.open)) return;

        if (tableDialog.open || quantityDialog.open) return;

        if (event.altKey && !event.ctrlKey && !event.shiftKey) {
            const tableShortcuts = {
                t: () => {
                    openTablePicker();
                },
                n: () => root.querySelector('[data-add-table-link]').click(),
                m: () => root.querySelector('[data-manage-tables-link]').click()
            };
            const tableAction = tableShortcuts[event.key.toLowerCase()];
            if (tableAction) {
                event.preventDefault();
                tableAction();
                return;
            }
        }

        if (active === search && !suggestions.hidden) {
            if (event.key === 'ArrowDown' || event.key === 'ArrowUp') {
                event.preventDefault();
                focusSuggestion(suggestionIndex + (event.key === 'ArrowDown' ? 1 : -1));
                return;
            }
            if (event.key === 'Enter') {
                event.preventDefault();
                const selected = suggestions.querySelector('[aria-selected="true"]') || suggestions.querySelector('[role="option"]');
                if (selected) selected.click();
                return;
            }
            if (event.key === 'Escape') { closeSuggestions(); return; }
        }

        if ((isTable || isProduct) && event.key.startsWith('Arrow')) {
            event.preventDefault();
            moveFocus(active, isTable ? tableCards : availableProducts(), event.key);
            return;
        }
        if ((isTable || isProduct) && event.key === 'Enter') {
            event.preventDefault();
            active.click();
            return;
        }

        const actions = {
            F1: () => setOrderType('take_out'),
            F2: () => setOrderType('dine_in'),
            F3: () => search.focus(),
            F4: toggleFullscreen,
            F5: () => root.querySelector('[data-pos-clear]').click(),
            F6: () => payment.value = 'cash',
            F7: () => payment.value = 'card',
            F8: () => payment.value = 'gcash',
            F9: () => document.getElementById('posCheckoutForm').requestSubmit(),
            F10: () => discount.focus(),
            F11: exitFullscreenMode,
            F12: () => tendered.focus()
        };
        if (actions[event.key]) {
            event.preventDefault();
            actions[event.key]();
        }
    });

    root.addEventListener('click', function (event) {
        if (!event.target.closest('.pos-search-wrap')) closeSuggestions();
    });

    setOrderType('take_out');
    root.classList.add('pos-immersive');
    root.dataset.keepImmersive = 'true';
    root.querySelector('#posTables').hidden = true;
    if (typeof root.requestFullscreen === 'function') {
        root.requestFullscreen().then(function () {
            root.classList.remove('pos-immersive');
        }).catch(function () {
            root.classList.add('pos-immersive');
        });
    } else {
        root.classList.add('pos-immersive');
    }
    render();
});
</script>
