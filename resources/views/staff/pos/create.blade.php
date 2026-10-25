@extends('layouts.staff', ['title' => 'Venta en Tienda (TPV / POS)'])

@section('content')
<div class="pos-layout">
    <!-- LEFT PANEL: Product Catalog & Instant Search -->
    <div class="pos-catalog-panel">
        <div class="pos-top-controls">
            <div class="pos-store-bar">
                <div style="display: flex; align-items: center; gap: 0.65rem;">
                    <div style="width: 32px; height: 32px; border-radius: var(--radius-sm); background: var(--emerald-50); color: var(--emerald-700); display: grid; place-items: center;">
                        <x-icon name="store" size="18" />
                    </div>
                    <div>
                        <label for="pos-location-select" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--slate-500); display: block;">
                            Tienda de Venta / Caja
                        </label>
                        <select id="pos-location-select" style="font-weight: 700; font-size: 0.95rem; border: none; padding: 0.2rem 0; background: transparent; cursor: pointer; color: var(--slate-900);">
                            @foreach($storeLocations as $store)
                                <option value="{{ $store->id }}" {{ old('location_id') == $store->id ? 'selected' : '' }}>
                                    {{ $store->name }} — {{ $store->address }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex" style="gap: 0.6rem;">
                    <span class="badge badge-green" style="font-size: 0.75rem;">
                        <x-icon name="user" size="12" />
                        <span>Cajero: {{ auth()->user()->name }}</span>
                    </span>
                    <span class="badge badge-blue" id="pos-clock" style="font-size: 0.75rem; font-variant-numeric: tabular-nums;">
                        <x-icon name="clock" size="12" />
                        <span id="pos-clock-time">{{ date('H:i') }}</span>
                    </span>
                </div>
            </div>

            <!-- Search Bar -->
            <div class="pos-search-wrap">
                <div class="search-icon">
                    <x-icon name="search" size="18" />
                </div>
                <input 
                    type="search" 
                    id="pos-search-input" 
                    placeholder="Buscar producto por nombre o ref... (Presiona '/' para buscar)" 
                    autocomplete="off"
                    aria-label="Buscar productos para venta"
                >
            </div>

            <!-- Category Filter Tabs -->
            <div class="pos-category-tabs" role="tablist">
                <button type="button" class="pos-tab active" data-cat="all">
                    <span>Todos</span>
                    <span class="badge" style="background: rgba(255,255,255,0.25); color: inherit; padding: 0.1rem 0.4rem; font-size: 0.7rem;">{{ count($products) }}</span>
                </button>
                @foreach($categories as $category)
                    <button type="button" class="pos-tab" data-cat="{{ $category->id }}">
                        <span>{{ $category->name }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Product Grid -->
        <div class="pos-grid" id="pos-product-grid">
            @foreach($products as $product)
                @php
                    $firstStore = $storeLocations->first();
                    $avail = $firstStore ? ($stockLevels[$firstStore->id][$product->id] ?? 0.0) : 0.0;
                @endphp
                <div 
                    class="pos-product-card" 
                    data-id="{{ $product->id }}"
                    data-name="{{ $product->name }}"
                    data-price="{{ $product->price }}"
                    data-unit="{{ $product->sale_unit }}"
                    data-unit-label="{{ $product->unitLabel() }}"
                    data-category="{{ $product->category_id }}"
                    data-category-name="{{ $product->category->name ?? 'General' }}"
                    data-stocks='@json($stockLevels->map(fn($loc) => $loc[$product->id] ?? 0.0))'
                    title="Clic para añadir al ticket"
                    tabindex="0"
                >
                    <div class="pos-card-head">
                        <span class="pos-card-tag">{{ $product->category->name ?? 'General' }}</span>
                        @if($product->shelf_location)
                            <span class="badge" style="font-size: 0.65rem; padding: 0.1rem 0.35rem;">{{ $product->shelf_location }}</span>
                        @endif
                    </div>
                    
                    <div class="pos-card-title">{{ $product->name }}</div>
                    
                    <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-top: auto; padding-top: 0.4rem;">
                        <div>
                            <div class="pos-card-price">{{ eur($product->price) }}</div>
                            <div class="pos-card-unit">por {{ $product->sale_unit === 'kg' ? '1 kg' : 'unidad' }}</div>
                        </div>
                        
                        <div class="pos-stock-pill" id="stock-pill-{{ $product->id }}">
                            @if($avail > 0)
                                <span class="pos-stock-in">✓ {{ qty($avail, $product->sale_unit) }}</span>
                            @else
                                <span class="pos-stock-out">Sin stock</span>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Empty search results placeholder -->
        <div id="pos-empty-search" class="card empty" style="display: none;">
            <div class="empty-icon">
                <x-icon name="search" size="24" />
            </div>
            <h3>No se encontraron productos</h3>
            <p>Comprueba el término de búsqueda o selecciona otra categoría.</p>
            <button type="button" class="btn btn-secondary btn-sm" style="margin-top: 0.8rem;" id="pos-clear-search-btn">
                Limpiar búsqueda
            </button>
        </div>
    </div>

    <!-- RIGHT PANEL: Live Order / Ticket -->
    <div class="pos-ticket-panel">
        <div class="pos-ticket-header">
            <div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <x-icon name="receipt" size="18" />
                    <h2 style="font-size: 1.05rem; font-weight: 700; margin: 0;">Ticket en Curso</h2>
                </div>
                <div class="muted" style="font-size: 0.78rem;" id="pos-ticket-store-name">
                    {{ $storeLocations->first()->name ?? 'Tienda Física' }}
                </div>
            </div>
            <span class="badge badge-green" id="pos-item-count-badge">0 artículos</span>
        </div>

        <!-- Scrollable Line Items -->
        <div class="pos-ticket-items" id="pos-ticket-list">
            <!-- Empty ticket placeholder -->
            <div class="empty" id="pos-ticket-empty" style="padding: 3.5rem 1rem;">
                <div class="empty-icon">
                    <x-icon name="shopping-cart" size="24" />
                </div>
                <h3 style="font-size: 1rem;">El ticket está vacío</h3>
                <p style="font-size: 0.82rem;">Haz clic en cualquier producto del catálogo para añadirlo a la venta en caja.</p>
            </div>
        </div>

        <!-- Totals & Actions Footer -->
        <div class="pos-ticket-footer">
            <div class="pos-row">
                <span>Base Imponible estimada:</span>
                <span id="pos-tax-base" style="font-variant-numeric: tabular-nums;">0,00 €</span>
            </div>
            <div class="pos-row">
                <span>IVA estimado incluido:</span>
                <span id="pos-tax-iva" style="font-variant-numeric: tabular-nums;">0,00 €</span>
            </div>
            
            <div class="pos-total-row">
                <span style="font-size: 1rem; font-weight: 700; color: var(--slate-900);">Total a Cobrar:</span>
                <span class="pos-total-val" id="pos-total-display">0,00 €</span>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 2fr; gap: 0.6rem;">
                <button type="button" class="btn btn-secondary" id="pos-clear-btn" disabled style="font-size: 0.85rem;">
                    <x-icon name="trash" size="15" />
                    <span>Vaciar</span>
                </button>
                <button type="button" class="btn btn-primary btn-lg" id="pos-checkout-btn" disabled style="font-weight: 700;">
                    <x-icon name="credit-card" size="18" />
                    <span>Cobrar Pedido</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- CHECKOUT / PAYMENT MODAL -->
<div class="pos-modal-backdrop" id="pos-payment-modal" role="dialog" aria-modal="true" aria-labelledby="pos-modal-title">
    <div class="pos-modal">
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 1.25rem; border-bottom: 1px solid var(--border-light); padding-bottom: 0.75rem;">
            <div>
                <h2 id="pos-modal-title" style="font-size: 1.3rem; font-weight: 700; margin: 0;">Finalizar Cobro en Caja</h2>
                <div class="muted" style="font-size: 0.82rem;">Selecciona el método de pago e introduce el importe</div>
            </div>
            <button type="button" id="pos-modal-close" class="btn btn-ghost btn-sm" aria-label="Cerrar modal" style="padding: 0.3rem;">
                <x-icon name="x" size="20" />
            </button>
        </div>

        <div style="background: var(--slate-50); border: 1px solid var(--border-light); border-radius: var(--radius); padding: 1rem 1.25rem; margin-bottom: 1.25rem; display: flex; justify-content: space-between; align-items: baseline;">
            <span style="font-weight: 600; color: var(--slate-700);">Importe Total de la Venta:</span>
            <span style="font-size: 1.8rem; font-weight: 800; color: var(--emerald-800); font-family: var(--font-head);" id="pos-modal-total-display">0,00 €</span>
        </div>

        <form action="{{ route('staff.pos.store') }}" method="POST" id="pos-real-form" data-once>
            @csrf
            <input type="hidden" name="location_id" id="pos-form-location-id" value="{{ $storeLocations->first()->id ?? '' }}">
            <div id="pos-hidden-items-container"></div>

            <!-- Payment Method Radio Buttons -->
            <div style="margin-bottom: 1.25rem;">
                <label style="display: block; font-weight: 600; font-size: 0.85rem; color: var(--slate-700); margin-bottom: 0.5rem;">Método de Pago</label>
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 0.75rem;">
                    <label style="display: flex; align-items: center; gap: 0.6rem; padding: 0.75rem 1rem; border: 2px solid var(--border-light); border-radius: var(--radius); cursor: pointer; background: #fff;" id="pos-pay-card-label">
                        <input type="radio" name="payment_method" value="card" checked style="accent-color: var(--emerald-600);">
                        <div>
                            <strong style="display: block; font-size: 0.95rem;">Tarjeta / Datáfono</strong>
                            <small class="muted">Cobro con TPV bancario</small>
                        </div>
                    </label>

                    <label style="display: flex; align-items: center; gap: 0.6rem; padding: 0.75rem 1rem; border: 2px solid var(--border-light); border-radius: var(--radius); cursor: pointer; background: #fff;" id="pos-pay-cash-label">
                        <input type="radio" name="payment_method" value="cash" style="accent-color: var(--emerald-600);">
                        <div>
                            <strong style="display: block; font-size: 0.95rem;">Efectivo en Caja</strong>
                            <small class="muted">Calcular cambio exacto</small>
                        </div>
                    </label>
                </div>
            </div>

            <!-- Cash Tendered & Change Due Section -->
            <div id="pos-cash-calculator" style="display: none; background: #f8fafc; border: 1px dashed var(--slate-300); border-radius: var(--radius); padding: 1rem; margin-bottom: 1.25rem;">
                <div class="field" style="margin-bottom: 0.5rem;">
                    <label for="pos-tendered-input" style="font-weight: 600; font-size: 0.85rem;">Importe entregado por el cliente (€):</label>
                    <input type="number" step="0.01" min="0" id="pos-tendered-input" name="tendered_amount" placeholder="0.00" style="font-size: 1.1rem; font-weight: 700; height: 44px;">
                </div>

                <!-- Quick cash buttons -->
                <div class="pos-quick-cash-grid">
                    <button type="button" class="pos-cash-btn" data-amt="exact">Exacto</button>
                    <button type="button" class="pos-cash-btn" data-amt="10">10 €</button>
                    <button type="button" class="pos-cash-btn" data-amt="20">20 €</button>
                    <button type="button" class="pos-cash-btn" data-amt="50">50 €</button>
                </div>

                <div style="display: flex; justify-content: space-between; align-items: center; padding-top: 0.5rem; border-top: 1px solid var(--border-light);">
                    <span style="font-weight: 700; font-size: 0.95rem; color: var(--slate-700);">Cambio a Devolver:</span>
                    <span id="pos-change-display" style="font-size: 1.35rem; font-weight: 800; color: var(--emerald-700); font-variant-numeric: tabular-nums;">0,00 €</span>
                </div>
            </div>

            <div style="display: flex; gap: 0.75rem; margin-top: 1.5rem;">
                <button type="button" class="btn btn-secondary btn-lg" id="pos-modal-cancel" style="flex: 1;">
                    Volver al Ticket
                </button>
                <button type="submit" class="btn btn-primary btn-lg" id="pos-modal-confirm" style="flex: 2; font-weight: 700;">
                    <x-icon name="check" size="18" stroke="2.5" />
                    <span>Confirmar Venta y Ticket</span>
                </button>
            </div>
        </form>
    </div>
</div>

<!-- THERMAL RECEIPT CONTAINER (VISIBLE ONLY ON PRINT) -->
<div id="printable-receipt" style="display: none;">
    <div style="text-align: center; margin-bottom: 10px;">
        <h2 style="margin: 0; font-size: 16px;">LOCALMARKET BARCELONA</h2>
        <div style="font-size: 10px;">Supermercado de Proximidad</div>
        <div style="font-size: 10px;">Carrer del Mercat, 12 · 08001 Barcelona</div>
        <div style="font-size: 10px;">CIF: B-08123456 · Tel: 93 123 45 67</div>
        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
        <div style="font-size: 10px;">FECHA: {{ date('d/m/Y H:i:s') }}</div>
        <div style="font-size: 10px;">CAJERO: {{ auth()->user()->name }}</div>
        <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
    </div>
    <div id="print-items-list" style="margin-bottom: 10px; font-size: 11px;"></div>
    <div style="border-bottom: 1px dashed #000; margin: 8px 0;"></div>
    <div style="display: flex; justify-content: space-between; font-weight: bold; font-size: 14px;">
        <span>TOTAL COBRADO:</span>
        <span id="print-total">0,00 €</span>
    </div>
    <div style="text-align: center; margin-top: 15px; font-size: 10px;">
        <div>IVA Incluido en todos los precios</div>
        <div>¡Gracias por su compra en LocalMarket!</div>
    </div>
</div>

@push('scripts')
<script>
document.addEventListener('DOMContentLoaded', function () {
    // State
    const cart = new Map(); // productId -> { id, name, price, unit, unitLabel, quantity }
    let activeCategory = 'all';

    // DOM Elements
    const searchInput = document.getElementById('pos-search-input');
    const categoryTabs = document.querySelectorAll('.pos-tab');
    const productGrid = document.getElementById('pos-product-grid');
    const productCards = document.querySelectorAll('.pos-product-card');
    const emptySearch = document.getElementById('pos-empty-search');
    const clearSearchBtn = document.getElementById('pos-clear-search-btn');

    const locationSelect = document.getElementById('pos-location-select');
    const ticketStoreName = document.getElementById('pos-ticket-store-name');
    const ticketList = document.getElementById('pos-ticket-list');
    const ticketEmpty = document.getElementById('pos-ticket-empty');
    const itemCountBadge = document.getElementById('pos-item-count-badge');
    const taxBaseDisplay = document.getElementById('pos-tax-base');
    const taxIvaDisplay = document.getElementById('pos-tax-iva');
    const totalDisplay = document.getElementById('pos-total-display');
    const clearBtn = document.getElementById('pos-clear-btn');
    const checkoutBtn = document.getElementById('pos-checkout-btn');

    const paymentModal = document.getElementById('pos-payment-modal');
    const modalCloseBtn = document.getElementById('pos-modal-close');
    const modalCancelBtn = document.getElementById('pos-modal-cancel');
    const modalTotalDisplay = document.getElementById('pos-modal-total-display');
    const realForm = document.getElementById('pos-real-form');
    const hiddenItemsContainer = document.getElementById('pos-hidden-items-container');
    const formLocationId = document.getElementById('pos-form-location-id');

    const payMethodRadios = document.querySelectorAll('input[name="payment_method"]');
    const cashCalc = document.getElementById('pos-cash-calculator');
    const tenderedInput = document.getElementById('pos-tendered-input');
    const changeDisplay = document.getElementById('pos-change-display');
    const quickCashBtns = document.querySelectorAll('.pos-cash-btn');

    // Helper: format currency
    function formatEur(val) {
        return new Intl.NumberFormat('es-ES', { style: 'currency', currency: 'EUR' }).format(val);
    }

    // Update real-time clock
    function updateClock() {
        const now = new Date();
        const timeStr = String(now.getHours()).padStart(2, '0') + ':' + String(now.getMinutes()).padStart(2, '0');
        const el = document.getElementById('pos-clock-time');
        if (el) el.textContent = timeStr;
    }
    setInterval(updateClock, 10000);

    // Sync Store Location & Stock Pills
    function syncLocation() {
        const locId = locationSelect.value;
        formLocationId.value = locId;
        const selectedOption = locationSelect.options[locationSelect.selectedIndex];
        if (ticketStoreName && selectedOption) {
            ticketStoreName.textContent = selectedOption.textContent.trim();
        }

        productCards.forEach(card => {
            const pid = card.dataset.id;
            const unit = card.dataset.unit;
            const pill = document.getElementById('stock-pill-' + pid);
            if (pill && card.dataset.stocks) {
                try {
                    const stocks = JSON.parse(card.dataset.stocks);
                    const qtyAvail = parseFloat(stocks[locId] || 0.0);
                    if (qtyAvail > 0) {
                        const unitTxt = unit === 'kg' ? ' kg' : ' ud.';
                        pill.innerHTML = `<span class="pos-stock-in">✓ ${qtyAvail.toLocaleString('es-ES')}${unitTxt}</span>`;
                    } else {
                        pill.innerHTML = `<span class="pos-stock-out">Sin stock</span>`;
                    }
                } catch(e) {}
            }
        });
    }
    locationSelect.addEventListener('change', syncLocation);
    syncLocation();

    // Instant Filter & Search
    function filterProducts() {
        const query = searchInput.value.trim().toLowerCase();
        let visibleCount = 0;

        productCards.forEach(card => {
            const name = card.dataset.name.toLowerCase();
            const cat = card.dataset.category;
            const matchesQuery = !query || name.includes(query);
            const matchesCat = activeCategory === 'all' || cat === activeCategory;

            if (matchesQuery && matchesCat) {
                card.style.display = 'flex';
                visibleCount++;
            } else {
                card.style.display = 'none';
            }
        });

        if (visibleCount === 0) {
            emptySearch.style.display = 'block';
        } else {
            emptySearch.style.display = 'none';
        }
    }

    searchInput.addEventListener('input', filterProducts);
    clearSearchBtn.addEventListener('click', function () {
        searchInput.value = '';
        filterProducts();
        searchInput.focus();
    });

    categoryTabs.forEach(tab => {
        tab.addEventListener('click', function () {
            categoryTabs.forEach(t => t.classList.remove('active'));
            tab.classList.add('active');
            activeCategory = tab.dataset.cat;
            filterProducts();
        });
    });

    // Add to Cart on Tile Click
    productCards.forEach(card => {
        card.addEventListener('click', function () {
            const id = parseInt(card.dataset.id);
            const name = card.dataset.name;
            const price = parseFloat(card.dataset.price);
            const unit = card.dataset.unit;
            const unitLabel = card.dataset.unitLabel;

            const step = unit === 'kg' ? 0.5 : 1.0;

            if (cart.has(id)) {
                const item = cart.get(id);
                item.quantity = parseFloat((item.quantity + step).toFixed(3));
            } else {
                cart.set(id, {
                    id: id,
                    name: name,
                    price: price,
                    unit: unit,
                    unitLabel: unitLabel,
                    quantity: step
                });
            }

            renderCart();
            // Subtle click vibration/feedback
            card.style.transform = 'scale(0.96)';
            setTimeout(() => card.style.transform = '', 120);
        });
    });

    // Render Ticket
    function renderCart() {
        if (cart.size === 0) {
            ticketEmpty.style.display = 'block';
            ticketList.querySelectorAll('.pos-line-item').forEach(el => el.remove());
            itemCountBadge.textContent = '0 artículos';
            taxBaseDisplay.textContent = '0,00 €';
            taxIvaDisplay.textContent = '0,00 €';
            totalDisplay.textContent = '0,00 €';
            clearBtn.disabled = true;
            checkoutBtn.disabled = true;
            return;
        }

        ticketEmpty.style.display = 'none';
        ticketList.querySelectorAll('.pos-line-item').forEach(el => el.remove());

        let total = 0.0;
        let totalItems = 0;

        cart.forEach((item, id) => {
            const lineTotal = item.price * item.quantity;
            total += lineTotal;
            totalItems += (item.unit === 'kg' ? 1 : item.quantity);

            const row = document.createElement('div');
            row.className = 'pos-line-item';
            row.innerHTML = `
                <div class="pos-item-meta">
                    <span class="pos-item-name">${item.name}</span>
                    <span class="pos-item-unit">${formatEur(item.price)} / ${item.unitLabel}</span>
                </div>
                <div class="pos-item-qty-control">
                    <button type="button" class="pos-qty-btn pos-btn-dec" data-id="${id}" aria-label="Disminuir cantidad">&minus;</button>
                    <span class="pos-qty-val">${item.unit === 'kg' ? item.quantity.toFixed(2) : item.quantity}</span>
                    <button type="button" class="pos-qty-btn pos-btn-inc" data-id="${id}" aria-label="Aumentar cantidad">&plus;</button>
                </div>
                <div style="display: flex; align-items: center; gap: 0.4rem;">
                    <span class="pos-item-total">${formatEur(lineTotal)}</span>
                    <button type="button" class="pos-item-del" data-id="${id}" title="Eliminar del ticket">
                        <svg width="14" height="14" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M18 6 6 18M6 6l12 12"/></svg>
                    </button>
                </div>
            `;

            ticketList.appendChild(row);
        });

        // Totals calculation (assuming standard 10% Spanish average grocery VAT for estimate display)
        const ivaEstimate = total * (10 / 110);
        const baseEstimate = total - ivaEstimate;

        taxBaseDisplay.textContent = formatEur(baseEstimate);
        taxIvaDisplay.textContent = formatEur(ivaEstimate);
        totalDisplay.textContent = formatEur(total);
        itemCountBadge.textContent = `${cart.size} líneas (${totalItems} ud.)`;

        clearBtn.disabled = false;
        checkoutBtn.disabled = false;

        // Attach listeners for line item controls
        ticketList.querySelectorAll('.pos-btn-inc').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = parseInt(btn.dataset.id);
                const item = cart.get(id);
                const step = item.unit === 'kg' ? 0.25 : 1.0;
                item.quantity = parseFloat((item.quantity + step).toFixed(3));
                renderCart();
            });
        });

        ticketList.querySelectorAll('.pos-btn-dec').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = parseInt(btn.dataset.id);
                const item = cart.get(id);
                const step = item.unit === 'kg' ? 0.25 : 1.0;
                if (item.quantity - step <= 0.001) {
                    cart.delete(id);
                } else {
                    item.quantity = parseFloat((item.quantity - step).toFixed(3));
                }
                renderCart();
            });
        });

        ticketList.querySelectorAll('.pos-item-del').forEach(btn => {
            btn.addEventListener('click', function () {
                const id = parseInt(btn.dataset.id);
                cart.delete(id);
                renderCart();
            });
        });
    }

    // Clear Ticket
    clearBtn.addEventListener('click', function () {
        if (confirm('¿Vaciar todos los artículos del ticket actual?')) {
            cart.clear();
            renderCart();
        }
    });

    // Open Payment Modal
    function openCheckoutModal() {
        if (cart.size === 0) return;

        let total = 0.0;
        hiddenItemsContainer.innerHTML = '';

        let i = 0;
        cart.forEach((item) => {
            total += (item.price * item.quantity);
            // Append hidden fields for multi-item submission
            hiddenItemsContainer.innerHTML += `
                <input type="hidden" name="items[${i}][product_id]" value="${item.id}">
                <input type="hidden" name="items[${i}][quantity]" value="${item.quantity}">
            `;
            i++;
        });

        modalTotalDisplay.textContent = formatEur(total);
        tenderedInput.value = '';
        changeDisplay.textContent = '0,00 €';
        paymentModal.classList.add('open');
    }

    checkoutBtn.addEventListener('click', openCheckoutModal);

    function closeCheckoutModal() {
        paymentModal.classList.remove('open');
    }
    modalCloseBtn.addEventListener('click', closeCheckoutModal);
    modalCancelBtn.addEventListener('click', closeCheckoutModal);

    // Toggle Cash Calculator
    payMethodRadios.forEach(radio => {
        radio.addEventListener('change', function () {
            if (this.value === 'cash' && this.checked) {
                cashCalc.style.display = 'block';
                tenderedInput.focus();
            } else {
                cashCalc.style.display = 'none';
            }
        });
    });

    // Calculate Cash Change
    function calculateChange() {
        let total = 0.0;
        cart.forEach(item => total += (item.price * item.quantity));

        const tendered = parseFloat(tenderedInput.value) || 0.0;
        const change = Math.max(0.0, tendered - total);
        changeDisplay.textContent = formatEur(change);

        if (tendered < total && tendered > 0) {
            changeDisplay.style.color = 'var(--red-600)';
            changeDisplay.textContent = 'Falta: ' + formatEur(total - tendered);
        } else {
            changeDisplay.style.color = 'var(--emerald-700)';
        }
    }
    tenderedInput.addEventListener('input', calculateChange);

    quickCashBtns.forEach(btn => {
        btn.addEventListener('click', function () {
            let total = 0.0;
            cart.forEach(item => total += (item.price * item.quantity));

            const amt = btn.dataset.amt;
            if (amt === 'exact') {
                tenderedInput.value = total.toFixed(2);
            } else {
                tenderedInput.value = parseFloat(amt).toFixed(2);
            }
            calculateChange();
        });
    });

    // Keyboard Shortcuts
    document.addEventListener('keydown', function (e) {
        // Press '/' to search
        if (e.key === '/' && document.activeElement !== searchInput) {
            e.preventDefault();
            searchInput.focus();
            searchInput.select();
        }
        // Escape closes modal or clears search
        if (e.key === 'Escape') {
            if (paymentModal.classList.contains('open')) {
                closeCheckoutModal();
            } else if (searchInput.value) {
                searchInput.value = '';
                filterProducts();
            }
        }
        // F2 or Ctrl+Enter to checkout
        if ((e.key === 'F2' || (e.ctrlKey && e.key === 'Enter')) && cart.size > 0 && !paymentModal.classList.contains('open')) {
            e.preventDefault();
            openCheckoutModal();
        }
    });
});
</script>
@endpush
@endsection
