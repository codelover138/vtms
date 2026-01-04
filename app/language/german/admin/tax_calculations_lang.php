<?php defined('BASEPATH') OR exit('No direct script access allowed');

// Tax Calculations
$lang['tax_calculations']                                  = "Steuerberechnungen";
$lang['tax_calculation']                                  = "Steuerberechnung";
$lang['view_tax_calculation']                            = "Steuerberechnung anzeigen";
$lang['calculate_tax']                                    = "Steuer berechnen";
$lang['calculate']                                        = "Berechnen";
$lang['calculate_tax_for_year']                           = "Steuer für Jahr berechnen";
$lang['tax_calculation_success']                          = "Steuerberechnung erfolgreich abgeschlossen";
$lang['tax_calculation_failed']                           = "Steuerberechnung fehlgeschlagen";
$lang['no_tax_calculation_found_for_year']                = "Keine Steuerberechnung für Jahr gefunden";
$lang['calculate_now']                                    = "Jetzt berechnen";

// Tax Settings
$lang['tax_settings']                                     = "Steuereinstellungen";
$lang['configure_tax_settings_for_customer']               = "Steuereinstellungen für diesen Kunden konfigurieren";
$lang['save_settings']                                     = "Einstellungen speichern";
$lang['tax_settings_updated']                             = "Steuereinstellungen erfolgreich aktualisiert";
$lang['tax_settings_update_failed']                       = "Fehler beim Aktualisieren der Steuereinstellungen";

// Customer Tax Fields
$lang['customer_type']                                    = "Kundentyp";
$lang['tax_regime']                                       = "Steuerregime";
$lang['coefficient_of_profitability']                     = "Rentabilitätskoeffizient";
$lang['tax_rate']                                         = "Steuersatz";
$lang['business_start_date']                              = "Geschäftsstartdatum";
$lang['inps_discount_eligible']                           = "INPS-Rabatt berechtigt";
$lang['annual_revenue_limit']                             = "Jahresumsatzgrenze";
$lang['employee_cost_limit']                              = "Mitarbeiterkostengrenze";

// Tax Calculation Fields
$lang['total_sales']                                      = "Gesamtumsatz";
$lang['previous_year_inps']                               = "INPS Vorjahr";
$lang['taxable_income']                                   = "Zu versteuerndes Einkommen";
$lang['tax_due']                                          = "Fällige Steuer";
$lang['advance_payments_made']                            = "Gezahlte Vorauszahlungen";
$lang['balance_payment']                                  = "Restzahlung";
$lang['next_year_advance_base']                           = "Vorauszahlungsbasis nächstes Jahr";
$lang['coefficient_used']                                 = "Verwendeter Koeffizient";
$lang['tax_rate_used']                                    = "Verwendeter Steuersatz";

// Tax Payments
$lang['tax_payments']                                     = "Steuerzahlungen";
$lang['payment_type']                                     = "Zahlungsart";
$lang['due_date']                                         = "Fälligkeitsdatum";
$lang['amount']                                           = "Betrag";
$lang['paid_amount']                                      = "Gezahlter Betrag";
$lang['paid_date']                                        = "Zahlungsdatum";
$lang['status']                                           = "Status";
$lang['pending']                                          = "Ausstehend";
$lang['paid']                                             = "Bezahlt";
$lang['overdue']                                          = "Überfällig";
$lang['mark_paid']                                        = "Als bezahlt markieren";
$lang['enter_paid_amount']                                = "Gezahlten Betrag eingeben";
$lang['enter_paid_date']                                  = "Zahlungsdatum eingeben (JJJJ-MM-TT)";
$lang['mark_payment_as_paid']                             = "Zahlung als bezahlt markieren";
$lang['please_enter_valid_amount']                        = "Bitte geben Sie einen gültigen Betrag ein";
$lang['please_enter_paid_date']                           = "Bitte geben Sie das Zahlungsdatum ein";
$lang['payment_updated']                                  = "Zahlung erfolgreich aktualisiert";
$lang['payment_update_failed']                            = "Fehler beim Aktualisieren der Zahlung";
$lang['balance']                                           = "Saldo";
$lang['first_advance']                                    = "Erste Vorauszahlung";
$lang['second_advance']                                   = "Zweite Vorauszahlung";

