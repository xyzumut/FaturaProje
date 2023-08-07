<?php 
    
    $paymendo_baglanti = $paymendo_utilities->get_paymendo_connection();

    // Form submit edildi ise yapılacaklar
    if (isset($_POST['operation']) && $_POST['operation'] ==='tahsilat form submit edildi') {

        if ($_POST['paymendo_api_base_url']) {

            if (substr($_POST['paymendo_api_base_url'] , 0, strlen('http://')) == 'http://' || substr($_POST['paymendo_api_base_url'] , 0, strlen('https://')) == 'https://') {
                update_option( 'tahsilat_ayar_api_base_url', $_POST['paymendo_api_base_url']);
                $paymendo_baglanti->set_paymendo_baseURL($_POST['paymendo_api_base_url']);
            }
            else{
                update_option( 'tahsilat_ayar_api_base_url', 'http://'.$_POST['paymendo_api_base_url']);
                $paymendo_baglanti->set_paymendo_baseURL('http://'.$_POST['paymendo_api_base_url']);
            }
        }

        // Değerler formda gelsede gelmesede optionlarda bu verileri ekledik
        update_option( 'tahsilat_ayar_mail', $_POST['paymendo_api_mail'] ?? null);
        $paymendo_baglanti->set_paymendo_mail(get_option( 'tahsilat_ayar_mail'));
        update_option( 'tahsilat_ayar_password', $_POST['paymendo_api_password'] ?? null);
        $paymendo_baglanti->set_paymendo_password(get_option( 'tahsilat_ayar_password'));
        // Değerler formda gelsede gelmesede optionlarda bu verileri ekledik


        // İsteği atıyoruz
        try{
            $response = $paymendo_utilities->loginWithPassword();
            ?>
            <div class="notice notice-success">
                <p><?php _e('Information saved and logged in.', 'Paymendo') ?></p>
            </div> 
        <?php 
        }
        catch (Exception $error){
            update_option( 'tahsilat_ayar_isLogin', 'false');
            // $paymendo_baglanti->set_paymendo_isLogin(false);
            ?>
                <div class="notice notice-error">
                    <p><?php echo $error->getMessage(); ?></p>
                </div> 
            <?php 
        }

       

    }
    // Form submit edildi ise yapılacaklar

    
    // Son Durumu Göstermek İçin oluşturduğum if - else blokları
    else {
        $is_login = get_option( 'tahsilat_ayar_isLogin');
        if (/* $paymendo_baglanti->get_paymendo_isLogin() === null*/ $is_login===null) {//Henüz hiç bir veri girişi olmadı ise
            ?>
                <div class="notice notice-info ">
                    <p><?php _e('Not logged in yet. To log in, enter the information and press submit.', 'Paymendo') ?></p>
                </div> 
            <?php 
        }
        else if (/*$paymendo_baglanti->get_paymendo_isLogin() === false */ $is_login === 'false' ) {// veri girişi eksik veya yanlış olmuşsa
            ?>
                <div class="notice notice-warning ">
                    <p><?php _e('Login failed because information is missing or incorrectly saved.', 'Paymendo') ?></p>
                </div> 
            <?php 
        }
        else if(/*$paymendo_baglanti->get_paymendo_isLogin() === true */ $is_login === 'true' && !isset($_POST['operation'])){// veri girişi temiz olmuşsa
            ?>
                <div class="notice notice-success">
                    <p><?php _e('Successfully logged in.', 'Paymendo') ?></p>
                </div> 
            <?php 
        }
    }
    
    // Son Durumu Göstermek İçin oluşturduğum if - else blokları
?>
<div id="tahsilat_main_container">
    <h1 id="tahsilat_header"><?php _e('Paymendo Settings Page', 'Paymendo') ?></h1>
    <form action="" id="paymende_settings_form" method="post">
        <div class="tahsilat_form_input_container">
            <label class="tahsilat_form_label">
                <div class="tahsilat_input_text">Base API URL</div> 
                <input type="text" name="paymendo_api_base_url" id="paymendo_api_base_url" class="paymendo_settings_input tahsilat_settings_url_input" value=<?php echo $paymendo_baglanti->get_paymendo_baseURL() ?> >
            </label>
        </div>
        <div class="tahsilat_form_input_container">
            <label class="tahsilat_form_label input-box">
                <div class="tahsilat_input_text "> <?php _e('E-mail Address', 'Paymendo') ?>  </div> 
                <input type="mail" name="paymendo_api_mail" id="paymendo_api_mail" class="paymendo_settings_input" value=<?php echo $paymendo_baglanti->get_paymendo_mail(); ?>>
                <span id="text"></span>
            </label>
        </div>
        <div class="tahsilat_form_input_container">
            <label class="tahsilat_form_label">
                <div class="tahsilat_input_text"><?php _e('Password', 'Paymendo') ?></div> 
                <input type="password" name="paymendo_api_password" id="paymendo_api_password" class="paymendo_settings_input" value=<?php echo $paymendo_baglanti->get_paymendo_password(); ?>>
            </label>
        </div>
        <input type="hidden" value="tahsilat form submit edildi" name="operation">
        <button id="tahsilat_form_submit" type="submit"> <?php _e('Submit', 'Paymendo') ?> </button>
    </form>
</div>