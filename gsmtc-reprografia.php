<?php
/*
Plugin Name: Gsmtc Reprografia
Description: Plugin para poder utilizar un producto de woocommerce para realizar copias de reprografia según el fichero
             que sube el usuario.
Version:     0.0.1
Author:      Carmelo Andres Desco
Author URI:  https://carmeloandres.com
Text Domain: gsmtc
Domain Path: /Languages
License:     GPLv2 or later
*/

if ( ! defined( 'ABSPATH' ) ) {die;} ; // to prevent direct access

require_once(dirname(__FILE__).'/includes/class-gsmtc-base.php');

$base = new Gsmtc_Base();

/**
 * Función para la activación del plugin
 */

function gsmtc_activate(){
    $base = new Gsmtc_Base();
   // $base->crear_post_metas();
}
register_activation_hook(__FILE__,'gsmtc_activate');   


/**
 * Funcion para el encolado condicional de assets
 */
function gsmtc_reprografia_assets(){
    global $post;

    $state = get_post_meta($post->ID,'reprografia_activado',true);
    if ($state == 'yes'){
        
        error_log('Id producto en el encolado de assets : '.var_export($post->ID,true));
    
        wp_register_style( 'reprografia_css',plugin_dir_url( __FILE__ ) .'assets/css/gsmtc-reprografia.css', false, '0.0.1' );
        wp_enqueue_style( 'reprografia_css' );
    
        wp_register_style( 'aplication_css',plugin_dir_url( __FILE__ ) .'static/css/main.f09f6b2a.css', false, '0.0.1' );
        wp_enqueue_style( 'aplication_css' );

//        wp_register_script('react_18_js',plugin_dir_url( __FILE__ ) .'assets/js/react.js', '18',true);
//        wp_enqueue_script('react_18_js');
    
//        wp_register_script('react_18_dom_js','https://unpkg.com/react-dom@18/umd/react-dom.development.js,false');
//        wp_enqueue_script('react_18_dom_js');
    
        wp_register_script('aplication_js',plugin_dir_url( __FILE__ ) .'static/js/main.ca13f624.js',array(),false,true);
        wp_enqueue_script('aplication_js');
  
        wp_register_script('reprografia_js',plugin_dir_url( __FILE__ ) .'assets/js/gsmtc-reprografia.js', '18',true);
        wp_enqueue_script('reprografia_js');


    }
}
add_action('wp_enqueue_scripts', 'gsmtc_reprografia_assets');

