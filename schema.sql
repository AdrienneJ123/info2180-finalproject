

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";

--
-- Table structure for table `contacts`
--

CREATE TABLE `contacts` (
  `id` int(11) NOT NULL,
  `title` varchar(10) DEFAULT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `telephone` varchar(20) DEFAULT NULL,
  `company` varchar(100) DEFAULT NULL,
  `type` enum('Sales Lead','Support') NOT NULL,
  `assigned_to` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contacts`
--

INSERT INTO `contacts` (`id`, `title`, `firstname`, `lastname`, `email`, `telephone`, `company`, `type`, `assigned_to`, `created_by`, `created_at`, `updated_at`) VALUES
(1, 'Mr', 'John', 'Doe', 'johndoe@gmail.com', '123456789', 'UWI', 'Sales Lead', 2, 2, '2025-12-23 05:22:14', '2025-12-23 14:35:38'),
(2, 'Dr', 'John', 'Brown', 'johnbrown@gmail.com', '123445778', 'UWI', 'Support', 2, 2, '2025-12-23 14:49:55', '2025-12-23 14:49:55'),
(3, 'Mr', 'John', 'Doe', 'johndoe@gmail.com', '123456789', 'UWI', 'Sales Lead', 2, 2, '2025-12-23 15:34:30', '2025-12-23 15:34:30'),
(4, 'Mr', 'John', 'Doe', 'johndoe1@gmail.com', '123456789', 'UWi', 'Support', 3, 2, '2025-12-23 16:21:05', '2025-12-23 16:53:06');

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `contact_id` int(11) NOT NULL,
  `comment` text NOT NULL,
  `created_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `contact_id`, `comment`, `created_by`, `created_at`) VALUES
(1, 1, 'Test', 2, '2025-12-23 06:06:34'),
(2, 1, 'Hello', 2, '2025-12-23 14:27:39'),
(3, 1, '.', 2, '2025-12-23 14:35:04'),
(4, 1, '.', 2, '2025-12-23 14:35:38'),
(5, 4, 'Testing', 2, '2025-12-23 16:53:06');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `firstname` varchar(50) NOT NULL,
  `lastname` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('Admin','Member') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `firstname`, `lastname`, `password`, `email`, `role`, `created_at`) VALUES
(2, 'Admin', 'User', '$2y$10$bqz8Lq/jlWj3//dWRjHbu.4GLJj7vBVxJ3x2PtjDhzONP1MaZIOBa', 'admin@project2.com', 'Admin', '2025-12-23 04:04:31'),
(3, 'Dora', 'Smith', '$2y$10$cW9AKjZIgIc2GKmN0FNd8ej97/j4WiW9XjBOddk4iMDXL1iRBmuqm', 'dora1@gmail.com', 'Member', '2025-12-23 16:20:09');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contacts`
--
ALTER TABLE `contacts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

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
-- AUTO_INCREMENT for table `contacts`
--
ALTER TABLE `contacts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;
COMMIT;

