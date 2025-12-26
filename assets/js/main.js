$(document).ready(function() {
    // Form submissions via AJAX
    $('.ajax-form').submit(function(e) {
        e.preventDefault();
        
        var form = $(this);
        var formData = form.serialize();
        
        $.ajax({
            url: form.attr('action'),
            type: 'POST',
            data: formData,
            success: function(response) {
                if (response.success) {
                    // Show success message
                    alert('Operation completed successfully');
                    // Optionally redirect or update UI
                } else {
                    alert('Error: ' + response.error);
                }
            },
            error: function() {
                alert('An error occurred. Please try again.');
            }
        });
    });
    
    // Load content via AJAX
    $('.ajax-link').click(function(e) {
        e.preventDefault();
        
        var url = $(this).attr('href');
        
        $.ajax({
            url: url,
            type: 'GET',
            success: function(data) {
                $('#content-area').html(data);
                updateBrowserState(url);
            }
        });
    });
    
    // Update browser state without refresh
    function updateBrowserState(url) {
        if (window.history && window.history.pushState) {
            window.history.pushState({path: url}, '', url);
        }
    }
});