// INPS
$lang['inps_calculation']                                 = "INPS-Berechnung";
$lang['inps_payments']                                    = "INPS-Zahlungen";
$lang['inps_rate']                                        = "INPS-Satz";
$lang['inps_amount']                                      = "INPS-Betrag";
$lang['discount_percentage']                              = "Rabattprozentsatz";
$lang['discount_amount']                                  = "Rabattbetrag";
$lang['inps_amount_after_discount']                       = "INPS-Betrag nach Rabatt";
$lang['installment_number']                               = "Ratennummer";
$lang['eligible_for_35_percent_inps_discount']           = "Berechtigt für 35% INPS-Rabatt";
$lang['for_commercianti_artigiani_only']                  = "(Nur für Commercianti/Artigiani)";
$lang['inps_discount_only_for_commercianti_artigiani']    = "INPS-Rabatt kann nur für die Kundentypen Commercianti oder Artigiani aktiviert werden";
$lang['inps_slab_breakdown']                              = "INPS-Steuerklassen-Aufschlüsselung";
$lang['start']                                            = "Start";
$lang['end']                                              = "Ende";
$lang['inps']                                             = "INPS";
$lang['rate']                                              = "Satz";
$lang['fixed']                                             = "Fest";
$lang['total']                                             = "Gesamt";

// INAIL
$lang['inail_settings']                                    = "INAIL-Einstellungen";
$lang['for_artigiani_only']                                = "(Nur für Artigiani)";
$lang['inail_ateco_code']                                  = "ATECO-Code";
$lang['inail_ateco_code_help']                             = "ATECO-Code für INAIL-Risikoklassifizierung (z.B. 43.32.10 für Tischler)";
$lang['inail_rate']                                        = "INAIL-Satz";
$lang['inail_rate_help']                                   = "INAIL-Satz in Prozent basierend auf Risikoklasse (z.B. 4,2 für 4,2%)";
$lang['inail_minimum_payment']                             = "Mindestzahlung (Jährlich)";
$lang['inail_minimum_payment_help']                        = "Mindestbetrag der jährlichen INAIL-Zahlung (auch bei niedrigem Einkommen)";
$lang['inail_calculation']                                 = "INAIL-Berechnung";
$lang['inail_payments']                                    = "INAIL-Zahlungen";
$lang['inail_coefficient']                                 = "INAIL-Koeffizient";
$lang['inail_base_amount']                                 = "INAIL-Grundbetrag";
$lang['inail_calculated_amount']                           = "INAIL-Berechneter Betrag";
$lang['inail_final_amount']                                = "INAIL-Endbetrag";
$lang['ateco_code']                                        = "ATECO-Code";
$lang['minimum_applied']                                   = "Minimum Angewendet";
$lang['payment_frequency']                                 = "Zahlungshäufigkeit";
$lang['one_payment_per_year']                              = "1 Zahlung pro Jahr";
$lang['february_16_following_year']                        = "16. Februar des folgenden Jahres";
$lang['cannot_change_customer_type_with_existing_calculations'] = "Kundentyp kann nicht geändert werden. Dieser Kunde hat bereits Steuerberechnungen. Der Kundentyp kann nicht mehr geändert werden, sobald Berechnungen vorhanden sind.";
$lang['customer_type_locked_existing_calculations']       = "Kundentyp ist gesperrt, da dieser Kunde bereits Steuerberechnungen hat. Kann nicht geändert werden.";

// Help Text
$lang['coefficient_help_text']                            = "Prozentsatz des Umsatzes, der als zu versteuerndes Einkommen gilt";
$lang['tax_rate_help_text']                               = "Pauschaler Steuersatz, der auf das zu versteuernde Einkommen angewendet wird";
$lang['annual_revenue_limit_help_text']                   = "Maximaler Jahresumsatz für Regime Forfettario";
$lang['employee_cost_limit_help_text']                   = "Maximale jährliche Mitarbeiterkosten für Regime Forfettario";

// Customer Types
$lang['gestione_separata']                                = "Gestione Separata";
$lang['commercianti']                                     = "Commercianti";
$lang['artigiani']                                        = "Artigiani";

// Tax Regimes
$lang['regime_forfettario']                                = "Regime Forfettario";

// General
$lang['year']                                             = "Jahr";
$lang['back_to_list']                                     = "Zurück zur Liste";
$lang['back']                                             = "Zurück";
$lang['view']                                             = "Anzeigen";
$lang['settings']                                         = "Einstellungen";
$lang['not_set']                                          = "Nicht gesetzt";
$lang['select_customer_to_view_calculate_tax']             = "Wählen Sie einen Kunden aus, um Steuern anzuzeigen oder zu berechnen";
$lang['customer_not_found']                                = "Kunde nicht gefunden";
$lang['invalid_request']                                  = "Ungültige Anfrage";

