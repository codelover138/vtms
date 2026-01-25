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
        //$this->db->where('pos', 0);
        $q = $this->db->get('income_data');
        
        if ($q->num_rows() > 0) {
            $result = $q->row();
            return $result->total_sales ? $result->total_sales : 0;
        }
        return 0;
    }

    /**
     * Get sales data grouped by month for a customer and year
     */
    public function getSalesByMonth($customer_id, $year)
    {
        $this->db->select('MONTH(date_transmission) as month, SUM(taxable_sales) as monthly_sales, COUNT(*) as entry_count');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('YEAR(date_transmission)', $year);
        $this->db->group_by('MONTH(date_transmission)');
        $this->db->order_by('month', 'ASC');
        $q = $this->db->get('income_data');
        
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return array();
    }

    /**
     * Predict taxable income based on existing sales data
     * This calculates what the taxable income would be if all months had sales data
     */
    public function predictTaxableIncome($customer_id, $year)
    {
        $customer = $this->getCustomerTaxSettings($customer_id);
        if (!$customer) {
            return FALSE;
        }

        // Get sales data by month
        $monthly_sales = $this->getSalesByMonth($customer_id, $year);
        
        // Get current total sales
        $current_total_sales = $this->getTotalSalesForYear($customer_id, $year);
        
        // Identify which months have data
        $months_with_data = array();
        $total_months_with_data = 0;
        $total_sales_from_data = 0;
        
        foreach ($monthly_sales as $month_data) {
            $months_with_data[] = (int)$month_data->month;
            $total_months_with_data++;
            $total_sales_from_data += $month_data->monthly_sales;
        }
        
        // Calculate missing months
        $all_months = range(1, 12);
        $missing_months = array_diff($all_months, $months_with_data);
        $missing_months_count = count($missing_months);
        
        // If no months have data, return false
        if ($total_months_with_data == 0) {
            return FALSE;
        }
        
        // Calculate average monthly sales from existing data
        $average_monthly_sales = $total_sales_from_data / $total_months_with_data;
        
        // Predict total sales if all months were filled
        $predicted_additional_sales = $average_monthly_sales * $missing_months_count;
        $predicted_total_sales = $current_total_sales + $predicted_additional_sales;
        
        // Get coefficient
        $coefficient = $customer->coefficient_of_profitability ? $customer->coefficient_of_profitability : 78.00;
        
        // Calculate predicted taxable income
        $predicted_taxable_income = $predicted_total_sales * $coefficient / 100;
        
        return array(
            'current_total_sales' => $current_total_sales,
            'current_taxable_income' => $current_total_sales * $coefficient / 100,
            'months_with_data' => $months_with_data,
            'missing_months' => array_values($missing_months),
            'missing_months_count' => $missing_months_count,
            'total_months_with_data' => $total_months_with_data,
            'average_monthly_sales' => $average_monthly_sales,
            'predicted_additional_sales' => $predicted_additional_sales,
            'predicted_total_sales' => $predicted_total_sales,
            'predicted_taxable_income' => $predicted_taxable_income,
            'coefficient' => $coefficient,
            'monthly_sales_data' => $monthly_sales
        );
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
     * @param float $taxable_income The taxable income amount
     * @param int $year The tax year (defaults to current year)
     * @param string $customer_type The customer type (Gestione Separata, Commercianti, Artigiani, or NULL for all)
     * @return object|bool The rate slab object or FALSE if not found
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
     * Get all applicable INPS rate slabs for progressive calculation
     * @param float $taxable_income The taxable income amount
     * @param int $year The tax year (defaults to current year)
     * @param string $customer_type The customer type (Gestione Separata, Commercianti, Artigiani, or NULL for all)
     * @return array Array of rate slab objects ordered by income_from ASC
     */
    public function getAllApplicableINPSSlabs($taxable_income, $year = NULL, $customer_type = NULL)
    {
        if ($year === NULL) {
            $year = date('Y');
        }
        
        // Get all active slabs for the year that start at or before the taxable income
        $this->db->where('slab_year', $year);
        $this->db->where('income_from <=', $taxable_income);
        $this->db->where('is_active', 1);
        
        // Filter by customer_type if provided, otherwise get slabs that apply to all (NULL) or match the customer type
        if ($customer_type !== NULL) {
            $this->db->group_start();
            $this->db->where('customer_type', $customer_type);
            $this->db->or_where('customer_type IS NULL', NULL, FALSE);
            $this->db->group_end();
        }
        
        // Order by customer_type NULL last (so specific customer_type slabs take precedence over generic ones)
        // Then order by income_from ASC for progressive calculation
        $this->db->order_by('customer_type IS NULL', 'ASC', FALSE);
        $this->db->order_by('income_from', 'ASC');
        $q = $this->db->get('inps_rate_slabs');

        if ($q->num_rows() > 0) {
            $all_slabs = $q->result();
            
            // Filter to get only slabs that overlap with the income range (0 to taxable_income)
            // and remove duplicates by keeping the most specific customer_type for each income_from
            $applicable_slabs = array();
            $seen_income_from = array();
            
            foreach ($all_slabs as $slab) {
                // Check if slab range overlaps with 0 to taxable_income
                $slab_from = (float)$slab->income_from;
                $slab_to = ($slab->income_to !== NULL) ? (float)$slab->income_to : PHP_INT_MAX;
                
                // Slab applies if it overlaps with the income range (0 to taxable_income)
                // We want slabs that start at or before the taxable income
                if ($slab_from > $taxable_income) {
                    continue; // Slab starts after taxable income, skip it
                }
                // If slab has an upper limit, it should be valid (to >= from)
                if ($slab->income_to !== NULL && $slab_to < $slab_from) {
                    continue; // Invalid slab range
                }
                
                // For each income_from, prefer specific customer_type over NULL
                $key = (string)$slab->income_from;
                if (!isset($seen_income_from[$key]) || 
                    ($seen_income_from[$key]->customer_type === NULL && $slab->customer_type !== NULL)) {
                    $seen_income_from[$key] = $slab;
                }
            }
            
            // Convert to array and sort by income_from
            $applicable_slabs = array_values($seen_income_from);
            usort($applicable_slabs, function($a, $b) {
                return (float)$a->income_from <=> (float)$b->income_from;
            });
            
            return $applicable_slabs;
        }
        return array();
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
        $inps_taxable_income = $tax_calc->taxable_income;

        // Get all applicable INPS slabs for progressive calculation
        $customer_type = $customer->customer_type ? $customer->customer_type : NULL;
        $slabs = $this->getAllApplicableINPSSlabs($inps_taxable_income, $year, $customer_type);
        
        // If no slabs found, try to get at least one slab using the old method as fallback
        if (empty($slabs)) {
            $rate_slab = $this->getINPSRate($inps_taxable_income, $year, $customer_type);
            if ($rate_slab) {
                // Convert single slab to array format for consistency
                $slabs = array($rate_slab);
            } else {
                // Log error for debugging
                log_message('error', 'INPS Calculation Failed: No slabs found for customer_id=' . $customer_id . ', year=' . $year . ', taxable_income=' . $inps_taxable_income . ', customer_type=' . $customer_type);
                return FALSE;
            }
        }
        
        // Ensure we have at least one slab
        if (empty($slabs)) {
            log_message('error', 'INPS Calculation Failed: Empty slabs array for customer_id=' . $customer_id . ', year=' . $year);
            return FALSE;
        }

        // Calculate INPS amount using progressive slab-based calculation
        // Based on the example table:
        // - Row 1: start=0, end=15000, amount=15000, inps=3000, %=20%
        // - Row 2: start=15001, end=17999, amount=2998, inps=667.055, %=22.25%
        // - Row 3: start=18000, end=18401.67, amount=401.67, inps=97.40497, %=24.25%
        // Total = 3764.46
        
        $inps_amount = 0;
        $inps_rate = 0;
        $slab_details = array();
        
        foreach ($slabs as $index => $slab) {
            // Determine the income range for this slab
            $slab_from = (float)$slab->income_from;
            $slab_to = ($slab->income_to !== NULL) ? (float)$slab->income_to : PHP_INT_MAX;
            
            // The range starts at the slab's income_from
            $range_start = $slab_from;
            
            // The range ends at the minimum of: taxable income, or this slab's upper limit
            $range_end = min($inps_taxable_income, $slab_to);
            
            // Skip this slab if the taxable income is below the slab's start
            if ($inps_taxable_income < $slab_from) {
                continue;
            }
            
            // Calculate how much income falls in this slab's range
            // Amount = end - start (as shown in the example table)
            $slab_income = max(0, $range_end - $range_start);
            
            if ($slab_income <= 0) {
                continue;
            }
            
            // Calculate tax for this slab
            $slab_tax = 0;
            $slab_rate = 0;
            
            // Check if fixed_amount should be used (only for first slab when income is within fixed amount range)
            if ($slab->fixed_amount > 0 && $index === 0 && $inps_taxable_income <= $slab_to) {
                // Use fixed amount for the first slab if it exists
                $slab_tax = (float)$slab->fixed_amount;
                $slab_rate = 0;
            } else {
                // Calculate percentage-wise for this slab portion
                $slab_rate = (float)$slab->inps_rate;
                $slab_tax = $slab_income * $slab_rate / 100;
            }
            
            $inps_amount += $slab_tax;
            
            // Store slab details for reference
            $slab_details[] = array(
                'from' => $range_start,
                'to' => $range_end,
                'income' => $slab_income,
                'rate' => $slab_rate,
                'tax' => $slab_tax,
                'fixed_amount' => $slab->fixed_amount
            );
            
            // If we've covered all income, break
            if ($range_end >= $inps_taxable_income) {
                break;
            }
        }
        
        // Calculate average rate for reporting
        if ($inps_taxable_income > 0 && $inps_amount > 0) {
            $inps_rate = ($inps_amount / $inps_taxable_income) * 100;
        }
        
        // Ensure we have calculated something (even if amount is 0, we should have slab_details)
        if (empty($slab_details)) {
            log_message('error', 'INPS Calculation Failed: No slab details calculated for customer_id=' . $customer_id . ', year=' . $year . ', taxable_income=' . $inps_taxable_income);
            return FALSE;
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
            'inps_amount_after_discount' => $inps_amount_after_discount,
            'slab_details' => $slab_details // Include detailed slab breakdown for display
        );
    }

    /**
     * Save INPS calculation
     */
    public function saveINPSCalculation($data)
    {
        // Convert slab_details array to JSON for storage
        $slab_details_json = null;
        if (isset($data['slab_details'])) {
            if (is_array($data['slab_details'])) {
                $slab_details_json = json_encode($data['slab_details']);
            } else {
                $slab_details_json = $data['slab_details'];
            }
            unset($data['slab_details']); // Remove from data array temporarily
        }
        
        $existing = $this->db->get_where('inps_calculations', array(
            'customer_id' => $data['customer_id'],
            'tax_year' => $data['tax_year']
        ), 1);

        // Check if slab_details column exists in the table
        $columns = $this->db->list_fields('inps_calculations');
        $has_slab_details_column = in_array('slab_details', $columns);
        
        // Add slab_details back if column exists
        if ($has_slab_details_column && $slab_details_json !== null) {
            $data['slab_details'] = $slab_details_json;
        }

        if ($existing->num_rows() > 0) {
            $this->db->where('customer_id', $data['customer_id']);
            $this->db->where('tax_year', $data['tax_year']);
            return $this->db->update('inps_calculations', $data);
        } else {
            return $this->db->insert('inps_calculations', $data);
        }
    }

    /**
     * Calculate and save INPS payments
     * - For Gestione Separata: 3 payments (Saldo + 1st Acconto on June 30, 2nd Acconto on November 30)
     * - For Commercianti/Artigiani: 4 installments per year
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

        // Get customer type to determine payment structure
        $customer = $this->getCustomerTaxSettings($customer_id);
        $customer_type = $customer ? $customer->customer_type : NULL;

        $total_inps = $inps_calc->inps_amount_after_discount;
        $payments = array();

        if ($customer_type === 'Gestione Separata') {
            // Gestione Separata: Same structure as tax payments
            // Saldo (Balance): Full INPS amount for current year - due June 30 of next year
            // 1st Acconto: 40% of INPS (50% of 80%) - due June 30 of next year
            // 2nd Acconto: 40% of INPS (50% of 80%) - due November 30 of next year
            
            $acconto_base = $total_inps * 0.80; // 80% of total INPS for advances
            $acconto_each = $acconto_base * 0.50; // 50% each (40% of total)

            // Delete any existing payments for this customer/year first (to handle switching from 4 installments)
            $this->db->where('customer_id', $customer_id);
            $this->db->where('payment_year', $year);
            $this->db->delete('inps_payments');

            $payments = array(
                // Saldo (Balance) for current year - due June 30 of next year
                array(
                    'customer_id' => $customer_id,
                    'inps_calculation_id' => $inps_calculation_id,
                    'installment_number' => 1, // 1 = Saldo
                    'payment_year' => $year,
                    'due_date' => ($year + 1) . '-06-30',
                    'amount' => $total_inps,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => 'Saldo INPS'
                ),
                // 1st Acconto - due June 30 of next year
                array(
                    'customer_id' => $customer_id,
                    'inps_calculation_id' => $inps_calculation_id,
                    'installment_number' => 2, // 2 = 1st Acconto
                    'payment_year' => $year,
                    'due_date' => ($year + 1) . '-06-30',
                    'amount' => $acconto_each,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => '1° Acconto INPS'
                ),
                // 2nd Acconto - due November 30 of next year
                array(
                    'customer_id' => $customer_id,
                    'inps_calculation_id' => $inps_calculation_id,
                    'installment_number' => 3, // 3 = 2nd Acconto
                    'payment_year' => $year,
                    'due_date' => ($year + 1) . '-11-30',
                    'amount' => $acconto_each,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => '2° Acconto INPS'
                )
            );
        } else {
            // Commercianti/Artigiani: 4 equal installments per year
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
                    'status' => 'pending',
                    'notes' => '1° Rata INPS'
                ),
                array(
                    'customer_id' => $customer_id,
                    'inps_calculation_id' => $inps_calculation_id,
                    'installment_number' => 2,
                    'payment_year' => $year,
                    'due_date' => $year . '-08-20',
                    'amount' => $installment_amount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => '2° Rata INPS'
                ),
                array(
                    'customer_id' => $customer_id,
                    'inps_calculation_id' => $inps_calculation_id,
                    'installment_number' => 3,
                    'payment_year' => $year,
                    'due_date' => $year . '-11-16',
                    'amount' => $installment_amount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => '3° Rata INPS'
                ),
                array(
                    'customer_id' => $customer_id,
                    'inps_calculation_id' => $inps_calculation_id,
                    'installment_number' => 4,
                    'payment_year' => $year,
                    'due_date' => ($year + 1) . '-02-16',
                    'amount' => $installment_amount,
                    'paid_amount' => 0,
                    'status' => 'pending',
                    'notes' => '4° Rata INPS'
                )
            );
        }

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
     * Calculate INAIL for Artigiani customer for a given year
     * INAIL is calculated as: Taxable Income × 60% × INAIL Rate
     * But minimum payment applies if calculated amount is less than minimum
     */
    public function calculateINAILForYear($customer_id, $year)
    {
        $customer = $this->getCustomerTaxSettings($customer_id);
        if (!$customer) {
            return FALSE;
        }

        // INAIL is only for Artigiani
        if ($customer->customer_type !== 'Artigiani') {
            return FALSE;
        }

        // Check if INAIL settings are configured
        if (empty($customer->inail_rate) || empty($customer->inail_minimum_payment)) {
            return FALSE;
        }

        $tax_calc = $this->getTaxCalculation($customer_id, $year);
        if (!$tax_calc) {
            return FALSE;
        }

        
        // Calculate INAIL base: Taxable Income × 60%
        $inail_base_amount = $tax_calc->taxable_income;
        
        // Calculate INAIL amount: Base × Rate
        $inail_rate = (float)$customer->inail_rate;
        $inail_calculated_amount = $inail_base_amount * $inail_rate / 100;
        
        // Apply minimum payment if calculated amount is less than minimum
        $inail_minimum_payment = (float)$customer->inail_minimum_payment;
        $inail_final_amount = max($inail_calculated_amount, $inail_minimum_payment);

        return array(
            'customer_id' => $customer_id,
            'tax_year' => $year,
            'taxable_income' => $inail_base_amount,
            'inail_coefficient' => $customer->coefficient_of_profitability,
            'inail_base_amount' => $inail_base_amount,
            'inail_rate' => $inail_rate,
            'inail_calculated_amount' => $inail_calculated_amount,
            'inail_minimum_payment' => $inail_minimum_payment,
            'inail_final_amount' => $inail_final_amount,
            'ateco_code' => $customer->inail_ateco_code
        );
    }

    /**
     * Save INAIL calculation
     */
    public function saveINAILCalculation($data)
    {
        $existing = $this->db->get_where('inail_calculations', array(
            'customer_id' => $data['customer_id'],
            'tax_year' => $data['tax_year']
        ), 1);

        if ($existing->num_rows() > 0) {
            $this->db->where('customer_id', $data['customer_id']);
            $this->db->where('tax_year', $data['tax_year']);
            return $this->db->update('inail_calculations', $data);
        } else {
            return $this->db->insert('inail_calculations', $data);
        }
    }

    /**
     * Calculate and save INAIL payment (1 payment per year, due February 16 of following year)
     */
    public function calculateAndSaveINAILPayment($customer_id, $year, $inail_calculation_id = NULL)
    {
        $inail_calc = $this->db->get_where('inail_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1)->row();

        if (!$inail_calc) {
            return FALSE;
        }

        // INAIL payment is due February 16 of the following year
        // Example: For 2024 work, payment is due February 16, 2025
        $due_date = ($year + 1) . '-02-16';

        $payment = array(
            'customer_id' => $customer_id,
            'inail_calculation_id' => $inail_calculation_id,
            'payment_year' => $year,
            'due_date' => $due_date,
            'amount' => $inail_calc->inail_final_amount,
            'paid_amount' => 0,
            'status' => 'pending'
        );

        // Check if payment already exists
        $existing = $this->db->get_where('inail_payments', array(
            'customer_id' => $customer_id,
            'payment_year' => $year
        ), 1);

        if ($existing->num_rows() > 0) {
            $this->db->where('customer_id', $customer_id);
            $this->db->where('payment_year', $year);
            return $this->db->update('inail_payments', $payment);
        } else {
            return $this->db->insert('inail_payments', $payment);
        }
    }

    /**
     * Get INAIL calculation for a customer and year
     */
    public function getINAILCalculation($customer_id, $year)
    {
        $q = $this->db->get_where('inail_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1);
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /**
     * Get all INAIL payments for a customer
     */
    public function getAllINAILPayments($customer_id, $year = NULL)
    {
        $this->db->where('customer_id', $customer_id);
        if ($year) {
            $this->db->where('payment_year', $year);
        }
        $this->db->order_by('due_date', 'ASC');
        $q = $this->db->get('inail_payments');
        
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
            $save_result = $this->saveINPSCalculation($inps_data);
            if ($save_result) {
                $inps_calc = $this->db->get_where('inps_calculations', array(
                    'customer_id' => $customer_id,
                    'tax_year' => $year
                ), 1)->row();
                
                if ($inps_calc) {
                    $this->calculateAndSaveINPSPayments($customer_id, $year, $inps_calc->id);
                }
            } else {
                log_message('error', 'INPS Save Failed: Could not save INPS calculation for customer_id=' . $customer_id . ', year=' . $year);
            }
        } else {
            log_message('error', 'INPS Calculation Failed: calculateINPSForYear returned FALSE for customer_id=' . $customer_id . ', year=' . $year);
        }

        // Calculate INAIL (only for Artigiani)
        $inail_data = $this->calculateINAILForYear($customer_id, $year);
        if ($inail_data) {
            $this->saveINAILCalculation($inail_data);
            $inail_calc = $this->db->get_where('inail_calculations', array(
                'customer_id' => $customer_id,
                'tax_year' => $year
            ), 1)->row();
            
            if ($inail_calc) {
                $this->calculateAndSaveINAILPayment($customer_id, $year, $inail_calc->id);
            }
        }

        // Calculate and save Diritto Annuale payment (for Artigiani and Commercianti)
        $customer = $this->getCustomerTaxSettings($customer_id);
        if ($customer && in_array($customer->customer_type, array('Artigiani', 'Commercianti')) && $customer->diritto_annuale_amount > 0) {
            $this->calculateAndSaveDirittoAnnualePayment($customer_id, $year, $customer->diritto_annuale_amount);
        }

        // Calculate Fattura Tra Privati (for all customers with pos=2 invoices >= €77.47)
        $fattura_privati_data = $this->calculateFatturaTraPrivatiForYear($customer_id, $year);
        if ($fattura_privati_data && $fattura_privati_data['total_invoices'] > 0) {
            $this->saveFatturaTraPrivatiCalculation($fattura_privati_data);
            $fattura_privati_calc = $this->db->get_where('fattura_tra_privati_calculations', array(
                'customer_id' => $customer_id,
                'tax_year' => $year
            ), 1)->row();
            
            if ($fattura_privati_calc) {
                $this->calculateAndSaveFatturaTraPrivatiPayment($customer_id, $year, $fattura_privati_calc->id);
            }
        }

        return TRUE;
    }

    /**
     * Get INPS Slab by ID
     */
    public function getINPSSlab($id)
    {
        $q = $this->db->get_where('inps_rate_slabs', array('id' => $id), 1);
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /**
     * Add INPS Slab
     */
    public function addINPSSlab($data)
    {
        return $this->db->insert('inps_rate_slabs', $data);
    }

    /**
     * Update INPS Slab
     */
    public function updateINPSSlab($id, $data)
    {
        $this->db->where('id', $id);
        return $this->db->update('inps_rate_slabs', $data);
    }

    /**
     * Delete INPS Slab
     */
    public function deleteINPSSlab($id)
    {
        $this->db->where('id', $id);
        return $this->db->delete('inps_rate_slabs');
    }

    /**
     * Get all INPS Slabs (for listing)
     */
    public function getAllINPSSlabs($year = NULL)
    {
        if ($year) {
            $this->db->where('slab_year', $year);
        }
        $this->db->order_by('slab_year', 'DESC');
        $this->db->order_by('income_from', 'ASC');
        $q = $this->db->get('inps_rate_slabs');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return FALSE;
    }

    /**
     * Calculate and save Diritto Annuale payment
     * Due date: March 31 of the same year
     */
    public function calculateAndSaveDirittoAnnualePayment($customer_id, $year, $amount)
    {
        if ($amount <= 0) {
            return FALSE;
        }

        // Due date is March 31 of the same year
        $due_date = $year . '-03-31';

        // Check if payment already exists
        $existing = $this->db->get_where('diritto_annuale_payments', array(
            'customer_id' => $customer_id,
            'payment_year' => $year
        ), 1);

        $payment_data = array(
            'customer_id' => $customer_id,
            'payment_year' => $year,
            'due_date' => $due_date,
            'amount' => $amount,
            'paid_amount' => 0,
            'status' => 'pending'
        );

        if ($existing->num_rows() > 0) {
            // Update existing payment
            $this->db->where('customer_id', $customer_id);
            $this->db->where('payment_year', $year);
            return $this->db->update('diritto_annuale_payments', $payment_data);
        } else {
            // Insert new payment
            return $this->db->insert('diritto_annuale_payments', $payment_data);
        }
    }

    /**
     * Get all Diritto Annuale payments for a customer
     */
    public function getAllDirittoAnnualePayments($customer_id, $year = NULL)
    {
        $this->db->where('customer_id', $customer_id);
        if ($year) {
            $this->db->where('payment_year', $year);
        }
        $this->db->order_by('payment_year', 'DESC');
        $this->db->order_by('due_date', 'ASC');
        $q = $this->db->get('diritto_annuale_payments');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return array();
    }

    /**
     * Calculate Fattura Tra Privati for a year
     * Counts invoices from income_data where pos=2 and sales_amount >= €77.47
     */
    public function calculateFatturaTraPrivatiForYear($customer_id, $year)
    {
        $minimum_amount = 77.47;
        $payment_per_invoice = 2.00;
        
        // Get invoices from income_data where pos=2 and sales_amount >= 77.47
        $this->db->select('COUNT(*) as total_invoices, SUM(sales_amount) as total_sales_amount');
        $this->db->from('income_data');
        $this->db->where('customer_id', $customer_id);
        $this->db->where('pos', 2);
        $this->db->where('YEAR(date_transmission)', $year);
        $this->db->where('sales_amount >=', $minimum_amount);
        
        $q = $this->db->get();
        
        if ($q->num_rows() > 0) {
            $result = $q->row();
            $total_invoices = (int)$result->total_invoices;
            $total_sales_amount = $result->total_sales_amount ? (float)$result->total_sales_amount : 0.00;
            
            if ($total_invoices > 0) {
                $total_payment_amount = $total_invoices * $payment_per_invoice;
                
                return array(
                    'customer_id' => $customer_id,
                    'tax_year' => $year,
                    'total_invoices' => $total_invoices,
                    'total_sales_amount' => $total_sales_amount,
                    'payment_per_invoice' => $payment_per_invoice,
                    'total_payment_amount' => $total_payment_amount,
                    'minimum_invoice_amount' => $minimum_amount
                );
            }
        }
        
        return FALSE;
    }

    /**
     * Save Fattura Tra Privati calculation
     */
    public function saveFatturaTraPrivatiCalculation($data)
    {
        // Check if calculation already exists
        $existing = $this->db->get_where('fattura_tra_privati_calculations', array(
            'customer_id' => $data['customer_id'],
            'tax_year' => $data['tax_year']
        ), 1);

        if ($existing->num_rows() > 0) {
            $this->db->where('customer_id', $data['customer_id']);
            $this->db->where('tax_year', $data['tax_year']);
            return $this->db->update('fattura_tra_privati_calculations', $data);
        } else {
            return $this->db->insert('fattura_tra_privati_calculations', $data);
        }
    }

    /**
     * Calculate and save Fattura Tra Privati payment
     * Due date: February 16 of the following year
     */
    public function calculateAndSaveFatturaTraPrivatiPayment($customer_id, $year, $calculation_id = NULL)
    {
        $calculation = $this->db->get_where('fattura_tra_privati_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1)->row();
        
        if (!$calculation || $calculation->total_payment_amount <= 0) {
            return FALSE;
        }

        // Due date is February 16 of the following year
        $due_date = ($year + 1) . '-02-16';

        // Check if payment already exists
        $existing = $this->db->get_where('fattura_tra_privati_payments', array(
            'customer_id' => $customer_id,
            'payment_year' => $year
        ), 1);

        $payment_data = array(
            'customer_id' => $customer_id,
            'fattura_tra_privati_calculation_id' => $calculation_id,
            'payment_year' => $year,
            'due_date' => $due_date,
            'amount' => $calculation->total_payment_amount,
            'paid_amount' => 0,
            'status' => 'pending'
        );

        if ($existing->num_rows() > 0) {
            // Update existing payment
            $this->db->where('customer_id', $customer_id);
            $this->db->where('payment_year', $year);
            return $this->db->update('fattura_tra_privati_payments', $payment_data);
        } else {
            // Insert new payment
            return $this->db->insert('fattura_tra_privati_payments', $payment_data);
        }
    }

    /**
     * Get Fattura Tra Privati calculation for a customer and year
     */
    public function getFatturaTraPrivatiCalculation($customer_id, $year)
    {
        $q = $this->db->get_where('fattura_tra_privati_calculations', array(
            'customer_id' => $customer_id,
            'tax_year' => $year
        ), 1);
        
        if ($q->num_rows() > 0) {
            return $q->row();
        }
        return FALSE;
    }

    /**
     * Get all Fattura Tra Privati payments for a customer
     */
    public function getAllFatturaTraPrivatiPayments($customer_id, $year = NULL)
    {
        $this->db->where('customer_id', $customer_id);
        if ($year) {
            $this->db->where('payment_year', $year);
        }
        $this->db->order_by('payment_year', 'DESC');
        $this->db->order_by('due_date', 'ASC');
        $q = $this->db->get('fattura_tra_privati_payments');
        if ($q->num_rows() > 0) {
            return $q->result();
        }
        return array();
    }
}