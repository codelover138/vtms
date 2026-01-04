<?php
use PhpOffice\PhpSpreadsheet\IOFactory;
defined('BASEPATH') or exit('No direct script access allowed');

class Transfers extends MY_Controller
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
        $this->lang->admin_load('transfers', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('transfers_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path         = 'assets/uploads/';
        $this->thumbs_path         = 'assets/uploads/thumbs/';
        $this->image_types         = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types  = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size   = '1024';
        $this->data['logo']        = true;
    }



    public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        if ($this->transfers_model->deleteTransfer($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(['error' => 0, 'msg' => lang('transfer_deleted')]);
            }
            $this->session->set_flashdata('message', lang('transfer_deleted'));
            admin_redirect('welcome');
        }
    }


    public function email($transfer_id = null)
    {
        $this->sma->checkPermissions(false, true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }
        $transfer = $this->transfers_model->getTransferByID($transfer_id);
        $this->form_validation->set_rules('to', lang('to') . ' ' . lang('email'), 'trim|required|valid_email');
        $this->form_validation->set_rules('subject', lang('subject'), 'trim|required');
        $this->form_validation->set_rules('cc', lang('cc'), 'trim|valid_emails');
        $this->form_validation->set_rules('bcc', lang('bcc'), 'trim|valid_emails');
        $this->form_validation->set_rules('note', lang('message'), 'trim');

        if ($this->form_validation->run() == true) {
            if (!$this->session->userdata('view_right')) {
                $this->sma->view_rights($transfer->created_by);
            }
            $to      = $this->input->post('to');
            $subject = $this->input->post('subject');
            if ($this->input->post('cc')) {
                $cc = $this->input->post('cc');
            } else {
                $cc = null;
            }
            if ($this->input->post('bcc')) {
                $bcc = $this->input->post('bcc');
            } else {
                $bcc = null;
            }

            $this->load->library('parser');
            $parse_data = [
                'reference_number' => $transfer->transfer_no,
                'site_link'        => base_url(),
                'site_name'        => $this->Settings->site_name,
                'logo'             => '<img src="' . base_url() . 'assets/uploads/logos/' . $this->Settings->logo . '" alt="' . $this->Settings->site_name . '"/>',
            ];
            $msg        = $this->input->post('note');
            $message    = $this->parser->parse_string($msg, $parse_data);
            $attachment = $this->pdf($transfer_id, null, 'S');

            try {
                if ($this->sma->send_email($to, $subject, $message, null, null, $attachment, $cc, $bcc)) {
                    delete_files($attachment);
                    $this->session->set_flashdata('message', lang('email_sent'));
                    admin_redirect('transfers');
                }
            } catch (Exception $e) {
                $this->session->set_flashdata('error', $e->getMessage());
                redirect($_SERVER['HTTP_REFERER']);
            }
        } elseif ($this->input->post('send_email')) {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->session->set_flashdata('error', $this->data['error']);
            redirect($_SERVER['HTTP_REFERER']);
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));

            if (file_exists('./themes/' . $this->Settings->theme . '/admin/views/email_templates/transfer.html')) {
                $transfer_temp = file_get_contents('themes/' . $this->Settings->theme . '/admin/views/email_templates/transfer.html');
            } else {
                $transfer_temp = file_get_contents('./themes/default/admin/views/email_templates/transfer.html');
            }
            $this->data['subject'] = ['name' => 'subject',
                'id'                         => 'subject',
                'type'                       => 'text',
                'value'                      => $this->form_validation->set_value('subject', lang('transfer_order') . ' (' . $transfer->transfer_no . ') ' . lang('from') . ' ' . $transfer->from_warehouse_name),
            ];
            $this->data['note'] = ['name' => 'note',
                'id'                      => 'note',
                'type'                    => 'text',
                'value'                   => $this->form_validation->set_value('note', $transfer_temp),
            ];
            $this->data['warehouse'] = $this->site->getWarehouseByID($transfer->to_warehouse_id);

            $this->data['id']       = $transfer_id;
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'transfers/email', $this->data);
        }
    }

    public function getTransfers()
    {
        $this->sma->checkPermissions('index');
        $detail_link = anchor('admin/transfers/details?id=$1', '<i class="fa fa-file-text-o"></i> ' . lang('Transfer_Details'));
        $pdf_link      = anchor('admin/transfers/pdf?id=$1', '<i class="fa fa-file-pdf-o"></i> ' . lang('download_pdf'));
        $delete_link   = "<a href='#' class='tip po' title='<b>" . lang('delete_transfer') . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' id='a__$1' href='" . admin_url('transfers/delete?id=$1') . "'>"
            . lang('i_m_sure') . "</a> <button class='btn po-close'>" . lang('no') . "</button>\"  rel='popover'><i class=\"fa fa-trash-o\"></i> "
            . lang('delete_transfer') . '</a>';
        $action = '<div class="text-center"><div class="btn-group text-left">'
            . '<button type="button" class="btn btn-default btn-xs btn-primary dropdown-toggle" data-toggle="dropdown">'
            . lang('actions') . ' <span class="caret"></span></button>
        <ul class="dropdown-menu pull-right" role="menu">
            <li>' . $detail_link . '</li>
            <li>' . $pdf_link . '</li>
            <li>' . $delete_link . '</li>
        </ul>
    </div></div>';

        $this->load->library('datatables');
        $table = $this->db->dbprefix('income_data');
        $this->datatables->select("$table.reference_no,
            $table.reference_no as ref,
            MIN($table.created_at) AS created_at,
            MIN($table.customer_name) AS customer_name,
            MIN($table.date_transmission) AS start_date,
            MAX($table.date_transmission) AS end_date,
            SUM($table.taxable_sales) AS total_taxable_sales,
            SUM($table.sale_taxes) AS total_sale_taxes,
            COUNT(*) AS number_of_data", true)
            ->from('income_data')
            ->group_by("$table.reference_no");

        

        if (!$this->Owner && !$this->Admin && !$this->session->userdata('view_right')) {
            $this->datatables->where("{$this->db->dbprefix('income_data')}.created_by", $this->session->userdata('user_id'));
        }

        $this->datatables->add_column('Actions', $action, 'ref');
        echo $this->datatables->generate();
    }

    public function index()
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');

        $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => '#', 'page' => lang('transfers')]];
        $meta = ['page_title' => lang('transfers'), 'bc' => $bc];
        $this->page_construct('transfers/index', $meta, $this->data);
    }

    public function pdf($transfer_id = null, $view = null, $save_bufffer = null)
    {
        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }

        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer            = $this->transfers_model->get_monthly_taxable_sales($transfer_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($transfer[0]->created_by, true);
        }
        
        $this->data['transfer']       = $transfer;
        $this->data['tid']            = $transfer_id;
        $this->data['created_by']     = $this->site->getUser($transfer[0]->created_by);
       
        $name                         = lang('transfer') . '_' . str_replace('/', '_', $transfer[0]->reference_no) . '.pdf';
        $html                         = $this->load->view($this->theme . 'transfers/pdf', $this->data, true);
        if (!$this->Settings->barcode_img) {
            $html = preg_replace("'\<\?xml(.*)\?\>'", '', $html);
        }
        if ($view) {
            $this->load->view($this->theme . 'transfers/pdf', $this->data);
        } elseif ($save_bufffer) {
            return $this->sma->generate_pdf($html, $name, $save_bufffer);
        } else {
            $this->sma->generate_pdf($html, $name);
        }
    }

    public function suggestions()
    {
        $this->sma->checkPermissions('index', true);
        $term         = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed  = $this->sma->analyze_term($term);
        $sr        = $analyzed['term'];
        $option_id = $analyzed['option_id'];
        $sr        = addslashes($sr);
        $qty       = $analyzed['quantity'] ?? null;
        $bprice    = $analyzed['price']    ?? null;

        $rows = $this->transfers_model->getProductNames($sr, $warehouse_id);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c                     = uniqid(mt_rand(), true);
                $option                = false;
                $row->quantity         = 0;
                $row->item_tax_method  = $row->tax_method;
                $row->base_quantity    = 1;
                $row->base_unit        = $row->unit;
                $row->base_unit_cost   = $row->cost;
                $row->unit             = $row->purchase_unit ? $row->purchase_unit : $row->unit;
                $row->qty              = 1;
                $row->discount         = '0';
                $row->expiry           = '';
                $row->quantity_balance = 0;
                $row->ordered_quantity = 0;
                $options               = $this->transfers_model->getProductOptions($row->id, $warehouse_id);
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->transfers_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt       = json_decode('{}');
                    $opt->cost = 0;
                    $option_id = false;
                }
                $row->option = $option_id;
                $pis         = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    foreach ($pis as $pi) {
                        $row->quantity += $pi->quantity_balance;
                    }
                }
                if ($options) {
                    $option_quantity = 0;
                    foreach ($options as $option) {
                        $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                        if ($pis) {
                            foreach ($pis as $pi) {
                                $option_quantity += $pi->quantity_balance;
                            }
                        }
                        if ($option->quantity > $option_quantity) {
                            $option->quantity = $option_quantity;
                        }
                    }
                }
                if ($opt->cost != 0) {
                    $row->cost = $opt->cost;
                }
                $row->real_unit_cost = $row->cost;
                $units               = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate            = $this->site->getTaxRateByID($row->tax_rate);
                $row->qty            = $qty ? $qty : ($bprice ? $bprice / $row->cost : 1);

                $pr[] = ['id' => sha1($c . $r), 'item_id' => $row->id, 'label' => $row->name . ' (' . $row->code . ')',
                    'row'     => $row, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options, ];
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json([['id' => 0, 'label' => lang('no_match_found'), 'value' => $term]]);
        }
    }

    public function transfer_actions()
    {
        if (!$this->Owner) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER['HTTP_REFERER']);
        }

        $this->form_validation->set_rules('form_action', lang('form_action'), 'required');

        if ($this->form_validation->run() == true) {
            if (!empty($_POST['val'])) {
                if ($this->input->post('form_action') == 'delete') {
                    foreach ($_POST['val'] as $id) {
                        $this->transfers_model->deleteTransfer($id);
                    }
                    $this->session->set_flashdata('message', lang('transfers_deleted'));
                    redirect($_SERVER['HTTP_REFERER']);
                } elseif ($this->input->post('form_action') == 'combine') {
                    $html = $this->combine_pdf($_POST['val']);
                } elseif ($this->input->post('form_action') == 'export_excel') {
                    $this->load->library('excel');
                    $this->excel->setActiveSheetIndex(0);
                    $this->excel->getActiveSheet()->setTitle(lang('transfers'));
                    $this->excel->getActiveSheet()->SetCellValue('A1', lang('date'));
                    $this->excel->getActiveSheet()->SetCellValue('B1', lang('reference_no'));
                    $this->excel->getActiveSheet()->SetCellValue('C1', lang('from_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('D1', lang('to_warehouse'));
                    $this->excel->getActiveSheet()->SetCellValue('E1', lang('grand_total'));
                    $this->excel->getActiveSheet()->SetCellValue('F1', lang('status'));

                    $row = 2;
                    foreach ($_POST['val'] as $id) {
                        $tansfer = $this->transfers_model->getTransferByID($id);
                        $this->excel->getActiveSheet()->SetCellValue('A' . $row, $this->sma->hrld($tansfer->date));
                        $this->excel->getActiveSheet()->SetCellValue('B' . $row, $tansfer->reference_no);
                        $this->excel->getActiveSheet()->SetCellValue('C' . $row, $tansfer->from_warehouse);
                        $this->excel->getActiveSheet()->SetCellValue('D' . $row, $tansfer->to_warehouse);
                        $this->excel->getActiveSheet()->SetCellValue('E' . $row, $tansfer->grand_total);
                        $this->excel->getActiveSheet()->SetCellValue('F' . $row, $tansfer->status);
                        $row++;
                    }

                    $this->excel->getActiveSheet()->getColumnDimension('A')->setWidth(20);
                    $this->excel->getActiveSheet()->getColumnDimension('B')->setWidth(20);
                    $this->excel->getDefaultStyle()->getAlignment()->setVertical('center');
                    $filename = 'tansfers_' . date('Y_m_d_H_i_s');
                    $this->load->helper('excel');
                    create_excel($this->excel, $filename);
                }
            } else {
                $this->session->set_flashdata('error', lang('no_transfer_selected'));
                redirect($_SERVER['HTTP_REFERER']);
            }
        } else {
            $this->session->set_flashdata('error', validation_errors());
            redirect($_SERVER['HTTP_REFERER']);
        }
    }

    public function transfer_by_pos()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('csv_file', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run()) {
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name . ' ' .$customer_details->last_name;
            $data = array(
                'reference_no' => $transfer_no,
                'customer_id' => $customer_id,
                'customer_name' => $customer,
                'created_by' => $this->session->userdata('user_id'),
                );
            
            if (isset($_FILES['userfile'])) {
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('transfers/transfer_by_csv');
                
                }

                $csv = $this->upload->file_name;
                $data=$this->import_file_to_db($this->digital_upload_path . $csv,$data);
                $id_sending_list = array_column($data, 'id_sending');
                $isExist=$this->transfers_model->checkDuplicateData($id_sending_list,$data);
                if($isExist !== ''){
                    $this->session->set_flashdata('error', 'Duplicate Data:'. ' '.$isExist);
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }

        }

        if ($this->form_validation->run() == true && $this->transfers_model->saveData($data,$transfer_no)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang('Data_added.'));
            admin_redirect('transfers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rnumber']    = $this->site->getReference('to');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('transfers'), 'page' => lang('transfers')], ['link' => '#', 'page' => lang('Add_Transfer_By_POS')]];
            $meta = ['page_title' => lang('Add_Transfer_By_POS'), 'bc' => $bc];
            $this->page_construct('transfers/transfer_by_csv', $meta, $this->data);
        }
    }


    public function transfer_by_fattura()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('csv_file', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run()) {
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name . ' ' .$customer_details->last_name;
            $data = array(
                'reference_no' => $transfer_no,
                'customer_id' => $customer_id,
                'customer_name' => $customer,
                'created_by' => $this->session->userdata('user_id'),
                );
            
            if (isset($_FILES['userfile'])) {
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('transfers/transfer_by_fattura');
                
                }

                $csv = $this->upload->file_name;
               
                $data=$this->read_and_save_excel($this->digital_upload_path . $csv,$data);
                $id_sending_list = array_column($data['cleaned_data'], 'id_sending');
                $isExist=$this->transfers_model->checkDuplicateData($id_sending_list,$data['cleaned_data']);
                if($isExist !== ''){
                    $this->session->set_flashdata('error', 'Duplicate Data:'. ' '.$isExist);
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }

        }

        if ($this->form_validation->run() == true && $this->transfers_model->saveFatturaData($data,$transfer_no)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang('Data_added.'));
            admin_redirect('transfers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rnumber']    = $this->site->getReference('so');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('transfers'), 'page' => lang('transfers')], ['link' => '#', 'page' => lang('Add_Transfer_By_Fattura')]];
            $meta = ['page_title' => lang('Add_Transfer_By_Fattura'), 'bc' => $bc];
            $this->page_construct('transfers/transfer_by_fattura', $meta, $this->data);
        }
    }

    private function import_file_to_db($file_path,$obj) {
        // Load PhpSpreadsheet
        try {
        $spreadsheet = IOFactory::load($file_path);
        $sheet = $spreadsheet->getActiveSheet();
        $data = $sheet->toArray();

        // Map and clean the data
        $expected_header = [
            'Id invio',
            'Matricola dispositivo',
            'Data e ora rilevazione',
            'Data e ora trasmissione',
            'Ammontare delle vendite (totale in euro)',
            'Imponibile vendite (totale in euro)',
            'Imposta vendite (totale in euro)',
            'Periodo di inattivita\' da',
            'Periodo di inattivita\' a'
        ];

        // Extract header from the uploaded file
        $header = array_shift($data); // Remove the header row

        // Validate the header
        if ($header !== $expected_header) {
             $this->session->set_flashdata('error', 'The CSV format does not match the expected structure.');
                redirect($_SERVER['HTTP_REFERER']);
        }


        $cleaned_data = [];
        foreach ($data as $row) {
            $cleaned_data[] = [
                'id_sending' => $row[0] ? str_replace("'", "", $row[0]) :'',
                'device_number' =>  $row[1] ? str_replace("'", "", $row[1]) :'',
                'date_detection' => $this->format_date($row[2]),
                'date_transmission' => $this->format_date($row[3]),
                'sales_amount' => $this->clean_decimal($row[4]),
                'taxable_sales' => $this->clean_decimal($row[5]),
                'sale_taxes' => $this->clean_decimal($row[6]),
                'periodo_inattivita_da' => $this->format_date($row[7]),
                'periodo_inattivita_a' => $this->format_date($row[8]),
                'created_by' => $this->session->userdata('user_id'),
                'customer_id' => $obj['customer_id'],
                'customer_name' => $obj['customer_name'],
                'reference_no' => $obj['reference_no'],
                'pos'=>true,
                

            ];
        }
    }catch (Exception $e) {
            echo "Error reading the Excel file: " . $e->getMessage();
        }

       return $cleaned_data;
    }


    private function read_and_save_excel($file_path,$obj) {
        try {
            // Load the spreadsheet
            $spreadsheet = IOFactory::load($file_path);
            $sheet = $spreadsheet->getActiveSheet();
            $data = $sheet->toArray();

            $expected_header = [
                'Tipo fattura', 'Tipo documento', 'Numero fattura / Documento', 
                'Data emissione', 'Data trasmissione', 'Codice fiscale fornitore', 
                'Partita IVA fornitore', 'Denominazione fornitore', 
                'Codice fiscale cliente', 'Partita IVA cliente', 'Denominazione cliente', 
                'Imponibile/Importo (totale in euro)', 'Imposta (totale in euro)', 
                'Sdi/file', 'Fatture consegnate', 'Data consegna/Presa visione', 'Bollo virtuale'
            ];
            // Extract header from the uploaded file
            $header = array_shift($data); // Remove the header row
    
            // Validate the header
            if ($header !== $expected_header) {
                 $this->session->set_flashdata('error', 'The CSV format does not match the expected structure.');
                    redirect($_SERVER['HTTP_REFERER']);
            }
    
            $insert_data = [];
            $cleaned_data = [];

            foreach ($data as $row) {
                $insert_data[] = [
                    'tipo_fattura' => $row[0] ? str_replace("'", "", $row[0]) :'',                  // Tipo fattura
                    'tipo_documento' =>$row[1] ? str_replace("'", "", $row[1]) :'',                 // Tipo documento
                    'numero_fattura' => $row[2] ? str_replace("'", "", $row[2]) :'',                 // Numero fattura / Documento
                    'data_emissione' => $this->format_date_without_time($row[3]), // Data emissione
                    'data_trasmissione' => $this->format_date_without_time($row[4]), // Data trasmissione
                    'codice_fiscale_fornitore' => $row[5] ? str_replace("'", "", $row[5]) :'',       // Codice fiscale fornitore
                    'partita_iva_fornitore' => $row[6] ? str_replace("'", "", $row[6]) :'',          // Partita IVA fornitore
                    'denominazione_fornitore' => $row[7] ? str_replace("'", "", $row[7]) :'',       // Denominazione fornitore
                    'codice_fiscale_cliente' => $row[8] ? str_replace("'", "", $row[8]) :'',         // Codice fiscale cliente
                    'partita_iva_cliente' => $row[9] ? str_replace("'", "", $row[9]) :'',         // Partita IVA cliente
                    'denominazione_cliente' => $row[10] ? str_replace("'", "", $row[10]) :'',         // Denominazione cliente
                    'imponibile_importo' => $this->clean_decimal($row[11]), // Imponibile/Importo (totale in euro)
                    'imposta_totale' => $this->clean_decimal($row[12]),     // Imposta (totale in euro)
                    'sdi_file' => $row[13] ? str_replace("'", "", $row[13]) :'',                     // Sdi/file
                    'fatture_consegnate' => $row[14] ? str_replace("'", "", $row[14]) :'',          // Fatture consegnate
                    'data_consegna' => $this->format_date_without_time($row[15]), // Data consegna/Presa visione
                    'bollo_virtuale' => $row[16] ? str_replace("'", "", $row[16]) :'',                // Bollo virtuale
                ];

                $cleaned_data[] = [
                    'id_sending' => $row[13] ? str_replace("'", "", $row[13]) :'',
                    'device_number' =>  $row[5] ? str_replace("'", "", $row[5]) :'',
                    'date_detection' => $this->format_date_without_time($row[3]),
                    'date_transmission' => $this->format_date_without_time($row[4]),
                    'sales_amount' => $this->clean_decimal($row[11]),
                    'taxable_sales' => $this->clean_decimal($row[11]),
                    'sale_taxes' => $this->clean_decimal($row[12]),
                    'periodo_inattivita_da' => $this->format_date_without_time($row[15]),
                    'periodo_inattivita_a' => $this->format_date_without_time($row[15]),
                    'created_by' => $this->session->userdata('user_id'),
                    'customer_id' => $obj['customer_id'],
                    'customer_name' => $obj['customer_name'],
                    'reference_no' => $obj['reference_no'],
                    'pos'=>false,
                    
    
                ];
      //            var_dump($cleaned_data);
      //  var_dump( $insert_data);
      //  die();
            }
        
    }catch (Exception $e) {
        echo "Error reading the Excel file: " . $e->getMessage();
    }
    return array('cleaned_data'=>$cleaned_data,'insert_data'=>$insert_data);
}

    private function format_date($date_string) {
        // Convert date strings like '31/03/2024 19:10:55' to '2024-03-31 19:10:55'
        return DateTime::createFromFormat('d/m/Y H:i:s', $date_string) 
            ? DateTime::createFromFormat('d/m/Y H:i:s', $date_string)->format('Y-m-d H:i:s') 
            : null;
    }

    private function format_date_without_time($date_string) {
        // Convert date strings to 'YYYY-MM-DD' format for MySQL
        return DateTime::createFromFormat('d/m/Y', $date_string) 
            ? DateTime::createFromFormat('d/m/Y', $date_string)->format('Y-m-d') 
            : null;
    }

    private function clean_decimal($value) {
        // Convert '000000000360,66' to 360.66
        $cleaned = str_replace(['.', ','], ['', '.'], $value);
        return floatval($cleaned);
    }

    public function transfer_by_fattura_privati()
    {
        $this->sma->checkPermissions('csv');
        $this->load->helper('security');
        $this->form_validation->set_message('is_natural_no_zero', lang('no_zero_required'));
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('csv_file', lang('upload_file'), 'xss_clean');

        if ($this->form_validation->run()) {
            $transfer_no = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name . ' ' .$customer_details->last_name;
            $data = array(
                'reference_no' => $transfer_no,
                'customer_id' => $customer_id,
                'customer_name' => $customer,
                'created_by' => $this->session->userdata('user_id'),
                );
            
            if (isset($_FILES['userfile'])) {
                $config['upload_path']   = $this->digital_upload_path;
                $config['allowed_types'] = 'csv';
                $config['max_size']      = $this->allowed_file_size;
                $config['overwrite']     = true;
                $this->load->library('upload', $config);
                $this->upload->initialize($config);

                if (!$this->upload->do_upload()) {
                    $error = $this->upload->display_errors();
                    $this->session->set_flashdata('error', $error);
                    admin_redirect('transfers/transfer_by_fattura_privati');
                
                }

                $csv = $this->upload->file_name;
               
                $data=$this->read_and_save_csv_privati($this->digital_upload_path . $csv,$data);
                $id_sending_list = array_column($data['cleaned_data'], 'id_sending');
                $isExist=$this->transfers_model->checkDuplicateData($id_sending_list,$data['cleaned_data']);
                if($isExist !== ''){
                    $this->session->set_flashdata('error', 'Duplicate Data:'. ' '.$isExist);
                    redirect($_SERVER['HTTP_REFERER']);
                }
            }

        }

        if ($this->form_validation->run() == true && $this->transfers_model->savePrivatiData($data,$transfer_no)) {
            $this->session->set_userdata('remove_tols', 1);
            $this->session->set_flashdata('message', lang('Data_added.'));
            admin_redirect('transfers');
        } else {
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
            $this->data['rnumber']    = $this->site->getReference('to');
            $bc   = [['link' => base_url(), 'page' => lang('home')], ['link' => admin_url('transfers'), 'page' => lang('transfers')], ['link' => '#', 'page' => lang('Add_Transfer_By_Fattura_Privati')]];
            $meta = ['page_title' => lang('Add_Transfer_By_Fattura_Privati'), 'bc' => $bc];
            $this->page_construct('transfers/transfer_by_fattura_privati', $meta, $this->data);
        }
    }

    private function read_and_save_csv_privati($file_path,$obj) {
        try {
            // Read CSV file with semicolon delimiter
            $handle = fopen($file_path, 'r');
            if ($handle === FALSE) {
                throw new Exception('Cannot open file: ' . $file_path);
            }

            // Read header row
            $header = fgetcsv($handle, 0, ';');
            
            $expected_header = [
                'Id invio',
                'Matricola dispositivo',
                'Data e ora rilevazione',
                'Data e ora trasmissione',
                'Ammontare delle vendite (totale in euro)',
                'Imponibile vendite (totale in euro)',
                'Imposta vendite (totale in euro)',
                'Periodo di inattivita\' da',
                'Periodo di inattivita\' a'
            ];

            // Validate the header
            if ($header !== $expected_header) {
                fclose($handle);
                $this->session->set_flashdata('error', 'The CSV format does not match the expected structure.');
                redirect($_SERVER['HTTP_REFERER']);
            }
    
            $insert_data = [];
            $cleaned_data = [];

            // Read data rows
            while (($row = fgetcsv($handle, 0, ';')) !== FALSE) {
                if (count($row) < 9) continue; // Skip incomplete rows
                
                // Clean and check sales and tax values
                $sales_amount = $this->clean_decimal($row[4]);
                $tax_amount = $this->clean_decimal($row[6]);
                
                // Skip rows where both sales amount and tax amount are zero
                if ($sales_amount == 0 && $tax_amount == 0) {
                    continue;
                }
                
                // Clean other decimal values
                $imponibile_vendite = $this->clean_decimal($row[5]);
                
                $insert_data[] = [
                    'id_invio' => $row[0] ? trim($row[0]) : '',
                    'matricola_dispositivo' => $row[1] ? trim($row[1]) : '',
                    'data_ora_rilevazione' => $this->format_date($row[2]),
                    'data_ora_trasmissione' => $this->format_date($row[3]),
                    'ammontare_vendite' => $sales_amount,
                    'imponibile_vendite' => $imponibile_vendite,
                    'imposta_vendite' => $tax_amount,
                    'periodo_inattivita_da' => $row[7] ? $this->format_date($row[7]) : NULL,
                    'periodo_inattivita_a' => $row[8] ? $this->format_date($row[8]) : NULL,
                    'created_by' => $this->session->userdata('user_id'),
                    'customer_id' => $obj['customer_id'],
                    'customer_name' => $obj['customer_name'],
                    'reference_no' => $obj['reference_no'],
                ];

                $cleaned_data[] = [
                    'id_sending' => $row[0] ? trim($row[0]) : '',
                    'device_number' => $row[1] ? trim($row[1]) : '',
                    'date_detection' => $this->format_date($row[2]),
                    'date_transmission' => $this->format_date($row[3]),
                    'sales_amount' => $sales_amount,
                    'taxable_sales' => $imponibile_vendite,
                    'sale_taxes' => $tax_amount,
                    'periodo_inattivita_da' => $row[7] ? $this->format_date($row[7]) : NULL,
                    'periodo_inattivita_a' => $row[8] ? $this->format_date($row[8]) : NULL,
                    'created_by' => $this->session->userdata('user_id'),
                    'customer_id' => $obj['customer_id'],
                    'customer_name' => $obj['customer_name'],
                    'reference_no' => $obj['reference_no'],
                    'pos' => 2, // Set pos to 2 for privati
                ];
            }
            
            fclose($handle);
        
        } catch (Exception $e) {
            if (isset($handle) && $handle !== FALSE) {
                fclose($handle);
            }
            $this->session->set_flashdata('error', 'Error reading the CSV file: ' . $e->getMessage());
            redirect($_SERVER['HTTP_REFERER']);
        }
        
        return array('cleaned_data'=>$cleaned_data,'insert_data'=>$insert_data);
    }

    public function update_status($id)
    {
        $this->form_validation->set_rules('status', lang('status'), 'required');

        if ($this->form_validation->run() == true) {
            $status = $this->input->post('status');
            $note   = $this->sma->clear_tags($this->input->post('note'));
        } elseif ($this->input->post('update')) {
            $this->session->set_flashdata('error', validation_errors());
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'transfers');
        }

        if ($this->form_validation->run() == true && $this->transfers_model->updateStatus($id, $status, $note)) {
            $this->session->set_flashdata('message', lang('status_updated'));
            admin_redirect($_SERVER['HTTP_REFERER'] ?? 'transfers');
        } else {
            $this->data['inv']      = $this->transfers_model->getTransferByID($id);
            $this->data['modal_js'] = $this->site->modal_js();
            $this->load->view($this->theme . 'transfers/update_status', $this->data);
        }
    }

    public function view($transfer_id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $transfer_id = $this->input->get('id');
        }

       
        $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
        $transfer            = $this->transfers_model->get_monthly_taxable_sales($transfer_id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($transfer[0]->created_by, true);
        }
        
        $this->data['transfer']       = $transfer;
        $this->data['tid']            = $transfer_id;
        $this->data['created_by']     = $this->site->getUser($transfer[0]->created_by);
        
       
        $this->load->view($this->theme . 'transfers/view', $this->data);
    }


    public function details($id = null)
    {
        $this->sma->checkPermissions('index');
        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $transfer_list = $this->transfers_model->getTransferByReferenceNo($id);
        $transfer = $this->transfers_model->get_monthly_taxable_sales($id);
        if (!$this->session->userdata('view_right')) {
            $this->sma->view_rights($transfer[0]->created_by, true);
        }
        $this->data['transfer']       = $transfer;
        $this->data['transfer_list']  = $transfer_list;
        $this->data['tid']            = $id;
        $this->data['customer'] = $this->site->getCompanyByID($transfer[0]->customer_id);
        $this->data['created_by']     = $this->site->getUser($transfer[0]->created_by);

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('transfers'), 'page' => lang('transfers')), array('link' => '#', 'page' => lang('Details')));
        $meta = array('page_title' => lang('view_sales_details'), 'bc' => $bc);
        $this->page_construct('transfers/details', $meta, $this->data);
    }
}