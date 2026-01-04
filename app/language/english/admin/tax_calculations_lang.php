<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Tax Calculations
$lang['tax_calculations']                                  = "Tax Calculations";
$lang['tax_calculation']                                  = "Tax Calculation";
$lang['view_tax_calculation']                            = "View Tax Calculation";
$lang['calculate_tax']                                    = "Calculate Tax";
$lang['calculate']                                        = "Calculate";
$lang['calculate_tax_for_year']                           = "Calculate tax for year";
$lang['tax_calculation_success']                          = "Tax calculation completed successfully";
$lang['tax_calculation_failed']                           = "Tax calculation failed";
$lang['no_tax_calculation_found_for_year']                = "No tax calculation found for year";
$lang['calculate_now']                                    = "Calculate Now";

// Tax Settings
$lang['tax_settings']                                     = "Tax Settings";
$lang['configure_tax_settings_for_customer']               = "Configure tax settings for this customer";
$lang['save_settings']                                     = "Save Settings";
$lang['tax_settings_updated']                             = "Tax settings updated successfully";
$lang['tax_settings_update_failed']                       = "Failed to update tax settings";

// Customer Tax Fields
$lang['customer_type']                                    = "Customer Type";
$lang['tax_regime']                                       = "Tax Regime";
$lang['coefficient_of_profitability']                     = "Coefficient of Profitability";
$lang['tax_rate']                                         = "Tax Rate";
$lang['business_start_date']                              = "Business Start Date";
$lang['inps_discount_eligible']                           = "INPS Discount Eligible";
$lang['annual_revenue_limit']                             = "Annual Revenue Limit";
$lang['employee_cost_limit']                              = "Employee Cost Limit";

// Tax Calculation Fields
$lang['total_sales']                                      = "Total Sales";
$lang['previous_year_inps']                               = "Previous Year INPS";
$lang['taxable_income']                                   = "Taxable Income";
$lang['tax_due']                                          = "Tax Due";
$lang['advance_payments_made']                            = "Advance Payments Made";
$lang['balance_payment']                                  = "Balance Payment";
$lang['next_year_advance_base']                           = "Next Year Advance Base";
$lang['coefficient_used']                                 = "Coefficient Used";
$lang['tax_rate_used']                                    = "Tax Rate Used";

// Tax Payments
$lang['tax_payments']                                     = "Tax Payments";
$lang['payment_type']                                     = "Payment Type";
$lang['due_date']                                         = "Due Date";
$lang['amount']                                           = "Amount";
$lang['paid_amount']                                      = "Paid Amount";
$lang['paid_date']                                        = "Paid Date";
$lang['status']                                           = "Status";
$lang['pending']                                          = "Pending";
$lang['paid']                                             = "Paid";
$lang['overdue']                                          = "Overdue";
$lang['mark_paid']                                        = "Mark as Paid";
$lang['enter_paid_amount']                                = "Enter Paid Amount";
$lang['enter_paid_date']                                  = "Enter Paid Date (YYYY-MM-DD)";
$lang['mark_payment_as_paid']                             = "Mark Payment as Paid";
$lang['please_enter_valid_amount']                        = "Please enter a valid amount";
$lang['please_enter_paid_date']                           = "Please enter the paid date";
$lang['payment_updated']                                  = "Payment updated successfully";
$lang['payment_update_failed']                            = "Failed to update payment";
$lang['balance']                                           = "Balance";
$lang['first_advance']                                    = "First Advance";
$lang['second_advance']                                   = "Second Advance";

// INPS
$lang['inps_calculation']                                 = "INPS Calculation";
$lang['inps_payments']                                    = "INPS Payments";
$lang['inps_rate']                                        = "INPS Rate";
$lang['inps_amount']                                      = "INPS Amount";
$lang['discount_percentage']                              = "Discount Percentage";
$lang['discount_amount']                                  = "Discount Amount";
$lang['inps_amount_after_discount']                       = "INPS Amount After Discount";
$lang['installment_number']                               = "Installment Number";
$lang['eligible_for_35_percent_inps_discount']           = "Eligible for 35% INPS Discount";
$lang['for_commercianti_artigiani_only']                  = "(For Commercianti/Artigiani only)";
$lang['inps_slab_breakdown']                              = "INPS Slab Breakdown";
$lang['start']                                            = "Start";
$lang['end']                                              = "End";
$lang['inps']                                             = "INPS";
$lang['rate']                                              = "Rate";
$lang['fixed']                                             = "Fixed";
$lang['total']                                             = "Total";

