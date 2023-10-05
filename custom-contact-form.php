<?php
/*
Plugin Name: Formulario Bluecell
Description: Añade un formulario de contacto despues del contenido de una entrada
Version: 1.0
Author: Daniel Rodríguez Delgado
*/

// Crear la tabla en la base de datos al activar el plugin
function custom_contact_form_create_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'contact_entries'; // Nombre de la tabla en la base de datos

    $charset_collate = $wpdb->get_charset_collate(); // Collate para la base de datos

    // Consulta SQL para crear la tabla
    $sql = "CREATE TABLE $table_name ( 
        id mediumint(9) NOT NULL AUTO_INCREMENT,
        nombre varchar(255) NOT NULL,
        email varchar(255) NOT NULL,
        telefono varchar(20) NOT NULL,
        mensaje text NOT NULL,
        asunto varchar(255) NOT NULL,
        aceptacion_politicas tinyint(1) NOT NULL,
        fecha datetime DEFAULT CURRENT_TIMESTAMP NOT NULL,
        PRIMARY KEY  (id)
    ) $charset_collate;";

    require_once ABSPATH . 'wp-admin/includes/upgrade.php'; // Incluir archivo necesario
    dbDelta($sql); // Ejecutar la consulta para crear la tabla
}



register_activation_hook(__FILE__, 'custom_contact_form_create_table'); // Registrar función para ejecutar al activar el plugin



// Eliminar la tabla al desinstalar el plugin
function custom_contact_form_drop_table() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'contact_entries'; // Nombre de la tabla en la base de datos

    $sql = "DROP TABLE IF EXISTS $table_name;"; // Consulta SQL para eliminar la tabla

    $wpdb->query($sql); // Ejecutar la consulta para eliminar la tabla
}

register_uninstall_hook(__FILE__, 'custom_contact_form_drop_table'); // Registrar función para ejecutar al desinstalar el plugin

// Añadir el formulario después del contenido
function custom_contact_form_after_content($content) {
    if (is_single() && in_the_loop() && is_main_query()) { // Comprobar si estamos en una entrada individual
        $form = '
        <div class="custom-contact-form">
            <h2>Contacto</h2>
            <form id="custom-contact-form" action="#" method="post">
                <label for="nombre">Nombre:</label>
                <input type="text" name="nombre" required><br>

                <label for="email">Email:</label>
                <input type="email" name="email" required><br>

                <label for="telefono">Teléfono:</label>
                <input type="tel" name="telefono" required><br>

                <label for="mensaje">Mensaje:</label>
                <input type="text" name="mensaje" required></input><br>

                <label for="asunto">Asunto:</label>
                <input type="text" name="asunto" required><br>

                <label for="aceptar">Acepto las políticas:</label>
                <input class="acepto" type="checkbox" name="aceptar" required><br>

                <button><input type="submit" value="Enviar"></button>
            </form>
        </div>
        <div id="form-message"></div>';

        $content .= $form; // Añadir el formulario al contenido de la entrada
    }

    return $content;
}

// Funciones para cargar scripts y estilos

function custom_contact_form_scripts() {
    wp_enqueue_script('custom-contact-form', plugin_dir_url(__FILE__) . 'js/custom-contact-form.js', array('jquery'), '1.0', false);

    // Añade una variable personalizada para la URL de administración de Ajax
    wp_localize_script('custom-contact-form', 'customAjax', array('ajaxurl' => admin_url('admin-ajax.php')));

    // Añade tu archivo CSS
    wp_enqueue_style('custom-contact-form-style', plugin_dir_url(__FILE__) . 'css/custom-contact-form.css');

}

