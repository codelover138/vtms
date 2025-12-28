<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Payments extends MY_Controller
{

    function __construct()
    {
        parent::__construct();
        
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        $this->load->model('pay_model');
        $this->load->library('form_validation');
        $this->lang->admin_load('sales', $this->Settings->user_language);
    }

    function index_b()
    {
        show_404();
    }

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Payments')));
        $meta = array('page_title' => lang('Payments'), 'bc' => $bc);
        $this->page_construct('payments/index', $meta, $this->data);
    }

    public function getData($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        
        $edit_link = anchor('admin/payments/edit/$1', '<i class="fa fa-edit"></i> ' . lang('Edit'), 'class="sledit"');
       
        $delete_link = "<a href='#' class='po' title='<b>" . lang("Delete") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('payments/delete/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('Delete') . "</a>";
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
           
            <li>' . $edit_link . '</li>
           
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

    $this->load->library('datatables');
    if($user->id){
       
        $this->datatables
            ->select("{$this->db->dbprefix('vt_payments')}.id as id, {$this->db->dbprefix('vt_payments')}.pay_date, {$this->db->dbprefix('vt_payments')}.reference_no, CONCAT(" . $this->db->dbprefix('companies') . ".name, ' ', " . $this->db->dbprefix('companies') . ".last_name),{$this->db->dbprefix('vt_payments')}.payment_type,pay_method,amount, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by")
            ->from('vt_payments')
            ->join('companies', 'companies.id=vt_payments.customer_id', 'inner')
             ->join('users', 'users.id=vt_payments.created_by', 'inner')
             ->where('vt_payments.created_by',$user->id);
    
    $this->datatables->add_column("Actions", $action, "id");
    echo $this->datatables->generate();
    }else{
        $this->datatables->select("{$this->db->dbprefix('vt_payments')}.id as id, {$this->db->dbprefix('vt_payments')}.pay_date, {$this->db->dbprefix('vt_payments')}.reference_no, CONCAT(" . $this->db->dbprefix('companies') . ".name, ' ', " . $this->db->dbprefix('companies') . ".last_name),{$this->db->dbprefix('vt_payments')}.payment_type,pay_method,amount, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by")   
        ->from('payments')
        ->join('companies', 'companies.id=vt_payments.customer_id', 'inner')
         ->join('users', 'users.id=vt_payments.created_by', 'inner');
    
    $this->datatables->add_column("Actions", $action, "id");
    echo $this->datatables->generate();
    }
       
    }

    public function add($id = null)
    {
         $this->sma->checkPermissions('payments', true);
         $this->load->helper('security');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        

        //$this->form_validation->set_rules('reference_no', lang("reference_no"), 'required');
        $this->form_validation->set_rules('amount', lang("Amount"), 'required');
        $this->form_validation->set_rules('date', lang("Date"), 'required');
        $this->form_validation->set_rules('type', lang("Payment_Type"), 'required');
        $this->form_validation->set_rules('customer', lang("Customer"), 'required');
        $this->form_validation->set_rules('paid_by', lang("paid_by"), 'required');
        if ($this->form_validation->run() == true) {
            $date = $this->sma->fsd(trim($this->input->post('date')));
            $payment = array(
                'pay_date' => $date,
                'customer_id' => $this->input->post('customer'),
                'reference_no' => $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('pay'),
                'amount' => $this->input->post('amount'),
                'pay_method' => ucfirst($this->input->post('paid_by')),
                'cheque_no' => $this->input->post('cheque_no'),
                'cc_no' => $this->input->post('paid_by') == 'gift_card' ? $this->input->post('gift_card_no') : $this->input->post('pcc_no'),
                'cc_holder' => $this->input->post('pcc_holder'),
                'cc_month' => $this->input->post('pcc_month'),
                'cc_year' => $this->input->post('pcc_year'),
                'cc_type' => $this->input->post('pcc_type'),
                'note' => $this->input->post('note'),
                'created_by' => $this->session->userdata('user_id'),
                'payment_type' => $this->input->post('type'),
            );


        }
        

        if ($this->form_validation->run() == true && $this->pay_model->addPayments($payment)) {
            $this->session->set_flashdata('message', lang("Payment_Added"));
            admin_redirect('/payments');
        } else {

            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            // $this->data['inv'] = $sale;
            $this->data['payment_ref'] = $this->site->getReference('pay');
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('payments'), 'page' => lang('Payments')), array('link' => '#', 'page' => lang('Add')));
            $meta = array('page_title' => lang('Add'), 'bc' => $bc);
            $this->page_construct('payments/add', $meta, $this->data);
        }
    }


    function paypalipn()
    {

        $this->load->admin_model('sales_model');
        $paypal = $this->sales_model->getPaypalSettings();
        $this->sma->log_payment('Paypal IPN called');

        $req = 'cmd=_notify-validate';
        foreach ($_POST as $key => $value) {
            $value = urlencode(stripslashes($value));
            $req .= "&$key=$value";
        }

        $header = "POST /cgi-bin/webscr HTTP/1.1\r\n";
        $header .= "Content-Type: application/x-www-form-urlencoded\r\n";
        $header .= "Host: www.paypal.com\r\n";  // www.sandbox.paypal.com for a test site
        $header .= "Content-Length: " . strlen($req) . "\r\n";
        $header .= "Connection: close\r\n\r\n";

        //$fp = fsockopen ('ssl://www.sandbox.paypal.com', 443, $errno, $errstr, 30);
        $fp = fsockopen('ssl://www.paypal.com', 443, $errno, $errstr, 30);

        if (!$fp) {

            $this->sma->log_payment('Paypal Payment Failed (IPN HTTP ERROR)', $errstr);
            $this->session->set_flashdata('error', lang('payment_failed'));

        } else {
            fputs($fp, $header . $req);
            while (!feof($fp)) {
                $res = fgets($fp, 1024);
                //log_message('error', 'Paypal IPN - fp handler -'.$res);
                if (stripos($res, "VERIFIED") !== false) {
                    $this->sma->log_payment('Paypal IPN - VERIFIED');

                    $custom = explode('__', $_POST['custom']);
                    $payer_email = $_POST['payer_email'];

                    if (($_POST['payment_status'] == 'Completed' || $_POST['payment_status'] == 'Processed' || $_POST['payment_status'] == 'Pending') &&
                        ($_POST['receiver_email'] == $paypal->account_email) &&
                        ($_POST['mc_gross'] == ($custom[1] + $custom[2]))
                    ) {

                        $invoice_no = $_POST['item_number'];
                        $reference = $_POST['item_name'];
                        if ($_POST['mc_currency'] == $this->Settings->default_currency) {
                            $amount = $_POST['mc_gross'];
                        } else {
                            $currency = $this->site->getCurrencyByCode($_POST['mc_currency']);
                            $amount = $_POST['mc_gross'] * (1 / $currency->rate);
                        }
                        if ($inv = $this->sales_model->getInvoiceByID($invoice_no)) {
                            $payment = array(
                                'date' => date('Y-m-d H:i:s'),
                                'sale_id' => $invoice_no,
                                'reference_no' => $this->site->getReference('pay'),
                                'amount' => $amount,
                                'paid_by' => 'paypal',
                                'transaction_id' => $_POST['txn_id'],
                                'type' => 'received',
                                'note' => $_POST['mc_currency'] . ' ' . $_POST['mc_gross'] . ' had been paid for the Sale Reference No ' . $reference
                            );
                            if ($this->sales_model->addPayment($payment)) {
                                $customer = $this->site->getCompanyByID($inv->customer_id);
                                $this->site->updateReference('pay');

                                $this->load->library('parser');
                                $parse_data = array(
                                    'reference_number' => $reference,
                                    'contact_person' => $customer->name,
                                    'company' => $customer->company,
                                    'site_link' => base_url(),
                                    'site_name' => $this->Settings->site_name,
                                    'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                                );
                                $temp_path = is_dir('./themes/' . $this->Settings->theme . '/admin/views/email_templates/');
                                $theme = $temp_path ? $this->theme : 'default';
                                $msg = file_get_contents('./themes/' . $theme . '/admin/views/email_templates/payment.html');
                                $message = $this->parser->parse_string($msg, $parse_data);
                                $this->sma->log_payment('Payment has been made for Sale Reference #' . $_POST['item_name'] . ' via Paypal (' . $_POST['txn_id'] . ').', print_r($_POST, ture));
                                try {
                                    $this->sma->send_email($paypal->account_email, 'Payment has been made via Paypal', $message);
                                } catch (Exception $e) {
                                    $this->sma->log_payment('Email Notification Failed: ' . $e->getMessage());
                                }
                                $this->session->set_flashdata('message', lang('payment_added'));
                            }
                        }
                    } else {

                        $this->sma->log_payment('Payment failed for Sale Reference #' . $reference . ' via Paypal (' . $_POST['txn_id'] . ').', print_r($_POST, ture));
                        $this->session->set_flashdata('error', lang('payment_failed'));

                    }
                } else if (stripos($res, "INVALID") !== false) {
                    $this->sma->log_payment('INVALID response from Paypal. Payment failed via Paypal.', print_r($_POST, ture));
                    $this->session->set_flashdata('error', lang('payment_failed'));
                }
            }
            fclose($fp);
        }
        redirect('/');
        exit();

    }

    function skrillipn()
    {
        $this->load->admin_model('sales_model');
        $skrill = $this->sales_model->getSkrillSettings();
        $this->sma->log_payment('Skrill IPN called');

        $concatFields = $_POST['merchant_id'] . $_POST['transaction_id'] . strtoupper(md5($skrill->secret_word)) . $_POST['mb_amount'] . $_POST['mb_currency'] . $_POST['status'];

        if (strtoupper(md5($concatFields)) == $_POST['md5sig'] && $_POST['status'] == 2 && $_POST['pay_to_email'] == $skrill->account_email) {
            $invoice_no = $_POST['item_number'];
            $reference = $_POST['item_name'];
            if ($_POST['mb_currency'] == $this->Settings->default_currency) {
                $amount = $_POST['mb_amount'];
            } else {
                $currency = $this->site->getCurrencyByCode($_POST['mb_currency']);
                $amount = $_POST['mb_amount'] * (1 / $currency->rate);
            }
            if ($inv = $this->sales_model->getInvoiceByID($invoice_no)) {
                $payment = array(
                    'date' => date('Y-m-d H:i:s'),
                    'sale_id' => $invoice_no,
                    'reference_no' => $this->site->getReference('pay'),
                    'amount' => $amount,
                    'paid_by' => 'skrill',
                    'transaction_id' => $_POST['mb_transaction_id'],
                    'type' => 'received',
                    'note' => $_POST['mb_currency'] . ' ' . $_POST['mb_amount'] . ' had been paid for the Sale Reference No ' . $reference
                );
                if ($this->sales_model->addPayment($payment)) {
                    $customer = $this->site->getCompanyByID($inv->customer_id);
                    $this->site->updateReference('pay');

                    $this->load->library('parser');
                    $parse_data = array(
                        'reference_number' => $reference,
                        'contact_person' => $customer->name,
                        'company' => $customer->company,
                        'site_link' => base_url(),
                        'site_name' => $this->Settings->site_name,
                        'logo' => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>'
                    );
                    $temp_path = is_dir('./themes/' . $this->Settings->theme . '/admin/views/email_templates/');
                    $theme = $temp_path ? $this->theme : 'default';
                    $msg = file_get_contents('./themes/' . $theme . '/admin/views/email_templates/payment.html');
                    $message = $this->parser->parse_string($msg, $parse_data);
                    $this->sma->log_payment('Payment has been made for Sale Reference #' . $_POST['item_name'] . ' via Skrill (' . $_POST['mb_transaction_id'] . ').', print_r($_POST, ture));
                    try {
                        $this->sma->send_email($skrill->account_email, 'Payment has been made via Skrill', $message);
                    } catch (Exception $e) {
                        $this->sma->log_payment('Email Notification Failed: ' . $e->getMessage());
                    }
                    $this->session->set_flashdata('message', lang('payment_added'));
                }
            }
        } else {
            $this->sma->log_payment('Payment failed for via Skrill.', print_r($_POST, ture));
            $this->session->set_flashdata('error', lang('payment_failed'));
        }
        redirect('/');
        exit();

    }

}