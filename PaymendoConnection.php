<?php
    class PaymendoConnection{

        private $tahsilat_ayar_isLogin;

        private $tahsilat_ayar_api_base_url;
        private $tahsilat_ayar_password;
        private $tahsilat_ayar_mail;
        
        private $tahsilat_ayar_access_token;
        private $tahsilat_ayar_refresh_token;
        private $tahsilat_ayar_token_expires_in;
        
        const tahsilat_api_login_url = '/login';
        const tahsilat_payment_api_url = '/api/v2/payment/make';
        const tahsilat_get_post_api_url = '/api/v2/order' ;

        // public function get_paymendo_isLogin(){return $this->tahsilat_ayar_isLogin;}
        public function get_paymendo_baseURL(){return $this->tahsilat_ayar_api_base_url;}
        public function get_paymendo_password(){return $this->tahsilat_ayar_password;}
        public function get_paymendo_mail(){return $this->tahsilat_ayar_mail;}
        public function get_paymendo_accessToken(){return $this->tahsilat_ayar_access_token;}
        public function get_paymendo_refreshToken(){return $this->tahsilat_ayar_refresh_token;}
        public function get_paymendo_expiresIN(){return $this->tahsilat_ayar_token_expires_in;}
        

        // public function set_paymendo_isLogin($new){$this->tahsilat_ayar_isLogin = $new;}
        public function set_paymendo_baseURL($new){$this->tahsilat_ayar_api_base_url = $new;}
        public function set_paymendo_password($new){$this->tahsilat_ayar_password = $new;}
        public function set_paymendo_mail($new){$this->tahsilat_ayar_mail = $new;}
        public function set_paymendo_accessToken($new){$this->tahsilat_ayar_access_token = $new;}
        public function set_paymendo_refreshToken($new){$this->tahsilat_ayar_refresh_token = $new;}
        public function set_paymendo_expiresIN($new){$this->tahsilat_ayar_token_expires_in = $new;}
    }   
?>