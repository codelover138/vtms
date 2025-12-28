<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Communication_model extends CI_Model
{

    public function __construct()
    {
        parent::__construct();
    }

 


    public function getCommunicationByID($id)
    {
        $q = $this->db->get_where('communication', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

   

    public function add($data = array())
    {
        if ($this->db->insert('communication', $data)) {
            $id = $this->db->insert_id();
            return $id;
        }

        return false;
    }

    public function update($id, $data)
    {
        if ($this->db->update('communication', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function updateStatus($id, $status, $note,$service_provider=null,$inv=null)
    {

        $sale = $this->getInvoiceByID($id);
        $items = $this->getAllInvoiceItems($id);
        $cost = array();
        if ($status == 'completed' && $sale->sale_status != 'completed') {
            foreach ($items as $item) {
                $items_array[] = (array) $item;
            }
            $cost = $this->site->costing($items_array);
        }
        if ($status != 'completed' && $sale->sale_status == 'completed') {
            $this->resetSaleActions($id);
        }

        ///  file upload
        //$attachment='';
        if (isset($_FILES['document']) && count($_FILES['document']['name']) > 0) {
            $customer_name = $this->site->getCustomerById($inv->customer_id);
            $user = $this->site->getUserById($this->session->userdata('user_id'));
            $this->load->library('upload');
            $uploadPath = 'assets/document/' . $user->username . '/' . $customer_name->vat_no . '/';
            $basePath = FCPATH . 'assets/document/';
            $directoryPath = $basePath . $user->username . '/' . $customer_name->vat_no;
            if (!is_dir($directoryPath)) {
                mkdir($directoryPath, 0755, true);
            }
            $uploadedFiles = [];
            foreach ($_FILES['document']['name'] as $key => $fileName) {
                if ($_FILES['document']['size'][$key] > 0) {
                    $_FILES['single_document']['name'] = $_FILES['document']['name'][$key];
                    $_FILES['single_document']['type'] = $_FILES['document']['type'][$key];
                    $_FILES['single_document']['tmp_name'] = $_FILES['document']['tmp_name'][$key];
                    $_FILES['single_document']['error'] = $_FILES['document']['error'][$key];
                    $_FILES['single_document']['size'] = $_FILES['document']['size'][$key];

                    $date = date('Ymd_His');  // Current date and time
                    $originalFileName = pathinfo($fileName, PATHINFO_FILENAME);  // Get the original file name without extension
                    $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);    // Get the file extension
                    $customerSafeName = preg_replace('/[^a-zA-Z0-9_]/', '_', $customer_name->name);
                    $customFileName = $originalFileName.'_'.$date . '_' . $customerSafeName . '_' . $customer_name->phone . '.' . $fileExtension;
                    $config['upload_path'] = $uploadPath;
                    $config['allowed_types'] = $this->digital_file_types;
                    $config['max_size'] = $this->allowed_file_size;
                    $config['overwrite'] = false;
                    $config['encrypt_name'] = false;
                    $config['file_name'] = $customFileName;
                    $this->upload->initialize($config);
                    if (!$this->upload->do_upload('single_document')) {
                        $error = $this->upload->display_errors();
                        $this->session->set_flashdata('error', $error);
                        redirect($_SERVER["HTTP_REFERER"]);
                    } else {
                        $uploadedFiles[] = $uploadPath . $this->upload->data('file_name');
                    }
                }
            }

            if (!empty($uploadedFiles)) {
                if (!empty($inv->attachment)) {
                    $photo = $inv->attachment . ',' . implode(',', $uploadedFiles);
                } else {
                    $photo = implode(',', $uploadedFiles);
                }

                $inv->attachment = $photo;
            }

        }



        if ($this->db->update('sales', array('sale_status' => $status, 'service_provider'=>$service_provider,'note' => $note,'attachment'=>$inv->attachment), array('id' => $id)) && $this->db->delete('costing', array('sale_id' => $id))) {
            if ($status == 'completed' && $sale->sale_status != 'completed') {
                foreach ($items as $item) {
                    $item = (array) $item;
                    if ($this->site->getProductByID($item['product_id'])) {
                        $item_costs = $this->site->item_costing($item);
                        foreach ($item_costs as $item_cost) {
                            $item_cost['sale_item_id'] = $item['id'];
                            $item_cost['sale_id'] = $id;
                            $item_cost['date'] = date('Y-m-d', strtotime($sale->date));
                            if(! isset($item_cost['pi_overselling'])) {
                                $this->db->insert('costing', $item_cost);
                            }
                        }
                    }
                }
            }

            if (!empty($cost)) { $this->site->syncPurchaseItems($cost); }
            $this->site->syncQuantity($id);
            return true;
        }
        return false;
    }

    public function delete($id)
    {
       
        if ($this->db->delete('communication', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function resetSaleActions($id, $return_id = NULL, $check_return = NULL)
    {
        if ($sale = $this->getInvoiceByID($id)) {
            if ($check_return && $sale->sale_status == 'returned') {
                $this->session->set_flashdata('warning', lang('sale_x_action'));
                redirect(isset($_SERVER["HTTP_REFERER"]) ? $_SERVER["HTTP_REFERER"] : 'welcome');
            }

            if ($sale->sale_status == 'completed') {
                if ($costings = $this->getSaleCosting($id)) {
                    foreach ($costings as $costing) {
                        if ($pi = $this->getPurchaseItemByID($costing->purchase_item_id)) {
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        } else {
                            // $sale_item = $this->getSaleItemByID($costing->sale_item_id);
                            $pi = $this->site->getPurchasedItem(['product_id' => $costing->product_id, 'option_id' => $costing->option_id ? $costing->option_id : NULL, 'purchase_id' => NULL, 'transfer_id' => NULL, 'warehouse_id' => $sale->warehouse_id]);
                            $this->site->setPurchaseItem(['id' => $pi->id, 'product_id' => $pi->product_id, 'option_id' => $pi->option_id], $costing->quantity);
                        }
                    }
                }
                $items = $this->getAllInvoiceItems($id);
                $this->site->syncQuantity(NULL, NULL, $items);
                $this->sma->update_award_points($sale->grand_total, $sale->customer_id, $sale->created_by, TRUE);
                return $items;
            }
        }
    }

    public function getPurchaseItemByID($id)
    {
        $q = $this->db->get_where('purchase_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getCostingLines($sale_item_id, $product_id, $sale_id = NULL)
    {
        if ($sale_id) { $this->db->where('sale_id', $sale_id); }
        $orderby = ($this->Settings->accounting_method == 1) ? 'asc' : 'desc';
        $this->db->order_by('id', $orderby);
        $q = $this->db->get_where('costing', array('sale_item_id' => $sale_item_id, 'product_id' => $product_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getSaleItemByID($id)
    {
        $q = $this->db->get_where('sale_items', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getProductByName($name)
    {
        $q = $this->db->get_where('products', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function addDelivery($data = array())
    {
        if ($this->db->insert('deliveries', $data)) {
            if ($this->site->getReference('do') == $data['do_reference_no']) {
                $this->site->updateReference('do');
            }
            return true;
        }
        return false;
    }

    public function updateDelivery($id, $data = array())
    {
        if ($this->db->update('deliveries', $data, array('id' => $id))) {
            return true;
        }
        return false;
    }

    public function getDeliveryByID($id)
    {
        $q = $this->db->get_where('deliveries', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getDeliveryBySaleID($sale_id)
    {
        $q = $this->db->get_where('deliveries', array('sale_id' => $sale_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function deleteDelivery($id)
    {
        if ($this->db->delete('deliveries', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getInvoicePayments($sale_id)
    {
        $this->db->order_by('id', 'asc');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
    }

    public function getPaymentByID($id)
    {
        $q = $this->db->get_where('payments', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getPaymentsForSale($sale_id)
    {
        $this->db->select('payments.date, payments.paid_by, payments.amount, payments.cc_no, payments.cheque_no, payments.reference_no, users.first_name, users.last_name, type')
            ->join('users', 'users.id=payments.created_by', 'left');
        $q = $this->db->get_where('payments', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function addPayment($data = array(), $customer_id = null)
    {
        if ($this->db->insert('payments', $data)) {
            if ($this->site->getReference('pay') == $data['reference_no']) {
                $this->site->updateReference('pay');
            }
            $this->site->syncSalePayments($data['sale_id']);
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }

    public function updatePayment($id, $data = array(), $customer_id = null)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->update('payments', $data, array('id' => $id))) {
            $this->site->syncSalePayments($data['sale_id']);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                if (!$customer_id) {
                    $sale = $this->getInvoiceByID($opay->sale_id);
                    $customer_id = $sale->customer_id;
                }
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            if ($data['paid_by'] == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($data['cc_no']);
                $this->db->update('gift_cards', array('balance' => ($gc->balance - $data['amount'])), array('card_no' => $data['cc_no']));
            } elseif ($customer_id && $data['paid_by'] == 'deposit') {
                $customer = $this->site->getCompanyByID($customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount-$data['amount'])), array('id' => $customer_id));
            }
            return true;
        }
        return false;
    }

    public function deletePayment($id)
    {
        $opay = $this->getPaymentByID($id);
        if ($this->db->delete('payments', array('id' => $id))) {
            $this->site->syncSalePayments($opay->sale_id);
            if ($opay->paid_by == 'gift_card') {
                $gc = $this->site->getGiftCardByNO($opay->cc_no);
                $this->db->update('gift_cards', array('balance' => ($gc->balance+$opay->amount)), array('card_no' => $opay->cc_no));
            } elseif ($opay->paid_by == 'deposit') {
                $sale = $this->getInvoiceByID($opay->sale_id);
                $customer = $this->site->getCompanyByID($sale->customer_id);
                $this->db->update('companies', array('deposit_amount' => ($customer->deposit_amount+$opay->amount)), array('id' => $customer->id));
            }
            return true;
        }
        return FALSE;
    }

    public function getWarehouseProductQuantity($warehouse_id, $product_id)
    {
        $q = $this->db->get_where('warehouses_products', array('warehouse_id' => $warehouse_id, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /* ----------------- Gift Cards --------------------- */

    public function addGiftCard($data = array(), $ca_data = array(), $sa_data = array())
    {
        if ($this->db->insert('gift_cards', $data)) {
            if (!empty($ca_data)) {
                $this->db->update('companies', array('award_points' => $ca_data['points']), array('id' => $ca_data['customer']));
            } elseif (!empty($sa_data)) {
                $this->db->update('users', array('award_points' => $sa_data['points']), array('id' => $sa_data['user']));
            }
            return true;
        }
        return false;
    }

    public function updateGiftCard($id, $data = array())
    {
        $this->db->where('id', $id);
        if ($this->db->update('gift_cards', $data)) {
            return true;
        }
        return false;
    }

    public function deleteGiftCard($id)
    {
        if ($this->db->delete('gift_cards', array('id' => $id))) {
            return true;
        }
        return FALSE;
    }

    public function getPaypalSettings()
    {
        $q = $this->db->get_where('paypal', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getSkrillSettings()
    {
        $q = $this->db->get_where('skrill', array('id' => 1));
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getQuoteByID($id)
    {
        $q = $this->db->get_where('quotes', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getAllQuoteItems($quote_id)
    {
        $q = $this->db->get_where('quote_items', array('quote_id' => $quote_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getStaff()
    {
        if (!$this->Owner) {
            $this->db->where('group_id !=', 1);
        }
        $this->db->where('group_id !=', 3)->where('group_id !=', 4);
        $q = $this->db->get('users');
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getProductVariantByName($name, $product_id)
    {
        $q = $this->db->get_where('product_variants', array('name' => $name, 'product_id' => $product_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function getTaxRateByName($name)
    {
        $q = $this->db->get_where('tax_rates', array('name' => $name), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    public function topupGiftCard($data = array(), $card_data = NULL)
    {
        if ($this->db->insert('gift_card_topups', $data)) {
            $this->db->update('gift_cards', $card_data, array('id' => $data['card_id']));
            return true;
        }
        return false;
    }

    public function getAllGCTopups($card_id)
    {
        $this->db->select("{$this->db->dbprefix('gift_card_topups')}.*, {$this->db->dbprefix('users')}.first_name, {$this->db->dbprefix('users')}.last_name, {$this->db->dbprefix('users')}.email")
        ->join('users', 'users.id=gift_card_topups.created_by', 'left')
        ->order_by('id', 'desc')->limit(10);
        $q = $this->db->get_where('gift_card_topups', array('card_id' => $card_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function getItemRack($product_id, $warehouse_id)
    {
        $q = $this->db->get_where('warehouses_products', array('product_id' => $product_id, 'warehouse_id' => $warehouse_id), 1);
        if ($q->num_rows() > 0) {
            $wh = $q->row();
            return $wh->rack;
        }
        return FALSE;
    }

    public function getSaleCosting($sale_id)
    {
        $q = $this->db->get_where('costing', array('sale_id' => $sale_id));
        if ($q->num_rows() > 0) {
            foreach (($q->result()) as $row) {
                $data[] = $row;
            }
            return $data;
        }
        return FALSE;
    }

    public function UpdateCostingAndPurchaseItem($return_item, $product_id, $quantity)
    {
        $bln_quantity = $quantity;
        if ($costings = $this->getCostingLines($return_item['id'], $product_id)) {
            foreach ($costings as $costing) {
                if ($costing->quantity > $bln_quantity && $bln_quantity != 0) {
                    $qty = $costing->quantity - $bln_quantity;
                    $bln = $costing->quantity_balance && $costing->quantity_balance >= $bln_quantity ? $costing->quantity_balance - $bln_quantity : 0;
                    $this->db->update('costing', array('quantity' => $qty, 'quantity_balance' => $bln), array('id' => $costing->id));
                    $bln_quantity = 0;
                    break;
                } elseif ($costing->quantity <= $bln_quantity && $bln_quantity != 0) {
                    $this->db->delete('costing', array('id' => $costing->id));
                    $bln_quantity = ($bln_quantity - $costing->quantity);
                }
            }
        }
        $clause = ['product_id' => $product_id, 'warehouse_id' => $return_item['warehouse_id'], 'purchase_id' => null, 'transfer_id' => null, 'option_id' => $return_item['option_id']];
        $this->site->setPurchaseItem($clause, $quantity);
        $this->site->syncQuantity(null, null, null, $product_id);
    }

}