// Additional
$lang['view_tax_calculations']                            = "Steuerberechnungen anzeigen";
$lang['no_customers_found']                                = "Keine Kunden gefunden";

// INPS Rate Slabs Management
$lang['inps_rate_slabs']                                  = "INPS-Steuersätze";
$lang['inps_slab']                                        = "INPS-Steuersatz";
$lang['add_inps_slab']                                    = "INPS-Steuersatz hinzufügen";
$lang['edit_inps_slab']                                   = "INPS-Steuersatz bearbeiten";
$lang['manage_inps_rate_slabs']                           = "INPS-Steuersätze verwalten";
$lang['income_from']                                      = "Einkommen Von";
$lang['income_to']                                         = "Einkommen Bis";
$lang['fixed_amount']                                     = "Fester Betrag";
$lang['is_active']                                         = "Ist Aktiv";
$lang['all_types']                                        = "Alle Typen";
$lang['leave_empty_for_unlimited']                        = "Leer lassen für unbegrenzt";
$lang['leave_empty_for_unlimited_help']                  = "Leer lassen, um kein oberes Limit für diesen Steuersatz anzugeben";
$lang['fixed_amount_help']                                = "Optionaler fester Betrag. Wenn gesetzt, wird dieser Betrag anstelle der Prozentberechnung für diesen Steuersatz verwendet";
$lang['optional']                                         = "Optional";
$lang['inps_slab_added']                                  = "INPS-Steuersatz erfolgreich hinzugefügt";
$lang['inps_slab_add_failed']                             = "Fehler beim Hinzufügen des INPS-Steuersatzes";
$lang['inps_slab_updated']                                = "INPS-Steuersatz erfolgreich aktualisiert";
$lang['inps_slab_update_failed']                          = "Fehler beim Aktualisieren des INPS-Steuersatzes";
$lang['inps_slab_deleted']                                = "INPS-Steuersatz erfolgreich gelöscht";
$lang['inps_slab_delete_failed']                          = "Fehler beim Löschen des INPS-Steuersatzes";
$lang['inps_slab_not_found']                              = "INPS-Steuersatz nicht gefunden";
$lang['either_inps_rate_or_fixed_amount_required']        = "Entweder INPS-Satz oder fester Betrag muss angegeben werden";
$lang['inps_rate_help_text']                               = "Entweder INPS-Satz oder fester Betrag muss angegeben werden. Wenn beide angegeben sind, hat der feste Betrag Vorrang für anwendbare Einkommensbereiche.";

// Diritto Annuale
$lang['diritto_annuale_settings']                          = "Diritto Annuale Einstellungen";
$lang['for_artigiani_commercianti_only']                    = "(Nur für Artigiani und Commercianti)";
$lang['diritto_annuale_amount']                            = "Diritto Annuale Betrag";
$lang['diritto_annuale_amount_help']                        = "Jährliche Gebühr für Artigiani und Commercianti. Die Zahlung ist am 31. März desselben Jahres fällig.";
$lang['diritto_annuale_payments']                           = "Diritto Annuale Zahlungen";
$lang['diritto_annuale_info']                               = "Diritto Annuale (Jährliche Gebühr) gilt für Artigiani und Commercianti. Die Zahlung ist bis zum 31. März desselben Steuerjahres fällig.";
$lang['march_31_same_year']                                 = "31. März desselben Jahres";

// Fattura Tra Privati
$lang['fattura_tra_privati_calculation']                    = "Fattura Tra Privati Berechnung";
$lang['fattura_tra_privati_payments']                       = "Fattura Tra Privati Zahlungen";
$lang['fattura_tra_privati_info']                           = "Fattura Tra Privati (Rechnung zwischen Privatpersonen): Berechnet aus Rechnungen mit Betrag ≥ €77,47 aus income_data wo pos=2. Die Zahlung beträgt €2 pro Rechnung, fällig bis zum 16. Februar des folgenden Jahres.";
$lang['total_invoices']                                     = "Gesamtzahl Rechnungen";
$lang['payment_per_invoice']                                = "Zahlung Pro Rechnung";
$lang['minimum_invoice_amount']                             = "Mindestrechnungsbetrag";
$lang['calculation_basis']                                  = "Berechnungsgrundlage";
$lang['fattura_tra_privati_basis']                          = "Betrag ≥ €77,47";