function cargar_librerias_admin() {
    wp_enqueue_script('jquery', 'https://code.jquery.com/jquery-3.7.0.js', array(), '3.7.0', false);

    // Registrar y cargar DataTables
    wp_enqueue_script('datatables', 'https://cdn.datatables.net/1.13.6/js/jquery.dataTables.min.js', array('jquery'), '1.13.6', false);

    // Registrar y cargar DataTables Buttons
    wp_enqueue_script('datatables-buttons', 'https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js', array('jquery'), '2.4.1', false);

    // Registrar y cargar JSZip
    wp_enqueue_script('jszip', 'https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js', array(), '3.10.1', false);

    // Registrar y cargar pdfmake
    wp_enqueue_script('pdfmake', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js', array(), '0.1.53', false);
    wp_enqueue_script('vfs_fonts', 'https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js', array(), '0.1.53', false);

    // Registrar y cargar DataTables Buttons HTML5
    wp_enqueue_script('buttons-html5', 'https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js', array('datatables-buttons'), '2.4.1', false);
    wp_enqueue_script('custom-admin-scripts', plugin_dir_url(__FILE__) . 'js/custom-admin-scripts.js', array('jquery'), '1.0', true);

    wp_enqueue_style('datatables-css', plugin_dir_url(__FILE__) . 'libraries/jquery.dataTables.min.css');
    wp_enqueue_style('datatables-buttons-css', plugin_dir_url(__FILE__) . 'libraries/buttons.dataTables.min.css');
}

// Carga scripts y estilos en el área de administración de WordPress.
add_action('admin_enqueue_scripts', 'cargar_librerias_admin');

// Carga scripts y estilos en el frontend
add_action('wp_enqueue_scripts', 'custom_contact_form_scripts');

// Añade una página de administración para mostrar los datos del formulario
function custom_contact_form_admin_page() {
    add_menu_page(
        'Formulario de Contacto', // Título de la página en el menú de administración
        'Formulario de Contacto', // Título del menú
        'manage_options', // Capacidad requerida para acceder a la página
        'custom-contact-form-data', // Slug de la página
        'custom_contact_form_data_page' // Función que mostrará la página
    );
}

// Función que mostrará la página de administración de datos del formulario
function custom_contact_form_data_page() {
    global $wpdb;
    $table_name = $wpdb->prefix . 'contact_entries';
    $entries = $wpdb->get_results("SELECT * FROM $table_name", ARRAY_A);

    echo '<h1>Datos del Formulario de Contacto</h1>';
    echo '<table id="example" class="display" style="width:100%">';
    echo '<thead><tr><th>ID</th><th>Nombre</th><th>Email</th><th>Teléfono</th><th>Mensaje</th><th>Asunto</th><th>Aceptación Políticas</th><th>Fecha</th></tr></thead>';
    echo '<tbody>';

    foreach ($entries as $entry) {
        echo '<tr>';
        echo '<td>' . $entry['id'] . '</td>';
        echo '<td>' . $entry['nombre'] . '</td>';
        echo '<td>' . $entry['email'] . '</td>';
        echo '<td>' . $entry['telefono'] . '</td>';
        echo '<td>' . $entry['mensaje'] . '</td>';
        echo '<td>' . $entry['asunto'] . '</td>';
        echo '<td>' . ($entry['aceptacion_politicas'] ? 'Sí' : 'No') . '</td>';
        echo '<td>' . $entry['fecha'] . '</td>';
        echo '</tr>';
    }

    echo '</tbody></table>';
}

add_action('admin_menu', 'custom_contact_form_admin_page'); // Añadir la página al menú de administración

// Añade una función de acción para procesar el formulario
function custom_contact_form_submit() {
    global $wpdb;

    $table_name = $wpdb->prefix . 'contact_entries';

    $nombre = $_POST['nombre'];
    $email = $_POST['email'];
    $telefono = $_POST['telefono'];
    $mensaje = $_POST['mensaje'];
    $asunto = $_POST['asunto'];
    $aceptacion_politicas = isset($_POST['aceptar']) ? 1 : 0;

    $wpdb->insert(
        $table_name,
        array(
            'nombre' => $nombre,
            'email' => $email,
            'telefono' => $telefono,
            'mensaje' => $mensaje,
            'asunto' => $asunto,
            'aceptacion_politicas' => $aceptacion_politicas
        )
    );

    echo '¡Formulario enviado con éxito!';
    wp_die();
}

add_action('wp_ajax_custom_contact_form_submit', 'custom_contact_form_submit'); // Para usuarios autenticados
add_action('wp_ajax_nopriv_custom_contact_form_submit', 'custom_contact_form_submit'); // Para usuarios no autenticados

add_filter('the_content', 'custom_contact_form_after_content'); // Añadir el formulario después del contenido