//    add_action('woocommerce_before_add_to_cart_button', 'abc_build_select_field');

    function abc_build_select_field() {       // Se Crea el control de selección y el cambio dinámico de precio
    
        global $product;

        $id = $product->ID;

        $nombre_fichero = get_post_meta($id,'calculadora_archivo_precios',true);

        if ((! ($nombre_fichero === false)) && (! ($nombre_fichero === '')))
        {

            $datos = abc_cargar_archivo_precios($nombre_fichero);
        
            $unidades = $datos['anchos'][0];

            $mecanismos = $datos['matriz'];
        
            $opciones_alto;
        
            for ($contador = 0; $contador < count($datos['altos']); $contador++ )
            {
                $indice = $datos['altos'][$contador];
                $opciones_alto[$indice] = $datos['altos'][$contador].' '.$unidades;
            } 

            woocommerce_form_field('extra_alto', array(
                'type'          => 'select',
                'label'         => __('Seleccionar el alto','abc'),
                'required'      => true,
                'options'       => $opciones_alto,
            ),'');
        
            $opciones_ancho;
        
            for ($contador = 1; $contador < count($datos['anchos']); $contador++ )
            {
                $indice = $datos['anchos'][$contador];
                $indice = strval($datos['anchos'][$contador]);
                $opciones_ancho[$indice] = $datos['anchos'][$contador].' '.$unidades;
            };
        
            woocommerce_form_field('extra_ancho', array(
                'type'          => 'select',
                'label'         => __('Seleccionar el ancho','abc'),
                'required'      => true,
              'options'       => $opciones_ancho,
            ),'');
        
            $base_price = (float) wc_get_price_to_display( $product );
            $currency = get_woocommerce_currency_symbol();
        
        ?>
        <script>
        jQuery(function($){
                const base_price = <?php echo $base_price; ?>;
                const currency = "<?php echo $currency; ?>";
                const mecanismos = <?php echo json_encode($mecanismos); ?>;
                
                let precio;
                
                function calcula_precio (){
                    let alto = $('select[id=extra_alto]').val();
                    let ancho = $('select[id=extra_ancho]').val();

                    let precio = mecanismos[alto][ancho];

                    return precio;
                }

                $(document).ready( function(){
                    let precio = currency + calcula_precio();
                    $('p.price').html( precio );
                });

            $('#extra_alto').on( 'change', function(){
                let precio = currency + calcula_precio();
                $('p.price').html( precio );
            });

            $('#extra_ancho').on( 'change', function(){
                let precio = currency + calcula_precio();
                $('p.price').html( precio );
            });
        });
        </script>
        <?php
        }
    }




    /**
     * Añade una nueva opción a las opciones del producto en la administración
     */
    //add_action('woocommerce_product_options_general_product_data', 'gsmtc_reprografia_product_custom_fields');


    function gsmtc_reprografia_product_custom_fields() {  // añadir campos propios al detalle de producto
    
        global $woocommerce, $post, $base;
        $state = get_post_meta($post->ID,'reprografia_activado',true);
        echo '<div class="options_group">';
            woocommerce_wp_checkbox(array('id' => 'gsmtc_reprografia_activar_producto', 'label' => __('Activar reprografía?', 'gsmtc'), 'desc_tip' => true, 'description' => __('Si quiere usar este producto con soporte para reprografía, debe marcar la casilla para activarlo".', 'gsmtc'), 'wrapper_class' => 'gsmtc_reprografia_activar_producto', 'value' => $state));
        echo '</div>';
        ?>
            <script>
                let ajax_vars = {
                    "url":"<?php echo admin_url('admin-ajax.php') ?>",
                    "nonce":"<?php echo wp_create_nonce($base->nonce_base) ?>",
                    "idproducto":"<?php echo $post->ID; ?>",                    
                };
                let gsmtc_checkbox = document.getElementById("gsmtc_reprografia_activar_producto");

                if (gsmtc_checkbox != null){
                    gsmtc_checkbox.addEventListener("change",() => {
                        if (gsmtc_checkbox.checked){
                            gsmtc_activar_desactivar_reprografia('activar');
                        }else {
                            gsmtc_activar_desactivar_reprografia('desactivar');
                        }
                    });
                }
                function gsmtc_activar_desactivar_reprografia(accion){
        
                    if (ajax_vars.idproducto > 0){
                        let gsmtc_datos = new FormData();
                            gsmtc_datos.append("action","gsmtc_peticion_ajax");
                            gsmtc_datos.append("nonce",ajax_vars.nonce);
                            gsmtc_datos.append("gsmtc_id_producto",ajax_vars.idproducto);
                            gsmtc_datos.append("gsmtc_peticion","activar_desactivar_reprografia");
                            if (accion == 'activar')
                                gsmtc_datos.append("gsmtc_accion","activar");
                            else
                                gsmtc_datos.append("gsmtc_accion","desactivar");

                        fetch(ajax_vars.url,{
                                    method:'POST',
                                    body: gsmtc_datos
                        })
                        .then (response => response.json())
                        .then (response => {
                            console.log(response);
                        });         
                    }
        
                }
              </script>
    <?php
        
    }


    // registro de la funcion en respuesta a todas las peticion ajax
    //add_action( 'wp_ajax_gsmtc_peticion_ajax', 'gsmtc_peticion_ajax' );
    //add_action( 'wp_ajax_nopriv_gsmtc_peticion_ajax', 'gsmtc_peticion_ajax' );  
    
    /**
     * Función para validar y gestionar las peticiones ajax del plugin
     */
    function gsmtc_peticion_ajax(){
        global $base;
        
        $resultado = '';
            
        if (isset($_POST['gsmtc_id_producto']) && isset($_POST['nonce'])) {
            $id_producto = validar($_POST['gsmtc_id_producto']);
            if (wp_verify_nonce($_POST['nonce'],$base->nonce_base)){
                if (isset($_POST['gsmtc_peticion'])){
                    $peticion = validar($_POST['gsmtc_peticion']);
                        
                    switch ($peticion){
                        case 'activar_desactivar_reprografia' :
                            $resultado = gsmtc_peticion_activar_desactivar_reprografia($id_producto);
                            break;
            
                    }

                    error_log (" Se ha verificado el nonce de la funcion 'gsmtc_peticion_ajax' hay peticion".PHP_EOL.var_export($_POST,true)); 
                    error_log (" ficheros : ".PHP_EOL.var_export($_FILES,true));        
                } else error_log (" Se ha verificado el nonce de la funcion 'gsmtc_peticion_ajax' pero no hay peticion".PHP_EOL.var_export($_POST,true));
            } else error_log (" Se ha ejecutado la funcion 'gsmtc_peticion_ajax', pero no se ha validado el nonce ".PHP_EOL.var_export($_POST,true));
        } else error_log (" Se ha ejecutado la funcion 'gsmtc_peticion_ajax', pero no se ha posteado el idproducto o el nonce ".PHP_EOL.var_export($_POST,true));
    
        echo $resultado;
        exit();
    }         

    /**
     * Atiende la petición ajax para activar o desactivar la reprografia en un determinado producto
     */
    function gsmtc_peticion_activar_desactivar_reprografia($id_producto){

        if (isset($_POST['gsmtc_accion'])){
            $accion = validar($_POST['gsmtc_accion']);
            $resultado = false;
            error_log("gsmtc_activar_desactivar..   se ha ejecutado :".PHP_EOL.var_export($_POST,true));
            if ($accion == 'activar'){
                $resultado = update_post_meta($id_producto,'reprografia_activado','yes');
                error_log("accion == 'activar'..   se ha ejecutado :".PHP_EOL.var_export($id_producto,true));
                
            }
            else{
                $resultado = update_post_meta($id_producto,'reprografia_activado','no');
                error_log("accion != 'activar'..   se ha ejecutado :".PHP_EOL.var_export($id_producto,true));

            }

            return json_encode($resultado);
        }
    }

    /**
     *  Función para validar datos provenientes de los formularios
     */
    function validar($input){

        $resultado = trim($input);
        $resultado = stripslashes($resultado);
        $resultado = htmlspecialchars($resultado);
        
        return $resultado;
    }
