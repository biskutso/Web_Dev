// assets/app.js

// Imports for Symfony/Stimulus (if you're using it)
import './bootstrap.js'; // This is often for Stimulus and other base configurations

/*
 * Welcome to your app's main JavaScript file!
 * (Comment for context)
 */

// Any CSS you import will output into a single css file (app.css in this case)
import './styles/app.css';

// Redundant import: You have './bootstrap.js' and './bootstrap' - usually one is enough.
// './bootstrap.js' typically handles Stimulus setup.
// If './bootstrap' is for something else, keep it; otherwise, you might remove one.
// Assuming './bootstrap.js' is the primary one for Symfony/Stimulus.
// import './bootstrap'; // <-- Consider if this is truly needed given './bootstrap.js'

// Enable the interactive UI components from Flowbite
import 'flowbite';

// ==========================
// Imports
// ==========================
import 'datatables.net-dt/js/dataTables.dataTables';
import 'datatables.net-dt/css/dataTables.dataTables.css';
import $ from 'jquery';

// ==========================
// Initialize DataTables
// ==========================
$(document).ready(function () {
  $('#productsTable').DataTable();
  $('#customersTable').DataTable();
  $('#categoriesTable').DataTable();
  $('#servicesTable').DataTable();
  $('#usersTable').DataTable();
  $('#ordersTable').DataTable();
});

// ==========================
// Match "Products Index" Design
// ==========================
const style = document.createElement('style');
style.innerHTML = `
/* ================================
   CONTAINER
=================================== */
.dataTables_wrapper {
  background-color: #ffffff !important;
  padding: 1.25rem 1.5rem !important;
  border-radius: 12px !important;
  box-shadow: 0 2px 8px rgba(0,0,0,0.05) !important;
  font-family: 'Montserrat' !important;
}

/* ================================
   TABLE
=================================== */
table.dataTable {
  width: 100% !important;
  border-collapse: collapse !important;
  font-size: 0.88rem !important; /* smaller but readable */
  color: #111827 !important;
}

table.dataTable thead th {
  background-color: #f9fafb !important;
  color: #374151 !important;
  font-weight: 600 !important;
  text-align: left !important;
  padding: 10px 14px !important;
  border-bottom: 2px solid #e5e7eb !important;
}

table.dataTable tbody td {
  padding: 10px 14px !important;
  border-top: 1px solid #f3f4f6 !important;
  vertical-align: middle !important;
}

table.dataTable tbody tr:hover {
  background-color: #f9fafb !important;
  transition: background 0.2s ease-in-out;
}

/* ================================
   SEARCH BOX
=================================== */
input.dt-input {
  width: 220px !important;
  height: 36px !important;
  font-size: 0.85rem !important;
  border-radius: 8px !important;
  border: 1px solid #d1d5db !important;
  background-color: #f9fafb !important;
  color: #111827 !important;
  padding: 6px 10px !important;
  transition: all 0.2s ease-in-out !important;
}

input.dt-input:focus {
  border-color: #2563eb !important;
  background-color: #ffffff !important;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.1) !important;
  outline: none !important;
}

/* ================================
   SELECT DROPDOWN (ENTRIES)
=================================== */
select.dt-input {
  appearance: none !important;
  width: 60px !important;
  height: 36px !important;
  font-size: 0.85rem !important;
  border-radius: 8px !important;
  border: 1px solid #d1d5db !important;
  background-color: #f9fafb !important;
  padding: 0 1.75rem 0 0.75rem !important;
  background-image: url("data:image/svg+xml;utf8,<svg xmlns='http://www.w3.org/2000/svg' fill='none' stroke='%236b7280' stroke-width='2' viewBox='0 0 24 24'><path stroke-linecap='round' stroke-linejoin='round' d='M19 9l-7 7-7-7'/></svg>");
  background-repeat: no-repeat;
  background-position: right 0.6rem center;
  background-size: 0.9rem;
  color: #111827 !important;
  transition: all 0.2s ease-in-out !important;
}

select.dt-input:focus {
  border-color: #2563eb !important;
  background-color: #ffffff !important;
  box-shadow: 0 0 0 3px rgba(37,99,235,0.1) !important;
}

/* ================================
   INFO TEXT & PAGINATION
=================================== */
.dataTables_info {
  color: #6b7280 !important;
  font-size: 0.83rem !important;
  margin-top: 10px !important;
}

.dataTables_paginate {
  margin-top: 10px !important;
}

.dataTables_paginate .paginate_button {
  padding: 5px 10px !important;
  border-radius: 6px !important;
  margin: 0 2px !important;
  border: 1px solid transparent !important;
  font-size: 0.85rem !important;
  color: #2563eb !important;
  transition: all 0.2s ease-in-out !important;
}

.dataTables_paginate .paginate_button:hover {
  background-color: #2563eb !important;
  color: white !important;
}

.dataTables_paginate .paginate_button.current {
  background-color: #2563eb !important;
  color: white !important;
  border-color: #2563eb !important;
}

/* ================================
   SPACING FOR HEADER ELEMENTS
=================================== */
.dataTables_length,
.dataTables_filter {
  margin-bottom: 1rem !important;
}

.dataTables_length label,
.dataTables_filter label {
  color: #374151 !important;
  font-size: 0.85rem !important;
  display: flex;
  align-items: center;
  gap: 6px;
}
`;
document.head.appendChild(style);


