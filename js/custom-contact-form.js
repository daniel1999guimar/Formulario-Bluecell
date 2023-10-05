jQuery(document).ready(function($) {
    // Esta función se ejecuta cuando el documento HTML ha cargado completamente.

    $('#custom-contact-form').on('submit', function(e) {
        // Previene la acción predeterminada del formulario (enviar a una nueva página).
        e.preventDefault();
        
        // Variable que indica si el formulario es válido.
        var formValid = true;

        // Resetear los mensajes de error
        $('.error-message').remove();

        // Obtener los valores de los campos del formulario.
        var nombre = $('input[name="nombre"]').val();
        var email = $('input[name="email"]').val();
        var telefono = $('input[name="telefono"]').val();
        var mensaje = $('input[name="mensaje"]').val();
        var asunto = $('input[name="asunto"]').val();
        var acepto = $('.acepto').is(':checked');

        // Validar los campos uno por uno.
        if (nombre == '') {
            $('input[name="nombre"]').after('<span class="error-message">El campo es requerido</span>');
            formValid = false;
            return;
        }

        // Validar el formato del correo electrónico.
        var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        if (email == '' || !email.match(emailRegex)) {
            $('input[name="email"]').after('<span class="error-message">Ingrese un email válido</span>');
            formValid = false;
            return;
        }

        // Validar el formato del número de teléfono.
        var telefonoRegex = /^\d+$/;
        if (telefono == '' || !telefono.match(telefonoRegex)) {
            $('input[name="telefono"]').after('<span class="error-message">Ingrese un número de teléfono válido</span>');
            formValid = false;
            return;
        }
        // Validar el campo es requerido.
        if (mensaje == '') {
            $('input[name="mensaje"]').after('<span class="error-message">El campo es requerido</span>');
            formValid = false;
            return;
        }
        // Validar el campo es requerido.
        if (asunto == '') {
            $('input[name="asunto"]').after('<span class="error-message">El campo es requerido</span>');
            formValid = false;
            return;
        }
        // Validar el campo es requerido.
        if (!acepto) {
            $('.acepto').after('<span class="error-message">Debe aceptar las políticas</span>');
            formValid = false;
            return;
        }

        if (formValid) {
            // Si el formulario es válido, prepara los datos para enviar por Ajax.
            var formData = $(this).serialize();
        }
        
        // Enviar el formulario por Ajax.
        $.ajax({
            url: customAjax.ajaxurl,
            type: 'POST',
            data: formData + '&action=custom_contact_form_submit',
            success: function(response) {
                // Cuando la solicitud Ajax tiene éxito, muestra el mensaje de éxito y reinicia el formulario.
                $('#form-message').html(response);
                $('#custom-contact-form')[0].reset();
            }
        });

    });

});