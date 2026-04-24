-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 24, 2026 at 08:23 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `rentdress`
--

-- --------------------------------------------------------

--
-- Table structure for table `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `pickup_date` date NOT NULL,
  `return_date` date NOT NULL,
  `total_price` decimal(10,2) NOT NULL,
  `deposit_amount` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','ready','rented','returned','completed','cancelled') DEFAULT 'pending',
  `late_fee` decimal(10,2) DEFAULT 0.00,
  `damage_fee` decimal(10,2) DEFAULT 0.00,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `product_id`, `pickup_date`, `return_date`, `total_price`, `deposit_amount`, `status`, `late_fee`, `damage_fee`, `notes`, `created_at`, `updated_at`) VALUES
(1, 9, 9, '2026-04-29', '2026-04-30', 3000.00, 2500.00, 'completed', 0.00, 0.00, '', '2026-04-24 03:49:59', '2026-04-24 04:06:14'),
(2, 9, 2, '2026-04-29', '2026-04-30', 7000.00, 7000.00, 'confirmed', 0.00, 0.00, '', '2026-04-24 03:50:56', '2026-04-24 03:59:57');

-- --------------------------------------------------------

--
-- Table structure for table `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_type` enum('deposit','rental','refund','late_fee','damage_fee') NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `payment_status` enum('pending','completed','failed','refunded') DEFAULT 'pending',
  `payment_date` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `products`
--

CREATE TABLE `products` (
  `id` int(11) NOT NULL,
  `name` varchar(200) NOT NULL,
  `description` text DEFAULT NULL,
  `category` enum('evening','thai','suit','casual') NOT NULL,
  `size` enum('S','M','L','XL','XXL') NOT NULL,
  `color` varchar(50) DEFAULT NULL,
  `rental_price` decimal(10,2) NOT NULL,
  `deposit_price` decimal(10,2) NOT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `status` enum('available','rented','maintenance') DEFAULT 'available',
  `buffer_days` int(11) DEFAULT 2,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `products`
--

INSERT INTO `products` (`id`, `name`, `description`, `category`, `size`, `color`, `rental_price`, `deposit_price`, `image_url`, `status`, `buffer_days`, `created_at`) VALUES
(1, 'ชุดราตรียาวสีทอง', 'ชุดราตรียาวผ้าไหมเกรดพรีเมียม ปักเลื่อมทอง เหมาะสำหรับงานกาล่า', 'evening', 'M', 'ทอง', 2500.00, 5000.00, '/images/dress1.jpg', 'available', 2, '2026-03-17 13:51:32'),
(2, 'ชุดไทยจักรีสีชมพู', 'ชุดไทยจักรีผ้าไหมแท้ ทอลายดอกพิกุล พร้อมเครื่องประดับครบชุด', 'thai', 'S', 'ชมพู', 3500.00, 7000.00, '/images/dress2.jpg', 'available', 2, '2026-03-17 13:51:32'),
(3, 'สูทผู้ชายสีดำ', 'สูทผู้ชายตัดเย็บพิเศษ ผ้าวูลเกรด A พร้อมเสื้อเชิ้ตและเนคไท', 'suit', 'L', 'ดำ', 2000.00, 4000.00, '/images/suit1.jpg', 'available', 2, '2026-03-17 13:51:32'),
(4, 'ชุดค็อกเทลสีแดง', 'ชุดค็อกเทลสั้นสีแดงเข้ม ดีไซน์เรียบหรู เหมาะสำหรับปาร์ตี้', 'casual', 'M', 'แดง', 1500.00, 3000.00, '/images/dress3.jpg', 'available', 2, '2026-03-17 13:51:32'),
(5, 'ชุดราตรียาวสีน้ำเงิน', 'ชุดราตรียาวผ้าชีฟอง สีน้ำเงินเข้ม ประดับคริสตัล', 'evening', 'L', 'น้ำเงิน', 2800.00, 5500.00, '/images/dress4.jpg', 'available', 2, '2026-03-17 13:51:32'),
(6, 'ชุดไทยประยุกต์สีม่วง', 'ชุดไทยประยุกต์ดีไซน์ทันสมัย ผ้าไหมผสมผ้าลูกไม้', 'thai', 'M', 'ม่วง', 3000.00, 6000.00, '/images/dress5.jpg', 'available', 2, '2026-03-17 13:51:32'),
(7, 'สูทผู้ชายสีกรมท่า', 'สูท Slim Fit สีกรมท่า ผ้านำเข้าจากอิตาลี', 'suit', 'M', 'กรมท่า', 2500.00, 5000.00, '/images/suit2.jpg', 'available', 2, '2026-03-17 13:51:32'),
(8, 'ชุดเดรสลำลองสีขาว', 'เดรสสั้นสีขาว ผ้าลินินเกรดพรีเมียม สไตล์มินิมอล', 'casual', 'S', 'ขาว', 1200.00, 2500.00, '/images/dress6.jpg', 'available', 2, '2026-03-17 13:51:32'),
(9, 'ชุดราตรี สีเขียว', 'ชุดราตรี สีเขียว', 'evening', 'M', 'เขียว', 1500.00, 2500.00, '/images/dress7.jpg', 'available', 2, '2026-04-10 17:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `product_images`
--

CREATE TABLE `product_images` (
  `id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `image_url` varchar(500) NOT NULL,
  `is_primary` tinyint(1) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `product_images`
