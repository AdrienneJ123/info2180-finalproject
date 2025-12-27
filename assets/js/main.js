// Toggle contact type functionality - CORRECTED VERSION
$('.toggle-type').click(function(e) {
    e.preventDefault();
    
    const button = $(this);
    const contactId = button.data('contact-id');
    const currentType = button.data('current-type');
    const newType = currentType === 'Sales Lead' ? 'Support' : 'Sales Lead';
    
    if (button.prop('disabled')) return;
    
    if (!confirm(`Are you sure you want to change this contact from ${currentType} to ${newType}?`)) {
        return;
    }
    
    // Store original button content
    const originalHtml = button.html();
    
    button.prop('disabled', true);
    button.html('<i class="bi bi-arrow-repeat spin"></i> Changing...');
    
    $.ajax({
        url: '../ajax/update_contact_type.php',
        type: 'POST',
        data: { 
            contact_id: contactId,
            new_type: newType 
        },
        dataType: 'json',
        success: function(response) {
            console.log('Response:', response);
            
            if (response.success) {
                showAlert(response.message, 'success');
                
                // Update button data attribute
                button.data('current-type', newType);
                
                // Calculate the OPPOSITE type for the button text
                const oppositeType = newType === 'Sales Lead' ? 'Support' : 'Sales Lead';
                
                // Update button text
                button.html(`<i class="bi bi-arrow-left-right"></i> <b>Switch to ${oppositeType}</b>`);
                
                // Update button color
                button.removeClass('purple yellow');
                if (newType === 'Sales Lead') {
                    // If contact is NOW Sales Lead, button should show "Switch to Support" (purple)
                    button.addClass('yellow');
                } else {
                    // If contact is NOW Support, button should show "Switch to Sales Lead" (yellow)
                    button.addClass('purple');
                }
                
                // Update contact type badge
                const badge = $('#contact-type-badge');
                badge.text(newType)
                    .removeClass('badge-primary badge-success');
                
                if (newType === 'Sales Lead') {
                    badge.addClass('badge-primary');
                } else {
                    badge.addClass('badge-success');
                }
                
                // Update timestamp
                updateTimestamp();
                
                // Re-enable button
                button.prop('disabled', false);
                
            } else {
                showAlert(response.message, 'error');
                button.html(originalHtml);
                button.prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error:', error);
            console.error('Response:', xhr.responseText);
            showAlert('An error occurred. Please try again.', 'error');
            button.html(originalHtml);
            button.prop('disabled', false);
        }
    });
});
// Assign to me functionality - WITH BETTER ERROR HANDLING
$('.assign-to-me').click(function() {
    const button = $(this);
    const contactId = button.data('contact-id');
    
    if (button.prop('disabled')) return;
    
    button.prop('disabled', true).text('Assigning...');
    
    console.log('Attempting to assign contact ID:', contactId);
    
    $.ajax({
        url: '../ajax/assign_contact.php',
        type: 'POST',
        data: { contact_id: contactId },
        dataType: 'json',
        success: function(response) {
            console.log('Assign response:', response);
            
            if (response.success) {
                showAlert(response.message, 'success');
                
                // Update UI without reload
                button.prop('disabled', true).text('✋ Already Assigned to You');
                
                // Update the "Assigned To" field
                $('#assigned-to-text').text(response.assignee_name || 'You');
                
                // Update timestamp
                updateTimestamp();
                
            } else {
                // Show detailed error message
                const errorMsg = response.message || 'Failed to assign contact';
                const debugMsg = response.debug ? ' (' + response.debug + ')' : '';
                showAlert(errorMsg + debugMsg, 'error');
                button.prop('disabled', false);
                button.html('✋ Assign to Me');
            }
        },
        error: function(xhr, status, error) {
            console.error('AJAX Error Details:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Response Text:', xhr.responseText);
            
            let detailedError = 'AJAX Request Failed: ';
            if (xhr.status === 0) {
                detailedError += 'Network error or CORS issue';
            } else if (xhr.status === 404) {
                detailedError += 'Endpoint not found (404)';
            } else if (xhr.status === 500) {
                detailedError += 'Server error (500)';
            } else {
                detailedError += 'Status: ' + xhr.status;
            }
            
            showAlert(detailedError, 'error');
            button.prop('disabled', false);
            button.html('✋ Assign to Me');
        }
    });
});

// Toggle contact type functionality - WITH BETTER ERROR HANDLING
$('.toggle-type').click(function(e) {
    e.preventDefault();
    
    const button = $(this);
    const contactId = button.data('contact-id');
    const currentType = button.data('current-type');
    const newType = currentType === 'Sales Lead' ? 'Support' : 'Sales Lead';
    
    if (button.prop('disabled')) return;
    
    if (!confirm(`Are you sure you want to change this contact from ${currentType} to ${newType}?`)) {
        return;
    }
    
    const originalHtml = button.html();
    button.prop('disabled', true);
    button.html('<i class="bi bi-arrow-repeat spin"></i> Changing...');
    
    console.log('Changing contact type:', {
        contactId: contactId,
        from: currentType,
        to: newType
    });
    
    $.ajax({
        url: '../ajax/update_contact_type.php',
        type: 'POST',
        data: { 
            contact_id: contactId,
            new_type: newType 
        },
        dataType: 'json',
        success: function(response) {
            console.log('Type change response:', response);
            
            if (response.success) {
                showAlert(response.message, 'success');
                
                // Update button data attribute
                button.data('current-type', newType);
                
                // Calculate the OPPOSITE type for the button text
                const oppositeType = newType === 'Sales Lead' ? 'Support' : 'Sales Lead';
                
                // Update button text
                button.html(`<i class="bi bi-arrow-left-right"></i> <b>Switch to ${oppositeType}</b>`);
                
                // Update button color
                button.removeClass('purple yellow');
                if (newType === 'Sales Lead') {
                    button.addClass('yellow'); // Yellow for "Switch to Support"
                } else {
                    button.addClass('purple'); // Purple for "Switch to Sales Lead"
                }
                
                // Update contact type badge
                const badge = $('#contact-type-badge');
                badge.text(newType)
                    .removeClass('badge-primary badge-success');
                
                if (newType === 'Sales Lead') {
                    badge.addClass('badge-primary');
                } else {
                    badge.addClass('badge-success');
                }
                
                // Update timestamp
                updateTimestamp();
                
                button.prop('disabled', false);
                
            } else {
                const errorMsg = response.message || 'Failed to update contact type';
                const debugMsg = response.debug ? ' (' + response.debug + ')' : '';
                showAlert(errorMsg + debugMsg, 'error');
                button.html(originalHtml);
                button.prop('disabled', false);
            }
        },
        error: function(xhr, status, error) {
            console.error('Type Change AJAX Error:');
            console.error('Status:', status);
            console.error('Error:', error);
            console.error('Full Response:', xhr.responseText);
            
            let detailedError = 'Failed to update contact type. ';
            if (xhr.responseText) {
                try {
                    const response = JSON.parse(xhr.responseText);
                    detailedError = response.message || detailedError;
                } catch (e) {
                    detailedError += 'Response: ' + xhr.responseText.substring(0, 100);
                }
            }
            
            showAlert(detailedError, 'error');
            button.html(originalHtml);
            button.prop('disabled', false);
        }
    });
});