
<?php 
    
    function renderMyOrdersTemplate($paymendo_utilities){

        $payment_page_base_url = '';
        $orders_page_base_url = '';
        $paymendo_baglanti = $paymendo_utilities->get_paymendo_connection();
        
        for ($i=0; $i < count(get_pages()) ; $i++) { 
            $page = get_pages()[$i];
            if ($page->ID == get_option( 'odeme_sayfasi_id')) {
                $payment_page_base_url = $page->guid;
            }
            if ($page->ID == get_option( 'faturalar_sayfasi_id')) {
                $orders_page_base_url = $page->guid;
            }
        } 

        if (!isset($_GET['paymendo_page_number'])) {
            $_GET['paymendo_page_number'] = 0;
        }

        if (/*$paymendo_baglanti->get_paymendo_isLogin() == */true ) {

            try{
                $order_data = '';
                $response = $paymendo_utilities->getOrder(null, 7, $_GET['paymendo_page_number'] );

                $max_page = $response['meta']['max_page'];

                $order_data = array_map(function ($order){
                    return [
                        'order_id' => $order['id'],
                        'order_balance' => $order['attributes']['balance'],
                        'order_notes' => $order['attributes']['notes'],
                        'order_created' => $order['attributes']['created'],
                        'order_status' => $order['attributes']['status'],
                        'order_num' => $order['attributes']['order_num'],
                    ];
                            
                }, $response['data']);
                
                $paginator = '<div id="paymendo_paginator_container"><div id="paymendo_paginator_page_numbers_container">';
                for ($i = 0; $i < $max_page+1; $i++){
                    $activeClass = $i ==  $_GET['paymendo_page_number'] ? 'paymendo_paginator_badge_active"' : '';
                    $paginator = $paginator.'<a href="'.$orders_page_base_url.'?paymendo_page_number='.$i.'" class="paymendo_page_number paymendo_paginator_badge '.$activeClass.' "> '.($i+1).' </a>';
                }
                $paginator = $paginator.'</div><div id="paymendo_paginator_current_page">'.__('Current page', 'Paymendo').':<span class="paymendo_paginator_badge paymendo_paginator_badge_active">'.((int)$_GET['paymendo_page_number']+1).'</span></div></div>';

                $render = '<div id="orders_table_container">'.$paginator;

                $render = $render.'
                    <table id="orders_table">
                        <thead>
                            <tr class="orders_table_row">
                                <th class="orders_table_id">'.__('ID', 'Paymendo').'</th>
                                <th class="orders_table_order_number">'.__('Order Number', 'Paymendo').'</th>
                                <th class="orders_table_status">'.__('Status', 'Paymendo').'</th>
                                <th class="orders_table_notes">'.__('Note', 'Paymendo').'</th>
                                <th class="orders_table_created">'.__('Created', 'Paymendo').'</th>
                                <th class="orders_table_balance">'.__('Balance', 'Paymendo').'</th>
                                <th class="orders_table_link">'.__('Pay', 'Paymendo').'</th>
                            </tr>
                        </thead>
                    <tbody>';
                for ($i = 0; $i < count($order_data); $i++){
                    $item = $order_data[$i];
                    $render = $render.'<tr class="orders_table_row">';
                    $render = $render.'<td class="orders_table_id">'.$item['order_id'].'</td>';
                    $render = $render.'<td class="orders_table_order_number">'.$item['order_num'].'</td>';
                    $render = $render.'<td class="orders_table_status">'.$item['order_status'].'</td>';
                    $render = $render.'<td class="orders_table_notes">'.$item['order_notes'].'</td>';
                    $render = $render.'<td class="orders_table_created">'.$item['order_created'].'</td>';
                    $render = $render.'<td class="orders_table_balance">'.$item['order_balance'].'</td>';
                    $render = $render.'<td class="orders_table_link"><a href="'.$payment_page_base_url.'?order_id='.$item['order_id'].'">'."Link".'</a></td>';
                    $render = $render.'</tr>';
                }
                $render = $render.'</tbody></table></div>';
                return $render.createOrderTemplate($paymendo_utilities);
            }
            catch (Exception $error){
                return '<h4>'.$error->getMessage().'</h4>';
            }
            header("Refresh:0");
        }
        else{
            return '<h4>'.__('An error occurs, please login again.', 'Paymendo').'</h4>';
        }
    }

    function createOrderTemplate($paymendo_utilities){
        
        if (isset($_POST['operation']) && $_POST['operation'] == 'createNewOrder') {

            $paymendo_create_order_data = [
                'amount' => $_POST['paymende_amount'], 
                'notes' => $_POST['paymende_notes'], 
                'currency_code' => $_POST['paymende_currency_code']
            ];
            try{
                $response = $paymendo_utilities->createOrder($paymendo_create_order_data);

            }catch(Exception $error){
                return '<h4>'.$error->getMessage().'</h4>';
            }
        }
        return'
            <form action="." id="create_order_paymende_form" method="post">
                <h4>'.__('Add Order', 'Paymendo').'</h4>
                <label class="create_order_paymendo_label">
                    <div class="create_order_paymende_form_label_text">'.__('Balance', 'Paymendo').'</div>
                    <input type="number" step=0.01 name="paymende_amount" id="create_order_paymende_form_amount" name="create_order_paymende_form_amount">
                </label>
                <label class="create_order_paymendo_label">
                    <div class="create_order_paymende_form_label_text">'.__('Your Note', 'Paymendo').'</div>
                    <input type="text"  value="" name="paymende_notes" id="create_order_paymende_form_notes" name="create_order_paymende_form_notes">
                </label>
                <select name="paymende_currency_code" id="create_order_paymendo_select">
                    <option value="TRY">TRY</option>
                    <option value="USD">USD</option>
                </select>
                <button type="submit" id="create_order_paymende_form_submit_button">'.__('Create Order', 'Paymendo').'</button>
                <input type="hidden" id="operation" name="operation" value="createNewOrder">
                <div id="toasts">
                    <span id="toast_message" style="display:none;">'.__('Please enter all information completely.', 'Paymendo').'</span>
                </div>
            </form>';
    }
?>