--

INSERT INTO `product_images` (`id`, `product_id`, `image_url`, `is_primary`, `sort_order`, `created_at`) VALUES
(10, 9, '/uploads/products/dress_69d931de8c11e_1775841758.jpg', 1, 0, '2026-04-10 17:22:38');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `product_id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` >= 1 and `rating` <= 5),
  `comment` text DEFAULT NULL,
  `image_url` varchar(500) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `reviews`
--

INSERT INTO `reviews` (`id`, `user_id`, `product_id`, `booking_id`, `rating`, `comment`, `image_url`, `created_at`) VALUES
(1, 9, 9, 1, 5, 'เริ่ดด', '', '2026-04-24 04:55:36');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('customer','admin') DEFAULT 'customer',
  `session_token` varchar(64) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `password`, `phone`, `address`, `role`, `session_token`, `created_at`) VALUES
(1, 'Admin', 'admin@rentdress.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0812345678', NULL, 'admin', 'bcfd399eb07e5e019f83f87eed5fec9f', '2026-03-17 13:51:32'),
(2, 'สมชาย ใจดี', 'somchai@email.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', '0898765432', '123 ถ.สุขุมวิท กรุงเทพฯ 10110', 'customer', 'aee915cbb1b8510bd405282da3d9789d', '2026-03-17 13:51:32'),
(4, 'User1', 'unsr1@gmail.com', '$2y$10$eCkC9ubOrgSa5oKBBLLQfOz/7wDYQSR/0b.1NthasarJ1DOAmkuJ6', '0389555642', 'Rayong', 'customer', NULL, '2026-04-10 17:11:23'),
(5, 'Nitipon Pakdeewong', 'nick.nitipon@gmail.com', 'admin', '191', 'Rayong hi', 'admin', NULL, '2026-04-10 17:19:25'),
(6, 'nitipon p', 'nitipon@spu.com', '$2y$10$YX7L0eDa5BlQ1FfLOQa9r.HfgYTOYI9qNtKop2zKRa3OQUwbGlwLi', '1191', 'rayong', 'customer', '14ec92f2fba5cad54be3d49177d7e4fa376fd6bb21a71ece05fe5080c602e9a8', '2026-04-24 01:55:50'),
(7, 'nitipon pa', 'nitiponpa@spu.com', '$2y$10$hcI6pxyDB3rNQKFXkmU6wOlNbs4XnvPT6GWivjg8GDjPJohd7wAM6', '1191', 'rayong', 'customer', 'afecc32603c8f6581fd0c007e3cc7240cba3947cbb9bd7d1b0df84b3427519cf', '2026-04-24 02:17:42'),
(8, 'nick', 'nick@spu.com', '$2y$10$N3BPYL8B6ZqHQpvry1cQ5OP6rBTIycJ6FVx8.1CkjFG0dn13OmGHa', '11111', 'rayongg', 'customer', '688ce69362c5fb8194110fc82b00bf78', '2026-04-24 02:57:14'),
(9, 'test', 'test@email.com', '$2y$10$hST/qn/T3VUZUOmA95UVcunME7Pq/rCWFoaszeKTZ.HM7ZS3Kifyu', '11911', 'rayong', 'customer', '42c6f1dc5060f08a11532da478e9d073', '2026-04-24 03:45:46');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_bookings_user` (`user_id`),
  ADD KEY `idx_bookings_product` (`product_id`),
  ADD KEY `idx_bookings_dates` (`pickup_date`,`return_date`),
  ADD KEY `idx_bookings_status` (`status`);

--
-- Indexes for table `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`);

--
-- Indexes for table `products`
--
ALTER TABLE `products`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_products_category` (`category`),
  ADD KEY `idx_products_status` (`status`);

--
-- Indexes for table `product_images`
--
ALTER TABLE `product_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_product_images_product` (`product_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `idx_reviews_product` (`product_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

--
-- AUTO_INCREMENT for table `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `products`
--
ALTER TABLE `products`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `product_images`
--
ALTER TABLE `product_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `product_images`
--
ALTER TABLE `product_images`
  ADD CONSTRAINT `product_images_ibfk_1` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`product_id`) REFERENCES `products` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_3` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
