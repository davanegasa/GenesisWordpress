function showAlert(message, duration = 5000) {
    const alertDiv = $('<div class="alert-custom"></div>').text(message);
    $('body').append(alertDiv);
    alertDiv.addClass('show');

    setTimeout(() => {
        alertDiv.removeClass('show');
        setTimeout(() => alertDiv.remove(), 500);
    }, duration);
} 