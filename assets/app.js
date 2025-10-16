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

