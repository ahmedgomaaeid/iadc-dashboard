// Invoice Form JavaScript

let productIndex = 0;
const productsContainer = document.getElementById('productsContainer');
const emptyState = document.getElementById('emptyState');
const addProductBtn = document.getElementById('addProductBtn');
const paymentStatusRadios = document.querySelectorAll('input[name="payment_status"]');
const amountPaidSection = document.getElementById('amountPaidSection');
const summaryCard = document.getElementById('summaryCard');
const clientSelect = document.getElementById('client_id');
const clientBalanceSection = document.getElementById('clientBalanceSection');
const useClientBalanceCheckbox = document.getElementById('useClientBalance');
let currentClientBalance = 0;
let productOptionsCache = ''; // Cache for product options HTML

// Debounce function for performance
function debounce(func, wait) {
    let timeout;
    return function executedFunction(...args) {
        const later = () => {
            clearTimeout(timeout);
            func(...args);
        };
        clearTimeout(timeout);
        timeout = setTimeout(later, wait);
    };
}

// Debug log
console.log('Invoice Form Initialized:', {
    productsContainer: !!productsContainer,
    clientSelect: !!clientSelect,
    clientBalanceSection: !!clientBalanceSection,
    useClientBalanceCheckbox: !!useClientBalanceCheckbox
});

// Initialize
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM Content Loaded');
    initializeSelect2();
    checkEmptyState();
    setupEventListeners();
    setupKeyboardShortcuts();
});

// Setup Keyboard Shortcuts for better UX
function setupKeyboardShortcuts() {
    document.addEventListener('keydown', function(e) {
        // Ctrl/Cmd + Enter to submit form
        if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
            e.preventDefault();
            const submitBtn = document.querySelector('.btn-submit');
            if (submitBtn) submitBtn.click();
        }

        // Ctrl/Cmd + B to add new product
        if ((e.ctrlKey || e.metaKey) && e.key === 'b') {
            e.preventDefault();
            addProductBtn.click();
        }
    });
}

function initializeSelect2() {
    // Initialize Select2 for client select
    if ($('#client_id').length) {
        // Destroy existing Select2 instance if it exists
        if ($('#client_id').hasClass('select2-hidden-accessible')) {
            $('#client_id').select2('destroy');
        }

        $('#client_id').select2({
            placeholder: '-- اختر عميل --',
            allowClear: true,
            dir: 'rtl',
            width: '100%',
            language: {
                noResults: function() {
                    return "لا توجد نتائج";
                },
                searching: function() {
                    return "جاري البحث...";
                }
            }
        });

        // Auto-focus search box when dropdown opens
        $('#client_id').on('select2:open', function() {
            setTimeout(function() {
                document.querySelector('.select2-search__field').focus();
            }, 100);
        });
    }
}

