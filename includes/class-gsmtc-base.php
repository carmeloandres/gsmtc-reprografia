<?php
 if ( ! defined( 'ABSPATH' ) ) {die;} ; // para evitar el acceso directo

/**
 * Clase para manejar la gestion de la base de datos
 */

class Gsmtc_Base{

    public $nonce_base;

    function __construct()
    {
        global $wpdb;
        $this->nonce_base = "gsmtc_nonce".NONCE_KEY.date('y-m-d');


        // Acción para añadir un nuevo checkbox a las opciones del producto en la administración y poder activar la opción de reprografia para ese producto
        add_action('woocommerce_product_options_general_product_data',array($this,'admin_product_custom_fields'));
        // Acción para almacenar los product meta tras el submit de la administración de producto
        add_action('woocommerce_admin_process_product_object',array($this,'admin_save_product_meta'));

        //        add_action('after_setup_theme',array($this,'gsmtc_reprografia_product_hooks'));
        // Acción para cargar el producto de reprografia
        add_action('woocommerce_before_single_product',array($this,'gsmtc_reprografia_product_hooks'));
    }


    /**
     * Añade una nueva opción a las opciones del producto en la administración
     */
    public function admin_product_custom_fields() {  // añadir campos propios al detalle de producto
    
        global $woocommerce, $post, $base;
        $state = get_post_meta($post->ID,'reprografia_activado',true);
        echo '<div class="options_group">';
            woocommerce_wp_checkbox(array('id' => 'gsmtc_reprografia_activar_producto', 'label' => __('Activar reprografía?', 'gsmtc'), 'desc_tip' => true, 'description' => __('Si quiere usar este producto con soporte para reprografía, debe marcar la casilla para activarlo".', 'gsmtc'), 'wrapper_class' => 'gsmtc_reprografia_activar_producto', 'value' => $state));
        echo '</div>';
    }

    /**
     * Metodo para almacenar los post_meta de los productos tras el submit del formulario de producto en la administacióm
     */
    public function admin_save_product_meta($product){
        if (isset($_POST['gsmtc_reprografia_activar_producto']))
            update_post_meta($product->id,'reprografia_activado','yes');
        else update_post_meta($product->id,'reprografia_activado','no');
    }


    /**
     * Metodo para limpiar la vista del producto y poder mostra lo que queramos
     */
    public function gsmtc_reprografia_product_hooks(){
        global $post;
        error_log("Se han ejecutado gsmtc_reprografia_product_hooks");

        if ($post != null && $post->ID > 0){
            error_log("Se han ejecutado gsmtc_reprografia_product_hooks, el post->ID es: ".var_export($post->ID,true));

            $state = get_post_meta($post->ID,'reprografia_activado',true);
            if ($state == 'yes'){

                error_log("Se han desactivado los hooks de producto");
                // desactivo todo el contenido de producto que viene de serie
                remove_action( 'woocommerce_before_single_product', 'wc_print_notices', 10 );
                remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_sale_flash', 10 );
                remove_action( 'woocommerce_before_single_product_summary', 'woocommerce_show_product_images', 20 );

                remove_action( 'woocommerce_product_thumbnails', 'woocommerce_show_product_thumbnails', 20 );
     
                remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_title', 5 );
                remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_rating', 10 );
                remove_action( 'woocommerce_single_product_summary', 'woocommerce_template_single_price', 10 );
                ?>
                <div class="contenedor-reprografia">
                    <div class="izquierda"><p>Izquierda</p></div>
                    <div class="centro"><p>Centro</p></div>
                    <div class="derecha"><p>Derecha</p></div>
                </div>
                <div id="root"></div>
                <?php
                woocommerce_form_field('extra_alto', array(
                    'type'          => 'text',
                    'label'         => __('Seleccionar el alto','abc'),
                ),'');

/*                echo '<script>
                let elementoArchivo = document.getElementById("calculadora_archivo_precios");
                console.log("Hola carmelo");
                window.onload = function(){
                    if (elementoArchivo.value == "")
                    {
                        elementoArchivo.setAttribute("type","file");
                        
                        elementoArchivo.setAttribute("type","file");
                        elementoArchivo.onchange = function() {
                            let archivo = elementoArchivo.files[0].name;
                            archivar_asincrona(elementoArchivo.files);
                            
                        };
                    };
    
                    elementoArchivo.onchange = function() {
                        if (elementoArchivo.getAttribute("type") == "file")
                        {
                            let archivo = elementoArchivo.files[0].name;
                            archivar_asincrona(elementoArchivo.files);
                        };
                    };
                };
                </script>'; */
            }

        }
    }

    


    /**
     * Metodo para crear el campo post_meta 'reprografia_activado' para cada post tras la activacion del plugin
     */

    public function crear_post_metas(){

        $args = array(
            'post_type' => 'product'
        );
        $posts = get_posts($args);
        foreach ($posts as $post){
            update_post_meta($post->ID,'reprografia_activado','no');
        }
    }

}
