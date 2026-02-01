-- Add permissions for Tax Calculations and Transfers (Fattura, Privati)
-- Run this once to add new permission columns to sma_permissions.
-- If a column already exists, skip that statement or run individually.

ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-index` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-view` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-edit` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-settings` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-inps_slabs` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `transfers-csv` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `transfers-transfer_by_fattura` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `transfers-transfer_by_fattura_privati` tinyint(1) DEFAULT 0;

-- INPS Slabs: separate list, add, edit, delete
ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-add_inps_slab` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-edit_inps_slab` tinyint(1) DEFAULT 0;
ALTER TABLE `sma_permissions` ADD COLUMN `tax_calculations-delete_inps_slab` tinyint(1) DEFAULT 0;
