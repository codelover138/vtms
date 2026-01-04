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
        if ($this->loggedIn && $this->Customer) {
            redirect('dashboard');
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
            redirect('/login'); 
        }
        if ($this->Staff) { redirect('admin/welcome'); }
        if (!$this->Customer) { redirect('/'); }

        // Load tax calculations model and language
        $this->load->admin_model('tax_calculations_model');
        $user_language = isset($this->Settings->user_language) ? $this->Settings->user_language : (isset($this->Settings->language) ? $this->Settings->language : 'english');
        $this->lang->admin_load('sma', $user_language);
        $this->lang->admin_load('tax_calculations', $user_language);
        
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

        // Get current year
        $current_year = date('Y');
        
        // Get all tax calculations for this customer
        $this->data['tax_calculations'] = $this->tax_calculations_model->getAllTaxCalculations($customer_id);
        
        // Get tax year from request or use latest
        $requested_year = $this->input->get('year');
        $latest_calculation = !empty($this->data['tax_calculations']) ? $this->data['tax_calculations'][0] : null;
        $tax_year = $requested_year ? $requested_year : ($latest_calculation ? $latest_calculation->tax_year : $current_year);
        $this->data['tax_year'] = $tax_year;
        
        // Get tax calculation for selected year
        $this->data['tax_calculation'] = $this->tax_calculations_model->getTaxCalculation($customer_id, $tax_year);
        $this->data['latest_tax_calculation'] = $this->data['tax_calculation'] ? $this->data['tax_calculation'] : $latest_calculation;
        
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

    function language($lang) {
        $folder = 'app/language/';
        $languagefiles = scandir($folder);
        if (in_array($lang, $languagefiles)) {
            set_cookie('shop_language', $lang, 31536000);
        }
        redirect($_SERVER["HTTP_REFERER"]);
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
