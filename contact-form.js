jQuery(document).ready(function($) {
    const contactForm = $('#progepesa-contact-form');   
    const submitButton = contactForm.find('.submit-button');
    const messages = $('#form-messages');

    // Form submission
    contactForm.on('submit', function(e) {
        e.preventDefault();

        // Disable button
        submitButton.prop('disabled', true);
        submitButton.addClass('loading');
        submitButton.html('<i class="fas fa-spinner fa-spin"></i> ' + contactFormData.sending);

        // Hide previous messages
        messages.removeClass('success error').hide();

        // Get form data
        const formData = {
            'action': 'submit_contact_form',
            'nonce': contactFormData.nonce,
            'name': $('#contact-name').val(),
            'email': $('#contact-email').val(),
            'subject': $('#contact-subject').val(),
            'message': $('#contact-message').val(),
        }

        // Send AJAX request
        $.ajax({
            url: contactFormData.ajax_url,
            type: 'POST',
            data: formData,
            success: function(response) {
                if(response.success) {
                    // Show success message
                    messages.addClass('success').html('<i class="fas fa-check-circle"></i> ${response.data} ').fadeIn();
                    // Reset form
                    contactForm[0].reset();
                    // Scroll to message
                    $('html, body').animate({
                        scrollTop: messages.offset().top - 100
                    }, 500);
                } else {
                    // Show error message
                    messages.addClass('error').html('<i class="fas fa-exclamation-circle"></i> ${response.data} ').fadeIn();
                }
            },
            error: function() {
                // Server error
                messages.addClass('error').html('<i class="fas fa-exclamation-circle"></i> ${contactFormData.error} ').fadeIn();
            },
            complete: function() {
                // Restore button
                submitButton.prop('disabled', false).removeClass('loading').html('<i class="fas fa-paper-plane"></i> Saada sõnum');
            }
        });
    });

    // Real-time validation
    const emailField = $('#contact-email');
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;

    emailField.on('blur', function() {
        const email = $(this).val();

        if(email && !emailRegex.test(email)) {
            $(this).css('border-color', '#EF4444');
        } else {
            $(this).css('border-color', '#E5E7EB');
        }
    });
    
});