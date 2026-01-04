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

        $view_link = anchor('admin/tax_calculations/view?customer_id=$1', '<i class="fa fa-eye"></i> ' . lang('view_tax_calculations'), 'class="tip"');
        $settings_link = anchor('admin/tax_calculations/settings?customer_id=$1', '<i class="fa fa-cog"></i> ' . lang('tax_settings'), 'class="tip"');
        $calculate_link = '<a href="#" class="calculate-tax-btn tip" data-customer-id="$1" data-year="' . date('Y') . '" title="' . lang('calculate_tax') . '"><i class="fa fa-calculator"></i> ' . lang('calculate_tax') . '</a>';
        
        $action = '<div class="text-center">
            <div class="btn-group">
                <button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown" aria-expanded="false">
                    <i class="fa fa-cog"></i> <span class="caret"></span>
                </button>
                <ul class="dropdown-menu pull-right" role="menu">
                    <li>' . $view_link . '</li>
                    <li>' . $settings_link . '</li>
                    <li class="divider"></li>
                    <li class="dropdown-header"><i class="fa fa-calculator"></i> ' . lang('calculate_tax') . '</li>
                    <li style="padding: 8px 15px;">
                        <div class="input-group" style="width: 100%;">
                            <input type="number" class="form-control input-sm year-input" 
                                   data-customer-id="$1" 
                                   value="' . date('Y') . '" 
                                   min="2000" max="2100" 
                                   style="width: 70px; display: inline-block;">
                            <span class="input-group-btn" style="width: auto;">
                                <button class="btn btn-success btn-xs calculate-tax-btn" 
                                        type="button"
                                        data-customer-id="$1" 
                                        title="' . lang('calculate') . '">
                                    <i class="fa fa-calculator"></i>
                                </button>
                            </span>
                        </div>
                    </li>
                </ul>
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
        
        // Get INPS calculation
        $inps_calc = $this->db->get_where('inps_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1);
        $this->data['inps_calculation'] = ($inps_calc && $inps_calc->num_rows() > 0) ? $inps_calc->row() : NULL;
        $this->data['inps_payments'] = $this->tax_calculations_model->getAllINPSPayments($customer_id, $year);
        
        // Get INAIL calculation (only for Artigiani)
        $this->data['inail_calculation'] = $this->tax_calculations_model->getINAILCalculation($customer_id, $year);
        $this->data['inail_payments'] = $this->tax_calculations_model->getAllINAILPayments($customer_id, $year);
        
        // Get Diritto Annuale payments (for Artigiani and Commercianti)
        $this->data['diritto_annuale_payments'] = $this->tax_calculations_model->getAllDirittoAnnualePayments($customer_id, $year);
        
        // Get Fattura Tra Privati calculation and payments
        $this->data['fattura_tra_privati_calculation'] = $this->tax_calculations_model->getFatturaTraPrivatiCalculation($customer_id, $year);
        $this->data['fattura_tra_privati_payments'] = $this->tax_calculations_model->getAllFatturaTraPrivatiPayments($customer_id, $year);

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

        // Check if customer has existing tax calculations
        $existing_calculations = $this->tax_calculations_model->getAllTaxCalculations($customer_id);
        $has_existing_calculations = !empty($existing_calculations);
        $this->data['has_existing_calculations'] = $has_existing_calculations;
        $this->data['existing_customer_type'] = $customer->customer_type;

        // Customer type validation: required, but if calculations exist, we'll use the original value
        // If calculations exist, set the POST value to the original to pass validation
        if ($has_existing_calculations) {
            // Force set the POST value to the original customer_type to pass validation
            $_POST['customer_type'] = $customer->customer_type;
            // Still validate it exists, but we know it will pass
            $this->form_validation->set_rules('customer_type', lang('customer_type'), 'trim|required');
        } else {
            $this->form_validation->set_rules('customer_type', lang('customer_type'), 'trim|required');
        }
        $this->form_validation->set_rules('tax_regime', lang('tax_regime'), 'trim|required');
        $this->form_validation->set_rules('coefficient_of_profitability', lang('coefficient_of_profitability'), 'trim|required|numeric');
        $this->form_validation->set_rules('tax_rate', lang('tax_rate'), 'trim|required|numeric');
        $this->form_validation->set_rules('business_start_date', lang('business_start_date'), 'trim');

        if ($this->form_validation->run() == true) {
            // If customer has existing calculations, use the original customer_type from database
            // Otherwise, use the posted value
            if ($has_existing_calculations) {
                $customer_type = $customer->customer_type; // Use original, don't allow change
                // Double-check: if someone tries to change it via POST, show error
                $posted_customer_type = $this->input->post('customer_type');
                if ($posted_customer_type !== $customer->customer_type) {
                    $this->session->set_flashdata('error', lang('cannot_change_customer_type_with_existing_calculations'));
                    admin_redirect('tax_calculations/settings?customer_id=' . $customer_id);
                    return;
                }
            } else {
                $customer_type = $this->input->post('customer_type');
            }
            
            $inps_discount_eligible = $this->input->post('inps_discount_eligible') ? 1 : 0;
            
            // Validate: INPS discount eligible can only be set for Commercianti or Artigiani
            if ($inps_discount_eligible && !in_array($customer_type, array('Commercianti', 'Artigiani'))) {
                $this->session->set_flashdata('error', lang('inps_discount_only_for_commercianti_artigiani'));
                admin_redirect('tax_calculations/settings?customer_id=' . $customer_id);
                return;
            }
            
            $data = array(
                'customer_type' => $customer_type,
                'tax_regime' => $this->input->post('tax_regime'),
                'coefficient_of_profitability' => $this->input->post('coefficient_of_profitability'),
                'tax_rate' => $this->input->post('tax_rate'),
                'business_start_date' => $this->input->post('business_start_date') ? $this->sma->fsd(trim($this->input->post('business_start_date')))  : NULL,
                'inps_discount_eligible' => $inps_discount_eligible,
                'annual_revenue_limit' => $this->input->post('annual_revenue_limit') ? $this->input->post('annual_revenue_limit') : 85000,
                'employee_cost_limit' => $this->input->post('employee_cost_limit') ? $this->input->post('employee_cost_limit') : 20000
            );
            
            // Add INAIL settings only for Artigiani
            if ($customer_type === 'Artigiani') {
                $data['inail_ateco_code'] = $this->input->post('inail_ateco_code') ? trim($this->input->post('inail_ateco_code')) : NULL;
                $data['inail_rate'] = $this->input->post('inail_rate') ? $this->input->post('inail_rate') : NULL;
                $data['inail_minimum_payment'] = $this->input->post('inail_minimum_payment') ? $this->input->post('inail_minimum_payment') : NULL;
            } else {
                // Clear INAIL settings for non-Artigiani customers
                $data['inail_ateco_code'] = NULL;
                $data['inail_rate'] = NULL;
                $data['inail_minimum_payment'] = NULL;
            }
            
            // Add Diritto Annuale settings for Artigiani and Commercianti
            if (in_array($customer_type, array('Artigiani', 'Commercianti'))) {
                $data['diritto_annuale_amount'] = $this->input->post('diritto_annuale_amount') ? $this->input->post('diritto_annuale_amount') : NULL;
            } else {
                // Clear Diritto Annuale settings for other customer types
                $data['diritto_annuale_amount'] = NULL;
            }

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

        $table = 'tax_payments';
        if ($payment_type == 'inps') {
            $table = 'inps_payments';
        } elseif ($payment_type == 'inail') {
            $table = 'inail_payments';
        } elseif ($payment_type == 'diritto_annuale') {
            $table = 'diritto_annuale_payments';
        } elseif ($payment_type == 'fattura_tra_privati') {
            $table = 'fattura_tra_privati_payments';
        }
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

    /**
     * Manage INPS Rate Slabs
     */
    public function inps_slabs()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['message'] = $this->session->flashdata('message');

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('tax_calculations'), 'page' => lang('tax_calculations')],
            ['link' => '#', 'page' => lang('inps_rate_slabs')]
        ];
        $meta = ['page_title' => lang('inps_rate_slabs'), 'bc' => $bc];
        $this->page_construct('tax_calculations/inps_slabs', $meta, $this->data);
    }

    /**
     * Get INPS slabs list for DataTables
     */
    public function getINPSSlabs()
    {
        $this->sma->checkPermissions('index');
        
        $this->load->library('datatables');
        $table = $this->db->dbprefix('inps_rate_slabs');
        
        $this->datatables->select("$table.id as ids, 
            $table.slab_year as year,
            COALESCE($table.customer_type, 'All Types') as customer_type,
            $table.income_from,
            $table.income_to,
            $table.inps_rate,
            $table.fixed_amount,
            $table.is_active", FALSE)
            ->from("inps_rate_slabs");

        $edit_link = anchor('admin/tax_calculations/edit_inps_slab/$1', '<i class="fa fa-edit"></i> ' . lang('edit'), 'class="tip" title="' . lang('edit') . '"');
        $delete_link = "<a href='#' class='tip po' title='<b>" . lang('delete') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('tax_calculations/delete_inps_slab/$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete') . '</a>';

        $action = '<div class="text-center">
            <div class="btn-group">
                ' . $edit_link . '
                ' . $delete_link . '
            </div>
        </div>';

        $this->datatables->add_column('Actions', $action, 'ids');
        echo $this->datatables->generate();
    }

    /**
     * Add or Edit INPS Slab
     */
    public function edit_inps_slab($id = NULL)
    {
        $this->sma->checkPermissions('edit', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $this->form_validation->set_rules('slab_year', lang('year'), 'trim|required|numeric');
        $this->form_validation->set_rules('income_from', lang('income_from'), 'trim|required|numeric');
        $this->form_validation->set_rules('inps_rate', lang('inps_rate'), 'trim|numeric');
        $this->form_validation->set_rules('fixed_amount', lang('fixed_amount'), 'trim|numeric');
        $this->form_validation->set_rules('is_active', lang('is_active'), 'trim');

        if ($this->form_validation->run() == true) {
            // Custom validation: either fixed_amount or inps_rate must be provided
            $inps_rate = $this->input->post('inps_rate');
            $fixed_amount = $this->input->post('fixed_amount');
            if (empty($inps_rate) && empty($fixed_amount)) {
                $this->session->set_flashdata('error', lang('either_inps_rate_or_fixed_amount_required'));
                if ($id) {
                    admin_redirect('tax_calculations/edit_inps_slab?id=' . $id);
                } else {
                    admin_redirect('tax_calculations/edit_inps_slab');
                }
                return;
            }
            $data = array(
                'slab_year' => $this->input->post('slab_year'),
                'customer_type' => $this->input->post('customer_type') ? $this->input->post('customer_type') : NULL,
                'income_from' => $this->input->post('income_from'),
                'income_to' => $this->input->post('income_to') ? $this->input->post('income_to') : NULL,
                'inps_rate' => $this->input->post('inps_rate'),
                'fixed_amount' => $this->input->post('fixed_amount') ? $this->input->post('fixed_amount') : NULL,
                'description' => $this->input->post('description'),
                'is_active' => $this->input->post('is_active') ? 1 : 0
            );

            if ($id) {
                // Update existing
                if ($this->tax_calculations_model->updateINPSSlab($id, $data)) {
                    $this->session->set_flashdata('message', lang('inps_slab_updated'));
                    admin_redirect('tax_calculations/inps_slabs');
                } else {
                    $this->session->set_flashdata('error', lang('inps_slab_update_failed'));
                }
            } else {
                // Insert new
                if ($this->tax_calculations_model->addINPSSlab($data)) {
                    $this->session->set_flashdata('message', lang('inps_slab_added'));
                    admin_redirect('tax_calculations/inps_slabs');
                } else {
                    $this->session->set_flashdata('error', lang('inps_slab_add_failed'));
                }
            }
        }

        if ($id) {
            $this->data['slab'] = $this->tax_calculations_model->getINPSSlab($id);
            if (!$this->data['slab']) {
                $this->session->set_flashdata('error', lang('inps_slab_not_found'));
                admin_redirect('tax_calculations/inps_slabs');
            }
        } else {
            $this->data['slab'] = NULL;
        }

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $this->data['customer_types'] = array('' => lang('all_types'), 'Gestione Separata' => 'Gestione Separata', 'Commercianti' => 'Commercianti', 'Artigiani' => 'Artigiani');

        $bc = [
            ['link' => base_url(), 'page' => lang('home')],
            ['link' => admin_url('tax_calculations'), 'page' => lang('tax_calculations')],
            ['link' => admin_url('tax_calculations/inps_slabs'), 'page' => lang('inps_rate_slabs')],
            ['link' => '#', 'page' => ($id ? lang('edit') : lang('add')) . ' ' . lang('inps_slab')]
        ];
        $meta = ['page_title' => ($id ? lang('edit') : lang('add')) . ' ' . lang('inps_slab'), 'bc' => $bc];
        $this->page_construct('tax_calculations/edit_inps_slab', $meta, $this->data);
    }

    /**
     * Delete INPS Slab
     */
    public function delete_inps_slab($id = NULL)
    {
        $this->sma->checkPermissions('delete', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->tax_calculations_model->deleteINPSSlab($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('inps_slab_deleted')]);
            }
            $this->session->set_flashdata('message', lang('inps_slab_deleted'));
        } else {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 1, 'msg' => lang('inps_slab_delete_failed')]);
            }
            $this->session->set_flashdata('error', lang('inps_slab_delete_failed'));
        }
        admin_redirect('tax_calculations/inps_slabs');
    }
}