function setupEventListeners() {
    console.log('Setting up event listeners...');

    // Client Selection Handler - Fetch Balance (using jQuery for Select2 compatibility)
    if (clientSelect) {
        $('#client_id').on('change', function() {
            console.log('Client selected, ID:', this.value);
            const clientId = this.value;

            if (clientId) {
                fetchClientBalance(clientId);

                // Update prices for all selected products with special offers
                const productRows = document.querySelectorAll('.product-row:not(.empty-state)');
                productRows.forEach(row => {
                    const productSelect = row.querySelector('.product-select');
                    const index = productSelect.dataset.index;

                    // Remove old special offer badge if exists
                    const oldBadge = row.querySelector('.special-offer-badge');
                    if (oldBadge) {
                        oldBadge.remove();
                    }
                    const productLabel = row.querySelector('.product-field-label');
                    if (productLabel) {
                        productLabel.classList.remove('with-offer');
                    }

                    // Reset to original price
                    const selectedOption = productSelect.options[productSelect.selectedIndex];
                    if (selectedOption.dataset.originalPrice) {
                        selectedOption.dataset.price = selectedOption.dataset.originalPrice;
                    }

                    // Fetch new price with special offer if product is selected
                    if (productSelect.value) {
                        fetchProductDetails(productSelect.value, clientId, index);
                    }
                });
            } else {
                clientBalanceSection.style.display = 'none';
                if (useClientBalanceCheckbox) {
                    useClientBalanceCheckbox.checked = false;
                }
                currentClientBalance = 0;
                updateSummary();

                // Reset all product prices to original
                const productRows = document.querySelectorAll('.product-row:not(.empty-state)');
                productRows.forEach(row => {
                    const productSelect = row.querySelector('.product-select');
                    const index = productSelect.dataset.index;

                    // Remove special offer badge
                    const badge = row.querySelector('.special-offer-badge');
                    if (badge) {
                        badge.remove();
                    }
                    const productLabel = row.querySelector('.product-field-label');
                    if (productLabel) {
                        productLabel.classList.remove('with-offer');
                    }

                    // Reset to original price
                    const selectedOption = productSelect.options[productSelect.selectedIndex];
                    if (selectedOption.dataset.originalPrice) {
                        selectedOption.dataset.price = selectedOption.dataset.originalPrice;
                    }

                    updateProductPrice(index);
                });
            }
        });
    }

    // Client Balance Checkbox Handler
    if (useClientBalanceCheckbox) {
        useClientBalanceCheckbox.addEventListener('change', function() {
            console.log('Balance checkbox changed:', this.checked);
            updateClientBalanceDisplay();
            updateSummary();
            generatePriceSummaryTable();
        });
    }

    // Payment Status Handler
    paymentStatusRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            const statusCards = document.querySelectorAll('.status-card');
            statusCards.forEach(card => card.classList.remove('active'));
            this.closest('.status-card').classList.add('active');

            if (this.value === 'partially_paid') {
                amountPaidSection.classList.add('show');
            } else {
                amountPaidSection.classList.remove('show');
                document.querySelector('input[name="paid_amount"]').value = '';
                updateSummary();
                generatePriceSummaryTable();
            }
        });
    });

    // Paid Amount Input Handler - Update summary when amount changes
    const paidAmountInput = document.querySelector('input[name="paid_amount"]');
    if (paidAmountInput) {
        paidAmountInput.addEventListener('input', function() {
            // Clear error state
            this.style.borderColor = '';
            updateClientBalanceDisplay();
            updateSummary();
            generatePriceSummaryTable();
        });
    }

    // Add Product Button
    addProductBtn.addEventListener('click', function() {
        addProductRow();
    });

    // Form Submission
    document.getElementById('invoiceForm').addEventListener('submit', function(e) {
        handleFormSubmit(e);
    });
}

