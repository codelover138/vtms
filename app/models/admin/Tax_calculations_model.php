<?php defined('BASEPATH') OR exit('No direct script access allowed');

class Tax_calculations_model extends CI_Model
{
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Get customer tax settings
     */
    public function getCustomerTaxSettings($customer_id)
    {
        $q = $this->db->get_where('companies', array('id' => $customer_id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /**
     * Update customer tax settings
     */
    public function updateCustomerTaxSettings($customer_id, $data)
    {
        $this->db->where('id', $customer_id);
        return $this->db->update('companies', $data);
    }

    /**
     * Get total sales for a customer for a given year from income_data table
     */
    public function getTotalSalesForYear($customer_id, $year)
    {
        $this->db->select_sum('taxable_sales', 'total_sales');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('YEAR(date_transmission)', $year);
        $q = $this->db->get('income_data');
        
        if ($q->num_rows() > 0) {
            $result = $q->row();
            return $result->total_sales ? $result->total_sales : 0;
        }
        return 0;
    }

    /**
     * Get previous year INPS amount paid
     */
    public function getPreviousYearINPS($customer_id, $year)
    {
        $prev_year = $year - 1;
        $this->db->select('inps_amount_after_discount');
        $q = $this->db->get_where('inps_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $prev_year
        ), 1);
        
        if ($q->num_rows() > 0) {
            $result = $q->row();
            return $result->inps_amount_after_discount ? $result->inps_amount_after_discount : 0;
        }
        return 0;
    }

    /**
     * Calculate tax for a customer for a given year
     */
    public function calculateTaxForYear($customer_id, $year)
    {
        $customer = $this->getCustomerTaxSettings($customer_id);
        if (!$customer) {
            return FALSE;
        }

        $total_sales = $this->getTotalSalesForYear($customer_id, $year);

        // Get coefficient and tax rate from customer settings or use defaults
        $coefficient = $customer->coefficient_of_profitability ? $customer->coefficient_of_profitability : 78.00;
        $tax_rate = $customer->tax_rate ? $customer->tax_rate : 5.00;

        // Calculate taxable income: Total Sales × Coefficient of Profitability
        // Note: INPS is NOT deducted from taxable income - it's calculated separately
        $taxable_income = $total_sales * $coefficient / 100;
        if ($taxable_income < 0) {
            $taxable_income = 0;
        }

        // Get advance payments made for this year (first and second advance)
        // These were calculated based on the previous year's tax and paid in the current year
        $advance_payments = $this->getAdvancePaymentsForYear($customer_id, $year);

        // Calculate tax due: Taxable Income × Tax Rate (gross amount, NOT deducted by advances)
        $tax_due = $taxable_income * $tax_rate / 100;

        // Balance payment = Tax Due - Advance Payments Made
        $balance_payment = $tax_due - $advance_payments;
        if ($balance_payment < 0) {
            $balance_payment = 0;
        }

        // Calculate next year advance base (80% of tax due)
        $next_year_advance_base = $tax_due * 0.80;

        // Get previous year INPS for display purposes only (not used in calculation)
        $previous_year_inps = $this->getPreviousYearINPS($customer_id, $year);

        return array(
            'customer_id' => $customer_id,
            'tax_year' => $year,
            'total_sales' => $total_sales,
            'previous_year_inps' => $previous_year_inps, // For display only
            'taxable_income' => $taxable_income,
            'tax_due' => $tax_due,
            'advance_payments_made' => $advance_payments,
            'balance_payment' => $balance_payment,
            'next_year_advance_base' => $next_year_advance_base,
            'coefficient_used' => $coefficient,
            'tax_rate_used' => $tax_rate
        );
    }

    /**
     * Get advance payments made for a year
     * Returns the sum of advance payment amounts (scheduled amounts) for the given year
     */
    public function getAdvancePaymentsForYear($customer_id, $year)
    {
        $this->db->select_sum('amount', 'total_amount');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('payment_year', $year);
        $this->db->where_in('payment_type', array('first_advance', 'second_advance'));
        $q = $this->db->get('tax_payments');
        
        if ($q->num_rows() > 0) {
            $result = $q->row();
            return $result->total_amount ? $result->total_amount : 0;
        }
        return 0;
    }

    /**
     * Save tax calculation
     */
    public function saveTaxCalculation($data)
    {
        // Check if calculation already exists
        $existing = $this->db->get_where('tax_calculations', array(
            'customer_id' => $data['customer_id'],
            'tax_year' => $data['tax_year']
        ), 1);

        if ($existing->num_rows() > 0) {
            $this->db->where('customer_id', $data['customer_id']);
            $this->db->where('tax_year', $data['tax_year']);
            return $this->db->update('tax_calculations', $data);
        } else {
            return $this->db->insert('tax_calculations', $data);
        }
    }

    /**
     * Get tax calculation for a customer and year
     */
    public function getTaxCalculation($customer_id, $year)
    {
        $q = $this->db->get_where('tax_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1);
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /**
     * Calculate and save tax payments for a year
     */
    public function calculateAndSaveTaxPayments($customer_id, $year, $tax_calculation_id = NULL)
    {
        $tax_calc = $this->getTaxCalculation($customer_id, $year);
        if (!$tax_calc) {
            return FALSE;
        }

        $payments = array();

        // Balance payment for the tax year being calculated (due February 16 of the NEXT year)
        // Example: When calculating 2024 taxes (in 2025), balance for 2024 is due February 16, 2025
        if ($year > date('Y', strtotime($tax_calc->business_start_date ?? '2000-01-01'))) {
            $payments[] = array(
                'customer_id' => $customer_id,
                'tax_calculation_id' => $tax_calculation_id,
                'payment_type' => 'balance',
                'payment_year' => $year, // This is the tax year (e.g., 2024)
                'due_date' => ($year + 1) . '-02-16', // Due February 16 of the next year (e.g., 2025-02-16)
                'amount' => $tax_calc->balance_payment,
                'paid_amount' => 0,
                'status' => 'pending'
            );
        }

        // First advance payment for the NEXT year (due June 30 of the next year)
        // Example: When calculating 2024 taxes (in 2025), first advance for 2025 is due June 30, 2025
        // This advance is based on 2024's next_year_advance_base
        $first_advance = $tax_calc->next_year_advance_base * 0.50;
        if ($first_advance > 0) {
            $payments[] = array(
                'customer_id' => $customer_id,
                'tax_calculation_id' => $tax_calculation_id,
                'payment_type' => 'first_advance',
                'payment_year' => $year + 1, // This is for the next tax year (e.g., 2025)
                'due_date' => ($year + 1) . '-06-30', // Due June 30 of next year (e.g., 2025-06-30)
                'amount' => $first_advance,
                'paid_amount' => 0,
                'status' => 'pending'
            );
        }

        // Second advance payment for the NEXT year (due June 30 of the next year - same as first advance)
        // Example: When calculating 2024 taxes (in 2025), second advance for 2025 is due June 30, 2025
        // This advance is based on 2024's next_year_advance_base
        if ($first_advance > 0) {
            $second_advance = $tax_calc->next_year_advance_base * 0.50;
            $payments[] = array(
                'customer_id' => $customer_id,
                'tax_calculation_id' => $tax_calculation_id,
                'payment_type' => 'second_advance',
                'payment_year' => $year + 1, // This is for the next tax year (e.g., 2025)
                'due_date' => ($year + 1) . '-11-30', // Due June 30 of next year (e.g., 2025-06-30)
                'amount' => $second_advance,
                'paid_amount' => 0,
                'status' => 'pending'
            );
        }

        // Save payments (update existing or insert new)
        foreach ($payments as $payment) {
            $existing = $this->db->get_where('tax_payments', array(
                'customer_id' => $customer_id,
                'payment_type' => $payment['payment_type'],
                'payment_year' => $payment['payment_year']
            ), 1);

            if ($existing->num_rows() > 0) {
                $this->db->where('customer_id', $customer_id);
                $this->db->where('payment_type', $payment['payment_type']);
                $this->db->where('payment_year', $payment['payment_year']);
                $this->db->update('tax_payments', $payment);
            } else {
                $this->db->insert('tax_payments', $payment);
            }
        }

        return TRUE;
    }

    /**
     * Get INPS rate for taxable income
     * @param decimal $taxable_income The taxable income amount
     * @param int $year The tax year (defaults to current year)
     * @param string $customer_type The customer type (Gestione Separata, Commercianti, Artigiani, or NULL for all)
     * @return object|FALSE The rate slab object or FALSE if not found
     */
    public function getINPSRate($taxable_income, $year = NULL, $customer_type = NULL)
    {
        if ($year === NULL) {
            $year = date('Y');
        }
        
        $this->db->where('slab_year', $year);
        $this->db->where('income_from <=', $taxable_income);
        $this->db->where('(income_to IS NULL OR income_to >=', $taxable_income . ')', FALSE);
        $this->db->where('is_active', 1);
        
        // Filter by customer_type if provided, otherwise get slabs that apply to all (NULL) or match the customer type
        if ($customer_type !== NULL) {
            $this->db->group_start();
            $this->db->where('customer_type', $customer_type);
            $this->db->or_where('customer_type IS NULL', NULL, FALSE);
            $this->db->group_end();
        }
        
        // Order by customer_type NULL last (so specific customer_type slabs take precedence over generic ones)
        $this->db->order_by('customer_type IS NULL', 'ASC', FALSE);
        $this->db->order_by('income_from', 'DESC');
        $q = $this->db->get('inps_rate_slabs', 1);

        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /**
     * Calculate INPS for a customer for a given year
     */
    public function calculateINPSForYear($customer_id, $year)
    {
        $tax_calc = $this->getTaxCalculation($customer_id, $year);
        if (!$tax_calc) {
            return FALSE;
        }

        $customer = $this->getCustomerTaxSettings($customer_id);
        if (!$customer) {
            return FALSE;
        }

        // Get taxable income (67% of total sales as per example)
        // Using taxable_income from tax calculation, but INPS uses different calculation
        // According to the doc: Taxable: €50,000 × 67% = €33,500
        $total_sales = $tax_calc->total_sales;
        $inps_taxable_income = $total_sales * 0.67;

        // Get INPS rate for the specific year and customer type
        $customer_type = $customer->customer_type ? $customer->customer_type : NULL;
        $rate_slab = $this->getINPSRate($inps_taxable_income, $year, $customer_type);
        if (!$rate_slab) {
            return FALSE;
        }

        // Calculate INPS amount
        if ($rate_slab->fixed_amount && $inps_taxable_income <= 18555) {
            $inps_amount = $rate_slab->fixed_amount;
            $inps_rate = 0;
        } else {
            $inps_rate = $rate_slab->inps_rate;
            $inps_amount = $inps_taxable_income * $inps_rate / 100;
        }

        // Check if eligible for 35% discount (Commercianti/Artigiani)
        $discount_percentage = 0;
        if ($customer->inps_discount_eligible && 
            in_array($customer->customer_type, array('Commercianti', 'Artigiani'))) {
            $discount_percentage = 35;
        }

        $discount_amount = $inps_amount * $discount_percentage / 100;
        $inps_amount_after_discount = $inps_amount - $discount_amount;

        return array(
            'customer_id' => $customer_id,
            'tax_year' => $year,
            'taxable_income' => $inps_taxable_income,
            'inps_rate' => $inps_rate,
            'inps_amount' => $inps_amount,
            'discount_percentage' => $discount_percentage,
            'discount_amount' => $discount_amount,
            'inps_amount_after_discount' => $inps_amount_after_discount
        );
    }

    /**
     * Save INPS calculation
     */
    public function saveINPSCalculation($data)
    {
        $existing = $this->db->get_where('inps_calculations', array(
            'customer_id' => $data['customer_id'],
            'tax_year' => $data['tax_year']
        ), 1);

        if ($existing->num_rows() > 0) {
            $this->db->where('customer_id', $data['customer_id']);
            $this->db->where('tax_year', $data['tax_year']);
            return $this->db->update('inps_calculations', $data);
        } else {
            return $this->db->insert('inps_calculations', $data);
        }
    }

    /**
     * Calculate and save INPS payments (4 installments per year)
     */
    public function calculateAndSaveINPSPayments($customer_id, $year, $inps_calculation_id = NULL)
    {
        $inps_calc = $this->db->get_where('inps_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1)->row();

        if (!$inps_calc) {
            return FALSE;
        }

        $total_inps = $inps_calc->inps_amount_after_discount;
        $installment_amount = $total_inps / 4;

        $payments = array(
            array(
                'customer_id' => $customer_id,
                'inps_calculation_id' => $inps_calculation_id,
                'installment_number' => 1,
                'payment_year' => $year,
                'due_date' => $year . '-05-16',
                'amount' => $installment_amount,
                'paid_amount' => 0,
                'status' => 'pending'
            ),
            array(
                'customer_id' => $customer_id,
                'inps_calculation_id' => $inps_calculation_id,
                'installment_number' => 2,
                'payment_year' => $year,
                'due_date' => $year . '-08-20',
                'amount' => $installment_amount,
                'paid_amount' => 0,
                'status' => 'pending'
            ),
            array(
                'customer_id' => $customer_id,
                'inps_calculation_id' => $inps_calculation_id,
                'installment_number' => 3,
                'payment_year' => $year,
                'due_date' => $year . '-11-16',
                'amount' => $installment_amount,
                'paid_amount' => 0,
                'status' => 'pending'
            ),
            array(
                'customer_id' => $customer_id,
                'inps_calculation_id' => $inps_calculation_id,
                'installment_number' => 4,
                'payment_year' => $year,
                'due_date' => ($year + 1) . '-02-16',
                'amount' => $installment_amount,
                'paid_amount' => 0,
                'status' => 'pending'
            )
        );

        foreach ($payments as $payment) {
            $existing = $this->db->get_where('inps_payments', array(
                'customer_id' => $customer_id,
                'installment_number' => $payment['installment_number'],
                'payment_year' => $payment['payment_year']
            ), 1);

            if ($existing->num_rows() > 0) {
                $this->db->where('customer_id', $customer_id);
                $this->db->where('installment_number', $payment['installment_number']);
                $this->db->where('payment_year', $payment['payment_year']);
                $this->db->update('inps_payments', $payment);
            } else {
                $this->db->insert('inps_payments', $payment);
            }
        }

        return TRUE;
    }

    /**
     * Get all tax calculations for a customer
     */
    public function getAllTaxCalculations($customer_id)
    {
        $this->db->where('customer_id', $customer_id);
        $this->db->order_by('tax_year', 'DESC');
        $q = $this->db->get('tax_calculations');
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return array();
    }

    /**
     * Get all tax payments for a customer
     */
    public function getAllTaxPayments($customer_id, $year = NULL)
    {
        $this->db->where('customer_id', $customer_id);
        if ($year) {
            $this->db->where('payment_year', $year);
        }
        $this->db->order_by('due_date', 'ASC');
        $q = $this->db->get('tax_payments');
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return array();
    }

    /**
     * Get all INPS payments for a customer
     */
    public function getAllINPSPayments($customer_id, $year = NULL)
    {
        $this->db->where('customer_id', $customer_id);
        if ($year) {
            $this->db->where('payment_year', $year);
        }
        $this->db->order_by('due_date', 'ASC');
        $q = $this->db->get('inps_payments');
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return array();
    }

    /**
     * Process tax calculation for a customer for a year
     * This is the main method that orchestrates the entire calculation
     */
    public function processTaxCalculation($customer_id, $year)
    {
        // Calculate tax
        $tax_data = $this->calculateTaxForYear($customer_id, $year);
        if (!$tax_data) {
            return FALSE;
        }

        // Save tax calculation
        $this->saveTaxCalculation($tax_data);
        $tax_calc = $this->getTaxCalculation($customer_id, $year);

        // Calculate and save tax payments
        $this->calculateAndSaveTaxPayments($customer_id, $year, $tax_calc->id);

        // Calculate INPS
        $inps_data = $this->calculateINPSForYear($customer_id, $year);
        if ($inps_data) {
            $this->saveINPSCalculation($inps_data);
            $inps_calc = $this->db->get_where('inps_calculations', array(
                'customer_id' => $customer_id,
                'tax_year' => $year
            ), 1)->row();
            
            if ($inps_calc) {
                $this->calculateAndSaveINPSPayments($customer_id, $year, $inps_calc->id);
            }
        }

        return TRUE;
    }
}

