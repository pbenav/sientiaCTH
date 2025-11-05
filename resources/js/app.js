require('./bootstrap');

require("flatpickr");
// Change this to get localized app in Datepicker
const lang = require("flatpickr/dist/l10n/es.js").default.es;
// or import { lang } from "flatpickr/dist/l10n/es.js"
flatpickr.localize(lang); // default locale is now Spanish

import Swal from 'sweetalert2';
window.Swal = Swal;
// Import SweetAlert2 CSS so it's included in the compiled bundle
import 'sweetalert2/dist/sweetalert2.min.css';

import Alpine from 'alpinejs';

window.Alpine = Alpine;

Alpine.start();

// Auto-select text content when input fields get focus
document.addEventListener('DOMContentLoaded', function() {
    // Function to add auto-select behavior to input fields
    function addAutoSelectBehavior() {
        const inputSelectors = [
            'input[type="text"]',
            'input[type="email"]', 
            'input[type="number"]',
            'input[type="tel"]',
            'input[type="url"]',
            'textarea'
        ];
        
        inputSelectors.forEach(selector => {
            document.querySelectorAll(selector).forEach(input => {
                // Only add if not already added (to avoid duplicate listeners)
                if (!input.hasAttribute('data-auto-select')) {
                    input.setAttribute('data-auto-select', 'true');
                    
                    input.addEventListener('focus', function() {
                        // Use setTimeout to ensure the cursor is properly positioned
                        setTimeout(() => {
                            this.select();
                        }, 50);
                    });
                }
            });
        });
    }
    
    // Add behavior to existing elements
    addAutoSelectBehavior();
    
    // Also add behavior when Livewire updates the DOM
    document.addEventListener('livewire:load', addAutoSelectBehavior);
    document.addEventListener('livewire:update', addAutoSelectBehavior);
    
    // For dynamically added content (modals, etc.)
    const observer = new MutationObserver(function(mutations) {
        let shouldUpdate = false;
        mutations.forEach(function(mutation) {
            if (mutation.type === 'childList' && mutation.addedNodes.length > 0) {
                mutation.addedNodes.forEach(function(node) {
                    if (node.nodeType === Node.ELEMENT_NODE && 
                        (node.matches('input, textarea') || node.querySelector('input, textarea'))) {
                        shouldUpdate = true;
                    }
                });
            }
        });
        
        if (shouldUpdate) {
            setTimeout(addAutoSelectBehavior, 100);
        }
    });
    
    // Start observing
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
});




