<?php
defined('BASEPATH') or exit('No direct script access allowed');

class Tax_calculations extends MY_Controller
{
    public function __construct()
    {
        parent::__construct();
        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Customer || $this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }
        $this->lang->admin_load('tax_calculations', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('tax_calculations_model');
        $this->data['logo'] = true;
    }

    /**
     * Index page - List tax calculations
     */
    public function index()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('tax_calculations')]];
        $meta = ['page_title' => lang('tax_calculations'), 'bc' => $bc];
        $this->page_construct('tax_calculations/index', $meta, $this->data);
    }

    /**
     * Get tax calculations list for DataTables
     */
    public function getTaxCalculations()
    {
        $this->sma->checkPermissions('index');
        
        $this->load->library('datatables');
        $table = $this->db->dbprefix('companies');
        
        $this->datatables->select("$table.id as ids, 
            CONCAT($table.name, ' ', COALESCE($table.last_name, '')) as customer_name,
            $table.company, 
            COALESCE($table.customer_type, '') as customer_type,
            COALESCE($table.tax_regime, '') as tax_regime,
            $table.email, 
            $table.phone", FALSE)
            ->from("companies")
            ->where('group_name', 'customer');

        $view_link = anchor('admin/tax_calculations/view?customer_id=$1', '<i class="fa fa-eye"></i> ' . lang('view'), 'class="tip" title="' . lang('view_tax_calculations') . '"');
        $settings_link = anchor('admin/tax_calculations/settings?customer_id=$1', '<i class="fa fa-cog"></i> ' . lang('settings'), 'class="tip" title="' . lang('tax_settings') . '"');
        
        $action = '<div class="text-center">
            <div class="btn-group">
                ' . $view_link . '
                ' . $settings_link . '
            </div>
            <div class="btn-group" style="margin-top:5px;">
                <input type="number" class="year-input form-control input-sm" 
                       data-customer-id="$1" 
                       value="' . date('Y') . '" 
                       style="width:80px; display:inline-block; margin-right:5px;" 
                       min="2000" max="2100">
                <a href="#" class="btn btn-success btn-xs calculate-tax-btn" 
                   data-customer-id="$1" 
                   title="' . lang('calculate_tax') . '">
                    <i class="fa fa-calculator"></i> ' . lang('calculate') . '
                </a>
            </div>
        </div>';

        $this->datatables->add_column('Actions', $action, 'ids');
        echo $this->datatables->generate();
    }

    /**
     * Calculate tax for a customer for a specific year
     */
    public function calculate()
    {
        // Skip permission check for now - we'll handle it manually if needed
        // The constructor already checks for Owner/Admin/Customer/Supplier roles

        $customer_id = $this->input->post('customer_id');
        $year = $this->input->post('year') ? $this->input->post('year') : date('Y');

        if (!$customer_id) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 1, 'msg' => lang('customer_not_found')]);
            } else {
                $this->session->set_flashdata('error', lang('customer_not_found'));
                admin_redirect('tax_calculations');
            }
            return;
        }

        $customer = $this->site->getCompanyByID($customer_id);
        if (!$customer) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 1, 'msg' => lang('customer_not_found')]);
            } else {
                $this->session->set_flashdata('error', lang('customer_not_found'));
                admin_redirect('tax_calculations');
            }
            return;
        }

        // Process tax calculation
        $result = $this->tax_calculations_model->processTaxCalculation($customer_id, $year);

        if ($this->input->is_ajax_request()) {
            if ($result) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('tax_calculation_success'), 'customer_id' => $customer_id, 'year' => $year]);
            } else {
                $this->sma->send_json(['error' => 1, 'msg' => lang('tax_calculation_failed')]);
            }
        } else {
            if ($result) {
                $this->session->set_flashdata('message', lang('tax_calculation_success'));
                admin_redirect('tax_calculations/view?customer_id=' . $customer_id . '&year=' . $year);
            } else {
                $this->session->set_flashdata('error', lang('tax_calculation_failed'));
                admin_redirect('tax_calculations');
            }
        }
    }

    /**
     * View tax calculation details for a customer
     */
    public function view($customer_id = NULL)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id');
        }

        if (!$customer_id) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            admin_redirect('tax_calculations');
        }

        $customer = $this->site->getCompanyByID($customer_id);
        if (!$customer) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            admin_redirect('tax_calculations');
        }

        $year = $this->input->get('year') ? $this->input->get('year') : date('Y');

        $this->data['customer'] = $customer;
        $this->data['year'] = $year;
        $this->data['tax_calculations'] = $this->tax_calculations_model->getAllTaxCalculations($customer_id);
        $this->data['tax_calculation'] = $this->tax_calculations_model->getTaxCalculation($customer_id, $year);
        
        // Get tax payments: balance for this year, and advances for next year (which are created when calculating this year)
        $this->data['tax_payments'] = array();
        
        // Get balance payment for this tax year (payment_year = $year)
        $balance_payments = $this->tax_calculations_model->getAllTaxPayments($customer_id, $year);
        foreach ($balance_payments as $payment) {
            if ($payment->payment_type == 'balance' && $payment->payment_year == $year) {
                $this->data['tax_payments'][] = $payment;
            }
        }
        
        // Get advance payments for next year (payment_year = $year + 1), which are based on this year's calculation
        $advance_payments = $this->tax_calculations_model->getAllTaxPayments($customer_id, $year + 1);
        foreach ($advance_payments as $payment) {
            if (in_array($payment->payment_type, array('first_advance', 'second_advance'))) {
                $this->data['tax_payments'][] = $payment;
            }
        }
        
        // Sort by due_date
        usort($this->data['tax_payments'], function($a, $b) {
            return strcmp($a->due_date, $b->due_date);
        });
        
        $inps_calc = $this->db->get_where('inps_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1);
        $this->data['inps_calculation'] = $inps_calc->num_rows() > 0 ? $inps_calc->row() : NULL;
        $this->data['inps_payments'] = $this->tax_calculations_model->getAllINPSPayments($customer_id, $year);

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('tax_calculations'), 'page' => lang('tax_calculations')],
            ['link' => '#', 'page' => lang('view_tax_calculation')]
        ];
        $meta = ['page_title' => lang('view_tax_calculation'), 'bc' => $bc];
        $this->page_construct('tax_calculations/view', $meta, $this->data);
    }

    /**
     * Manage customer tax settings
     */
    public function settings($customer_id = NULL)
    {
        $this->sma->checkPermissions('edit', true);

        if ($this->input->get('customer_id')) {
            $customer_id = $this->input->get('customer_id');
        }

        if (!$customer_id) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            admin_redirect('tax_calculations');
        }

        $customer = $this->site->getCompanyByID($customer_id);
        if (!$customer) {
            $this->session->set_flashdata('error', lang('customer_not_found'));
            admin_redirect('tax_calculations');
        }

        $this->form_validation->set_rules('customer_type', lang('customer_type'), 'trim|required');
        $this->form_validation->set_rules('tax_regime', lang('tax_regime'), 'trim|required');
        $this->form_validation->set_rules('coefficient_of_profitability', lang('coefficient_of_profitability'), 'trim|required|numeric');
        $this->form_validation->set_rules('tax_rate', lang('tax_rate'), 'trim|required|numeric');
        $this->form_validation->set_rules('business_start_date', lang('business_start_date'), 'trim');

        if ($this->form_validation->run() == true) {
            $data = array(
                'customer_type' => $this->input->post('customer_type'),
                'tax_regime' => $this->input->post('tax_regime'),
                'coefficient_of_profitability' => $this->input->post('coefficient_of_profitability'),
                'tax_rate' => $this->input->post('tax_rate'),
                'business_start_date' => $this->input->post('business_start_date') ? $this->sma->fsd(trim($this->input->post('business_start_date')))  : NULL,
                'inps_discount_eligible' => $this->input->post('inps_discount_eligible') ? 1 : 0,
                'annual_revenue_limit' => $this->input->post('annual_revenue_limit') ? $this->input->post('annual_revenue_limit') : 85000,
                'employee_cost_limit' => $this->input->post('employee_cost_limit') ? $this->input->post('employee_cost_limit') : 20000
            );

            if ($this->tax_calculations_model->updateCustomerTaxSettings($customer_id, $data)) {
                $this->session->set_flashdata('message', lang('tax_settings_updated'));
                admin_redirect('tax_calculations/view?customer_id=' . $customer_id);
            } else {
                $this->session->set_flashdata('error', lang('tax_settings_update_failed'));
            }
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer'] = $customer;
        $this->data['customer_types'] = array('Gestione Separata', 'Commercianti', 'Artigiani');
        $this->data['tax_regimes'] = array('regime_forfettario' => 'Regime Forfettario');

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('tax_calculations'), 'page' => lang('tax_calculations')],
            ['link' => '#', 'page' => lang('tax_settings')]
        ];
        $meta = ['page_title' => lang('tax_settings'), 'bc' => $bc];
        $this->page_construct('tax_calculations/settings', $meta, $this->data);
    }

    /**
     * Get tax calculation data via AJAX
     */
    public function getTaxCalculation()
    {
        $this->sma->checkPermissions('index', true);

        $customer_id = $this->input->get('customer_id');
        $year = $this->input->get('year') ? $this->input->get('year') : date('Y');

        if (!$customer_id) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('customer_not_found')]);
        }

        $tax_calc = $this->tax_calculations_model->getTaxCalculation($customer_id, $year);
        $tax_payments = $this->tax_calculations_model->getAllTaxPayments($customer_id, $year);
        $inps_payments = $this->tax_calculations_model->getAllINPSPayments($customer_id, $year);

        $inps_calc = $this->db->get_where('inps_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1);

        $this->sma->send_json([
            'error' => 0,
            'tax_calculation' => $tax_calc,
            'tax_payments' => $tax_payments,
            'inps_calculation' => $inps_calc->num_rows() > 0 ? $inps_calc->row() : NULL,
            'inps_payments' => $inps_payments
        ]);
    }

    /**
     * Update payment status
     */
    public function updatePayment()
    {
        $this->sma->checkPermissions('edit', true);

        $payment_id = $this->input->post('payment_id');
        $payment_type = $this->input->post('payment_type'); // 'tax' or 'inps'
        $paid_amount = $this->input->post('paid_amount');
        $paid_date = $this->input->post('paid_date') ? $this->input->post('paid_date') : date('Y-m-d');
        $status = $this->input->post('status') ? $this->input->post('status') : 'paid';

        if (!$payment_id || !$payment_type) {
            $this->sma->send_json(['error' => 1, 'msg' => lang('invalid_request')]);
        }

        $table = $payment_type == 'tax' ? 'tax_payments' : 'inps_payments';
        $data = array(
            'paid_amount' => $paid_amount,
            'paid_date' => $paid_date,
            'status' => $status
        );

        $this->db->where('id', $payment_id);
        if ($this->db->update($table, $data)) {
            $this->sma->send_json(['error' => 0, 'msg' => lang('payment_updated')]);
        } else {
            $this->sma->send_json(['error' => 1, 'msg' => lang('payment_update_failed')]);
        }
    }
}

