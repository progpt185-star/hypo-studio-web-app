// Main JavaScript for Hypo Studio Admin Dashboard

$(document).ready(function() {
    console.log('Hypo Studio Admin Dashboard Loaded');

    // Initialize tooltips
    initializeTooltips();

    // Auto hide alerts after 5 seconds
    autoHideAlerts();

    // Toggle sidebar on mobile
    toggleSidebar();

    // Set CSRF token for AJAX
    setCSRFToken();
});

/**
 * Initialize Bootstrap tooltips
 */
function initializeTooltips() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
}

/**
 * Auto hide alerts after 5 seconds
 */
function autoHideAlerts() {
    setTimeout(function() {
        $('.alert').fadeOut('slow', function() {
            $(this).remove();
        });
    }, 5000);
}

/**
 * Toggle sidebar on mobile
 */
function toggleSidebar() {
    $('.toggle-sidebar').on('click', function() {
        $('.sidebar').slideToggle(300);
    });
}

/**
 * Set CSRF token for AJAX requests
 */
function setCSRFToken() {
    $.ajaxSetup({
        headers: {
            'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content')
        }
    });
}

/**
 * Format currency to Rupiah
 */
function formatCurrency(amount) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(amount);
}

/**
 * Format date to readable format
 */
function formatDate(date) {
    var options = { year: 'numeric', month: 'long', day: 'numeric' };
    return new Date(date).toLocaleDateString('id-ID', options);
}

/**
 * Show loading spinner
 */
function showLoading() {
    var loader = $('<div class="spinner-border text-primary" role="status"><span class="visually-hidden">Loading...</span></div>');
    $('body').append(loader);
}

/**
 * Hide loading spinner
 */
function hideLoading() {
    $('.spinner-border').remove();
}

/**
 * Confirm delete action
 */
function confirmDelete(message = 'Yakin ingin menghapus data ini?') {
    return confirm(message);
}

/**
 * Show success message
 */
function showSuccess(message) {
    var alert = $('<div class="alert alert-success alert-dismissible fade show" role="alert">' +
        '<i class="fas fa-check-circle"></i> ' + message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>');
    
    $('.page-content').prepend(alert);
    
    setTimeout(function() {
        alert.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}

/**
 * Show error message
 */
function showError(message) {
    var alert = $('<div class="alert alert-danger alert-dismissible fade show" role="alert">' +
        '<i class="fas fa-exclamation-circle"></i> ' + message +
        '<button type="button" class="btn-close" data-bs-dismiss="alert"></button>' +
        '</div>');
    
    $('.page-content').prepend(alert);
    
    setTimeout(function() {
        alert.fadeOut('slow', function() {
            $(this).remove();
        });
    }, 3000);
}

/**
 * Validate form before submit
 */
function validateForm(formId) {
    var form = document.getElementById(formId);
    if (form.checkValidity() === false) {
        event.preventDefault();
        event.stopPropagation();
        form.classList.add('was-validated');
        return false;
    }
    return true;
}

/**
 * Clear form inputs
 */
function clearForm(formId) {
    document.getElementById(formId).reset();
    var form = document.getElementById(formId);
    form.classList.remove('was-validated');
}

/**
 * Export table to CSV
 */
function exportTableToCSV(filename) {
    var csv = [];
    var rows = document.querySelectorAll("table tr");
    
    for (var i = 0; i < rows.length; i++) {
        var row = [], cols = rows[i].querySelectorAll("td, th");
        
        for (var j = 0; j < cols.length; j++) {
            row.push(cols[j].innerText);
        }
        
        csv.push(row.join(","));
    }
    
    downloadCSV(csv.join("\n"), filename);
}

/**
 * Download CSV file
 */
function downloadCSV(csv, filename) {
    var csvFile;
    var downloadLink;

    csvFile = new Blob([csv], {type: "text/csv"});
    downloadLink = document.createElement("a");
    downloadLink.setAttribute("href", URL.createObjectURL(csvFile));
    downloadLink.setAttribute("download", filename);
    downloadLink.click();
}

/**
 * Highlight search results
 */
function highlightSearch(text) {
    var inputVal = new RegExp($('#searchInput').val(), 'gi');
    $('.search-results').each(function() {
        $(this).html($(this).text().replace(inputVal, function(match) {
            return "<mark>" + match + "</mark>";
        }));
    });
}

/**
 * Initialize data table
 */
function initializeDataTable(tableId, options = {}) {
    var defaultOptions = {
        pageLength: 10,
        lengthMenu: [[10, 25, 50], [10, 25, 50]],
        language: {
            url: '//cdn.datatables.net/plug-ins/1.13.0/i18n/id.json'
        }
    };
    
    // $(tableId).DataTable($.extend({}, defaultOptions, options));
}

/**
 * Disable form while processing
 */
function disableFormWhileProcessing(formId) {
    $('#' + formId).on('submit', function() {
        $(this).find('button[type="submit"]').prop('disabled', true);
        $(this).find('button[type="submit"]').html('<span class="spinner-border spinner-border-sm me-2"></span>Processing...');
    });
}

/**
 * Number only input
 */
function onlyNumbers(e) {
    return (e.charCode >= 48 && e.charCode <= 57);
}

/**
 * Reset form validation
 */
function resetFormValidation(formId) {
    var form = document.getElementById(formId);
    form.classList.remove('was-validated');
}

// API Helper Functions

/**
 * Make AJAX GET request
 */
function apiGet(url, callback) {
    $.ajax({
        url: url,
        type: 'GET',
        success: function(response) {
            callback(response, null);
        },
        error: function(error) {
            callback(null, error);
        }
    });
}

/**
 * Make AJAX POST request
 */
function apiPost(url, data, callback) {
    $.ajax({
        url: url,
        type: 'POST',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(response) {
            callback(response, null);
        },
        error: function(error) {
            callback(null, error);
        }
    });
}

/**
 * Make AJAX PUT request
 */
function apiPut(url, data, callback) {
    $.ajax({
        url: url,
        type: 'PUT',
        data: JSON.stringify(data),
        contentType: 'application/json',
        success: function(response) {
            callback(response, null);
        },
        error: function(error) {
            callback(null, error);
        }
    });
}

/**
 * Make AJAX DELETE request
 */
function apiDelete(url, callback) {
    $.ajax({
        url: url,
        type: 'DELETE',
        success: function(response) {
            callback(response, null);
        },
        error: function(error) {
            callback(null, error);
        }
    });
}
