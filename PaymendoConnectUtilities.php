<?php

    class PaymendoConnectUtilities{

        private PaymendoConnection $paymendo_connection;

        public function set_paymendo_connection($paymendo_connection){ $this->paymendo_connection = $paymendo_connection; }
        public function get_paymendo_connection(){return $this->paymendo_connection;}
        
        public function handleLogin($response, $isRefreshToken = false){

            if (isset($response['status']) && ($response['status'] === false || $response['status'] === 'false') && isset($response['errors'])) {
                # Bu if bloğuna girdi ise email garanti yanlış gönderilmiştir
                if ($isRefreshToken) {
                    $this->loginWithPassword();
                }
                throw new Exception(__('Email Address Sent Wrong', 'Paymendo'));
            }
            else if(isset($response['error']) && ($response['error'] === 'invalid_grant' || $response['error'] === 'invalid_request')) {
                # Bu else-if bloğuna girdiyse şifre hatalıdır
                if ($isRefreshToken) {
                    $this->loginWithPassword();
                }
                throw new Exception(__('Password Sent Wrong', 'Paymendo'));
            }
            update_option( 'tahsilat_ayar_token_expires_in', $response['expires_in']+time()); # access_token'ın öleceği zamanı belirlediğimiz yer
            $this->get_paymendo_connection()->set_paymendo_expiresIN(get_option('tahsilat_ayar_token_expires_in'));
            update_option( 'tahsilat_ayar_access_token', $response['access_token']);
            $this->get_paymendo_connection()->set_paymendo_accessToken($response['access_token']);
            update_option( 'tahsilat_ayar_refresh_token', $response['refresh_token']);
            $this->get_paymendo_connection()->set_paymendo_refreshToken($response['refresh_token']);
            update_option( 'tahsilat_ayar_isLogin', 'true');
            // $this->get_paymendo_connection()->set_paymendo_isLogin(true);
        }

        public function loginWithPassword(){
            // die('DENEME');
            $response = $this->paymendoRequest(
                'POST', 
                $this->get_paymendo_connection()::tahsilat_api_login_url,
                (object) [
                    'data' => [
                        'attributes' => [
                            'grant_type'=> 'password',
                            'login'=> $this->get_paymendo_connection()->get_paymendo_mail(),
                            'auth'=> $this->get_paymendo_connection()->get_paymendo_password()
                        ]
                    ]
                ]
            );
            return $this->handleLogin($response);
        }

        public function loginWithRefreshToken(){

            $response = $this->paymendoRequest(
                'POST', 
                $this->get_paymendo_connection()::tahsilat_api_login_url,
                (object) [
                    'data' => [
                        'attributes' => [
                            'grant_type'=> 'refresh_token',
                            'login'=> $this->get_paymendo_connection()->get_paymendo_mail(),
                            'auth'=> $this->get_paymendo_connection()->get_paymendo_refreshToken()
                        ]
                    ]
                ]
            );
            return $this->handleLogin($response,true);
        }

        public function paymendoRequest($method = 'GET', $url = '', $data = array(), $refresh=false) {
            $baseUrl = $this->get_paymendo_connection()->get_paymendo_baseURL();
            $loginSwitch = PaymendoConnection::tahsilat_api_login_url === $url ? true : false;

            $method = strtoupper($method);
            $request_function = "wp_remote_get";
            if($method !== "GET"){
                $request_function = "wp_remote_post";
            }
            $headers = array(
                'Content-Type' => 'application/json'
            );
            if( strpos($url, PaymendoConnection::tahsilat_api_login_url) === false ) {
                $headers['Authorization'] = 'Bearer ' . $this->getAccessToken($refresh);
            }
            // var_dump($headers);die;
            $request_options = array(
                'method' => $method,
                'headers' => $headers
            );
            if($method !== "GET" && !empty($data)){
                $request_options['body'] = wp_json_encode($data);
            }
            if(substr($url, 0, 4) !== 'http' && substr($url,0,1) !== '/'){
                $url  = '/' . $url;
            }
            if (strpos($url, $baseUrl) === false) {
                $url = $this->get_paymendo_connection()->get_paymendo_baseURL() . $url;
            }
            $responseRaw = $request_function($url, (object)$request_options);
            if (is_wp_error($responseRaw)) {
                throw new Exception(__('There was a problem, try again later.', 'Paymendo'));
            }

            $status_code = wp_remote_retrieve_response_code($responseRaw);
            $response =  json_decode( wp_remote_retrieve_body( $responseRaw ), true);

            if ($status_code>=200 && $status_code<300) {
                return $response;
            }

            if ($status_code === 401 || (isset($response['error']) && $response['error'] === 'invalid_grant')) {
                if($loginSwitch)
                    throw new Exception(__('The information entered was incorrect, so the login failed.', 'Paymendo'));

                if($refresh)
                    throw new Exception(__('Authentication error, please sign in again.', 'Paymendo'));
                
                return $this->paymendoRequest($method, $url, $data, true);
            }

            throw new Exception(__('There was a problem, try again later.', 'Paymendo'));
        }

        public function getAccessToken($refresh=false) : string {
            if ($this->paymendo_connection->get_paymendo_expiresIN() < time() || $refresh===true) {
                try{
                    $this->loginWithRefreshToken();
                    return $this->paymendo_connection->get_paymendo_accessToken();
                }
                catch (Exception $error){
                    try{
                        $this->loginWithPassword();
                    }
                    catch (Exception $error)
                    {
                        throw new Exception(__('Authentication error, please sign in again.', 'Paymendo'));
                    }
                }
            }
            return $this->paymendo_connection->get_paymendo_accessToken();
        }

        public function createOrder($data){
            $url = $this->get_paymendo_connection()::tahsilat_get_post_api_url;
            $data = (object) [
                'data' => [
                    'attributes' => [
                        'amount'=>$data['amount'],
                        'notes'=> $data['notes'],
                        'currency_code'=> $data['currency_code']
                    ]
                ]
                    ];
            return $this->paymendoRequest('POST', $url, $data);
        }

        public function getOrder($id=null, $page_size=8, $page_number=0){
            $url = $this->get_paymendo_connection()->get_paymendo_baseURL().$this->get_paymendo_connection()::tahsilat_get_post_api_url;
            if (!is_null($id)) {
                $url = $url.'/'.$id;
            }
            else {
                $url = $url."/?&page[size]=$page_size&page[number]=$page_number";//http build query
            }
            return $this->paymendoRequest('GET', $url);
        }

        public function makePayment($credit_card_data){
            $url = PaymendoConnection::tahsilat_payment_api_url;
            $data = (object) [
                'data' => [
                    'attributes' => [
                        'cc_cvv'=> $credit_card_data['cc_cvv'],
                        'cc_number'=> $credit_card_data['cc_number'],
                        'cc_exp'=> $credit_card_data['cc_exp'],
                        'cc_holder'=> $credit_card_data['cc_holder'],
                        'order_id'=> $credit_card_data['order_id'],
                        'installment'=> '1'
                    ]
                ]
            ];
            return $this->paymendoRequest('POST', $url, $data);
        }
    }
?>