// Fetch Client Balance from API
function fetchClientBalance(clientId) {
    fetch(`/clients/${clientId}/get-client-balance`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(data => {
            console.log('Balance data received:', data);
            if (data.success || data.balance !== undefined) {
                currentClientBalance = parseFloat(data.balance) || 0;
                if(currentClientBalance > 0) {
                    document.getElementById('clientBalanceAmount').textContent =
                        currentClientBalance.toFixed(2) + ' ريال';
                        clientBalanceSection.style.display = 'block';
                        updateClientBalanceDisplay();
                        updateSummary();
                        console.log('Balance fetched successfully:', currentClientBalance);
                } else {
                    clientBalanceSection.style.display = 'none';
                    useClientBalanceCheckbox.checked = false;
                    currentClientBalance = 0;
                    updateSummary();
                    console.log('Client has zero balance.');
                }
            } else {
                console.error('Failed to fetch balance:', data.message);
                clientBalanceSection.style.display = 'none';
            }
        })
        .catch(error => {
            console.error('Error fetching client balance:', error);
            clientBalanceSection.style.display = 'none';
            useClientBalanceCheckbox.checked = false;
        });
}

// Update Client Balance Display
function updateClientBalanceDisplay() {
    const balanceAfterSection = document.getElementById('balanceAfterSection');
    const balanceAfterElement = document.getElementById('clientBalanceAfter');

    let showBalanceAfter = false;
    let balanceToDeduct = 0;

    // Check if using balance checkbox
    if (useClientBalanceCheckbox && useClientBalanceCheckbox.checked && currentClientBalance > 0) {
        const originalTotal = parseFloat(document.getElementById('subtotal').textContent) || 0;
        const discountTotal = parseFloat(document.getElementById('discountTotal').textContent) || 0;
        const totalAfterDiscount = originalTotal - discountTotal;
        balanceToDeduct = Math.min(currentClientBalance, totalAfterDiscount);
        showBalanceAfter = true;
    }

    // Check if partially paid is selected
    const paymentStatus = document.querySelector('input[name="payment_status"]:checked');
    const paidAmountInput = document.querySelector('input[name="paid_amount"]');

    if (paymentStatus && paymentStatus.value === 'partially_paid' && paidAmountInput && paidAmountInput.value) {
        const paidAmount = parseFloat(paidAmountInput.value) || 0;
        if (paidAmount > 0) {
            balanceToDeduct = paidAmount;
            showBalanceAfter = true;
        }
    }

    if (showBalanceAfter && currentClientBalance > 0) {
        // Show balance after deduction
        const balanceAfter = Math.max(0, currentClientBalance - balanceToDeduct);
        balanceAfterElement.textContent = balanceAfter.toFixed(2) + ' ريال';
        balanceAfterSection.style.display = 'block';
    } else {
        balanceAfterSection.style.display = 'none';
    }
}

// Fetch Product Details with Special Offer
function fetchProductDetails(productId, clientId, index) {
    // Add loading state
    const productRow = document.getElementById(`product-${index}`);
    const priceDisplay = productRow.querySelector('.price-display');
    const originalPlaceholder = priceDisplay.placeholder;
    priceDisplay.placeholder = 'جاري التحميل...';

    fetch(`/invoices/${productId}/product-details?client_id=${clientId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            return response.json();
        })
        .then(product => {
            console.log('Product details with special offer:', product);

            // Update the product select option with the new price
            const productSelect = document.querySelector(`.product-select[data-index="${index}"]`);
            const selectedOption = productSelect.options[productSelect.selectedIndex];

            // Store original price if not already stored
            if (!selectedOption.dataset.originalPrice) {
                selectedOption.dataset.originalPrice = selectedOption.dataset.price;
            }

            // Update the price with special offer applied
            selectedOption.dataset.price = product.price;

            // Add visual indicator if special offer is applied
            const originalPrice = parseFloat(selectedOption.dataset.originalPrice);
            if (product.price < originalPrice) {
                // Add special offer badge next to the product label
                const productLabel = productRow.querySelector('.product-field-label');
                let badge = productRow.querySelector('.special-offer-badge');

                if (!badge && productLabel) {
                    badge = document.createElement('span');
                    badge.className = 'special-offer-badge';
                    badge.innerHTML = '<i class="fe fe-tag"></i> عرض خاص';
                    productLabel.classList.add('with-offer');
                    productLabel.appendChild(badge);
                }
            }

            // Reset placeholder and update the price calculation
            priceDisplay.placeholder = originalPlaceholder;
            updateProductPrice(index);
            generatePriceSummaryTable();
        })
        .catch(error => {
            console.error('Error fetching product details:', error);
            // Reset placeholder and fallback to regular price update
            priceDisplay.placeholder = originalPlaceholder;
            updateProductPrice(index);
            generatePriceSummaryTable();
        });
}

// Add Product Row
function addProductRow(productId = '', quantity = '1', discount = '', discountType = 'fixed') {
    emptyState.style.display = 'none';

    const productRow = document.createElement('div');
    productRow.className = 'product-row';
    productRow.id = `product-${productIndex}`;

    productRow.innerHTML = `
        <div class="product-field-group">
            <div>
                <label class="product-field-label">المنتج</label>
                <select class="product-field-input product-select form-control form-select select2-show-search" data-index="${productIndex}" required>
                    <option value="">-- اختر منتج --</option>
                    ${getProductOptions()}
                </select>
            </div>
            <div>
                <label class="product-field-label">الكمية</label>
                <input type="number" class="product-field-input quantity-input" data-index="${productIndex}" value="${quantity}" step="0.01" min="0" placeholder="0" required>
            </div>
            <div>
                <label class="product-field-label">السعر</label>
                <input type="number" class="product-field-input price-display" data-index="${productIndex}" readonly step="0.01" placeholder="0">
            </div>
            <div>
                <label class="product-field-label">إجمالي</label>
                <input type="number" class="product-field-input subtotal-display" data-index="${productIndex}" readonly step="0.01" placeholder="0">
            </div>
            <div>
                <label class="product-field-label">خصم</label>
                <input type="number" class="product-field-input discount-input" data-index="${productIndex}" value="${discount}" step="0.01" min="0" placeholder="0">
            </div>
            <div class="btn-group-actions">
                <button type="button" class="btn-sm-custom btn-remove-product" onclick="removeProductRow(${productIndex})">
                    <i class="fe fe-trash-2"></i> حذف
                </button>
            </div>
        </div>

        <div class="discount-toggle" style="margin-top: 1rem;">
            <button type="button" class="discount-type-btn discount-type-fixed active" data-index="${productIndex}" data-type="fixed">
                <i class="fe fe-minus"></i> قيمة ثابتة
            </button>
            <button type="button" class="discount-type-btn discount-type-percentage" data-index="${productIndex}" data-type="percentage">
                <i class="fe fe-percent"></i> نسبة مئوية
            </button>
        </div>

        <div class="price-summary" style="margin-top: 0.75rem;">
            <div class="price-summary-row">
                <span>السعر الواحد:</span>
                <span class="unit-price-summary">0</span>
            </div>
            <div class="price-summary-row">
                <span>الإجمالي قبل الخصم:</span>
                <span class="subtotal-summary">0</span>
            </div>
            <div class="price-summary-row">
                <span><i class="fe fe-tag"></i> الخصم:</span>
                <span class="discount-summary">0</span>
            </div>
            <div class="price-summary-row total">
                <span>الإجمالي بعد الخصم:</span>
                <span class="final-summary">0</span>
            </div>
        </div>
    `;

    productsContainer.appendChild(productRow);

    // Setup event listeners for this row
    setupProductRowListeners(productIndex, discountType);
    productIndex++;

    // Smooth scroll to the new product row
    setTimeout(() => {
        productRow.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }, 100);

    updateSummary();
    generatePriceSummaryTable();
}

// Get Product Options HTML
function getProductOptions() {
    // Use cached options if available for better performance
    if (productOptionsCache) {
        return productOptionsCache;
    }

    if (!productsData || productsData.length === 0) {
        return '';
    }

    let options = '';
    productsData.forEach(product => {
        options += `<option value="${product.id}" data-price="${product.price}">${product.name}</option>`;
    });

    // Cache the options
    productOptionsCache = options;
    return options;
}

// Setup Event Listeners for Product Row
function setupProductRowListeners(index, defaultDiscountType = 'fixed') {
    const productSelect = document.querySelector(`.product-select[data-index="${index}"]`);
    const quantityInput = document.querySelector(`.quantity-input[data-index="${index}"]`);
    const discountInput = document.querySelector(`.discount-input[data-index="${index}"]`);
    const discountTypeButtons = document.querySelectorAll(`.discount-type-btn[data-index="${index}"]`);

    // Initialize Select2 for product select
    if ($(productSelect).length) {
        // Check if Select2 is already initialized
        if (!$(productSelect).hasClass('select2-hidden-accessible')) {
            $(productSelect).select2({
                placeholder: '-- اختر منتج --',
                allowClear: true,
                dir: 'rtl',
                width: '100%',
                dropdownParent: $(productSelect).parent(),
                language: {
                    noResults: function() {
                        return "لا توجد نتائج";
                    },
                    searching: function() {
                        return "جاري البحث...";
                    }
                }
            });

            // Auto-focus search box when dropdown opens
            $(productSelect).on('select2:open', function() {
                setTimeout(function() {
                    document.querySelector('.select2-search__field').focus();
                }, 100);
            });
        }
    }

    // Set default discount type
    if (defaultDiscountType === 'percentage') {
        document.querySelector(`.discount-type-percentage[data-index="${index}"]`).click();
    }

    // Use jQuery for Select2 compatibility
    $(productSelect).on('change', function() {
        // Clear invalid state
        this.style.borderColor = '';

        const clientId = document.getElementById('client_id').value;

        // Set default quantity to 1 if not set
        if (!quantityInput.value || parseFloat(quantityInput.value) === 0) {
            quantityInput.value = 1;
        }

        if (clientId && this.value) {
            // Fetch product details with special offer applied
            fetchProductDetails(this.value, clientId, index);
        } else {
            updateProductPrice(index);
            generatePriceSummaryTable();
        }
    });

    quantityInput.addEventListener('input', function() {
        // Clear invalid state
        this.style.borderColor = '';
        updateProductPrice(index);
        generatePriceSummaryTable();
    });

    discountInput.addEventListener('input', function() {
        updateProductPrice(index);
        generatePriceSummaryTable();
    });

    discountTypeButtons.forEach(btn => {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            document.querySelectorAll(`.discount-type-btn[data-index="${index}"]`).forEach(b => {
                b.classList.remove('active');
            });
            this.classList.add('active');
            updateProductPrice(index);
            generatePriceSummaryTable();
        });
    });
}

// Update Product Price
function updateProductPrice(index) {
    const productSelect = document.querySelector(`.product-select[data-index="${index}"]`);
    const quantityInput = document.querySelector(`.quantity-input[data-index="${index}"]`);
    const discountInput = document.querySelector(`.discount-input[data-index="${index}"]`);
    const priceDisplay = document.querySelector(`.price-display[data-index="${index}"]`);
    const subtotalDisplay = document.querySelector(`.subtotal-display[data-index="${index}"]`);
    const activeDiscountType = document.querySelector(`.discount-type-btn[data-index="${index}"].active`);

    const selectedOption = productSelect.options[productSelect.selectedIndex];
    const unitPrice = parseFloat(selectedOption.dataset.price) || 0;
    const quantity = parseFloat(quantityInput.value) || 0;
    const subtotal = unitPrice * quantity;
    const discount = parseFloat(discountInput.value) || 0;

    let finalAmount = subtotal;

    if (activeDiscountType.dataset.type === 'fixed') {
        finalAmount = subtotal - discount;
    } else {
        finalAmount = subtotal - (subtotal * (discount / 100));
    }

    priceDisplay.value = unitPrice.toFixed(2);
    subtotalDisplay.value = finalAmount.toFixed(2);

    // Update summaries
    updatePriceSummary(index, unitPrice, subtotal, discount, finalAmount, activeDiscountType.dataset.type);
    updateSummary();
}

// Update Price Summary
function updatePriceSummary(index, unitPrice, subtotal, discount, finalAmount, discountType) {
    const row = document.getElementById(`product-${index}`);
    const unitPriceSummary = row.querySelector('.unit-price-summary');
    const subtotalSummary = row.querySelector('.subtotal-summary');
    const discountSummary = row.querySelector('.discount-summary');
    const finalSummary = row.querySelector('.final-summary');

    unitPriceSummary.textContent = unitPrice.toFixed(2);
    subtotalSummary.textContent = subtotal.toFixed(2);

    if (discountType === 'fixed') {
        discountSummary.textContent = discount.toFixed(2);
    } else {
        discountSummary.textContent = ((subtotal * discount) / 100).toFixed(2) + ' (' + discount.toFixed(2) + '%)';
    }

    finalSummary.textContent = finalAmount.toFixed(2);
}

// Remove Product Row
function removeProductRow(index) {
    const row = document.getElementById(`product-${index}`);
    if (row) {
        row.style.animation = 'fadeIn 0.3s ease reverse';
        setTimeout(() => {
            row.remove();
            checkEmptyState();
            updateSummary();
            generatePriceSummaryTable();
        }, 300);
    }
}

// Check Empty State
function checkEmptyState() {
    const productRows = document.querySelectorAll('.product-row:not(.empty-state)');
    if (productRows.length === 0) {
        emptyState.style.display = 'block';
        summaryCard.style.display = 'none';
    }
}

// Update Summary Card
function updateSummary() {
    let totalProducts = 0;
    let totalSubtotal = 0;
    let totalDiscount = 0;
    let totalFinal = 0;

    const productRows = document.querySelectorAll('.product-row:not(.empty-state)');

    productRows.forEach(row => {
        totalProducts++;

        const subtotalValue = parseFloat(row.querySelector('.subtotal-display').value) || 0;
        const finalSummaryText = row.querySelector('.final-summary').textContent;
        const finalValue = parseFloat(finalSummaryText) || 0;

        totalSubtotal += subtotalValue;
        totalFinal += finalValue;
        totalDiscount += (subtotalValue - finalValue);
    });

    // Apply client balance if checkbox is checked
    let displayFinalTotal = totalFinal;
    if (useClientBalanceCheckbox.checked && currentClientBalance > 0) {
        const balanceToDeduct = Math.min(currentClientBalance, totalFinal);
        displayFinalTotal = totalFinal - balanceToDeduct;
    }

    document.getElementById('productCount').textContent = totalProducts;
    document.getElementById('subtotal').textContent = totalSubtotal.toFixed(2);
    document.getElementById('discountTotal').textContent = totalDiscount.toFixed(2);
    document.getElementById('finalTotal').textContent = displayFinalTotal.toFixed(2);

    if (productRows.length > 0) {
        summaryCard.style.display = 'block';
    }

    // Update client balance display
    updateClientBalanceDisplay();

    // Also update hidden form fields for submission
    updateFormFields();
}

// Update Form Fields (Hidden JSON)
function updateFormFields() {
    const formData = [];
    const productRows = document.querySelectorAll('.product-row:not(.empty-state)');

    productRows.forEach(row => {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const discountInput = row.querySelector('.discount-input');
        const activeDiscountType = row.querySelector('.discount-type-btn.active');
        const finalValue = row.querySelector('.final-summary').textContent;

        if (productSelect.value) {
            formData.push({
                product_id: productSelect.value,
                quantity: quantityInput.value,
                discount: discountInput.value,
                discount_type: activeDiscountType.dataset.type,
                final_price: finalValue
            });
        }
    });

    // Create or update hidden input with JSON data
    let dataInput = document.getElementById('productsData');
    if (!dataInput) {
        dataInput = document.createElement('input');
        dataInput.type = 'hidden';
        dataInput.name = 'products';
        dataInput.id = 'productsData';
        document.getElementById('invoiceForm').appendChild(dataInput);
    }
    dataInput.value = JSON.stringify(formData);
}

// Generate Price Summary Table
function generatePriceSummaryTable() {
    const productRows = document.querySelectorAll('.product-row:not(.empty-state)');
    const priceTableContainer = document.getElementById('priceSummaryTable');
    const priceSummaryCard = document.getElementById('priceSummaryCard');

    if (productRows.length === 0) {
        if (priceTableContainer) {
            priceTableContainer.innerHTML = '';
        }
        if (priceSummaryCard) {
            priceSummaryCard.style.display = 'none';
        }
        return;
    }

    // Show the card
    if (priceSummaryCard) {
        priceSummaryCard.style.display = 'block';
    }

    let tableHTML = `
        <div class="table-wrapper">
            <table class="summary-table w-100">
                <thead>
                    <tr style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                        <th style="padding: 1rem; text-align: right; border-radius: 8px 0 0 0;">المنتج</th>
                        <th style="padding: 1rem; text-align: center;">الكمية</th>
                        <th style="padding: 1rem; text-align: center;">السعر الواحد</th>
                        <th style="padding: 1rem; text-align: center;">الإجمالي</th>
                        <th style="padding: 1rem; text-align: center;">الخصم</th>
                        <th style="padding: 1rem; text-align: left; border-radius: 0 8px 0 0;">الإجمالي النهائي</th>
                    </tr>
                </thead>
                <tbody>
    `;

    let grandSubtotal = 0;
    let grandDiscount = 0;
    let grandTotal = 0;

    productRows.forEach((row, idx) => {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');
        const discountInput = row.querySelector('.discount-input');
        const activeDiscountType = row.querySelector('.discount-type-btn.active');
        const priceDisplay = row.querySelector('.price-display');
        const subtotalDisplay = row.querySelector('.subtotal-display');
        const finalSummary = row.querySelector('.final-summary');

        const productName = productSelect.options[productSelect.selectedIndex]?.text || 'منتج';
        const quantity = parseFloat(quantityInput.value) || 0;
        const unitPrice = parseFloat(priceDisplay.value) || 0;
        const subtotal = quantity * unitPrice;
        const discount = parseFloat(discountInput.value) || 0;
        let discountAmount = 0;

        if (activeDiscountType?.dataset.type === 'fixed') {
            discountAmount = discount;
        } else {
            discountAmount = (subtotal * discount) / 100;
        }

        const finalPrice = subtotal - discountAmount;

        grandSubtotal += subtotal;
        grandDiscount += discountAmount;
        grandTotal += finalPrice;

        tableHTML += `
            <tr style="border-bottom: 1px solid #e2e8f0;">
                <td style="padding: 0.75rem 1rem; text-align: right; color: #2d3748;">${productName}</td>
                <td style="padding: 0.75rem 1rem; text-align: center; color: #4a5568;">${quantity.toFixed(2)}</td>
                <td style="padding: 0.75rem 1rem; text-align: center; color: #4a5568;">${unitPrice.toFixed(2)} ريال</td>
                <td style="padding: 0.75rem 1rem; text-align: center; color: #4a5568;">${subtotal.toFixed(2)} ريال</td>
                <td style="padding: 0.75rem 1rem; text-align: center; color: #e53e3e; font-weight: 500;">-${discountAmount.toFixed(2)} ريال</td>
                <td style="padding: 0.75rem 1rem; text-align: left; font-weight: 600; color: #667eea;">${finalPrice.toFixed(2)} ريال</td>
            </tr>
        `;
    });

    tableHTML += `
                </tbody>
            </table>
        </div>
        <div style="margin-top: 1.5rem; display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 1rem;">
            <div class="balance-stat" style="background: linear-gradient(135deg, #667eea15 0%, #764ba215 100%); border-left: 4px solid #667eea;">
                <div class="balance-stat-label">الإجمالي قبل الخصم</div>
                <div class="balance-stat-value" style="color: #667eea;">${grandSubtotal.toFixed(2)} ريال</div>
            </div>
            <div class="balance-stat" style="background: linear-gradient(135deg, #f6ad5515 0%, #f6ad5515 100%); border-left: 4px solid #f6ad55;">
                <div class="balance-stat-label">إجمالي الخصم</div>
                <div class="balance-stat-value" style="color: #f6ad55;">-${grandDiscount.toFixed(2)} ريال</div>
            </div>
    `;

    // Add balance deduction row if balance is being used OR if partially paid is selected
    let balanceDeductionAmount = 0;

    if (useClientBalanceCheckbox.checked && currentClientBalance > 0) {
        balanceDeductionAmount = Math.min(currentClientBalance, grandTotal);
    }

    // Check if partially paid is selected - show paid_amount as balance deduction
    const paymentStatus = document.querySelector('input[name="payment_status"]:checked');
    const paidAmountInput = document.querySelector('input[name="paid_amount"]');

    if (paymentStatus && paymentStatus.value === 'partially_paid' && paidAmountInput && paidAmountInput.value) {
        const paidAmount = parseFloat(paidAmountInput.value) || 0;
        if (paidAmount > 0) {
            balanceDeductionAmount = paidAmount;
        }
    }

    if (balanceDeductionAmount > 0) {
        tableHTML += `
            <div class="table-balance-row balance-stat" style="background: linear-gradient(135deg, #f687b315 0%, #c12a7515 100%); border-left: 4px solid #ed64a6;">
                <div class="balance-stat-label">خصم رصيد العميل</div>
                <div class="balance-stat-value" style="color: #ed64a6;">-${balanceDeductionAmount.toFixed(2)} ريال</div>
            </div>
        `;
    }

    let finalAmount = grandTotal;

    // Deduct balance if checkbox is checked
    if (useClientBalanceCheckbox.checked && currentClientBalance > 0) {
        finalAmount = Math.max(0, grandTotal - Math.min(currentClientBalance, grandTotal));
    }

    // Deduct paid_amount if partially paid is selected
    if (paymentStatus && paymentStatus.value === 'partially_paid' && paidAmountInput && paidAmountInput.value) {
        const paidAmount = parseFloat(paidAmountInput.value) || 0;
        if (paidAmount > 0) {
            finalAmount = Math.max(0, grandTotal - paidAmount);
        }
    }

    tableHTML += `
            <div class="balance-stat table-total-row" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border-left: 4px solid #667eea;">
                <div class="balance-stat-label">الإجمالي النهائي</div>
                <div class="balance-stat-value">${finalAmount.toFixed(2)} ريال</div>
            </div>
        </div>
`

    priceTableContainer.innerHTML = tableHTML;
}

// Handle Form Submission
function handleFormSubmit(e) {
    const productRows = document.querySelectorAll('.product-row:not(.empty-state)');

    // Better validation messages
    if (productRows.length === 0) {
        e.preventDefault();
        showValidationMessage('يرجى إضافة منتج واحد على الأقل إلى الفاتورة', 'error');
        return false;
    }

    // Validate all products have required fields
    let hasInvalidProduct = false;
    productRows.forEach(row => {
        const productSelect = row.querySelector('.product-select');
        const quantityInput = row.querySelector('.quantity-input');

        if (!productSelect.value) {
            hasInvalidProduct = true;
            productSelect.style.borderColor = '#fc8181';
        }
        if (!quantityInput.value || parseFloat(quantityInput.value) <= 0) {
            hasInvalidProduct = true;
            quantityInput.style.borderColor = '#fc8181';
        }
    });

    if (hasInvalidProduct) {
        e.preventDefault();
        showValidationMessage('يرجى التأكد من اختيار المنتج وإدخال الكمية لجميع الصفوف', 'error');
        return false;
    }

    const paidAmountInput = document.querySelector('input[name="paid_amount"]');
    const paymentStatus = document.querySelector('input[name="payment_status"]:checked');

    if (paymentStatus.value === 'partially_paid') {
        const paidAmount = parseFloat(paidAmountInput.value) || 0;

        // Get total before balance deduction for proper validation
        const originalTotal = parseFloat(document.getElementById('subtotal').textContent) || 0;
        const discountTotal = parseFloat(document.getElementById('discountTotal').textContent) || 0;
        const totalBeforeBalance = originalTotal - discountTotal;

        if (!paidAmountInput.value || paidAmount <= 0) {
            e.preventDefault();
            paidAmountInput.style.borderColor = '#fc8181';
            showValidationMessage('يرجى إدخال المبلغ المدفوع', 'error');
            return false;
        }

        // Validate against total BEFORE balance deduction
        if (paidAmount > totalBeforeBalance) {
            e.preventDefault();
            paidAmountInput.style.borderColor = '#fc8181';
            showValidationMessage('المبلغ المدفوع لا يمكن أن يكون أكبر من إجمالي الفاتورة', 'error');
            return false;
        }

        // Validate against client balance
        if (paidAmount > currentClientBalance) {
            e.preventDefault();
            paidAmountInput.style.borderColor = '#fc8181';
            showValidationMessage('المبلغ المدفوع لا يمكن أن يكون أكبر من رصيد العميل (' + currentClientBalance.toFixed(2) + ' ريال)', 'error');
            return false;
        }
    }

    // Add balance information to form
    if (useClientBalanceCheckbox && useClientBalanceCheckbox.checked && currentClientBalance > 0) {
        const originalTotal = parseFloat(document.getElementById('subtotal').textContent) || 0;
        const discountTotal = parseFloat(document.getElementById('discountTotal').textContent) || 0;
        const totalAfterDiscount = originalTotal - discountTotal;
        const balanceToDeduct = Math.min(currentClientBalance, totalAfterDiscount);

        let balanceInput = document.getElementById('clientBalanceValue');
        if (!balanceInput) {
            balanceInput = document.createElement('input');
            balanceInput.type = 'hidden';
            balanceInput.name = 'client_balance_used';
            balanceInput.id = 'clientBalanceValue';
            document.getElementById('invoiceForm').appendChild(balanceInput);
        }
        balanceInput.value = balanceToDeduct;

        // If using client balance and partial payment, adjust the paid amount
        if (paymentStatus.value === 'partially_paid' && paidAmountInput.value) {
            const currentPaidAmount = parseFloat(paidAmountInput.value);
            // The actual paid amount from client is what they entered
            // Balance will be deducted separately on the backend
        }
    }

    updateFormFields();

    // Add loading state to submit button
    const submitBtn = document.querySelector('.btn-submit');
    if (submitBtn) {
        submitBtn.innerHTML = '<i class="fe fe-loader"></i> جاري الحفظ...';
        submitBtn.disabled = true;
    }
}

// Show validation message
function showValidationMessage(message, type = 'error') {
    // Remove existing message if any
    const existingMessage = document.querySelector('.validation-message');
    if (existingMessage) {
        existingMessage.remove();
    }

    const messageDiv = document.createElement('div');
    messageDiv.className = `validation-message ${type}`;
    messageDiv.innerHTML = `
        <i class="fe fe-alert-circle"></i>
        <span>${message}</span>
    `;

    const form = document.getElementById('invoiceForm');
    form.insertBefore(messageDiv, form.firstChild);

    // Scroll to message
    messageDiv.scrollIntoView({ behavior: 'smooth', block: 'center' });

    // Auto-remove after 5 seconds
    setTimeout(() => {
        messageDiv.style.opacity = '0';
        setTimeout(() => messageDiv.remove(), 300);
    }, 5000);
}
