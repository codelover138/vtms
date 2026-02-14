<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Main extends MY_Shop_Controller
{

    function __construct() {
        parent::__construct();

        if ($this->Settings->mmode && $this->v != 'login') { redirect('notify/offline'); }
        $this->load->library('ion_auth');
        $this->load->library('form_validation');
        $user_language = isset($this->Settings->user_language) ? $this->Settings->user_language : (isset($this->Settings->language) ? $this->Settings->language : 'english');
        $this->lang->admin_load('auth', $user_language);
    }

    function index() {
        if (!SHOP) { redirect('admin'); }
        if ($this->shop_settings->private && !$this->loggedIn) { redirect('/login'); }
        // Redirect customer users to dashboard
        // Only redirect if we're not already on dashboard route
        if ($this->loggedIn && $this->Customer) {
            // Check current URI to avoid redirect loops
            $current_uri = $this->uri->uri_string();
            if ($current_uri != 'dashboard' && $current_uri != 'main/dashboard' && empty($current_uri)) {
                // Use site_url to ensure direct routing
                redirect(site_url('dashboard'));
                return;
            }
        }
        $this->data['featured_products'] = $this->shop_model->getFeaturedProducts();
        $this->data['slider'] = json_decode($this->shop_settings->slider);
        $this->data['page_title'] = $this->shop_settings->shop_name;
        $this->data['page_desc'] = $this->shop_settings->description;
        $this->page_construct('index', $this->data);
    }

    function dashboard() {
        if (!$this->loggedIn) { 
            $this->session->set_userdata('requested_page', 'dashboard');
            redirect('login'); 
            return;
        }
        if ($this->Staff) { 
            redirect('admin/welcome'); 
            return;
        }
        if (!$this->Customer) { 
            redirect('/'); 
            return;
        }

        // Load tax calculations model and language
        $this->load->admin_model('tax_calculations_model');
        
        // Get current language - check cookies first, then Settings
        $cookie_lang = $this->input->cookie('sma_language', TRUE);
        if (!$cookie_lang) {
            $cookie_lang = $this->input->cookie('shop_language', TRUE);
        }
        $user_language = $cookie_lang ? $cookie_lang : (isset($this->Settings->user_language) ? $this->Settings->user_language : (isset($this->Settings->language) ? $this->Settings->language : 'english'));
        
        $this->lang->admin_load('sma', $user_language);
        $this->lang->admin_load('tax_calculations', $user_language);
        $this->lang->admin_load('transfers', $user_language);
        
        // Pass current language to view
        $this->data['current_language'] = $user_language;
        
        // If SHOP is not enabled, we need to set up basic shop settings
        if (!SHOP) {
            // Create a minimal shop_settings object
            $this->shop_settings = (object)array(
                'shop_name' => $this->Settings->site_name,
                'description' => '',
                'private' => 0
            );
            $this->data['shop_settings'] = $this->shop_settings;
        }
        
        // Ensure loggedInUser is in data array
        if (!isset($this->data['loggedInUser']) && isset($this->loggedInUser)) {
            $this->data['loggedInUser'] = $this->loggedInUser;
        }
        
        // Get customer ID from user's company_id
        $user = $this->site->getUser();
        $customer_id = $user->company_id;
        
        if (!$customer_id) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            redirect('/');
        }

        // Load customer (company) to get service point (warehouse_id)
        $customer = $this->site->getCompanyByID($customer_id);
        $service_point_id = $customer && !empty($customer->warehouse_id) ? (int)$customer->warehouse_id : null;
        $this->data['service_point_id'] = $service_point_id;
        $this->data['customer'] = $customer;
        
        // Service point name for display
        if ($service_point_id) {
            $wh = $this->site->getWarehouseByID($service_point_id);
            $this->data['service_point_name'] = $wh ? $wh->name : '';
        } else {
            $this->data['service_point_name'] = '';
        }

        // When customer is assigned to a service point: load transfers for that service point only
        $this->data['transfers'] = array();
        if ($service_point_id) {
            $this->db->select('*');
            $this->db->from('transfers');
            $this->db->group_start();
            $this->db->where('from_warehouse_id', $service_point_id);
            $this->db->or_where('to_warehouse_id', $service_point_id);
            $this->db->group_end();
            $this->db->order_by('date', 'DESC');
            $q = $this->db->get();
            $rows = $q->num_rows() > 0 ? $q->result() : array();
            foreach ($rows as $row) {
                $from_wh = $this->site->getWarehouseByID($row->from_warehouse_id);
                $to_wh = $this->site->getWarehouseByID($row->to_warehouse_id);
                $row->from_warehouse_name = $from_wh ? $from_wh->name : '';
                $row->to_warehouse_name = $to_wh ? $to_wh->name : '';
            }
            $this->data['transfers'] = $rows;
        }

        // Get current year
        $current_year = date('Y');
        
        // Get all tax calculations for this customer (only this customer's data)
        $this->data['tax_calculations'] = $this->tax_calculations_model->getAllTaxCalculations($customer_id);
        
        // Get tax year from request or use latest
        $requested_year = $this->input->get('year');
        $latest_calculation = !empty($this->data['tax_calculations']) ? $this->data['tax_calculations'][0] : null;
        $tax_year = $requested_year ? $requested_year : ($latest_calculation ? $latest_calculation->tax_year : $current_year);
        $this->data['tax_year'] = $tax_year;
        
        // Get tax calculation for selected year
        $this->data['tax_calculation'] = $this->tax_calculations_model->getTaxCalculation($customer_id, $tax_year);
        $this->data['latest_tax_calculation'] = $this->data['tax_calculation'] ? $this->data['tax_calculation'] : $latest_calculation;
        
        // If no tax calculation exists for the selected year, get live sales data
        $this->data['live_sales_data'] = null;
        if (!$this->data['tax_calculation']) {
            // Get customer settings for coefficient
            $customer_settings = $this->tax_calculations_model->getCustomerTaxSettings($customer_id);
            $coefficient = $customer_settings && $customer_settings->coefficient_of_profitability 
                ? $customer_settings->coefficient_of_profitability : 78.00;
            $tax_rate = $customer_settings && $customer_settings->tax_rate 
                ? $customer_settings->tax_rate : 5.00;
            
            // Get live sales data for selected year
            $total_sales = $this->tax_calculations_model->getTotalSalesForYear($customer_id, $tax_year);
            $taxable_income = $total_sales * $coefficient / 100;
            $estimated_tax = $taxable_income * $tax_rate / 100;
            
            $this->data['live_sales_data'] = (object) array(
                'tax_year' => $tax_year,
                'total_sales' => $total_sales,
                'taxable_income' => $taxable_income,
                'coefficient_used' => $coefficient,
                'tax_rate' => $tax_rate,
                'estimated_tax' => $estimated_tax,
                'is_live' => true
            );
        }
        
        // Get all tax payments for the selected year
        $this->data['tax_payments'] = $this->tax_calculations_model->getAllTaxPayments($customer_id, $tax_year);
        
        // Get INPS calculation and payments
        $inps_calc_query = $this->db->get_where('inps_calculations', array('customer_id' => $customer_id, 'tax_year' => $tax_year), 1);
        $this->data['inps_calculation'] = $inps_calc_query->num_rows() > 0 ? $inps_calc_query->row() : FALSE;
        $this->data['inps_payments'] = $this->tax_calculations_model->getAllINPSPayments($customer_id, $tax_year);
        
        // Get INAIL calculation and payments (only for Artigiani)
        $this->data['inail_calculation'] = $this->tax_calculations_model->getINAILCalculation($customer_id, $tax_year);
        $this->data['inail_payments'] = $this->tax_calculations_model->getAllINAILPayments($customer_id, $tax_year);
        
        // Get Diritto Annuale payments (for Artigiani and Commercianti)
        $this->data['diritto_annuale_payments'] = $this->tax_calculations_model->getAllDirittoAnnualePayments($customer_id, $tax_year);
        
        // Get Fattura Tra Privati calculation and payments
        $this->data['fattura_tra_privati_calculation'] = $this->tax_calculations_model->getFatturaTraPrivatiCalculation($customer_id, $tax_year);
        $this->data['fattura_tra_privati_payments'] = $this->tax_calculations_model->getAllFatturaTraPrivatiPayments($customer_id, $tax_year);
        
        // Get prediction data for taxable income
        $this->data['income_prediction'] = $this->tax_calculations_model->predictTaxableIncome($customer_id, $tax_year);
        
        // Get upcoming payments (due date >= today)
        $upcoming_payments = array();
        $overdue_payments = array();
        $paid_payments = array();
        $today = date('Y-m-d');
        
        // Initialize payment arrays if they don't exist
        if (!is_array($this->data['tax_payments'])) $this->data['tax_payments'] = array();
        if (!is_array($this->data['inps_payments'])) $this->data['inps_payments'] = array();
        if (!is_array($this->data['inail_payments'])) $this->data['inail_payments'] = array();
        if (!is_array($this->data['diritto_annuale_payments'])) $this->data['diritto_annuale_payments'] = array();
        if (!is_array($this->data['fattura_tra_privati_payments'])) $this->data['fattura_tra_privati_payments'] = array();

        foreach ($this->data['tax_payments'] as $p) { $p->payment_type_slug = 'tax'; }
        foreach ($this->data['inps_payments'] as $p) { $p->payment_type_slug = 'inps'; }
        foreach ($this->data['inail_payments'] as $p) { $p->payment_type_slug = 'inail'; }
        foreach ($this->data['diritto_annuale_payments'] as $p) { $p->payment_type_slug = 'diritto_annuale'; }
        foreach ($this->data['fattura_tra_privati_payments'] as $p) { $p->payment_type_slug = 'fattura_tra_privati'; }
        
        foreach ($this->data['tax_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $paid_payments[] = $payment;
            } elseif (strtotime($payment->due_date) < strtotime($today)) {
                $overdue_payments[] = $payment;
            } else {
                $upcoming_payments[] = $payment;
            }
        }
        
        foreach ($this->data['inps_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $paid_payments[] = $payment;
            } elseif (strtotime($payment->due_date) < strtotime($today)) {
                $overdue_payments[] = $payment;
            } else {
                $upcoming_payments[] = $payment;
            }
        }
        
        $this->data['upcoming_payments'] = $upcoming_payments;
        $this->data['overdue_payments'] = $overdue_payments;
        $this->data['paid_payments'] = $paid_payments;
        
        // Calculate summary statistics
        $total_due = 0;
        $total_paid = 0;
        $total_overdue = 0;
        $total_upcoming = 0;
        
        foreach ($this->data['tax_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $total_paid += $payment->paid_amount;
            } else {
                $total_due += $payment->amount;
                if (strtotime($payment->due_date) < strtotime($today)) {
                    $total_overdue += $payment->amount;
                } else {
                    $total_upcoming += $payment->amount;
                }
            }
        }
        
        foreach ($this->data['inps_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $total_paid += $payment->paid_amount ? $payment->paid_amount : 0;
            } else {
                $total_due += $payment->amount;
                if (strtotime($payment->due_date) < strtotime($today)) {
                    $total_overdue += $payment->amount;
                } else {
                    $total_upcoming += $payment->amount;
                }
            }
        }
        
        // Include INAIL payments
        foreach ($this->data['inail_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $total_paid += $payment->paid_amount ? $payment->paid_amount : 0;
            } else {
                $total_due += $payment->amount;
                if (strtotime($payment->due_date) < strtotime($today)) {
                    $total_overdue += $payment->amount;
                } else {
                    $total_upcoming += $payment->amount;
                }
            }
        }
        
        // Include Diritto Annuale payments
        foreach ($this->data['diritto_annuale_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $total_paid += $payment->paid_amount ? $payment->paid_amount : 0;
            } else {
                $total_due += $payment->amount;
                if (strtotime($payment->due_date) < strtotime($today)) {
                    $total_overdue += $payment->amount;
                } else {
                    $total_upcoming += $payment->amount;
                }
            }
        }
        
        // Include Fattura Tra Privati payments
        foreach ($this->data['fattura_tra_privati_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $total_paid += $payment->paid_amount ? $payment->paid_amount : 0;
            } else {
                $total_due += $payment->amount;
                if (strtotime($payment->due_date) < strtotime($today)) {
                    $total_overdue += $payment->amount;
                } else {
                    $total_upcoming += $payment->amount;
                }
            }
        }
        
        // Add all payment types to payment arrays
        foreach ($this->data['inail_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $paid_payments[] = $payment;
            } elseif (strtotime($payment->due_date) < strtotime($today)) {
                $overdue_payments[] = $payment;
            } else {
                $upcoming_payments[] = $payment;
            }
        }
        
        foreach ($this->data['diritto_annuale_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $paid_payments[] = $payment;
            } elseif (strtotime($payment->due_date) < strtotime($today)) {
                $overdue_payments[] = $payment;
            } else {
                $upcoming_payments[] = $payment;
            }
        }
        
        foreach ($this->data['fattura_tra_privati_payments'] as $payment) {
            if ($payment->status == 'paid') {
                $paid_payments[] = $payment;
            } elseif (strtotime($payment->due_date) < strtotime($today)) {
                $overdue_payments[] = $payment;
            } else {
                $upcoming_payments[] = $payment;
            }
        }
        
        $this->data['total_due'] = $total_due;
        $this->data['total_paid'] = $total_paid;
        $this->data['total_overdue'] = $total_overdue;
        $this->data['total_upcoming'] = $total_upcoming;
        
        // Get customer info
        $this->data['customer'] = $this->site->getCompanyByID($customer_id);
        $this->data['user'] = $user;
        
        $this->data['page_title'] = lang('dashboard');
        $this->data['page_desc'] = lang('user_dashboard');
        $this->page_construct('user/dashboard', $this->data);
    }

    /**
     * Generate Annual Tax Report PDF for the logged-in customer (same as admin tax_calculations/view PDF).
     * Customer ID is taken from the logged-in user's company_id; year from GET.
     */
    function annual_tax_report_pdf()
    {
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', 'annual_tax_report_pdf');
            redirect('login');
            return;
        }
        if ($this->Staff) {
            redirect('admin/welcome');
            return;
        }
        if (!$this->Customer) {
            redirect('/');
            return;
        }

        $redirect_url = site_url('dashboard');
        $user = $this->site->getUser();
        $customer_id = $user->company_id;
        $year = $this->input->get('year');

        if (!$customer_id || !$year) {
            $this->session->set_flashdata('error', lang('invalid_request'));
            redirect($redirect_url);
            return;
        }

        $this->load->admin_model('tax_calculations_model');
        $user_language = isset($this->Settings->user_language) ? $this->Settings->user_language : (isset($this->Settings->language) ? $this->Settings->language : 'english');
        $this->lang->admin_load('tax_calculations', $user_language);

        $customer = $this->site->getCompanyByID($customer_id);
        if (!$customer) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            redirect($redirect_url);
            return;
        }

        $tax_calculation = $this->tax_calculations_model->getTaxCalculation($customer_id, $year);
        if (!$tax_calculation) {
            $this->session->set_flashdata('error', lang('no_tax_calculation_found_for_year') . ' ' . $year);
            redirect($redirect_url);
            return;
        }

        // Get actual first and last transaction dates from income_data
        $this->db->select_min('date_transmission', 'first_date');
        $this->db->select_max('date_transmission', 'last_date');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('YEAR(date_transmission)', $year);
        $period_query = $this->db->get('income_data');

        $period_start = null;
        $period_end = null;
        if ($period_query->num_rows() > 0 && $period_query->row()->first_date) {
            $first_date = new DateTime($period_query->row()->first_date);
            $last_date = new DateTime($period_query->row()->last_date);
            $period_start = $first_date->format('d/m/Y');
            $period_end = $last_date->format('d/m/Y');
        } else {
            if ($customer->business_start_date) {
                $start_date = new DateTime($customer->business_start_date);
                if ($start_date->format('Y') == $year) {
                    $period_start = $start_date->format('d/m/Y');
                } else {
                    $period_start = '01/01/' . $year;
                }
            } else {
                $period_start = '01/01/' . $year;
            }
            $period_end = '31/12/' . $year;
        }

        $total_sales = $tax_calculation->total_sales;
        $coefficient = $tax_calculation->coefficient_used;
        $costs = $total_sales * (100 - $coefficient) / 100;
        $net_income = $tax_calculation->taxable_income;

        $ateco_code = '';
        if (!empty($customer->cf1)) {
            $ateco_code = $customer->cf1;
        } elseif (!empty($customer->cf2)) {
            $ateco_code = $customer->cf2;
        }

        $pec_email = !empty($customer->cf3) ? $customer->cf3 : '';
        $tax_number = !empty($customer->vat_no) ? trim($customer->vat_no) : '';
        $vat_number = !empty($customer->cf4) ? trim($customer->cf4) : '';

        $activity_description = '';
        if (!empty($customer->cf5)) {
            $activity_description = $customer->cf5;
        } elseif (!empty($customer->cf6)) {
            $activity_description = $customer->cf6;
        }
        if (empty($activity_description) && !empty($customer->customer_type)) {
            $activity_map = array(
                'Gestione Separata' => 'prestazioni di servizi',
                'Commercianti' => 'commercio',
                'Artigiani' => 'artigianato'
            );
            $activity_description = isset($activity_map[$customer->customer_type]) ? $activity_map[$customer->customer_type] : strtolower($customer->customer_type);
        }

        $this->data['customer'] = $customer;
        $this->data['tax_calculation'] = $tax_calculation;
        $this->data['year'] = $year;
        $this->data['period_start'] = $period_start;
        $this->data['period_end'] = $period_end;
        $this->data['total_sales'] = $total_sales;
        $this->data['coefficient'] = $coefficient;
        $this->data['costs'] = $costs;
        $this->data['net_income'] = $net_income;
        $this->data['ateco_code'] = $ateco_code;
        $this->data['pec_email'] = $pec_email;
        $this->data['tax_number'] = $tax_number;
        $this->data['vat_number'] = $vat_number;
        $this->data['activity_description'] = $activity_description;
        $this->data['tax_regime'] = $customer->tax_regime ? $customer->tax_regime : 'regime_forfettario';
        $this->data['Settings'] = $this->Settings;
        $this->data['user_language'] = $user_language;

        $biller = null;
        if (!empty($this->Settings->default_biller)) {
            $biller = $this->site->getCompanyByID($this->Settings->default_biller);
        }
        if (!$biller) {
            $this->db->where('group_name', 'biller');
            $this->db->limit(1);
            $biller = $this->db->get('companies')->row();
        }
        $this->data['biller'] = $biller;

        try {
            $admin_theme = $this->Settings->theme . '/admin/views/';
            $html = $this->load->view($admin_theme . 'tax_calculations/annual_tax_report_pdf', $this->data, true);
            $name = 'Conto_Economico_' . $customer->name . '_' . $year . '.pdf';
            $name = str_replace(' ', '_', $name);
            $this->sma->generate_pdf($html, $name);
        } catch (Exception $e) {
            log_message('error', 'PDF Generation Error: ' . $e->getMessage());
            $this->session->set_flashdata('error', 'PDF generation failed: ' . $e->getMessage());
            redirect($redirect_url);
        }
    }

    /**
     * Download uploaded payment PDF for the logged-in customer (own payments only).
     */
    function download_payment_pdf($payment_id = null, $payment_type = 'tax')
    {
        if (!$this->loggedIn || $this->Staff || !$this->Customer) {
            redirect('/');
            return;
        }
        $user = $this->site->getUser();
        $customer_id = $user->company_id;
        if (!$customer_id) {
            redirect(site_url('dashboard'));
            return;
        }

        $payment_id = (int) $payment_id;
        $allowed_types = array('tax', 'inps', 'inail', 'diritto_annuale', 'fattura_tra_privati');
        if (!$payment_id || !in_array($payment_type, $allowed_types)) {
            $this->session->set_flashdata('error', lang('invalid_request'));
            redirect(site_url('dashboard'));
            return;
        }

        $tables = array(
            'tax' => 'tax_payments',
            'inps' => 'inps_payments',
            'inail' => 'inail_payments',
            'diritto_annuale' => 'diritto_annuale_payments',
            'fattura_tra_privati' => 'fattura_tra_privati_payments'
        );
        $table = $tables[$payment_type];
        $payment = $this->db->get_where($table, array('id' => $payment_id, 'customer_id' => $customer_id), 1)->row();
        if (!$payment || empty($payment->uploaded_pdf)) {
            $this->session->set_flashdata('error', lang('file_not_found'));
            redirect(site_url('dashboard'));
            return;
        }

        $file_path = FCPATH . $payment->uploaded_pdf;
        if (!is_file($file_path)) {
            $this->session->set_flashdata('error', lang('file_not_found'));
            redirect(site_url('dashboard'));
            return;
        }

        $this->load->helper('download');
        force_download(basename($payment->uploaded_pdf), file_get_contents($file_path));
    }

    function profile($act = NULL) {
        if (!$this->loggedIn) { redirect('/'); }
        if (!SHOP || $this->Staff) { redirect('admin/users/profile/'.$this->session->userdata('user_id')); }
        $user = $this->ion_auth->user()->row();
        if ($act == 'user') {

            $this->form_validation->set_rules('first_name', lang("first_name"), 'required');
            $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
            $this->form_validation->set_rules('phone', lang("phone"), 'required');
            $this->form_validation->set_rules('email', lang("email"), 'required|valid_email');
            $this->form_validation->set_rules('company', lang("company"), 'trim');
            $this->form_validation->set_rules('vat_no', lang("vat_no"), 'trim');
            $this->form_validation->set_rules('address', lang("billing_address"), 'required');
            $this->form_validation->set_rules('city', lang("city"), 'required');
            $this->form_validation->set_rules('state', lang("state"), 'required');
            $this->form_validation->set_rules('postal_code', lang("postal_code"), 'required');
            $this->form_validation->set_rules('country', lang("country"), 'required');
            if ($user->email != $this->input->post('email')) {
                $this->form_validation->set_rules('email', lang("email"), 'trim|is_unique[users.email]');
            }

            if ($this->form_validation->run() === TRUE) {

                $bdata = [
                    'name' => $this->input->post('first_name').' '.$this->input->post('last_name'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                    'company' => $this->input->post('company'),
                    'vat_no' => $this->input->post('vat_no'),
                    'address' => $this->input->post('address'),
                    'city' => $this->input->post('city'),
                    'state' => $this->input->post('state'),
                    'postal_code' => $this->input->post('postal_code'),
                    'country' => $this->input->post('country'),
                ];

                $udata = [
                    'first_name' => $this->input->post('first_name'),
                    'last_name' => $this->input->post('last_name'),
                    'company' => $this->input->post('company'),
                    'phone' => $this->input->post('phone'),
                    'email' => $this->input->post('email'),
                ];

                if ($this->ion_auth->update($user->id, $udata) && $this->shop_model->updateCompany($user->company_id, $bdata)) {
                    $this->session->set_flashdata('message', lang('user_updated'));
                    $this->session->set_flashdata('message', lang('billing_data_updated'));
                    redirect("profile");
                }

            } else {
                $this->session->set_flashdata('error', validation_errors());
                redirect($_SERVER["HTTP_REFERER"]);
            }

        } elseif ($act == 'password') {

            $this->form_validation->set_rules('old_password', lang('old_password'), 'required');
            $this->form_validation->set_rules('new_password', lang('new_password'), 'required|min_length[8]|max_length[25]');
            $this->form_validation->set_rules('new_password_confirm', lang('confirm_password'), 'required|matches[new_password]');

            if ($this->form_validation->run() == false) {
                $this->session->set_flashdata('error', validation_errors());
                redirect('profile');
            } else {
                if (DEMO) {
                    $this->session->set_flashdata('warning', lang('disabled_in_demo'));
                    redirect($_SERVER["HTTP_REFERER"]);
                }

                $identity = $this->session->userdata($this->config->item('identity', 'ion_auth'));
                $change = $this->ion_auth->change_password($identity, $this->input->post('old_password'), $this->input->post('new_password'));

                if ($change) {
                    $this->session->set_flashdata('message', $this->ion_auth->messages());
                    $this->logout('m');
                } else {
                    $this->session->set_flashdata('error', $this->ion_auth->errors());
                    redirect('profile');
                }
            }

        }

        $this->data['featured_products'] = $this->shop_model->getFeaturedProducts();
        $this->data['customer'] = $this->site->getCompanyByID($this->session->userdata('company_id'));
        $this->data['user'] = $this->site->getUser();
        $this->data['page_title'] = lang('profile');
        $this->data['page_desc'] = $this->shop_settings->description;
        $this->page_construct('user/profile', $this->data);
    }

    function login($m = NULL) {
        if (!SHOP || $this->Settings->mmode) { redirect('admin/login'); }
        if ($this->loggedIn) {
            $this->session->set_flashdata('error', $this->session->flashdata('error'));
            redirect('/');
        }

        if ($this->Settings->captcha) {
            $this->form_validation->set_rules('captcha', lang('captcha'), 'required|callback_captcha_check');
        }

        if ($this->form_validation->run('auth/login') == true) {

            $remember = (bool)$this->input->post('remember_me');

            if ($this->ion_auth->login($this->input->post('identity'), $this->input->post('password'), $remember)) {
                if ($this->Settings->mmode) {
                    if (!$this->ion_auth->in_group('owner')) {
                        $this->session->set_flashdata('error', lang('site_is_offline_plz_try_later'));
                        redirect('logout');
                    }
                }

                $this->session->set_flashdata('message', $this->ion_auth->messages());
                // Redirect customer users to dashboard
                if ($this->ion_auth->in_group('customer')) {
                    redirect('dashboard');
                }
                $referrer = ($this->session->userdata('requested_page') && $this->session->userdata('requested_page') != 'admin') ? $this->session->userdata('requested_page') : '/';
                redirect($referrer);
            } else {
                $this->session->set_flashdata('error', $this->ion_auth->errors());
                redirect('login');
            }

        } else {

            $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
            $this->data['message'] = $m ? lang('password_changed') : $this->session->flashdata('message');
            $this->data['page_title'] = lang('login');
            $this->data['page_desc'] = $this->shop_settings->description;
            if ($this->shop_settings->private) {
                $this->data['message'] = isset($data['message']) ? $data['message'] : $this->session->flashdata('message');
                $this->data['error'] = isset($data['error']) ? $data['error'] : $this->session->flashdata('error');
                $this->data['warning'] = isset($data['warning']) ? $data['warning'] : $this->session->flashdata('warning');
                $this->data['reminder'] = isset($data['reminder']) ? $data['reminder'] : $this->session->flashdata('reminder');
                $this->data['Settings'] = $this->Settings;
                $this->data['shop_settings'] = $this->shop_settings;
                $this->load->view($this->theme.'user/private_login.php', $this->data);
            } else {
                $this->page_construct('user/login', $this->data);
            }

        }
    }

    function logout($m = NULL) {
        if (!SHOP) { redirect('admin/logout'); }
        $logout = $this->ion_auth->logout();
        $referrer = (isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : '/');
        $this->session->set_flashdata('message', $this->ion_auth->messages());
        redirect($m ? 'login/m' : $referrer);
    }

    function register() {

        if ($this->shop_settings->private) { redirect('/login'); }
        $this->form_validation->set_rules('first_name', lang("first_name"), 'required');
        $this->form_validation->set_rules('last_name', lang("last_name"), 'required');
        $this->form_validation->set_rules('phone', lang("phone"), 'required');
        $this->form_validation->set_rules('company', lang("company"), 'required');
        $this->form_validation->set_rules('email', lang("email_address"), 'required|is_unique[users.email]');
        $this->form_validation->set_rules('username', lang("username"), 'required|is_unique[users.username]');
        $this->form_validation->set_rules('password', lang('password'), 'required|min_length[8]|max_length[20]|matches[password_confirm]');
        $this->form_validation->set_rules('password_confirm', lang('confirm_password'), 'required');

        if ($this->form_validation->run('') == true) {

            $email = strtolower($this->input->post('email'));
            $username = strtolower($this->input->post('username'));
            $password = $this->input->post('password');

            $customer_group = $this->shop_model->getCustomerGroup($this->Settings->customer_group);
            $price_group = $this->shop_model->getPriceGroup($this->Settings->price_group);

            $company_data = [
                'company' => $this->input->post('company') ? $this->input->post('company') : '-',
                'name' => $this->input->post('first_name').' '.$this->input->post('last_name'),
                'email' => $this->input->post('email'),
                'phone' => $this->input->post('phone'),
                'group_id' => 3,
                'group_name' => 'customer',
                'customer_group_id' => (!empty($customer_group)) ? $customer_group->id : NULL,
                'customer_group_name' => (!empty($customer_group)) ? $customer_group->name : NULL,
                'price_group_id' => (!empty($price_group)) ? $price_group->id : NULL,
                'price_group_name' => (!empty($price_group)) ? $price_group->name : NULL,
            ];

            $company_id = $this->shop_model->addCustomer($company_data);

            $additional_data = [
                'first_name' => $this->input->post('first_name'),
                'last_name' => $this->input->post('last_name'),
                'phone' => $this->input->post('phone'),
                'company' => $this->input->post('company'),
                'gender' => 'male',
                'company_id' => $company_id,
                'group_id' => 3
            ];
            $this->load->library('ion_auth');
        }

        if ($this->form_validation->run() == true && $this->ion_auth->register($username, $password, $email, $additional_data)) {
            $this->session->set_flashdata('message', lang("account_created"));
            redirect('login');
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect('login#register');
        }
    }

    function activate($id, $code) {
        if (!SHOP) { redirect('admin/auth/activate/'.$id.'/'.$code); }
        if ($code) {
            if ($activation = $this->ion_auth->activate($id, $code)) {
                $this->session->set_flashdata('message', $this->ion_auth->messages());
                redirect("login");
            }
        } else {
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect("login");
        }
    }

    function forgot_password() {
        if (!SHOP) { redirect('admin/auth/forgot_password'); }
        $this->form_validation->set_rules('email', lang('email_address'), 'required|valid_email');

        if ($this->form_validation->run() == false) {
            $this->sma->send_json(validation_errors());
        } else {

            $identity = $this->ion_auth->where('email', strtolower($this->input->post('email')))->users()->row();
            if (empty($identity)) {
                $this->sma->send_json(lang('forgot_password_email_not_found'));
            }

            $forgotten = $this->ion_auth->forgotten_password($identity->email);
            if ($forgotten) {
                $this->sma->send_json(['status' => 'success', 'message' => $this->ion_auth->messages()]);
            } else {
                $this->sma->send_json(['status' => 'error', 'message' => $this->ion_auth->errors()]);
            }
        }
    }

    function reset_password($code = NULL) {
        if (!SHOP) { redirect('admin/auth/reset_password/'.$code); }
        if (!$code) {
            $this->session->set_flashdata('error', lang('page_not_found'));
            redirect('/');
        }

        $user = $this->ion_auth->forgotten_password_check($code);

        if ($user) {

            $this->form_validation->set_rules('new', lang('password'), 'required|min_length[8]|max_length[25]|matches[new_confirm]');
            $this->form_validation->set_rules('new_confirm', lang('confirm_password'), 'required');

            if ($this->form_validation->run() == false) {

                $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
                $this->data['message'] = $this->session->flashdata('message');
                $this->data['min_password_length'] = $this->config->item('min_password_length', 'ion_auth');
                $this->data['new_password'] = [
                    'name' => 'new',
                    'id' => 'new',
                    'type' => 'password',
                    'class' => 'form-control',
                    'required' => 'required',
                    'pattern' => '(?=.*\d)(?=.*[a-z])(?=.*[A-Z]).{8,}',
                    'data-fv-regexp-message' => lang('pasword_hint'),
                    'placeholder' => lang('new_password')
                ];
                $this->data['new_password_confirm'] = [
                    'name' => 'new_confirm',
                    'id' => 'new_confirm',
                    'type' => 'password',
                    'class' => 'form-control',
                    'required' => 'required',
                    'data-fv-identical' => 'true',
                    'data-fv-identical-field' => 'new',
                    'data-fv-identical-message' => lang('pw_not_same'),
                    'placeholder' => lang('confirm_password')
                ];
                $this->data['user_id'] = [
                    'name' => 'user_id',
                    'id' => 'user_id',
                    'type' => 'hidden',
                    'value' => $user->id,
                ];
                $this->data['code'] = $code;
                $this->data['identity_label'] = $user->email;
                $this->data['page_title'] = lang('reset_password');
                $this->data['page_desc'] = '';
                $this->page_construct('user/reset_password', $this->data);

            } else {
                // do we have a valid request?
                if ($user->id != $this->input->post('user_id')) {

                    $this->ion_auth->clear_forgotten_password_code($code);
                    redirect('notify/csrf');

                } else {
                    // finally change the password
                    $identity = $user->email;

                    $change = $this->ion_auth->reset_password($identity, $this->input->post('new'));
                    if ($change) {
                        //if the password was successfully changed
                        $this->session->set_flashdata('message', $this->ion_auth->messages());
                        redirect('login');
                    } else {
                        $this->session->set_flashdata('error', $this->ion_auth->errors());
                        redirect('reset_password/' . $code);
                    }
                }
            }
        } else {
            //if the code is invalid then send them back to the forgot password page
            $this->session->set_flashdata('error', $this->ion_auth->errors());
            redirect('/');
        }
    }

    function captcha_check($cap) {
        $expiration = time() - 300; // 5 minutes limit
        $this->db->delete('captcha', ['captcha_time <' => $expiration]);

        $this->db->select('COUNT(*) AS count')
        ->where('word', $cap)
        ->where('ip_address', $this->input->ip_address())
        ->where('captcha_time >', $expiration);

        if ($this->db->count_all_results('captcha')) {
            return true;
        } else {
            $this->form_validation->set_message('captcha_check', lang('captcha_wrong'));
            return FALSE;
        }
    }

    function hide($id = NULL) {
        $this->session->set_userdata('hidden' . $id, 1);
        echo true;
    }

    function language($lang = NULL) {
        // Get language from URL parameter if not provided
        if (!$lang) {
            $lang = $this->uri->segment(2);
        }
        
        // Validate language
        $available_languages = array('english', 'italian');
        
        if (!$lang || !in_array($lang, $available_languages)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 1, 'msg' => 'Invalid language selected']);
            } else {
                $this->session->set_flashdata('error', 'Invalid language selected');
                redirect('dashboard');
            }
            return;
        }
        
        // Set both shop_language and sma_language cookies for compatibility
        // sma_language is used by admin language files (tax_calculations)
        // Cookies must be set before any output or redirect
        // Use explicit cookie parameters to ensure they're set correctly
        $cookie_expire = time() + 31536000; // 1 year
        $cookie_path = '/';
        $cookie_domain = '';
        
        // Set cookies using setcookie directly to ensure they're set
        setcookie('shop_language', $lang, $cookie_expire, $cookie_path, $cookie_domain);
        setcookie('sma_language', $lang, $cookie_expire, $cookie_path, $cookie_domain);
        
        // Also use CodeIgniter's set_cookie for compatibility
        set_cookie('shop_language', $lang, 31536000);
        set_cookie('sma_language', $lang, 31536000);
        
        // If AJAX request, just return success
        if ($this->input->is_ajax_request()) {
            $this->sma->send_json(['error' => 0, 'msg' => 'Language changed successfully']);
            return;
        }
        
        // Get year parameter from GET to preserve it
        $year = $this->input->get('year');
        
        // Build redirect URL using site_url to ensure proper absolute URL
        // This prevents redirect loops by going directly to dashboard route
        $redirect_url = site_url('dashboard');
        if ($year) {
            $redirect_url .= '?year=' . urlencode($year);
        }
        
        // Use direct header redirect to bypass any potential redirect loops
        // This ensures we go directly to dashboard without going through index()
        header('Location: ' . $redirect_url, TRUE, 302);
        exit;
    }

    function currency($currency) {
        set_cookie('shop_currency', $currency, 31536000);
        redirect($_SERVER["HTTP_REFERER"]);
    }

    function cookie($val) {
        set_cookie('shop_use_cookie', $val, 31536000);
        redirect($_SERVER["HTTP_REFERER"]);
    }

}