// INAIL
$lang['inail_settings']                                    = "INAIL Settings";
$lang['for_artigiani_only']                                = "(For Artigiani only)";
$lang['inail_ateco_code']                                  = "ATECO Code";
$lang['inail_ateco_code_help']                             = "ATECO code for INAIL risk classification (e.g., 43.32.10 for Carpenter)";
$lang['inail_rate']                                        = "INAIL Rate";
$lang['inail_rate_help']                                   = "INAIL rate percentage based on risk class (e.g., 4.2 for 4.2%)";
$lang['inail_minimum_payment']                             = "Minimum Payment (Yearly)";
$lang['inail_minimum_payment_help']                        = "Minimum yearly INAIL payment amount (even with low income)";
$lang['inail_calculation']                                 = "INAIL Calculation";
$lang['inail_payments']                                    = "INAIL Payments";
$lang['inail_coefficient']                                 = "INAIL Coefficient";
$lang['inail_base_amount']                                 = "INAIL Base Amount";
$lang['inail_calculated_amount']                           = "INAIL Calculated Amount";
$lang['inail_final_amount']                                = "INAIL Final Amount";
$lang['ateco_code']                                        = "ATECO Code";
$lang['minimum_applied']                                   = "Minimum Applied";
$lang['payment_frequency']                                 = "Payment Frequency";
$lang['one_payment_per_year']                              = "1 payment per year";
$lang['february_16_following_year']                        = "February 16 of following year";
$lang['cannot_change_customer_type_with_existing_calculations'] = "Cannot change customer type. This customer already has tax calculations. Customer type cannot be modified once calculations exist.";
$lang['customer_type_locked_existing_calculations']       = "Customer type is locked because this customer already has tax calculations. Cannot be changed.";
$lang['inps_discount_only_for_commercianti_artigiani']    = "INPS discount can only be enabled for Commercianti or Artigiani customer types";

// Help Text
$lang['coefficient_help_text']                            = "Percentage of sales considered as taxable income";
$lang['tax_rate_help_text']                               = "Flat tax rate applied to taxable income";
$lang['annual_revenue_limit_help_text']                   = "Maximum annual revenue allowed for Regime Forfettario";
$lang['employee_cost_limit_help_text']                   = "Maximum annual employee costs allowed for Regime Forfettario";

// Customer Types
$lang['gestione_separata']                                = "Gestione Separata";
$lang['commercianti']                                     = "Commercianti";
$lang['artigiani']                                        = "Artigiani";

// Tax Regimes
$lang['regime_forfettario']                               = "Regime Forfettario";

// General
$lang['year']                                             = "Year";
$lang['back_to_list']                                     = "Back to List";
$lang['back']                                             = "Back";
$lang['view']                                             = "View";
$lang['settings']                                         = "Settings";
$lang['not_set']                                          = "Not Set";
$lang['select_customer_to_view_calculate_tax']             = "Select a customer to view or calculate tax";
$lang['customer_not_found']                                = "Customer not found";
$lang['invalid_request']                                  = "Invalid request";

// Payment Context
$lang['for_tax_year']                                     = "For Tax Year";
$lang['calculated_in_year']                               = "Calculated in";
$lang['payments_due_in']                                  = "Payments due in";
$lang['tax_year']                                         = "Tax Year";

// Additional
$lang['view_tax_calculations']                            = "View Tax Calculations";
$lang['no_customers_found']                                = "No customers found";

// INPS Rate Slabs Management
$lang['inps_rate_slabs']                                  = "INPS Rate Slabs";
$lang['inps_slab']                                        = "INPS Slab";
$lang['add_inps_slab']                                    = "Add INPS Slab";
$lang['edit_inps_slab']                                   = "Edit INPS Slab";
$lang['manage_inps_rate_slabs']                           = "Manage INPS Rate Slabs";
$lang['income_from']                                      = "Income From";
$lang['income_to']                                        = "Income To";
$lang['fixed_amount']                                     = "Fixed Amount";
$lang['is_active']                                         = "Is Active";
$lang['all_types']                                        = "All Types";
$lang['leave_empty_for_unlimited']                        = "Leave empty for unlimited";
$lang['leave_empty_for_unlimited_help']                  = "Leave empty to indicate no upper limit for this slab";
$lang['fixed_amount_help']                                = "Optional fixed amount. If set, this amount will be used instead of percentage calculation for this slab";
$lang['optional']                                         = "Optional";
$lang['inps_slab_added']                                  = "INPS slab added successfully";
$lang['inps_slab_add_failed']                             = "Failed to add INPS slab";
$lang['inps_slab_updated']                                = "INPS slab updated successfully";
$lang['inps_slab_update_failed']                          = "Failed to update INPS slab";
$lang['inps_slab_deleted']                                = "INPS slab deleted successfully";
$lang['inps_slab_delete_failed']                          = "Failed to delete INPS slab";
$lang['inps_slab_not_found']                              = "INPS slab not found";
$lang['either_inps_rate_or_fixed_amount_required']        = "Either INPS Rate or Fixed Amount must be provided";
$lang['inps_rate_help_text']                               = "Either INPS Rate or Fixed Amount must be provided. If both are provided, Fixed Amount takes precedence for applicable income ranges.";

