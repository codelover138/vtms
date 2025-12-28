<?php defined('BASEPATH') or exit('No direct script access allowed');

class Communication extends MY_Controller
{

    public function __construct()
    {
        parent::__construct();

        if (!$this->loggedIn) {
            $this->session->set_userdata('requested_page', $this->uri->uri_string());
            $this->sma->md('login');
        }
        if ($this->Supplier) {
            $this->session->set_flashdata('warning', lang('access_denied'));
            redirect($_SERVER["HTTP_REFERER"]);
        }
        $this->lang->admin_load('sales', $this->Settings->user_language);
        $this->load->library('form_validation');
        $this->load->admin_model('communication_model');
        $this->digital_upload_path = 'files/';
        $this->upload_path = 'assets/uploads/';
        $this->thumbs_path = 'assets/uploads/thumbs/';
        $this->image_types = 'gif|jpg|jpeg|png|tif';
        $this->digital_file_types = 'zip|psd|ai|rar|pdf|doc|docx|xls|xlsx|ppt|pptx|gif|jpg|jpeg|png|tif|txt';
        $this->allowed_file_size = '20480';
        $this->data['logo'] = true;
    }

    public function index($warehouse_id = null)
    {
        $this->sma->checkPermissions();

        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        if ($this->Owner || $this->Admin || !$this->session->userdata('warehouse_id')) {
            $this->data['warehouses'] = $this->site->getAllWarehouses();
            $this->data['warehouse_id'] = $warehouse_id;
            $this->data['warehouse'] = $warehouse_id ? $this->site->getWarehouseByID($warehouse_id) : null;
        } else {
            $this->data['warehouses'] = null;
            $this->data['warehouse_id'] = $this->session->userdata('warehouse_id');
            $this->data['warehouse'] = $this->session->userdata('warehouse_id') ? $this->site->getWarehouseByID($this->session->userdata('warehouse_id')) : null;
        }

        $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => '#', 'page' => lang('Communication')));
        $meta = array('page_title' => lang('Communication'), 'bc' => $bc);
        $this->page_construct('communication/index', $meta, $this->data);
    }

    public function getData($warehouse_id = null)
    {
        $this->sma->checkPermissions('index');

        if ((!$this->Owner || !$this->Admin) && !$warehouse_id) {
            $user = $this->site->getUser();
            $warehouse_id = $user->warehouse_id;
        }
        
        $edit_link = anchor('admin/communication/edit/$1', '<i class="fa fa-edit"></i> ' . lang('Edit'), 'class="sledit"');
       
        $delete_link = "<a href='#' class='po' title='<b>" . lang("Delete") . "</b>' data-content=\"<p>"
            . lang('r_u_sure') . "</p><a class='btn btn-danger po-delete' href='" . admin_url('communication/delete/$1') . "'>"
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
        //$action = '<div class="text-center">' . $detail_link . ' ' . $edit_link . ' ' . $email_link . ' ' . $delete_link . '</div>';

        $this->load->library('datatables');
        if ($warehouse_id) {
            $this->datatables
                ->select("{$this->db->dbprefix('communication')}.id as id, {$this->db->dbprefix('communication')}.date, reference_no, {$this->db->dbprefix('warehouses')}.name as wname, {$this->db->dbprefix('communication')}.customer,{$this->db->dbprefix('companies')}.phone, CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by,SUBSTRING({$this->db->dbprefix('communication')}.note, 1, 30) AS short_description")
                ->from('communication')
                ->join('warehouses', 'warehouses.id=communication.warehouse_id', 'inner')
                  ->join('companies', 'companies.id=communication.customer_id', 'inner')
                 ->join('users', 'users.id=communication.created_by', 'inner')
                ->where('communication.warehouse_id', $warehouse_id);
        } else {
            $this->datatables
               ->select("{$this->db->dbprefix('communication')}.id as id, {$this->db->dbprefix('communication')}.date, reference_no,{$this->db->dbprefix('warehouses')}.name as wname, {$this->db->dbprefix('communication')}.customer,{$this->db->dbprefix('companies')}.phone,CONCAT(" . $this->db->dbprefix('users') . ".first_name, ' ', " . $this->db->dbprefix('users') . ".last_name) as created_by,SUBSTRING({$this->db->dbprefix('communication')}.note, 1, 30) AS short_description")
                ->from('communication')
                  ->join('users', 'users.id=communication.created_by', 'inner')
                  ->join('companies', 'companies.id=communication.customer_id', 'inner')
                ->join('warehouses', 'warehouses.id=communication.warehouse_id', 'inner');
        }
        $this->datatables->add_column("Actions", $action, "id");
        echo $this->datatables->generate();
    }

    public function modal_view($id = null)
    {
        $this->sma->checkPermissions('index', true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $this->data['error'] = (validation_errors()) ? validation_errors() : $this->session->flashdata('error');
        $inv = $this->communication_model->getCommunicationByID($id);
       
        $this->data['customer'] = $this->site->getCompanyByID($inv->customer_id);
       
        $this->data['created_by'] = $this->site->getUser($inv->created_by);
        $this->data['updated_by'] = $inv->updated_by ? $this->site->getUser($inv->updated_by) : null;
        $this->data['warehouse'] = $this->site->getWarehouseByID($inv->warehouse_id);
        $this->data['inv'] = $inv;
       
       
        $this->load->view($this->theme . 'communication/modal_view', $this->data);
    }

 

    /* ------------------------------------------------------------------ */

    public function add()
    {
        $this->sma->checkPermissions();
       
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
        $this->form_validation->set_rules('date', lang("Date"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        
        $this->form_validation->set_rules('note', lang("note"), 'required');
       

        if ($this->form_validation->run() == true) {

            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
          
            $date = $this->sma->fld(trim($this->input->post('date')));
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name . ' ' .$customer_details->last_name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $data = array(
                  'date' => $date,
                  'reference_no' => $reference,
                  'customer_id' => $customer_id,
                  'customer' => $customer,
                  'warehouse_id' => $warehouse_id,
                  'created_by' => $this->session->userdata('user_id'),
                
                  'note' => $note,
                  );
           
        }

        if ($this->form_validation->run() == true && $this->communication_model->add($data)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("Data_added."));
            admin_redirect("communication");
        } else {

         
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $this->data['warehouses'] = $this->site->getAllWarehouses();
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('communication'), 'page' => lang('Communication')), array('link' => '#', 'page' => lang('add')));
            $meta = array('page_title' => lang('add'), 'bc' => $bc);
            $this->page_construct('communication/add', $meta, $this->data);
        }
    }

    /* ------------------------------------------------------------------------ */

    public function edit($id = null)
    {
        $this->sma->checkPermissions();

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }
        $inv = $this->communication_model->getCommunicationByID($id);
        
       
        $this->form_validation->set_rules('customer', lang("customer"), 'required');
          $this->form_validation->set_rules('date', lang("Date"), 'required');
        $this->form_validation->set_rules('warehouse', lang("warehouse"), 'required');
        
        $this->form_validation->set_rules('note', lang("note"), 'required');

        if ($this->form_validation->run() == true) {
            $reference = $this->input->post('reference_no') ? $this->input->post('reference_no') : $this->site->getReference('to');
            $date = $this->sma->fld(trim($this->input->post('date')));
            $warehouse_id = $this->input->post('warehouse');
            $customer_id = $this->input->post('customer');
            $customer_details = $this->site->getCompanyByID($customer_id);
            $customer = $customer_details->name . ' ' .$customer_details->last_name;
            $note = $this->sma->clear_tags($this->input->post('note'));
            $data = array(
                  'date' => $date,
                  'reference_no' => $reference,
                  'customer_id' => $customer_id,
                  'customer' => $customer,
                  'warehouse_id' => $warehouse_id,
                  'updated_by' => $this->session->userdata('user_id'),
                  'note' => $note,
                  'updated_at'=>date('Y-m-d H:i:s')
                  );
        }

        if ($this->form_validation->run() == true && $this->communication_model->update($id, $data)) {
            $this->session->set_userdata('remove_slls', 1);
            $this->session->set_flashdata('message', lang("data_updated."));
            admin_redirect('communication');
        } else {

            
            $this->data['inv'] = $this->communication_model->getCommunicationByID($id);
            $this->data['error'] = (validation_errors() ? validation_errors() : $this->session->flashdata('error'));
           
            $this->data['warehouses'] = $this->site->getAllWarehouses();
           
            $bc = array(array('link' => base_url(), 'page' => lang('home')), array('link' => admin_url('communication'), 'page' => lang('Communication')), array('link' => '#', 'page' => lang('Edit')));
            $meta = array('page_title' => lang('Edit'), 'bc' => $bc);
            $this->page_construct('communication/edit', $meta, $this->data);
        }
    }

    /* ------------------------------- */

      public function delete($id = null)
    {
        $this->sma->checkPermissions(null, true);

        if ($this->input->get('id')) {
            $id = $this->input->get('id');
        }

        $inv = $this->communication_model->getCommunicationByID($id);
        if (!$inv) {
            $this->sma->send_json(array('error' => 1, 'msg' => lang("No_data_found")));
        }

        if ($this->communication_model->delete($id)) {
            if ($this->input->is_ajax_request()) {
                $this->sma->send_json(array('error' => 0, 'msg' => lang("Data_deleted.")));
            }
            $this->session->set_flashdata('message', lang('Data_deleted.'));
            admin_redirect('welcome');
        }
    }



    /* --------------------------------------------------------------------------------------------- */

    public function suggestions($pos = 0)
    {
        $term = $this->input->get('term', true);
        $warehouse_id = $this->input->get('warehouse_id', true);
        $customer_id = $this->input->get('customer_id', true);

        if (strlen($term) < 1 || !$term) {
            die("<script type='text/javascript'>setTimeout(function(){ window.top.location.href = '" . admin_url('welcome') . "'; }, 10);</script>");
        }

        $analyzed = $this->sma->analyze_term($term);
        $sr = $analyzed['term'];
        $option_id = $analyzed['option_id'];

        $warehouse = $this->site->getWarehouseByID($warehouse_id);
        $customer = $this->site->getCompanyByID($customer_id);
        $customer_group = $this->site->getCustomerGroupByID($customer->customer_group_id);
        $rows = $this->sales_model->getProductNames($sr, $warehouse_id, $pos);
        if ($rows) {
            $r = 0;
            foreach ($rows as $row) {
                $c = uniqid(mt_rand(), true);
                unset($row->cost, $row->details, $row->image, $row->barcode_symbology, $row->cf1, $row->cf2, $row->cf3, $row->cf4, $row->cf5, $row->cf6, $row->supplier1price, $row->supplier2price, $row->cfsupplier3price, $row->supplier4price, $row->supplier5price, $row->supplier1, $row->supplier2, $row->supplier3, $row->supplier4, $row->supplier5, $row->supplier1_part_no, $row->supplier2_part_no, $row->supplier3_part_no, $row->supplier4_part_no, $row->supplier5_part_no);
                $option = false;
                $row->quantity = 0;
                $row->item_tax_method = $row->tax_method;
                $row->qty = 1;
                $row->discount = '0';
                $row->serial = '';
                $row->product_details=$row->product_details;
                // $options = $this->sales_model->getProductOptions($row->id, $warehouse_id);
                $options=false;
                if ($options) {
                    $opt = $option_id && $r == 0 ? $this->sales_model->getProductOptionByID($option_id) : $options[0];
                    if (!$option_id || $r > 0) {
                        $option_id = $opt->id;
                    }
                } else {
                    $opt = json_decode('{}');
                    $opt->price = 0;
                    $option_id = FALSE;
                }
                $row->option = $option_id;
                $pis = $this->site->getPurchasedItems($row->id, $warehouse_id, $row->option);
                if ($pis) {
                    $row->quantity = 0;
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
                if ($this->sma->isPromo($row)) {
                    $row->price = $row->promo_price;
                } elseif ($customer->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $customer->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                } elseif ($warehouse->price_group_id) {
                    if ($pr_group_price = $this->site->getProductGroupPrice($row->id, $warehouse->price_group_id)) {
                        $row->price = $pr_group_price->price;
                    }
                }
                $row->price = $row->price + (($row->price * $customer_group->percent) / 100);
                $row->real_unit_price = $row->price;
                $row->base_quantity = 1;
                $row->base_unit = $row->unit;
                $row->base_unit_price = $row->price;
                $row->unit = $row->sale_unit ? $row->sale_unit : $row->unit;
                $row->comment = '';
                $combo_items = false;
                if ($row->type == 'combo') {
                    $combo_items = $this->sales_model->getProductComboItems($row->id, $warehouse_id);
                }
                $units = $this->site->getUnitsByBUID($row->base_unit);
                $tax_rate = $this->site->getTaxRateByID($row->tax_rate);

                $pr[] = array('id' => sha1($c.$r), 'item_id' => $row->id, 'label' => $row->name . " (" . $row->code . ")", 'category' => $row->category_id,
                    'row' => $row, 'combo_items' => $combo_items, 'tax_rate' => $tax_rate, 'units' => $units, 'options' => $options);
                $r++;
            }
            $this->sma->send_json($pr);
        } else {
            $this->sma->send_json(array(array('id' => 0, 'label' => lang('no_match_found'), 'value' => $term)));
        }
    }

    /* ------------------------------------ Gift Cards ---------------------------------- */

   


}