document.addEventListener('DOMContentLoaded', function() {
    // Password toggle functionality
    const toggleButtons = document.querySelectorAll('.toggle-password');
    
    toggleButtons.forEach(button => {
        button.addEventListener('click', function() {
            const targetId = this.getAttribute('data-target');
            const passwordInput = document.getElementById(targetId);
            const icon = this.querySelector('svg');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                // Change to eye-off icon
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.878 9.878L6.59 6.59m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                `;
            } else {
                passwordInput.type = 'password';
                // Change back to eye icon
                icon.innerHTML = `
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                `;
            }
        });
    });

    const forms = document.querySelectorAll('form');

    forms.forEach(function(form) {
        form.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');

            if (newPassword && confirmPassword && form.querySelector('[name="change_password"]')) {
                if (newPassword.value !== confirmPassword.value) {
                    e.preventDefault();
                    showAlert('New password and confirmation do not match!', 'error');
                    return;
                }
                if (newPassword.value.length < 6) {
                    e.preventDefault();
                    showAlert('Password must be at least 6 characters!', 'error');
                    return;
                }
            }

            const usernameInput = document.getElementById('username');
            if (usernameInput && form.querySelector('[name="update_profile"]')) {
                if (usernameInput.value.trim() === '') {
                    e.preventDefault();
                    showAlert('Username cannot be empty!', 'error');
                }
            }
        });
    });

    function showAlert(message, type = 'error') {
        // Remove existing alert if present
        const existingAlert = document.querySelector('.custom-alert');
        if (existingAlert) {
            existingAlert.remove();
        }

        const alertDiv = document.createElement('div');
        alertDiv.className = `custom-alert fixed top-6 left-1/2 transform -translate-x-1/2 z-50 bg-${type === 'error' ? 'red' : 'green'}-100 border border-${type === 'error' ? 'red' : 'green'}-400 text-${type === 'error' ? 'red' : 'green'}-700 px-6 py-4 rounded-lg shadow-lg flex items-center justify-between min-w-64 max-w-md`;
        
        alertDiv.innerHTML = `
            <span>${message}</span>
            <button type="button" onclick="this.parentElement.remove()" 
                    class="ml-4 text-${type === 'error' ? 'red' : 'green'}-700 hover:text-${type === 'error' ? 'red' : 'green'}-900">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M4.293 4.293a1 1 0 011.414 0L10 8.586l4.293-4.293a1 1 0 111.414 1.414L11.414 10l4.293 4.293a1 1 0 01-1.414 1.414L10 11.414l-4.293 4.293a1 1 0 01-1.414-1.414L8.586 10 4.293 5.707a1 1 0 010-1.414z" clip-rule="evenodd"/>
                </svg>
            </button>
        `;
        
        document.body.appendChild(alertDiv);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (alertDiv.parentNode) {
                alertDiv.remove();
            }
        }, 5000);
    }
});

// order
 document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('order-form');
        const selectedItemField = document.querySelector('#{{ form.selectedItem.vars.id }}');
        const itemTypeField = document.querySelector('#{{ form.itemType.vars.id }}');
        const priceField = document.querySelector('#{{ form.price.vars.id }}');
        const userSelect = document.getElementById('user-select');
        const submitBtn = document.getElementById('submit-btn');
        const selectedItemDisplay = document.getElementById('selected-item-display');
        const clearSelectionBtn = document.getElementById('clear-selection');
        
        // Display elements
        const selectedItemName = document.getElementById('selected-item-name');
        const selectedItemType = document.getElementById('selected-item-type');
        const selectedItemCategory = document.getElementById('selected-item-category');
        const selectedItemPrice = document.getElementById('selected-item-price');
        const selectedItemQuantity = document.getElementById('selected-item-quantity');
        
        let selectedCard = null;
        
        // Handle item selection
        document.querySelectorAll('.item-card1').forEach(card => {
            const selectBtn = card.querySelector('.select-item-btn');
            
            selectBtn.addEventListener('click', function(e) {
                e.stopPropagation();
                selectItem(card);
            });
            
            card.addEventListener('click', function(e) {
                if (!e.target.classList.contains('select-item-btn')) {
                    selectItem(card);
                }
            });
        });
        
        function selectItem(card) {
            // Remove previous selection
            if (selectedCard) {
                selectedCard.classList.remove('border-[#C49A41]', 'ring-2', 'ring-[#C49A41]/20');
                selectedCard.querySelector('.select-item-btn').classList.remove('from-blue-800', 'to-blue-900', 'from-purple-800', 'to-purple-900');
                
                const btn = selectedCard.querySelector('.select-item-btn');
                if (selectedCard.dataset.itemType === 'product') {
                    btn.classList.add('from-blue-600', 'to-blue-700');
                } else {
                    btn.classList.add('from-purple-600', 'to-purple-700');
                }
            }
            
            // Set new selection
            card.classList.add('border-[#C49A41]', 'ring-2', 'ring-[#C49A41]/20');
            
            // Update button color
            const btn = card.querySelector('.select-item-btn');
            if (card.dataset.itemType === 'product') {
                btn.classList.remove('from-blue-600', 'to-blue-700');
                btn.classList.add('from-blue-800', 'to-blue-900');
            } else {
                btn.classList.remove('from-purple-600', 'to-purple-700');
                btn.classList.add('from-purple-800', 'to-purple-900');
            }
            
            selectedCard = card;
            
            // Update form fields
            selectedItemField.value = card.dataset.itemId;
            itemTypeField.value = card.dataset.itemType;
            priceField.value = card.dataset.price;
            
            // Update display
            selectedItemName.textContent = card.dataset.name;
            selectedItemType.textContent = card.dataset.itemType.charAt(0).toUpperCase() + card.dataset.itemType.slice(1);
            selectedItemCategory.textContent = card.dataset.category;
            selectedItemPrice.textContent = '$' + parseFloat(card.dataset.price).toFixed(2);
            selectedItemQuantity.textContent = card.dataset.quantity;
            
            // Show quantity warning for low quantity products
            if (card.dataset.itemType === 'product') {
                const quantity = parseInt(card.dataset.quantity);
                if (quantity < 5) {
                    selectedItemQuantity.classList.add('text-red-600', 'font-bold');
                } else {
                    selectedItemQuantity.classList.remove('text-red-600', 'font-bold');
                }
            }
            
            // Show selected item display with animation
            selectedItemDisplay.classList.remove('hidden');
            selectedItemDisplay.classList.add('animate-fade-in');
            
            // Enable submit button if user is selected
            updateSubmitButton();
        }
        
        // Handle clear selection
        clearSelectionBtn.addEventListener('click', function() {
            if (selectedCard) {
                selectedCard.classList.remove('border-[#C49A41]', 'ring-2', 'ring-[#C49A41]/20');
                selectedCard.querySelector('.select-item-btn').classList.remove('from-blue-800', 'to-blue-900', 'from-purple-800', 'to-purple-900');
                
                const btn = selectedCard.querySelector('.select-item-btn');
                if (selectedCard.dataset.itemType === 'product') {
                    btn.classList.add('from-blue-600', 'to-blue-700');
                } else {
                    btn.classList.add('from-purple-600', 'to-purple-700');
                }
                
                selectedCard = null;
            }
            
            // Clear form fields
            selectedItemField.value = '';
            itemTypeField.value = '';
            priceField.value = '';
            
            // Hide selected item display
            selectedItemDisplay.classList.add('hidden');
            selectedItemDisplay.classList.remove('animate-fade-in');
            
            // Disable submit button
            submitBtn.disabled = true;
        });
        
        // Update submit button state based on user selection and item selection
        function updateSubmitButton() {
            if (userSelect) {
                const userSelected = userSelect.value && userSelect.value !== '';
                const itemSelected = selectedItemField.value && itemTypeField.value;
                submitBtn.disabled = !(userSelected && itemSelected);
            } else {
                // For regular users, just check if item is selected
                const itemSelected = selectedItemField.value && itemTypeField.value;
                submitBtn.disabled = !itemSelected;
            }
        }
        
        // Handle user selection change
        if (userSelect) {
            userSelect.addEventListener('change', updateSubmitButton);
        }
        
        // Form submission validation
        form.addEventListener('submit', function(e) {
            // Validate user selection for admin/staff
            if (userSelect && (!userSelect.value || userSelect.value === '')) {
                e.preventDefault();
                alert('Please select a customer before submitting.');
                return;
            }
            
            // Validate item selection
            if (!selectedItemField.value || !itemTypeField.value) {
                e.preventDefault();
                alert('Please select an item before submitting.');
                return;
            }
            
            // Additional validation for product quantity
            if (itemTypeField.value === 'product' && selectedCard) {
                const quantity = parseInt(selectedCard.dataset.quantity);
                if (quantity <= 0) {
                    e.preventDefault();
                    alert('This product is out of stock. Please select a different item.');
                    return;
                }
            }
            
            // Get customer name for confirmation message
            let customerName = 'Your';
            if (userSelect && userSelect.options[userSelect.selectedIndex]) {
                const selectedOption = userSelect.options[userSelect.selectedIndex];
                customerName = selectedOption.text + "'s";
            }
            
            // Confirm submission
            if (!confirm(`Are you sure you want to create this order for ${customerName} account?\n\nQuantity: 1\nTotal: $${parseFloat(priceField.value).toFixed(2)}`)) {
                e.preventDefault();
            }
        });
        
        // Initialize submit button state
        updateSubmitButton();
    });