// Diritto Annuale
$lang['diritto_annuale_settings']                          = "Diritto Annuale Settings";
$lang['for_artigiani_commercianti_only']                    = "(For Artigiani and Commercianti only)";
$lang['diritto_annuale_amount']                            = "Diritto Annuale Amount";
$lang['diritto_annuale_amount_help']                        = "Annual fee amount for Artigiani and Commercianti. Payment is due March 31 of the same year.";
$lang['diritto_annuale_payments']                           = "Diritto Annuale Payments";
$lang['diritto_annuale_info']                               = "Diritto Annuale (Annual Fee) applies to Artigiani and Commercianti. Payment is due by March 31 of the same tax year.";
$lang['march_31_same_year']                                 = "March 31 of same year";

// Fattura Tra Privati
$lang['fattura_tra_privati_calculation']                    = "Fattura Tra Privati Calculation";
$lang['fattura_tra_privati_payments']                       = "Fattura Tra Privati Payments";
$lang['fattura_tra_privati_info']                           = "Fattura Tra Privati (Invoice between Private Parties): Calculated from invoices with amount ≥ €77.47 from income_data where pos=2. Payment is €2 per invoice, due by February 16 of the following year.";
$lang['total_invoices']                                     = "Total Invoices";
$lang['payment_per_invoice']                                = "Payment Per Invoice";
$lang['minimum_invoice_amount']                             = "Minimum Invoice Amount";
$lang['calculation_basis']                                  = "Calculation Basis";
$lang['fattura_tra_privati_basis']                          = "Amount ≥ €77.47";

// Dashboard
$lang['dashboard_overview']                                  = "Your Tax & Payment Overview";
$lang['total_due']                                          = "Total Due";
$lang['upcoming_payments']                                  = "Upcoming Payments";
$lang['total_paid']                                         = "Total Paid";
$lang['no_tax_calculations']                                = "No Tax Calculations Available";
$lang['no_tax_calculations_message']                        = "Tax calculations will appear here once they are processed for your account.";
$lang['no_payments']                                        = "No Payments Found";
$lang['no_payments_message']                               = "Payment schedule will appear here once tax calculations are available.";
$lang['all_tax_calculations']                               = "All Tax Calculations";
$lang['payment_schedule']                                   = "Payment Schedule";
$lang['showing']                                            = "Showing";
$lang['of']                                                 = "of";
$lang['installment']                                        = "Installment";
$lang['user_dashboard']                                     = "User Dashboard";
$lang['select_year']                                        = "Select Year";
$lang['no_inps_calculation']                               = "No INPS Calculation Available";
$lang['no_inps_calculation_message']                       = "INPS calculations will appear here once they are processed for your account.";
$lang['inps_taxable_income']                               = "INPS Taxable Income";
$lang['calculations']                                      = "Calculations";
$lang['status']                                             = "Status";
$lang['active']                                             = "Active";
$lang['income_prediction']                                 = "Income Prediction";
$lang['current_taxable_income']                            = "Current Taxable Income";
$lang['predicted_taxable_income']                          = "Predicted Taxable Income";
$lang['based_on']                                          = "Based on";
$lang['months_of_data']                                    = "months of data";
$lang['if_all_months_completed']                           = "If all 12 months completed";
$lang['data_status']                                       = "Data Status";
$lang['months_entered']                                    = "months entered";
$lang['average_monthly_sales']                             = "Average Monthly Sales";
$lang['predicted_additional_sales']                        = "Predicted Additional Sales";
$lang['predicted_total_sales']                             = "Predicted Total Sales";
$lang['jan']                                               = "Jan";
$lang['feb']                                               = "Feb";
$lang['mar']                                               = "Mar";
$lang['apr']                                               = "Apr";
$lang['may']                                               = "May";
$lang['jun']                                               = "Jun";
$lang['jul']                                               = "Jul";
$lang['aug']                                               = "Aug";
$lang['sep']                                               = "Sep";
$lang['oct']                                               = "Oct";
$lang['nov']                                               = "Nov";
$lang['dec']                                               = "Dec";
$lang['overdue_payments']                                  = "Overdue Payments";
$lang['paid_payments']                                     = "Paid Payments";