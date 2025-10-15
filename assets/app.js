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

// DataTables imports (JS and CSS)
import 'datatables.net-dt/js/dataTables.dataTables';
import 'datatables.net-dt/css/dataTables.dataTables.css';

// jQuery import
import $ from 'jquery';

// Initialize DataTables when the DOM is ready
$(document).ready(function () {
  // Initialize the 'productsTable' if it exists
  $('#productsTable').DataTable();
  // Initialize the 'customersTable' if it exists
  $('#customersTable').DataTable();
});