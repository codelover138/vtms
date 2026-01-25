-- Updated INPS Rate Slabs Table with Customer Type
-- This table stores INPS rate slabs that can vary by year AND customer type
-- Slabs can change over time and vary based on customer type and year

CREATE TABLE `sma_inps_rate_slabs` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `slab_year` year(4) NOT NULL COMMENT 'Year this slab applies to (e.g., 2024)',
  `customer_type` varchar(50) DEFAULT NULL COMMENT 'Customer type: Gestione Separata, Commercianti, Artigiani, or NULL for all types',
  `income_from` decimal(15,2) NOT NULL DEFAULT '0.00',
  `income_to` decimal(15,2) DEFAULT NULL COMMENT 'NULL means no upper limit',
  `inps_rate` decimal(5,2) NOT NULL DEFAULT '0.00' COMMENT 'Percentage rate',
  `fixed_amount` decimal(15,2) DEFAULT NULL COMMENT 'Fixed minimum amount (for income range 0-18555)',
  `description` varchar(255) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT '1' COMMENT '1=active, 0=inactive',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uniq_slab_range_year_type` (`slab_year`,`customer_type`,`income_from`),
  KEY `idx_inps_slabs_year` (`slab_year`),
  KEY `idx_inps_slabs_customer_type` (`customer_type`),
  KEY `idx_inps_slabs_active` (`is_active`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8;

-- Insert default INPS rate slabs for current year (example for 2024)
-- NULL customer_type means the slab applies to all customer types
-- You can add specific slabs for different customer types by setting customer_type

INSERT INTO `sma_inps_rate_slabs` (`slab_year`, `customer_type`, `income_from`, `income_to`, `inps_rate`, `fixed_amount`, `description`, `is_active`) VALUES
-- Generic slabs (apply to all customer types)
(2024, NULL, 0.00, 18555.00, 0.00, 3900.00, 'Fixed minimum for income range 0-18555 (all types)', 1),
(2024, NULL, 18556.00, 50000.00, 24.25, NULL, 'INPS rate for income range 18556-50000 (all types)', 1),
(2024, NULL, 50001.00, 100000.00, 24.75, NULL, 'INPS rate for income range 50001-100000 (all types)', 1),
(2024, NULL, 100001.00, NULL, 25.25, NULL, 'INPS rate for income above 100000 (all types)', 1);

-- Example: Add specific slabs for different customer types (uncomment and modify as needed)
-- INSERT INTO `sma_inps_rate_slabs` (`slab_year`, `customer_type`, `income_from`, `income_to`, `inps_rate`, `fixed_amount`, `description`, `is_active`) VALUES
-- (2024, 'Commercianti', 0.00, 18555.00, 0.00, 3900.00, 'Fixed minimum for Commercianti income range 0-18555', 1),
-- (2024, 'Artigiani', 0.00, 18555.00, 0.00, 3900.00, 'Fixed minimum for Artigiani income range 0-18555', 1),
-- (2024, 'Gestione Separata', 0.00, 18555.00, 0.00, 4000.00, 'Fixed minimum for Gestione Separata income range 0-18555', 1);

-- Example: Add slabs for different years
-- INSERT INTO `sma_inps_rate_slabs` (`slab_year`, `customer_type`, `income_from`, `income_to`, `inps_rate`, `fixed_amount`, `description`, `is_active`) VALUES
-- (2025, NULL, 0.00, 18555.00, 0.00, 4000.00, 'Fixed minimum for income range 0-18555 (2025, all types)', 1),
-- (2025, NULL, 18556.00, 50000.00, 24.50, NULL, 'INPS rate for income range 18556-50000 (2025, all types)', 1),
-- (2025, NULL, 50001.00, 100000.00, 25.00, NULL, 'INPS rate for income range 50001-100000 (2025, all types)', 1),
-- (2025, NULL, 100001.00, NULL, 25.50, NULL, 'INPS rate for income above 100000 (2025, all types)', 1);



