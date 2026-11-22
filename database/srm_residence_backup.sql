-- SRM RESIDENCE COMPLETE DATABASE DUMP
-- Generated: 2026-11-22 11:24:24
SET FOREIGN_KEY_CHECKS = 0;

-- --------------------------------------------------------
-- Table: `users` (3 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `users`;
INSERT INTO `users` (`id`, `name`, `phone`, `email`, `image`, `password`, `type`, `status`, `delivery_point_id`, `full_address`, `device_id`, `fcm_token`, `remember_token`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', 'Jagan', '7010663823', 'ronaldojagan46@gmail.com', 'avatar.jpg', '$2y$12$H/2Hi1WE4MP.tGs8Mk14MeU9UY.E9DFWSHEhSi2bEjEE.tmEOmO8u', 'Admin', '1', NULL, NULL, NULL, NULL, 'OwcUCoY8Nv', '2026-11-04 00:16:07', '2026-11-07 23:15:22', NULL),
('16', 'SRM Admin', '9876543210', 'admin@srmresidence.in', NULL, '$2y$12$ChYRfyCbI8Bmo4cGb3B0M./538KI5nKd9Rap6nR8MWcUsthCX8WLO', 'Admin', '1', NULL, NULL, NULL, NULL, NULL, '2026-11-07 23:06:25', '2026-11-07 23:09:40', NULL),
('17', 'Ragavan', '6382016520', 'ragavanrocky93@gmail.com', 'avatar.jpg', '$2y$12$uTHAUAtT5o0sM8/U26GJceKQt7flu6NyeywMjBlls.PyoaOJWWV92', 'Admin', '1', NULL, NULL, NULL, NULL, NULL, '2026-11-07 23:52:25', '2026-11-07 23:52:25', NULL);

-- --------------------------------------------------------
-- Table: `applications` (3 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `applications`;
INSERT INTO `applications` (`id`, `value`, `group`, `name`, `deleted_at`, `created_at`, `updated_at`) VALUES
('1', '+91 7010663823', 'Contact', 'contact_phone', NULL, '2026-11-07 13:23:18', '2026-11-07 13:25:45'),
('2', 'ronaldojagan46@gmail.com', 'Contact', 'contact_email', NULL, '2026-11-07 13:23:18', '2026-11-07 13:25:45'),
('3', 'No.42 Ambethkar street, North sanjai gandhi nagar, Zamin Pallavaram Chennai 600043', 'Contact', 'contact_address', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45');

-- --------------------------------------------------------
-- Table: `banners` (1 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `banners`;
INSERT INTO `banners` (`id`, `name`, `image`, `status`, `deleted_at`, `created_at`, `updated_at`) VALUES
('1', 'SRM Fest Banner Updated', '1794038523_test_banner.jpg', '1', '2026-11-07 13:32:04', '2026-11-07 13:32:04', '2026-11-07 13:32:04');

-- --------------------------------------------------------
-- Table: `rooms` (24 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `rooms`;
INSERT INTO `rooms` (`id`, `room_number`, `floor`, `room_type`, `capacity`, `monthly_rent`, `eb_unit_rate`, `maintenance_charges`, `other_charges`, `security_deposit`, `occupancy_status`, `is_self`, `amenities`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', '101-A', '1st Floor', 'Executive Suite', '2', '14000.00', '10.00', '0.00', '0.00', '18000.00', 'available', '0', 'AC, Wi-Fi, Balcony, Smart TV', '1', '2026-11-04 23:17:04', '2026-11-04 23:17:04', '2026-11-04 23:17:04'),
('2', '2', 'Ground Floor', 'Single Deluxe', '8', '10000.00', '0.00', '0.00', '0.00', '5000.00', 'occupied', '0', NULL, '1', '2026-11-04 23:23:03', '2026-11-07 16:16:45', NULL),
('3', '1', 'Ground Floor', 'Single Deluxe', '6', '6500.00', '0.00', '0.00', '0.00', '20000.00', 'occupied', '0', NULL, '1', '2026-11-04 23:26:54', '2026-11-07 12:50:53', NULL),
('4', '3', 'Ground Floor', 'Single Deluxe', '1', '5000.00', '10.00', '0.00', '250.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-05 23:32:05', '2026-11-07 13:05:27', NULL),
('5', '4', 'Ground Floor', 'Single Deluxe', '7', '6000.00', '0.00', '0.00', '0.00', '15000.00', 'occupied', '0', NULL, '1', '2026-11-06 19:04:54', '2026-11-07 11:22:21', NULL),
('6', '5', 'Ground Floor', 'Single Deluxe', '7', '8500.00', '0.00', '0.00', '0.00', '12500.00', 'occupied', '0', NULL, '1', '2026-11-06 19:10:31', '2026-11-07 12:51:16', NULL),
('9', '6', 'Ground Floor', 'Single Deluxe', '8', '7000.00', '10.00', '0.00', '0.00', '20000.00', 'occupied', '0', NULL, '1', '2026-11-07 00:05:21', '2026-11-07 14:58:40', NULL),
('10', '7', 'Ground Floor', 'Single Deluxe', '2', '4000.00', '0.00', '0.00', '0.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-07 09:37:33', '2026-11-07 12:57:47', NULL),
('11', '8', '1st Floor', 'Single Deluxe', '1', '0.00', '0.00', '0.00', '0.00', '0.00', 'self', '1', NULL, '1', '2026-11-07 09:38:26', '2026-11-07 17:03:29', NULL),
('12', '9', 'Ground Floor', 'Single Deluxe', '4', '4500.00', '10.00', '0.00', '0.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-07 09:38:48', '2026-11-07 11:40:56', NULL),
('13', '10', 'Ground Floor', 'Single Deluxe', '8', '5000.00', '10.00', '0.00', '0.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-07 09:39:30', '2026-11-07 16:37:01', NULL),
('14', '11', 'Ground Floor', 'Single Deluxe', '7', '8500.00', '10.00', '0.00', '0.00', '12500.00', 'occupied', '0', NULL, '1', '2026-11-07 09:40:08', '2026-11-07 12:51:16', NULL),
('17', '12', 'Ground Floor', 'Single Deluxe', '4', '0.00', '0.00', '0.00', '0.00', '0.00', 'self', '1', NULL, '1', '2026-11-07 09:59:44', '2026-11-07 09:59:44', NULL),
('18', '13', 'Ground Floor', 'Single Deluxe', '2', '5000.00', '0.00', '0.00', '0.00', '20000.00', 'occupied', '0', NULL, '1', '2026-11-07 10:13:47', '2026-11-07 12:59:21', NULL),
('19', '14', 'Ground Floor', 'Single Deluxe', '5', '6000.00', '10.00', '0.00', '0.00', '15000.00', 'occupied', '0', NULL, '1', '2026-11-07 10:14:20', '2026-11-07 16:14:27', NULL),
('20', '15', '1st Floor', 'Single Deluxe', '8', '6000.00', '10.00', '0.00', '0.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-07 10:14:55', '2026-11-07 11:44:40', NULL),
('21', '16', '1st Floor', 'Single Deluxe', '6', '6000.00', '10.00', '0.00', '0.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-07 10:15:22', '2026-11-07 11:47:18', NULL),
('22', '17', '1st Floor', 'Single Deluxe', '6', '6000.00', '10.00', '0.00', '0.00', '10000.00', 'available', '0', NULL, '1', '2026-11-07 10:15:36', '2026-11-07 10:15:36', NULL),
('23', '18', 'Ground Floor', 'Single Deluxe', '1', '0.00', '0.00', '0.00', '0.00', '0.00', 'self', '1', NULL, '1', '2026-11-07 10:15:58', '2026-11-07 10:15:58', NULL),
('24', '19', 'Ground Floor', 'Single Deluxe', '1', '0.00', '0.00', '0.00', '0.00', '0.00', 'self', '1', NULL, '1', '2026-11-07 10:16:14', '2026-11-07 10:16:14', NULL),
('25', '20', 'Ground Floor', 'Single Deluxe', '1', '0.00', '0.00', '0.00', '0.00', '0.00', 'self', '1', NULL, '1', '2026-11-07 10:16:25', '2026-11-07 10:16:25', NULL),
('26', '21', '1st Floor', 'Single Deluxe', '6', '6000.00', '10.00', '0.00', '0.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-07 10:16:49', '2026-11-07 12:53:56', NULL),
('27', '22', '1st Floor', 'Single Deluxe', '1', '0.00', '0.00', '0.00', '0.00', '0.00', 'self', '1', NULL, '1', '2026-11-07 10:16:59', '2026-11-07 17:04:16', NULL),
('28', '23', '1st Floor', 'Single Deluxe', '4', '5000.00', '10.00', '0.00', '0.00', '10000.00', 'occupied', '0', NULL, '1', '2026-11-07 10:17:21', '2026-11-07 16:22:12', NULL);

-- --------------------------------------------------------
-- Table: `tenants` (17 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `tenants`;
INSERT INTO `tenants` (`id`, `room_id`, `name`, `email`, `phone`, `room_number`, `room_type`, `monthly_rent`, `eb_unit_rate`, `eb_start_reading`, `maintenance_charges`, `other_charges`, `security_deposit`, `commence_date`, `emergency_contact`, `address`, `status`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', '2', 'ATIULLAH KHAN', NULL, '9839035286', '2', 'Single Deluxe', '10000.00', '0.00', '0.00', '0.00', '0.00', '5000.00', '2026-11-06', NULL, NULL, '1', '2026-11-04 23:25:20', '2026-11-07 16:16:45', NULL),
('2', '3', 'Abid', NULL, '9840065836', '1, 6', 'Single Deluxe', '13500.00', '0.00', '0.00', '0.00', '0.00', '40000.00', '2026-08-31', NULL, NULL, '1', '2026-11-05 23:29:00', '2026-11-07 12:50:53', NULL),
('3', '4', 'Ravi', NULL, '7010663823', '3', 'Single Deluxe', '5000.00', '10.00', '0.00', '0.00', '250.00', '10000.00', '2026-11-03', NULL, NULL, '1', '2026-11-05 23:32:58', '2026-11-07 13:05:27', NULL),
('4', '5', 'Vimal Raj', NULL, '7092549999', '4', 'Single Deluxe', '6000.00', '0.00', '0.00', '0.00', '0.00', '15000.00', '2026-11-06', NULL, NULL, '1', '2026-11-07 11:22:21', '2026-11-07 11:28:50', NULL),
('5', '6', 'Surendhar', NULL, '9594170969', '5, 11', 'Single Deluxe', '17000.00', '0.00', '0.00', '0.00', '0.00', '25000.00', '2026-11-11', NULL, NULL, '1', '2026-11-07 11:36:20', '2026-11-07 12:51:16', NULL),
('6', '11', 'Abishek', NULL, '7349482713', '8', 'Single Deluxe', '5000.00', '0.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 11:39:29', '2026-11-07 17:00:08', '2026-11-07 17:00:08'),
('7', '12', 'Malar', NULL, '8056135985', '9', 'Single Deluxe', '4500.00', '10.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 11:40:56', '2026-11-07 11:40:56', NULL),
('8', '19', 'Anil mehandi', NULL, '6377652410', '14', 'Single Deluxe', '6000.00', '10.00', '0.00', '0.00', '0.00', '15000.00', '2026-11-06', NULL, NULL, '1', '2026-11-07 11:43:30', '2026-11-07 16:14:27', NULL),
('9', '20', 'Ramjii', NULL, '9798294011', '15', 'Single Deluxe', '6000.00', '10.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 11:44:40', '2026-11-07 11:44:40', NULL),
('10', '21', 'najrul khan', NULL, '8001381204', '16', 'Single Deluxe', '6000.00', '10.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 11:47:18', '2026-11-07 11:47:18', NULL),
('11', '9', 'Multi Room Tenant Test', 'multiroom@test.com', '9876543210', '6, 7', 'Single Deluxe, 2-Sharing AC', '7000.00', '10.00', '0.00', '0.00', '0.00', '20000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 12:50:44', '2026-11-07 12:56:45', '2026-11-07 12:56:45'),
('13', '26', 'Golden Kumar', NULL, '6287505002', '21', 'Single Deluxe', '6000.00', '10.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 12:53:56', '2026-11-07 12:53:56', NULL),
('14', '10', 'Murugasen  sivagami', NULL, '7010663823', '7', 'Single Deluxe', '4000.00', '0.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 12:57:47', '2026-11-07 12:57:47', NULL),
('15', '18', 'Merry', NULL, '7010663823', '13', 'Single Deluxe', '5000.00', '0.00', '0.00', '0.00', '0.00', '20000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 12:59:21', '2026-11-07 12:59:21', NULL),
('16', '27', 'boopathi', NULL, '7339452586', '22', 'Single Deluxe', '6000.00', '0.00', '0.00', '0.00', '0.00', '0.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 15:14:11', '2026-11-07 17:04:02', '2026-11-07 17:04:02'),
('17', '28', 'Satheesh', NULL, '6380180859', '23', 'Single Deluxe', '5000.00', '10.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 16:22:12', '2026-11-07 16:22:12', NULL),
('19', '13', 'Amirul islam', NULL, '9395264989', '10', 'Single Deluxe', '5000.00', '10.00', '0.00', '0.00', '0.00', '10000.00', '2026-11-07', NULL, NULL, '1', '2026-11-07 16:37:01', '2026-11-07 16:37:01', NULL);

-- --------------------------------------------------------
-- Table: `tenant_rooms` (16 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `tenant_rooms`;
INSERT INTO `tenant_rooms` (`id`, `tenant_id`, `room_id`, `created_at`, `updated_at`) VALUES
('1', '1', '2', '2026-11-04 23:25:20', '2026-11-07 11:26:37'),
('2', '2', '3', '2026-11-05 23:29:00', '2026-11-05 23:29:00'),
('3', '3', '4', '2026-11-05 23:32:58', '2026-11-07 11:28:21'),
('4', '4', '5', '2026-11-07 11:22:21', '2026-11-07 11:28:50'),
('5', '5', '6', '2026-11-07 11:36:20', '2026-11-07 11:36:20'),
('7', '7', '12', '2026-11-07 11:40:56', '2026-11-07 11:40:56'),
('8', '8', '19', '2026-11-07 11:43:30', '2026-11-07 11:43:30'),
('9', '9', '20', '2026-11-07 11:44:40', '2026-11-07 11:44:40'),
('10', '10', '21', '2026-11-07 11:47:18', '2026-11-07 11:47:18'),
('13', '2', '9', '2026-11-07 12:50:53', '2026-11-07 12:50:53'),
('16', '5', '14', '2026-11-07 12:51:16', '2026-11-07 12:51:16'),
('17', '13', '26', '2026-11-07 12:53:56', '2026-11-07 12:53:56'),
('18', '14', '10', '2026-11-07 12:57:47', '2026-11-07 12:57:47'),
('19', '15', '18', '2026-11-07 12:59:21', '2026-11-07 12:59:21'),
('21', '17', '28', '2026-11-07 16:22:12', '2026-11-07 16:22:12'),
('23', '19', '13', '2026-11-07 16:37:01', '2026-11-07 16:37:01');

-- --------------------------------------------------------
-- Table: `bills` (29 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `bills`;
INSERT INTO `bills` (`id`, `bill_number`, `tenant_id`, `room_id`, `bill_type`, `billing_month`, `bill_date`, `due_date`, `amount`, `eb_previous_reading`, `eb_current_reading`, `eb_consumed_units`, `eb_unit_rate`, `eb_amount`, `paid_amount`, `due_amount`, `status`, `notes`, `items`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', 'INV-202611-0001', '1', '2', 'Monthly Rent', 'November 2026', '2026-11-05', '2026-11-10', '5000.00', NULL, NULL, NULL, NULL, NULL, '5000.00', '0.00', 'paid', 'Test rent bill', NULL, '1', '2026-11-05 21:14:00', '2026-11-05 22:40:14', '2026-11-05 22:40:14'),
('3', 'INV-202611-0002', '1', '2', 'Monthly Rent & Utilities', 'November 2026', '2026-11-05', '2026-11-10', '6300.00', '0.00', '100.00', '100.00', '8.00', '800.00', '6300.00', '0.00', 'paid', NULL, '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":800,\"details\":\"100 units @ \\u20b98.00\\/unit (Meter: 0 \\u2794 100)\"}]', '1', '2026-11-05 22:53:16', '2026-11-05 23:03:30', '2026-11-05 23:03:30'),
('4', 'INV-202611-0003', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 22:56:26', '2026-11-05 22:56:26', '2026-11-05 22:56:26'),
('5', 'INV-202611-0004', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 22:58:30', '2026-11-05 22:58:30', '2026-11-05 22:58:30'),
('6', 'INV-202611-0005', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 23:01:56', '2026-11-05 23:01:57', '2026-11-05 23:01:57'),
('7', 'INV-202611-0006', '1', '2', 'Monthly Rent & Utilities', 'November 2026', '2026-11-05', '2026-11-10', '5500.00', '0.00', NULL, '0.00', '8.00', '0.00', '0.00', '5500.00', 'unpaid', NULL, '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":0,\"details\":null}]', '1', '2026-11-05 23:03:59', '2026-11-05 23:11:02', '2026-11-05 23:11:02'),
('8', 'INV-202611-0007', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 23:09:16', '2026-11-05 23:09:16', '2026-11-05 23:09:16'),
('9', 'INV-202611-0008', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 23:09:42', '2026-11-05 23:09:42', '2026-11-05 23:09:42'),
('10', 'INV-202611-0009', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 23:10:09', '2026-11-05 23:10:09', '2026-11-05 23:10:09'),
('11', 'INV-202611-0010', '1', '2', 'Monthly Rent & Utilities', 'November 2026', '2026-11-05', '2026-11-10', '5500.00', '0.00', NULL, '0.00', '8.00', '0.00', '5500.00', '0.00', 'paid', NULL, '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":0,\"details\":null}]', '1', '2026-11-05 23:11:11', '2026-11-07 12:16:38', '2026-11-07 12:16:38'),
('12', 'INV-202611-0011', '1', '2', 'Monthly Rent', 'November 2026', '2026-11-05', '2026-11-06', '5500.00', '0.00', NULL, '0.00', '8.00', '0.00', '0.00', '5500.00', 'overdue', NULL, '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":0,\"details\":null}]', '1', '2026-11-05 23:11:35', '2026-11-07 12:16:34', '2026-11-07 12:16:34'),
('13', 'INV-202611-0012', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 23:25:20', '2026-11-05 23:25:20', '2026-11-05 23:25:20'),
('14', 'INV-202611-0013', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-05', '2026-11-10', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-05 23:36:46', '2026-11-05 23:36:46', '2026-11-05 23:36:46'),
('15', 'INV-202611-0014', '1', '2', 'Monthly Rent & Utilities', 'December 2026', '2026-11-06', '2026-11-11', '7000.00', '0.00', '125.00', '125.00', '8.00', '1000.00', '2000.00', '5000.00', 'partially_paid', 'Automated test invoice for full module verification', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000,\"details\":null},{\"title\":\"Maintenance Charges\",\"type\":\"Maintenance\",\"amount\":300,\"details\":null},{\"title\":\"Other Charges\",\"type\":\"Other\",\"amount\":200,\"details\":null},{\"title\":\"Electricity Charges (EB)\",\"type\":\"Electricity\",\"amount\":1000,\"details\":\"125 units @ 8\"},{\"title\":\"Mess \\/ Food Charges\",\"type\":\"Custom\",\"amount\":500,\"details\":null}]', '1', '2026-11-06 19:21:53', '2026-11-06 19:21:53', '2026-11-06 19:21:53'),
('16', 'INV-202611-0015', '2', '3', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-10', '13500.00', NULL, NULL, NULL, NULL, NULL, '13500.00', '0.00', 'paid', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":13500}]', '1', '2026-11-07 13:13:02', '2026-11-07 16:15:12', NULL),
('17', 'INV-202611-0016', '1', '2', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-11', '10000.00', NULL, NULL, NULL, NULL, NULL, '10000.00', '0.00', 'paid', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]', '1', '2026-11-07 13:13:02', '2026-11-07 16:24:15', NULL),
('18', 'INV-202611-0017', '3', '4', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '5000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '5000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('19', 'INV-202611-0018', '4', '5', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '6000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '6000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('20', 'INV-202611-0019', '5', '6', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '17000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '17000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":17000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('21', 'INV-202611-0020', '14', '10', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '4000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '4000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":4000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('22', 'INV-202611-0021', '6', '11', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '5000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '5000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('23', 'INV-202611-0022', '7', '12', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '4500.00', NULL, NULL, NULL, NULL, NULL, '4500.00', '0.00', 'paid', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":4500}]', '1', '2026-11-07 13:13:02', '2026-11-07 16:13:03', NULL),
('24', 'INV-202611-0023', '15', '18', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '5000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '5000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('25', 'INV-202611-0024', '8', '19', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '6000.00', NULL, NULL, NULL, NULL, NULL, '6000.00', '0.00', 'paid', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]', '1', '2026-11-07 13:13:02', '2026-11-07 16:14:51', NULL),
('26', 'INV-202611-0025', '9', '20', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '6000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '6000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('27', 'INV-202611-0026', '10', '21', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '6000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '6000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('28', 'INV-202611-0027', '13', '26', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '6000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '6000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]', '1', '2026-11-07 13:13:02', '2026-11-14 20:40:36', NULL),
('29', 'INV-202611-0028', '16', '27', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '6000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '6000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":6000}]', '1', '2026-11-07 15:16:54', '2026-11-14 20:40:36', NULL),
('30', 'INV-202611-0029', '17', '28', 'Monthly Rent', 'November 2026', '2026-11-07', '2026-11-12', '5000.00', NULL, NULL, NULL, NULL, NULL, '0.00', '5000.00', 'overdue', 'Automated monthly rent invoice for November 2026', '[{\"title\":\"Room Monthly Rent\",\"type\":\"Rent\",\"amount\":5000}]', '1', '2026-11-07 16:23:21', '2026-11-14 20:40:36', NULL);

-- --------------------------------------------------------
-- Table: `bill_payments` (32 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `bill_payments`;
INSERT INTO `bill_payments` (`id`, `payment_number`, `bill_id`, `tenant_id`, `amount`, `payment_date`, `payment_method`, `transaction_id`, `receipt`, `notes`, `received_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', 'RCP-202611-0001', '1', '1', '3000.00', '2026-11-05', 'UPI / GPay', 'UPI1234567890', NULL, 'Advance partial payment', '1', '2026-11-05 21:14:00', '2026-11-05 22:40:14', '2026-11-05 22:40:14'),
('2', 'RCP-202611-0002', '1', '1', '2000.00', '2026-11-05', 'Cash', NULL, NULL, 'Remaining balance cleared', '1', '2026-11-05 21:14:01', '2026-11-05 22:40:14', '2026-11-05 22:40:14'),
('3', 'RCP-202611-0003', '3', '1', '6300.00', '2026-11-05', 'Cash', NULL, NULL, NULL, '1', '2026-11-05 22:53:43', '2026-11-05 23:03:30', '2026-11-05 23:03:30'),
('4', 'RCP-202611-0004', '4', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 22:56:26', '2026-11-05 22:56:26', '2026-11-05 22:56:26'),
('5', 'RCP-202611-0005', '4', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 22:56:26', '2026-11-05 22:56:26', '2026-11-05 22:56:26'),
('6', 'RCP-202611-0006', '5', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 22:58:30', '2026-11-05 22:58:30', '2026-11-05 22:58:30'),
('7', 'RCP-202611-0007', '5', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 22:58:30', '2026-11-05 22:58:30', '2026-11-05 22:58:30'),
('8', 'RCP-202611-0008', '6', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 23:01:56', '2026-11-05 23:01:57', '2026-11-05 23:01:57'),
('9', 'RCP-202611-0009', '6', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 23:01:57', '2026-11-05 23:01:57', '2026-11-05 23:01:57'),
('10', 'RCP-202611-0010', '8', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 23:09:16', '2026-11-05 23:09:16', '2026-11-05 23:09:16'),
('11', 'RCP-202611-0011', '8', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 23:09:16', '2026-11-05 23:09:16', '2026-11-05 23:09:16'),
('12', 'RCP-202611-0012', '9', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 23:09:42', '2026-11-05 23:09:42', '2026-11-05 23:09:42'),
('13', 'RCP-202611-0013', '9', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 23:09:42', '2026-11-05 23:09:42', '2026-11-05 23:09:42'),
('14', 'RCP-202611-0014', '10', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 23:10:09', '2026-11-05 23:10:09', '2026-11-05 23:10:09'),
('15', 'RCP-202611-0015', '10', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 23:10:09', '2026-11-05 23:10:09', '2026-11-05 23:10:09'),
('16', 'RCP-202611-0016', '11', '1', '5500.00', '2026-11-05', 'Cash', NULL, NULL, NULL, '1', '2026-11-05 23:11:16', '2026-11-07 12:16:38', '2026-11-07 12:16:38'),
('17', 'RCP-202611-0017', '13', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 23:25:20', '2026-11-05 23:25:20', '2026-11-05 23:25:20'),
('18', 'RCP-202611-0018', '13', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 23:25:20', '2026-11-05 23:25:20', '2026-11-05 23:25:20'),
('19', 'RCP-202611-0019', '14', '1', '2000.00', '2026-11-05', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-05 23:36:46', '2026-11-05 23:36:46', '2026-11-05 23:36:46'),
('20', 'RCP-202611-0020', '14', '1', '1000.00', '2026-11-05', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-05 23:36:46', '2026-11-05 23:36:46', '2026-11-05 23:36:46'),
('21', 'RCP-202611-0021', '15', '1', '2000.00', '2026-11-06', 'UPI / GPay', 'UPI-TEST-998877', NULL, 'Initial payment upon invoice creation.', '1', '2026-11-06 19:21:53', '2026-11-06 19:21:53', '2026-11-06 19:21:53'),
('22', 'RCP-202611-0022', '15', '1', '1000.00', '2026-11-06', 'Cash', NULL, NULL, 'Second partial payment test', '1', '2026-11-06 19:21:53', '2026-11-06 19:21:53', '2026-11-06 19:21:53'),
('23', 'RCP-202611-0023', '16', '2', '5000.00', '2026-11-07', 'UPI', 'UPI1234567890', NULL, NULL, '1', '2026-11-07 13:43:38', '2026-11-07 14:14:42', '2026-11-07 14:14:42'),
('24', 'RCP-202611-0024', '19', '4', '3000.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 14:48:28', '2026-11-07 14:48:39', '2026-11-07 14:48:39'),
('25', 'RCP-202611-0025', '23', '7', '4500.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 16:13:03', '2026-11-07 16:13:03', NULL),
('26', 'RCP-202611-0026', '25', '8', '6000.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 16:14:51', '2026-11-07 16:14:51', NULL),
('27', 'RCP-202611-0027', '16', '2', '13500.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 16:15:12', '2026-11-07 16:15:12', NULL),
('28', 'RCP-202611-0028', '17', '1', '5000.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 16:15:24', '2026-11-07 16:15:53', '2026-11-07 16:15:53'),
('29', 'RCP-202611-0029', '17', '1', '10000.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 16:24:15', '2026-11-07 16:24:15', NULL),
('30', 'RCP-202611-0030', '18', '3', '5000.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 22:45:23', '2026-11-07 22:45:37', '2026-11-07 22:45:37'),
('31', 'RCP-202611-0031', '30', '17', '5000.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 22:45:53', '2026-11-07 22:46:32', '2026-11-07 22:46:32'),
('32', 'RCP-202611-0032', '30', '17', '5000.00', '2026-11-07', 'Cash', NULL, NULL, NULL, '1', '2026-11-07 22:54:27', '2026-11-07 22:54:38', '2026-11-07 22:54:38');

-- --------------------------------------------------------
-- Table: `expenses` (8 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `expenses`;
INSERT INTO `expenses` (`id`, `expense_type`, `room_id`, `category`, `title`, `amount`, `paid_amount`, `payment_status`, `expense_date`, `expense_month`, `payment_method`, `paid_to`, `receipt`, `notes`, `items`, `status`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', 'personal', NULL, 'Groceries & Food', 'Test Monthly Supplies', '1550.00', '0.00', 'unpaid', '2026-11-07', 'November 2026', 'UPI', NULL, NULL, 'Total grocery test', '[{\"title\":\"Vegetables & Fruits\",\"amount\":450,\"notes\":\"Weekly market\"},{\"title\":\"Cooking Oil & Spices\",\"amount\":600,\"notes\":\"Store purchase\"},{\"title\":\"Dairy & Milk\",\"amount\":500,\"notes\":\"Morning delivery\"}]', '1', NULL, '2026-11-07 15:12:36', '2026-11-07 15:12:36', '2026-11-07 15:12:36'),
('2', 'personal', NULL, 'Personal', 'Weekly Groceries Test', '900.00', '0.00', 'unpaid', '2026-11-07', 'November 2026', NULL, 'Reliance Smart Point', NULL, 'Weekend grocery shopping', '[{\"title\":\"Basmati Rice\",\"quantity\":2,\"per_rate\":120,\"amount\":240,\"notes\":\"5kg bags\"},{\"title\":\"Sunflower Oil\",\"quantity\":3,\"per_rate\":140,\"amount\":420,\"notes\":\"1L pouches\"},{\"title\":\"Toor Dal\",\"quantity\":1.5,\"per_rate\":160,\"amount\":240,\"notes\":\"Loose dal\"}]', '1', NULL, '2026-11-07 15:33:27', '2026-11-07 15:35:04', '2026-11-07 15:35:04'),
('3', 'personal', NULL, 'Personal', 'Weekly Groceries Test', '900.00', '0.00', 'unpaid', '2026-11-07', 'November 2026', NULL, 'Reliance Smart Point', NULL, 'Weekend grocery shopping', '[{\"title\":\"Basmati Rice\",\"quantity\":2,\"per_rate\":120,\"amount\":240,\"notes\":\"5kg bags\"},{\"title\":\"Sunflower Oil\",\"quantity\":3,\"per_rate\":140,\"amount\":420,\"notes\":\"1L pouches\"},{\"title\":\"Toor Dal\",\"quantity\":1.5,\"per_rate\":160,\"amount\":240,\"notes\":\"Loose dal\"}]', '1', NULL, '2026-11-07 15:33:40', '2026-11-07 15:33:40', '2026-11-07 15:33:40'),
('4', 'personal', NULL, 'Personal', 'Updated Personal Expense', '1000.00', '0.00', 'unpaid', '2026-11-08', 'November 2026', NULL, NULL, NULL, NULL, '[{\"title\":\"A4 Paper Bundle\",\"quantity\":4,\"per_rate\":250,\"amount\":1000,\"notes\":\"75 GSM\"}]', '1', NULL, '2026-11-07 15:33:54', '2026-11-07 15:33:54', '2026-11-07 15:33:54'),
('5', 'personal', NULL, 'Personal', 'GROCERY EXPENSES', '15869.00', '0.00', 'unpaid', '2026-11-07', 'November 2026', NULL, NULL, NULL, NULL, '[{\"title\":\"Rice\",\"quantity\":1,\"per_rate\":2150,\"amount\":2150,\"notes\":\"\",\"is_paid\":false,\"paid_at\":null},{\"title\":\"gold vinner\",\"quantity\":5,\"per_rate\":200,\"amount\":1000,\"notes\":\"\"},{\"title\":\"Milk\",\"quantity\":1,\"per_rate\":3000,\"amount\":3000,\"notes\":\"\"},{\"title\":\"Egg\",\"quantity\":217,\"per_rate\":7,\"amount\":1519,\"notes\":\"\"},{\"title\":\"Vegitables\",\"quantity\":1,\"per_rate\":200,\"amount\":200,\"notes\":\"\"},{\"title\":\"gas\",\"quantity\":1,\"per_rate\":1000,\"amount\":1000,\"notes\":\"\"},{\"title\":\"other items\",\"quantity\":1,\"per_rate\":7000,\"amount\":7000,\"notes\":\"\"}]', '0', '1', '2026-11-07 15:41:37', '2026-11-16 13:54:25', NULL),
('6', 'personal', NULL, 'Personal', 'Loans and creditors', '40870.00', '0.00', 'unpaid', '2026-11-07', 'November 2026', NULL, NULL, NULL, NULL, '[{\"title\":\"narayanan cable\",\"quantity\":1,\"per_rate\":5000,\"amount\":5000,\"notes\":\"\"},{\"title\":\"sunderajan nagalkeni\",\"quantity\":1,\"per_rate\":3000,\"amount\":3000,\"notes\":\"\"},{\"title\":\"tambaram dharmalingam\",\"quantity\":1,\"per_rate\":2000,\"amount\":2000,\"notes\":\"\"},{\"title\":\"Sathish auto\",\"quantity\":1,\"per_rate\":5000,\"amount\":5000,\"notes\":\"\"},{\"title\":\"Gowri\",\"quantity\":1,\"per_rate\":5600,\"amount\":5600,\"notes\":\"\"},{\"title\":\"samsung tv  Emi\",\"quantity\":1,\"per_rate\":3300,\"amount\":3300,\"notes\":\"\"},{\"title\":\"Ragavan Loan\",\"quantity\":1,\"per_rate\":7400,\"amount\":7400,\"notes\":\"\"},{\"title\":\"pragathi loan\",\"quantity\":1,\"per_rate\":2720,\"amount\":2720,\"notes\":\"\"},{\"title\":\"gramam vidial loan\",\"quantity\":5,\"per_rate\":890,\"amount\":4450,\"notes\":\"\"},{\"title\":\"grow win loan\",\"quantity\":1,\"per_rate\":2400,\"amount\":2400,\"notes\":\"\"}]', '1', '1', '2026-11-07 16:00:15', '2026-11-07 16:00:15', NULL),
('7', 'personal', NULL, 'Personal', 'Recharge and phone bills', '13350.00', '0.00', 'unpaid', '2026-11-06', 'November 2026', NULL, NULL, NULL, NULL, '[{\"title\":\"jagan\",\"quantity\":1,\"per_rate\":350,\"amount\":350,\"notes\":\"\"},{\"title\":\"ragavan\",\"quantity\":1,\"per_rate\":300,\"amount\":300,\"notes\":\"\"},{\"title\":\"saritha\",\"quantity\":1,\"per_rate\":300,\"amount\":300,\"notes\":\"\"},{\"title\":\"act fibernet\",\"quantity\":1,\"per_rate\":2000,\"amount\":2000,\"notes\":\"\"},{\"title\":\"news paper\",\"quantity\":1,\"per_rate\":400,\"amount\":400,\"notes\":\"\"},{\"title\":\"Eb\",\"quantity\":1,\"per_rate\":10000,\"amount\":10000,\"notes\":\"\"}]', '1', '1', '2026-11-07 16:02:18', '2026-11-07 16:05:42', NULL),
('8', 'personal', NULL, 'Personal', 'Others Expenses', '14710.00', '0.00', 'unpaid', '2026-11-05', 'November 2026', NULL, NULL, NULL, NULL, '[{\"title\":\"Vignesh marriage Expenses\",\"quantity\":1,\"per_rate\":8000,\"amount\":8000,\"notes\":\"\"},{\"title\":\"Diwali sweets\",\"quantity\":1,\"per_rate\":640,\"amount\":640,\"notes\":\"\"},{\"title\":\"flower\",\"quantity\":1,\"per_rate\":200,\"amount\":200,\"notes\":\"\"},{\"title\":\"pooja items\",\"quantity\":1,\"per_rate\":120,\"amount\":120,\"notes\":\"\"},{\"title\":\"Advance return room no 2\",\"quantity\":1,\"per_rate\":5750,\"amount\":5750,\"notes\":\"\"}]', '1', '1', '2026-11-07 16:04:32', '2026-11-07 18:07:45', NULL);

-- --------------------------------------------------------
-- Table: `loans` (7 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `loans`;
INSERT INTO `loans` (`id`, `loan_title`, `loan_type`, `repayment_frequency`, `lender_name`, `account_number`, `principal_amount`, `interest_rate`, `tenure_months`, `already_paid_units`, `already_paid_amount`, `emi_amount`, `emi_day`, `start_date`, `end_date`, `total_repaid`, `outstanding_balance`, `status`, `bank_account_debited`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', 'Test Inverter EMI', 'appliance', 'monthly', 'Bajaj Finserv', NULL, '45000.00', '0.00', '6', '0', '0.00', '7500.00', '10', '2026-11-01', '2027-05-01', '7500.00', '37500.00', 'active', 'SBI Current A/C', 'Testing EMI', '1', '2026-11-08 16:40:04', '2026-11-08 16:40:04', '2026-11-08 16:40:04'),
('2', 'CCTV Security Camera Loan (Updated v2)', 'appliance', 'monthly', 'Bajaj Finserv Commercial', 'LAN-8839201', '65000.00', '11.50', '12', '0', '0.00', '5750.00', '10', '2026-11-01', '2027-11-01', '0.00', '69000.00', 'active', 'SBI Current A/C', 'Updated via POST /update', '1', '2026-11-08 17:26:42', '2026-11-08 17:26:43', '2026-11-08 17:26:43'),
('3', 'Weekly Business Loan', 'business', 'weekly', 'Weekly Test Financier', NULL, '50000.00', '12.00', '20', '5', '14125.00', '2825.00', '5', '2026-01-01', NULL, '14125.00', '42375.00', 'active', NULL, NULL, NULL, '2026-11-08 22:22:45', '2026-11-08 22:22:45', NULL),
('4', 'Weekly Business Loan', 'business', 'weekly', 'Weekly Test Financier', NULL, '50000.00', '12.00', '20', '5', '14125.00', '2825.00', '5', '2026-01-01', NULL, '14125.00', '42375.00', 'active', NULL, NULL, NULL, '2026-11-08 22:23:03', '2026-11-08 22:23:03', NULL),
('5', 'Weekly Business Loan', 'business', 'weekly', 'Weekly Test Financier', NULL, '50000.00', '12.00', '20', '5', '14125.00', '2825.00', '5', '2026-01-01', NULL, '14125.00', '42375.00', 'active', NULL, NULL, NULL, '2026-11-08 22:23:18', '2026-11-08 22:23:18', '2026-11-08 22:23:18'),
('6', 'Weekly Business Loan', 'business', 'weekly', 'Weekly Test Financier', NULL, '50000.00', '12.00', '20', '5', '14125.00', '2825.00', '5', '2026-01-01', NULL, '14125.00', '42375.00', 'active', NULL, NULL, NULL, '2026-11-08 22:23:30', '2026-11-08 22:23:30', '2026-11-08 22:23:30'),
('7', 'Fully Paid Old Loan', 'hand_loan', 'monthly', 'Old Financier', NULL, '30000.00', '0.00', '6', '6', '30000.00', '5000.00', '5', '2025-01-01', NULL, '30000.00', '0.00', 'closed', NULL, NULL, NULL, '2026-11-08 22:23:30', '2026-11-08 22:23:30', '2026-11-08 22:23:30');

-- --------------------------------------------------------
-- Table: `loan_emis` (2 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `loan_emis`;
INSERT INTO `loan_emis` (`id`, `loan_id`, `installment_number`, `payment_date`, `amount`, `principal_component`, `interest_component`, `payment_method`, `transaction_id`, `status`, `receipt`, `notes`, `recorded_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', '1', '1', '2026-11-08', '7500.00', '0.00', '0.00', 'UPI', 'TEST-UPI-123', 'paid', NULL, 'First EMI paid', '1', '2026-11-08 16:40:04', '2026-11-08 16:40:04', '2026-11-08 16:40:04'),
('2', '2', '1', '2026-11-08', '5750.00', '0.00', '0.00', 'UPI', 'UPI-REF-992810', 'paid', NULL, 'First installment cleared', '1', '2026-11-08 17:26:43', '2026-11-08 17:26:43', '2026-11-08 17:26:43');

-- --------------------------------------------------------
-- Table: `salaries` (1 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `salaries`;
INSERT INTO `salaries` (`id`, `salary_month`, `salary_title`, `recipient_name`, `user_id`, `base_amount`, `bonus_incentive`, `deductions`, `net_salary`, `credit_date`, `payment_method`, `credited_to_bank`, `transaction_reference`, `status`, `payslip_document`, `notes`, `created_by`, `created_at`, `updated_at`, `deleted_at`) VALUES
('1', '2026-11', 'Monthly Executive Remuneration (Revised)', 'Jagan', '1', '65000.00', '5000.00', '2000.00', '68000.00', '2026-11-05', 'Bank Transfer', 'SBI Current A/C ***2248', 'SBI-UTR-9081234', 'credited', NULL, 'Revised salary', '1', '2026-11-08 17:58:32', '2026-11-08 17:58:32', '2026-11-08 17:58:32');

-- --------------------------------------------------------
-- Table: `journal_entries` (0 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `journal_entries`;

-- --------------------------------------------------------
-- Table: `settings` (58 rows)
-- --------------------------------------------------------
TRUNCATE TABLE `settings`;
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
('1', 'residence_name', 'SRM Residence', NULL, '2026-11-07 13:23:18', '2026-11-07 14:10:45'),
('2', 'contact_phone', '+91 7010663823', NULL, '2026-11-07 13:23:18', '2026-11-07 13:25:45'),
('3', 'contact_email', 'ronaldojagan46@gmail.com', NULL, '2026-11-07 13:23:18', '2026-11-07 13:25:45'),
('4', 'razorpay_enabled', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('5', 'razorpay_mode', 'test', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('6', 'razorpay_key_id', 'rzp_test_AbCdEf123456', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('7', 'razorpay_key_secret', 'vicky1998m', NULL, '2026-11-07 13:23:18', '2026-11-07 14:26:39'),
('8', 'upi_enabled', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('9', 'upi_id', 'ronaldojagan@okhdfcbank', NULL, '2026-11-07 13:23:18', '2026-11-07 13:35:02'),
('10', 'upi_merchant_name', 'SRM RESIDENCE', NULL, '2026-11-07 13:23:18', '2026-11-07 13:35:02'),
('11', 'messaging_enabled', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('12', 'messaging_provider', 'whatsapp_cloud', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('13', 'notify_bill_generated', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 22:42:02'),
('14', 'notify_payment_received', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 22:42:02'),
('15', 'razorpay_auto_reconcile', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 13:35:02'),
('16', 'notify_due_reminder', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 22:42:02'),
('17', 'notify_overdue_alert', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 22:42:03'),
('18', 'default_eb_rate', '12.50', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('19', 'default_maintenance', '600.00', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('20', 'bill_generation_day', '1', NULL, '2026-11-07 13:23:18', '2026-11-07 13:23:18'),
('21', 'bill_due_day', '10', NULL, '2026-11-07 13:23:18', '2026-11-07 22:43:19'),
('22', 'late_fee_amount', '0', NULL, '2026-11-07 13:23:18', '2026-11-07 22:43:19'),
('23', 'residence_tagline', 'Executive PG & Co-Living Residence', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('24', 'contact_person', 'Property Manager', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('25', 'contact_designation', 'Resident Warden', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('26', 'contact_whatsapp', '+91 7010663823', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('27', 'emergency_phone', '+91 9025760270', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('28', 'contact_address', 'No.42 Ambethkar street, North sanjai gandhi nagar, Zamin Pallavaram Chennai 600043', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('29', 'gate_closing_time', '22:30', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('30', 'office_hours', '09:00 AM - 08:00 PM', NULL, '2026-11-07 13:25:45', '2026-11-07 13:25:45'),
('31', 'admin_alert_email', 'ronaldojagan46@gmail.com', NULL, '2026-11-07 13:27:05', '2026-11-07 13:27:05'),
('32', 'reminder_days_before_due', '3', NULL, '2026-11-07 13:27:05', '2026-11-07 13:27:05'),
('33', 'bill_sms_template', 'Dear {resident_name}, your room rent bill for {month} of Rs.{amount} has been generated. Due date: {due_date}. - SRM Residence', NULL, '2026-11-07 13:27:05', '2026-11-07 13:27:05'),
('34', 'payment_receipt_template', 'Dear {resident_name}, we received your payment of Rs.{amount} on {payment_date}. Receipt #{receipt_no}. Thank you! - SRM Residence', NULL, '2026-11-07 13:27:05', '2026-11-07 13:27:05'),
('35', 'upi_qr_code', 'settings/upi_qr_1794038702.jpeg', 'UPI QR Code Image', '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('36', 'razorpay_webhook_secret', '', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('37', 'bank_name', 'SBI', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('38', 'bank_account_name', 'SRM RESIDENCE', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('39', 'bank_account_number', '33988707975', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('40', 'bank_ifsc_code', 'sbin0002248', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('41', 'bank_branch', 'Pallavaram Branch', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('42', 'messaging_sender_id', 'SRMRES', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('43', 'messaging_api_key', '', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('44', 'whatsapp_phone_number_id', '', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('45', 'mail_mailer', 'smtp', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('46', 'mail_host', 'smtp.gmail.com', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('47', 'mail_port', '587', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('48', 'mail_username', 'ronaldojagan46@gmail.com', NULL, '2026-11-07 13:35:02', '2026-11-07 14:26:39'),
('49', 'mail_password', 'muta abxo vtxb gzhl', NULL, '2026-11-07 13:35:02', '2026-11-07 14:26:39'),
('50', 'mail_encryption', 'tls', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02');
INSERT INTO `settings` (`id`, `key`, `value`, `description`, `created_at`, `updated_at`) VALUES
('51', 'mail_from_address', 'ronaldojagan46@gmail.com', NULL, '2026-11-07 13:35:02', '2026-11-07 14:26:39'),
('52', 'mail_from_name', 'SRM Residence', NULL, '2026-11-07 13:35:02', '2026-11-07 13:35:02'),
('53', 'default_other_charges', '0.00', NULL, '2026-11-07 22:43:19', '2026-11-07 22:43:19'),
('54', 'default_security_deposit', '10000.00', NULL, '2026-11-07 22:43:19', '2026-11-07 22:43:19'),
('55', 'default_rent_type', 'custom', NULL, '2026-11-07 22:43:19', '2026-11-07 22:43:19'),
('56', 'grace_period_days', '3', NULL, '2026-11-07 22:43:19', '2026-11-07 22:43:19'),
('57', 'late_fee_type', 'fixed', NULL, '2026-11-07 22:43:19', '2026-11-07 22:43:19'),
('58', 'currency_symbol', '₹', NULL, '2026-11-07 22:43:19', '2026-11-07 22:43:19');

SET FOREIGN_KEY_CHECKS = 1;
-- Dump completed. Total rows exported: 201
