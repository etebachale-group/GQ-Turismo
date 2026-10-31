/**
 * GQ-Turismo: Travel Experience Interactive Engine
 * Proporciona micro-interacciones de alta gama para la plataforma:
 * - Conversor de divisa dinámico (FCFA / EUR / USD)
 * - Gestor de Favoritos / Wishlist con persistencia en localStorage
 * - Widget interactivo de reserva y cálculo de cotización en vivo
 * - Selector interactivo de viajeros para la cápsula de búsqueda
 */

(function () {
    'use strict';

    // 1. GESTOR DE DIVISAS
    const RATES = {
        XAF: 1,
        EUR: 1 / 655.957,
        USD: 1 / 610.0
    };

    const SYMBOLS = {
        XAF: 'FCFA',
        EUR: '€',
        USD: '$'
    };

    function getCurrentCurrency() {
        return localStorage.getItem('gq_currency') || 'XAF';
    }

    function setCurrency(currency) {
        if (!RATES[currency]) return;
        localStorage.setItem('gq_currency', currency);
        document.querySelectorAll('[data-currency-target]').forEach(el => {
            const baseAmount = parseFloat(el.getAttribute('data-base-xaf') || '0');
            if (baseAmount > 0) {
                if (currency === 'XAF') {
                    el.textContent = Math.round(baseAmount).toLocaleString('es-GQ') + ' ' + SYMBOLS.XAF;
                } else if (currency === 'EUR') {
                    el.textContent = (baseAmount * RATES.EUR).toFixed(1) + ' ' + SYMBOLS.EUR;
                } else if (currency === 'USD') {
                    el.textContent = (baseAmount * RATES.USD).toFixed(1) + ' ' + SYMBOLS.USD;
                }
            }
        });

        // Actualizar botones de selector
        document.querySelectorAll('.currency-toggle-btn').forEach(btn => {
            if (btn.getAttribute('data-currency') === currency) {
                btn.classList.add('active');
            } else {
                btn.classList.remove('active');
            }
        });
    }

    // 2. GESTOR DE FAVORITOS / WISHLIST
    function getWishlist() {
        try {
            return JSON.parse(localStorage.getItem('gq_wishlist') || '[]');
        } catch (e) {
            return [];
        }
    }

    function toggleWishlist(destinationId, buttonEl) {
        let wishlist = getWishlist();
        const idStr = String(destinationId);
        const idx = wishlist.indexOf(idStr);
        let added = false;

        if (idx > -1) {
            wishlist.splice(idx, 1);
        } else {
            wishlist.push(idStr);
            added = true;
        }

        localStorage.setItem('gq_wishlist', JSON.stringify(wishlist));
        updateWishlistButton(buttonEl, added);
        updateWishlistBadge();

        // Feedback toast ligero
        showToast(added ? 'Guardado en tu lista de deseos' : 'Eliminado de tu lista de deseos');
    }

    function updateWishlistButton(buttonEl, isSaved) {
        if (!buttonEl) return;
        const icon = buttonEl.querySelector('i');
        if (icon) {
            if (isSaved) {
                icon.className = 'bi bi-heart-fill text-danger';
                buttonEl.classList.add('is-favorited');
            } else {
                icon.className = 'bi bi-heart';
                buttonEl.classList.remove('is-favorited');
            }
        }
    }

    function updateWishlistBadge() {
        const wishlist = getWishlist();
        const badges = document.querySelectorAll('.wishlist-counter-badge');
        badges.forEach(b => {
            b.textContent = wishlist.length;
            if (wishlist.length > 0) {
                b.classList.remove('d-none');
            } else {
                b.classList.add('d-none');
            }
        });
    }

    // 3. TOAST NOTIFICACIÓN LIGERA
    function showToast(message) {
        let toast = document.getElementById('gqModernToast');
        if (!toast) {
            toast = document.createElement('div');
            toast.id = 'gqModernToast';
            toast.className = 'gq-modern-toast';
            document.body.appendChild(toast);
        }
        toast.textContent = message;
        toast.classList.add('show');
        setTimeout(() => {
            toast.classList.remove('show');
        }, 2600);
    }

    // 4. WIDGET DE RESERVA INTERACTIVO (Detalle de Destino)
    function initBookingCalculator() {
        const widget = document.getElementById('gqBookingWidget');
        if (!widget) return;

        const basePrice = parseFloat(widget.getAttribute('data-base-price') || '0');
        const travelersInput = widget.querySelector('#bookingTravelers');
        const dateInput = widget.querySelector('#bookingDate');
        const totalPriceEl = widget.querySelector('#bookingTotalPrice');
        const totalEurEl = widget.querySelector('#bookingTotalEur');
        const btnMinus = widget.querySelector('#btnMinusTraveler');
        const btnPlus = widget.querySelector('#btnPlusTraveler');
        const submitBtn = widget.querySelector('#btnProceedBooking');

        function recalculate() {
            const count = parseInt(travelersInput ? travelersInput.value : '1') || 1;
            const totalXaf = basePrice * count;
            const totalEur = Math.round(totalXaf / 655.957);

            if (totalPriceEl) {
                totalPriceEl.textContent = totalXaf.toLocaleString('es-GQ') + ' FCFA';
            }
            if (totalEurEl) {
                totalEurEl.textContent = '≈ ' + totalEur + ' € EUR';
            }

            // Actualizar URL del botón de confirmación
            if (submitBtn) {
                const destinoId = widget.getAttribute('data-destination-id');
                const fecha = dateInput ? encodeURIComponent(dateInput.value) : '';
                submitBtn.href = `reservas.php?destino=${destinoId}&personas=${count}${fecha ? '&fecha=' + fecha : ''}`;
            }

            // Actualizar floating mobile bar si existe
            const mobileTotal = document.getElementById('mobileBarPrice');
            if (mobileTotal) {
                mobileTotal.textContent = totalXaf.toLocaleString('es-GQ') + ' FCFA';
            }
        }

        if (btnMinus && travelersInput) {
            btnMinus.addEventListener('click', () => {
                let val = parseInt(travelersInput.value) || 1;
                if (val > 1) {
                    travelersInput.value = val - 1;
                    recalculate();
                }
            });
        }

        if (btnPlus && travelersInput) {
            btnPlus.addEventListener('click', () => {
                let val = parseInt(travelersInput.value) || 1;
                if (val < 20) {
                    travelersInput.value = val + 1;
                    recalculate();
                }
            });
        }

        if (travelersInput) {
            travelersInput.addEventListener('change', recalculate);
        }
        if (dateInput) {
            dateInput.addEventListener('change', recalculate);
        }

        recalculate();
    }

    // 5. SELECTOR DE VIAJEROS POPUP (Search Capsule)
    function initSearchTravelers() {
        const toggleBtn = document.getElementById('searchTravelersToggle');
        const popover = document.getElementById('searchTravelersPopover');
        const inputField = document.getElementById('searchTravelersInput');
        const summaryText = document.getElementById('searchTravelersSummary');

        if (!toggleBtn || !popover) return;

        toggleBtn.addEventListener('click', (e) => {
            e.stopPropagation();
            popover.classList.toggle('d-none');
        });

        document.addEventListener('click', (e) => {
            if (!popover.contains(e.target) && e.target !== toggleBtn) {
                popover.classList.add('d-none');
            }
        });

        const minusBtn = popover.querySelector('.popover-minus');
        const plusBtn = popover.querySelector('.popover-plus');
        const countDisplay = popover.querySelector('.popover-count');

        let travelers = 1;

        if (minusBtn) {
            minusBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (travelers > 1) {
                    travelers--;
                    updateDisplay();
                }
            });
        }

        if (plusBtn) {
            plusBtn.addEventListener('click', (e) => {
                e.stopPropagation();
                if (travelers < 16) {
                    travelers++;
                    updateDisplay();
                }
            });
        }

        function updateDisplay() {
            if (countDisplay) countDisplay.textContent = travelers;
            if (inputField) inputField.value = travelers;
            if (summaryText) {
                summaryText.textContent = travelers === 1 ? '1 Viajero' : `${travelers} Viajeros`;
            }
        }
    }

    // INICIALIZACIÓN AL CARGAR DOM
    document.addEventListener('DOMContentLoaded', () => {
        // Inicializar wishlist en botones existentes
        const wishlist = getWishlist();
        document.querySelectorAll('.btn-wishlist').forEach(btn => {
            const destId = btn.getAttribute('data-destination-id');
            if (destId && wishlist.includes(String(destId))) {
                updateWishlistButton(btn, true);
            }
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                e.stopPropagation();
                toggleWishlist(destId, btn);
            });
        });

        updateWishlistBadge();

        // Inicializar selectores de divisa
        document.querySelectorAll('.currency-toggle-btn').forEach(btn => {
            btn.addEventListener('click', (e) => {
                e.preventDefault();
                const cur = btn.getAttribute('data-currency');
                setCurrency(cur);
            });
        });

        // Aplicar divisa guardada si existe
        const cur = getCurrentCurrency();
        if (cur !== 'XAF') {
            setCurrency(cur);
        }

        initBookingCalculator();
        initSearchTravelers();
    });

    // Exponer API global ligera
    window.GQTravel = {
        setCurrency,
        toggleWishlist,
        getWishlist
    };
})();
