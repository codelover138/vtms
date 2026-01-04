-- --------------------------------------------------------
-- Host:                         127.0.0.1
-- Server version:               5.7.24 - MySQL Community Server (GPL)
-- Server OS:                    Win64
-- HeidiSQL Version:             12.11.0.7065
-- --------------------------------------------------------

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET NAMES utf8 */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

-- Dumping data for table vtms.sma_inps_rate_slabs: ~5 rows (approximately)
REPLACE INTO `sma_inps_rate_slabs` (`id`, `slab_year`, `customer_type`, `income_from`, `income_to`, `inps_rate`, `fixed_amount`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(1, '2025', 'Commercianti', 0.00, 10000.00, 20.00, 0.00, 'Fixed minimum for income range 0-18555 (all types)', 1, '2026-01-02 23:45:26', '2026-01-03 15:37:42'),
	(2, '2025', 'Commercianti', 10001.00, 12000.00, 22.25, NULL, 'INPS rate for income range 18556-50000 (all types)', 1, '2026-01-02 23:45:26', '2026-01-03 15:37:57'),
	(3, '2025', 'Commercianti', 12001.00, 100000.00, 24.75, NULL, 'INPS rate for income range 50001-100000 (all types)', 1, '2026-01-02 23:45:26', '2026-01-03 15:38:04'),
	(4, '2025', 'Commercianti', 100001.00, NULL, 25.25, NULL, 'INPS rate for income above 100000 (all types)', 1, '2026-01-02 23:45:26', '2026-01-02 23:47:16'),
	(5, '2024', 'Commercianti', 0.00, 18555.00, 21.00, NULL, 'Inps', 1, '2026-01-03 00:34:18', '2026-01-03 00:34:50');

    REPLACE INTO `sma_inps_rate_slabs` (`id`, `slab_year`, `customer_type`, `income_from`, `income_to`, `inps_rate`, `fixed_amount`, `description`, `is_active`, `created_at`, `updated_at`) VALUES
	(6, '2025', 'Artigiani', 0.00, 10000.00, 20.00, 0.00, 'Fixed minimum for income range 0-18555 (all types)', 1, '2026-01-02 23:45:26', '2026-01-03 15:37:42'),
	(7, '2025', 'Artigiani', 10001.00, 12000.00, 22.25, NULL, 'INPS rate for income range 18556-50000 (all types)', 1, '2026-01-02 23:45:26', '2026-01-03 15:37:57'),
	(8, '2025', 'Artigiani', 12001.00, 100000.00, 24.75, NULL, 'INPS rate for income range 50001-100000 (all types)', 1, '2026-01-02 23:45:26', '2026-01-03 15:38:04'),
	(9, '2025', 'Artigiani', 100001.00, NULL, 25.25, NULL, 'INPS rate for income above 10000 (all types)', 1, '2026-01-02 23:45:26', '2026-01-02 23:47:16'),
	(10, '2024', 'Artigiani', 18556.99, NULL, 38.75, NULL, 'Inps', 1, '2026-xx-xx xx:xx:xx', 'yyyy-mm-dd hh:mm:ss');

/*!40103 SET TIME_ZONE=IFNULL(@OLD_TIME_ZONE, 'system') */;
/*!40101 SET SQL_MODE=IFNULL(@OLD_SQL_MODE, '') */;
/*!40014 SET FOREIGN_KEY_CHECKS=IFNULL(@OLD_FOREIGN_KEY_CHECKS, 1) */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40111 SET SQL_NOTES=IFNULL(@OLD_SQL_NOTES, 1) */;
