<?php
/**
 * @package Fatura
 * @version 1.7.2
 */
/*
Plugin Name: Fatura 
Description: Fatura Eklentisi
Author: Umut Gedik
Version: 1.0.0
Text Domain: Paymendo
Domain Path: /lang
*/
    /* 
    Subversionu ve dili araştıracağım 
    Linkler:
    http://i18n.svn.wordpress.org/tools/trunk/
    https://subversion.apache.org/contributing.html
    */


    /*Public için JavaScript ve CSS'i dahil et*/
    require (__DIR__).'/PaymendoConnectUtilities.php';
    require (__DIR__).'/PaymendoConnection.php';

    add_action('init', function () {
        
        wp_register_script( 'general_order_script', plugin_dir_url( __FILE__ ).'/public/assets/genel_assets/faturalar_genel_script.js', null,'',true);
        wp_register_style( 'general_order_style', plugin_dir_url( __FILE__ ).'/public/assets/genel_assets/faturalar_genel_style.css');

        wp_register_script( 'payment_page_script', plugin_dir_url( __FILE__ ).'/public/assets/payment_page/fatura_odeme_sayfasi_script.js', null,'',true);
        wp_register_style( 'payment_page_style', plugin_dir_url( __FILE__ ).'/public/assets/payment_page/fatura_odeme_sayfasi_style.css');
        
        wp_register_script( 'orders_page_script', plugin_dir_url( __FILE__ ).'/public/assets/orders_page/faturalar_sayfasi_script.js', null,'',true);
        wp_register_style( 'orders_page_style', plugin_dir_url( __FILE__ ).'/public/assets/orders_page/faturalar_sayfasi_style.css');

        $odeme_sayfasi_id = (int)get_option('odeme_sayfasi_id');
        $faturalar_sayfasi_id = (int)get_option('faturalar_sayfasi_id');
        $odeme_sayfasi = array(
            'ID' => $odeme_sayfasi_id,
            'post_title' => __('Payment Page', 'Paymendo')
        );
        wp_update_post($odeme_sayfasi);
        $fatura_sayfasi = array(
            'ID' => $faturalar_sayfasi_id,
            'post_title' => __('Orders Page', 'Paymendo')
        );
        wp_update_post($fatura_sayfasi);
    });
    add_action('wp_enqueue_scripts', function (){

        wp_enqueue_script('general_order_script');
        wp_enqueue_style( 'general_order_style' );

        wp_enqueue_script('payment_page_script');
        wp_enqueue_style( 'payment_page_style' );

        wp_enqueue_script('orders_page_script');
        wp_enqueue_style( 'orders_page_style' );

    });
    /*Public için JavaScript ve CSS'i dahil et*/


    /*Admin için JavaScript ve CSS'i dahil et*/
    add_action( 'admin_enqueue_scripts', function () {
        wp_enqueue_script( 'payment_page_admin_script', plugin_dir_url( __FILE__ ).'/admin/assets/fature_proje_settings.js', array(), '', true);
        wp_enqueue_style( 'payment_page_admin_style', plugin_dir_url( __FILE__ ).'/admin/assets/fature_proje_settings.css');
    });
    /*Admin için JavaScript ve CSS'i dahil et*/


    /* Admin Menü Oluştur */
    add_action('admin_menu', 'menu_olustur');
    function menu_olustur(){
        add_menu_page( 
            'Tahsilat', 
            __('Paymendo Settings', 'Paymendo'), 
            'manage_options', 
            'tahsilat-ayar', 
            function () {
                $paymendo_connection = new PaymendoConnection();
                // $paymendo_connection->set_paymendo_isLogin(json_decode(get_option( 'tahsilat_ayar_isLogin' )) ?? null);
                $paymendo_connection->set_paymendo_baseURL( get_option('tahsilat_ayar_api_base_url') ?? null);
                $paymendo_connection->set_paymendo_password( get_option('tahsilat_ayar_password') ?? null);
                $paymendo_connection->set_paymendo_mail( get_option('tahsilat_ayar_mail') ?? null);
                $paymendo_connection->set_paymendo_accessToken( get_option('tahsilat_ayar_access_token') ?? null);
                $paymendo_connection->set_paymendo_refreshToken( get_option('tahsilat_ayar_refresh_token') ?? null);
                $paymendo_connection->set_paymendo_expiresIN( get_option('tahsilat_ayar_token_expires_in') ?? null);
                
                $paymendo_utilities = new PaymendoConnectUtilities();
                $paymendo_utilities->set_paymendo_connection($paymendo_connection);
                require (__DIR__).'/admin/partials/settings/fature_proje_settings.php';
            }, 
            'dashicons-admin-settings', 
            66
        );
    }
    /* Admin Menü Oluştur */

    add_action('wp_ajax_paymendo_make_payment', 'make_payment');
    add_action('wp_ajax_nopriv_paymendo_make_payment', 'make_payment');
    function make_payment(){
        $paymendo_connection = new PaymendoConnection();
        // $paymendo_connection->set_paymendo_isLogin(json_decode(get_option( 'tahsilat_ayar_isLogin' )) ?? null);
        $paymendo_connection->set_paymendo_baseURL( get_option('tahsilat_ayar_api_base_url') ?? null);
        $paymendo_connection->set_paymendo_password( get_option('tahsilat_ayar_password') ?? null);
        $paymendo_connection->set_paymendo_mail( get_option('tahsilat_ayar_mail') ?? null);
        $paymendo_connection->set_paymendo_accessToken( get_option('tahsilat_ayar_access_token') ?? null);
        $paymendo_connection->set_paymendo_refreshToken( get_option('tahsilat_ayar_refresh_token') ?? null);
        $paymendo_connection->set_paymendo_expiresIN( get_option('tahsilat_ayar_token_expires_in') ?? null);
                
        $paymendo_utilities = new PaymendoConnectUtilities();
        $paymendo_utilities->set_paymendo_connection($paymendo_connection);
        
        $credit_card_data = [
            'cc_cvv' => $_POST['creditcard_securitycode'],
            'cc_number' => str_replace(' ', '', $_POST['creditcard_cardnumber']),
            'cc_exp' => $_POST['creditcard_expirationdate'],
            'cc_holder' => $_POST['creditcard_ownerName'],
            'order_id' => $_POST['order_id']
        ];

        try{
            $response = $paymendo_utilities->makePayment($credit_card_data);
            if (isset($response['status']) && $response['status'] == 'true') {
                $form = $response['data']['attributes']['form'];
                $form = html_entity_decode($form);
                echo $form;
            }
            else {
                die('Error!');
            }
        }
        catch (Exception $error){
            return '<h4>Bir hata oluştu, daha sonra tekrar deneyin.</h4>';
        }

    }

    /* Short Code Oluştur */
    add_shortcode( 'odeme_sayfasi', function(){
        $paymendo_connection = new PaymendoConnection();
        // $paymendo_connection->set_paymendo_isLogin(json_decode(get_option( 'tahsilat_ayar_isLogin' )) ?? null);
        $paymendo_connection->set_paymendo_baseURL( get_option('tahsilat_ayar_api_base_url') ?? null);
        $paymendo_connection->set_paymendo_password( get_option('tahsilat_ayar_password') ?? null);
        $paymendo_connection->set_paymendo_mail( get_option('tahsilat_ayar_mail') ?? null);
        $paymendo_connection->set_paymendo_accessToken( get_option('tahsilat_ayar_access_token') ?? null);
        $paymendo_connection->set_paymendo_refreshToken( get_option('tahsilat_ayar_refresh_token') ?? null);
        $paymendo_connection->set_paymendo_expiresIN( get_option('tahsilat_ayar_token_expires_in') ?? null);

        $paymendo_utilities = new PaymendoConnectUtilities();
        $paymendo_utilities->set_paymendo_connection($paymendo_connection);
        require (__DIR__).'/public/partials/payment/credit_card.php';
        return renderTemplate($paymendo_utilities);
    });
    add_shortcode( 'faturalar_sayfasi', function(){
        $paymendo_connection = new PaymendoConnection();
        // $paymendo_connection->set_paymendo_isLogin(json_decode(get_option( 'tahsilat_ayar_isLogin' )) ?? null);
        $paymendo_connection->set_paymendo_baseURL( get_option('tahsilat_ayar_api_base_url') ?? null);
        $paymendo_connection->set_paymendo_password( get_option('tahsilat_ayar_password') ?? null);
        $paymendo_connection->set_paymendo_mail( get_option('tahsilat_ayar_mail') ?? null);
        $paymendo_connection->set_paymendo_accessToken( get_option('tahsilat_ayar_access_token') ?? null);
        $paymendo_connection->set_paymendo_refreshToken( get_option('tahsilat_ayar_refresh_token') ?? null);
        $paymendo_connection->set_paymendo_expiresIN( get_option('tahsilat_ayar_token_expires_in') ?? null);

        $paymendo_utilities = new PaymendoConnectUtilities();
        $paymendo_utilities->set_paymendo_connection($paymendo_connection);
        require (__DIR__).'/public/partials/orders/orders.php';
        return renderMyOrdersTemplate($paymendo_utilities);
    });
    /* Short Code Oluştur */

    /* Sayfaları oluşturup içlerine shortcode'ları dahit et */
    register_activation_hook(__FILE__, function () {


        // Ödeme sayfasının başlığını ve shortcode'unu tanımladık
        $page_title_odeme_sayfasi = __('Payment Page', 'Paymendo');
        $page_content_odeme_sayfasi = '[odeme_sayfasi]'; 
        // Ödeme sayfasının başlığını ve shortcode'unu tanımladık


        // Ödeme sayfası daha önce oluşturuldumu diye option'dan veri çektik
        $odeme_sayfasi = get_option('odeme_sayfasi_id');
        // Ödeme sayfası daha önce oluşturuldumu diye option'dan veri çektik


        // Çektiğimiz veri boşsa oluşturması için bu if bloğunu yazdık 
        if (empty($odeme_sayfasi)) {

            // Ödeme sayfasını oluşturduk ve optiona bir daha oluşmaması için kaydettik
            $page = array(
                'post_title' => $page_title_odeme_sayfasi,
                'post_content' => $page_content_odeme_sayfasi,
                'post_status' => 'publish',
                'post_type' => 'page',
            );

            update_option('odeme_sayfasi_id', wp_insert_post($page));
            // Ödeme sayfasını oluşturduk ve optiona bir daha oluşmaması için kaydettik

        }
        // Çektiğimiz veri boşsa oluşturması için bu if bloğunu yazdık 


        // Faturalar sayfasının başlığını ve shortcode'unu tanımladık
        $page_title_faturalar_sayfasi = __('Orders Page', 'Paymendo');
        $page_content_faturalar_sayfasi = '[faturalar_sayfasi]'; 
        // Faturalar sayfasının başlığını ve shortcode'unu tanımladık


        // Faturalar sayfası daha önce oluşturuldumu diye option'dan veri çektik
        $faturalar_sayfasi = get_option('faturalar_sayfasi_id');
        // Faturalar sayfası daha önce oluşturuldumu diye option'dan veri çektik


        // Çektiğimiz veri boşsa oluşturması için bu if bloğunu yazdık 
        if (empty($faturalar_sayfasi)) {

            // Faturalar sayfasını oluşturduk ve optiona bir daha oluşmaması için kaydettik
            $page = array(
                'post_title' => $page_title_faturalar_sayfasi,
                'post_content' => $page_content_faturalar_sayfasi,
                'post_status' => 'publish',
                'post_type' => 'page',
            );
            
            update_option('faturalar_sayfasi_id', wp_insert_post($page));
            // Faturalar sayfasını oluşturduk ve optiona bir daha oluşmaması için kaydettik
        
        }
        // Çektiğimiz veri boşsa oluşturması için bu if bloğunu yazdık 

    });
    /* Sayfaları oluşturup içlerine shortcode'ları dahit et */

    
    add_action( 'plugins_loaded', function () {
        //msgfmt -o fatura-tr_TR.mo fatura-tr_TR.po
        $plugin_dir = basename(dirname(__FILE__));
        load_plugin_textdomain( 'Paymendo', false, $plugin_dir . '/lang' );
        //msgfmt -o fatura-tr_TR.mo fatura-tr_TR.po
    } );
    // define( 'FATURA_PLUGIN_DIR', plugin_dir_path( __FILE__ ) );
?>