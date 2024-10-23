<?php
/*
Plugin Name: Tiempo de Lectura Plugin
Description: Este plugin calcula el tiempo de lectura aproximado de un post y permite configurar la velocidad de lectura desde el backoffice. Para configurar ir a -> Ajustes / Tiempo de Lectura
Version: 1.0
Author: Konstantin K.
Author URI: https://webdesignerk.com/wordpress/plugins/como-mostrar-tiempo-de-lectura-aproximado-en-wordpress/
*/

// Función para calcular el tiempo de lectura aproximado
function tiempo_lectura_shortcode() {
    // Obtener el contenido del post actual
    $content = get_post_field( 'post_content', get_the_ID() );

    // Verificar si ya se ha mostrado el tiempo de lectura mediante el shortcode
    if (strpos($content, '[tiempo_lectura]') !== false) {
        return '';
    }

    // Calcular la cantidad de palabras en el contenido
    $word_count = str_word_count( strip_tags( $content ) );

    // Obtener la velocidad de lectura desde las opciones
    $palabras_por_minuto = get_option( 'palabras_por_minuto', 200 );

    // Calcular el tiempo aproximado de lectura en minutos
    $tiempo_lectura = ceil( $word_count / $palabras_por_minuto );

    // Retornar el tiempo de lectura dentro de un div con el estilo proporcionado
    return '<div style="width: auto; margin-bottom: 14px; border: solid 1px #cacaca; padding: 10px; border-radius: 8px; background: #fbfbfb;">' . $tiempo_lectura . ' minutos de lectura</div>';
}

// Registrar el shortcode para usar la función
add_shortcode( 'tiempo_lectura', 'tiempo_lectura_shortcode' );

// Función para agregar el menú de opciones en el backoffice
function tiempo_lectura_options_menu() {
    add_options_page(
        'Configuración de Tiempo de Lectura',
        'Tiempo de Lectura',
        'manage_options',
        'tiempo-lectura-options',
        'tiempo_lectura_options_page'
    );
}
add_action( 'admin_menu', 'tiempo_lectura_options_menu' );

// Función para mostrar la página de opciones en el backoffice
function tiempo_lectura_options_page() {
    ?>
    <div class="wrap">
        <h2>Configuración de Tiempo de Lectura</h2>
        <p>Puedes agregar manualmente el tiempo de lectura con el shortcode <code>[tiempo_lectura]</code> dentro de tu post o con <code>&lt;?php echo do_shortcode('[tiempo_lectura]'); ?&gt;</code> en tu código personalizado.</p>
        <form method="post" action="options.php">
            <?php settings_fields( 'tiempo_lectura_options_group' ); ?>
            <?php do_settings_sections( 'tiempo-lectura-options' ); ?>
            <?php submit_button(); ?>
        </form>
    </div>
    <?php
}

// Función para registrar y añadir campos de opciones
function tiempo_lectura_register_settings() {
    register_setting( 'tiempo_lectura_options_group', 'palabras_por_minuto', 'intval' );
    register_setting( 'tiempo_lectura_options_group', 'tiempo_lectura_auto_show', 'intval' );

    add_settings_section(
        'tiempo_lectura_options_section',
        'Configuración de Velocidad de Lectura',
        'tiempo_lectura_options_section_callback',
        'tiempo-lectura-options'
    );

    add_settings_field(
        'palabras_por_minuto_field',
        'Palabras por Minuto:',
        'palabras_por_minuto_field_callback',
        'tiempo-lectura-options',
        'tiempo_lectura_options_section'
    );

    add_settings_field(
        'tiempo_lectura_auto_show_field',
        'Mostrar automáticamente el tiempo de lectura en el post:',
        'tiempo_lectura_auto_show_field_callback',
        'tiempo-lectura-options',
        'tiempo_lectura_options_section'
    );
}
add_action( 'admin_init', 'tiempo_lectura_register_settings' );

// Callback para la sección de opciones
function tiempo_lectura_options_section_callback() {
    echo '<p>Configure la velocidad de lectura en palabras por minuto.</p>';
    echo '<p>La velocidad de lectura promedio de las personas es de 200 palabras por minuto. Si escribió un artículo de 1000 palabras, necesitará aproximadamente 5 minutos para terminarlo y comprenderlo.</p>';
}

// Callback para el campo de palabras por minuto
function palabras_por_minuto_field_callback() {
    $value = get_option( 'palabras_por_minuto', 200 );
    echo '<input type="number" min="1" name="palabras_por_minuto" value="' . esc_attr( $value ) . '" />';
}

// Callback para el campo de visualización automática
function tiempo_lectura_auto_show_field_callback() {
    $auto_show = get_option( 'tiempo_lectura_auto_show', false );
    echo '<input type="checkbox" name="tiempo_lectura_auto_show" value="1" ' . checked( $auto_show, 1, false ) . ' />';
}

// Función para agregar el tiempo de lectura encima del título en los posts individuales
function tiempo_lectura_auto_show_single_post( $content ) {
    // Verificar si estamos en un post individual y la opción de visualización automática está activada
    if ( is_single() && get_option( 'tiempo_lectura_auto_show', false ) ) {
        // Obtener el tiempo de lectura
        $tiempo_lectura = tiempo_lectura_shortcode();
        
        // Buscar el primer encabezado h1 en el contenido del post
        $posicion_h1 = strpos($content, '<h1');
        
        if ($posicion_h1 !== false) {
            // Si se encuentra un encabezado h1, insertar el tiempo de lectura justo antes
            $content = substr_replace($content, $tiempo_lectura, $posicion_h1, 0);
        } else {
            // Si no se encuentra un encabezado h1, agregar el tiempo de lectura al inicio del contenido
            $content = $tiempo_lectura . $content;
        }
    }

    return $content;
}
add_filter( 'the_content', 'tiempo_lectura_auto_show_single_post' );
?>
