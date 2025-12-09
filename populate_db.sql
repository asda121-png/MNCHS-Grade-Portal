-- MNCHS Grade Portal - Comprehensive Data Population Script
-- Junior High School Program (Grades 7-10)
-- Each grade level has 10 sections (A-J)
-- 10 Teachers, 8 Subjects per Grade Level

USE mnchs_grade_portal;

-- =====================================================
-- 1. ACADEMIC YEARS
-- =====================================================
INSERT IGNORE INTO academic_years (year, start_date, end_date, is_active) VALUES
('2025-2026', '2025-06-02', '2026-03-27', TRUE),
('2024-2025', '2024-06-03', '2025-03-28', FALSE);

-- =====================================================
-- 2. USERS - ADMIN (1)
-- =====================================================
INSERT INTO users (username, email, password, role, first_name, last_name, is_active) VALUES
('admin_user', 'admin@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'admin', 'Admin', 'User', TRUE);

-- =====================================================
-- 3. USERS - TEACHERS (10) with credentials
-- =====================================================
INSERT INTO users (username, email, password, role, first_name, last_name, is_active) VALUES
('teacher_smith', 'smith@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'John', 'Smith', TRUE),
('teacher_alfayed', 'alfayed@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Dr. Ahmed', 'Al-Fayed', TRUE),
('teacher_johnson', 'johnson@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Maria', 'Johnson', TRUE),
('teacher_reyes', 'reyes@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Bb.', 'Reyes', TRUE),
('teacher_cruz', 'cruz@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'G.', 'Cruz', TRUE),
('teacher_santos', 'santos@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Gng.', 'Santos', TRUE),
('teacher_davis', 'davis@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Robert', 'Davis', TRUE),
('teacher_garcia', 'garcia@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Ms.', 'Garcia', TRUE),
('teacher_lopez', 'lopez@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Carlos', 'Lopez', TRUE),
('teacher_villanueva', 'villanueva@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'teacher', 'Ana', 'Villanueva', TRUE);

-- =====================================================
-- 4. TEACHERS PROFILE
-- =====================================================
INSERT INTO teachers (user_id, teacher_id, department, specialization, hire_date) VALUES
(2, 'TCH001', 'Mathematics', 'Algebra & Calculus', '2020-06-01'),
(3, 'TCH002', 'Science', 'Chemistry & Biology', '2019-06-01'),
(4, 'TCH003', 'English', 'Literature & Composition', '2021-06-01'),
(5, 'TCH004', 'Languages', 'Filipino & Social Studies', '2020-06-01'),
(6, 'TCH005', 'Physical Education', 'Sports & Wellness', '2022-06-01'),
(7, 'TCH006', 'Mathematics', 'Statistics & Geometry', '2019-06-01'),
(8, 'TCH007', 'Science', 'Physics & Environmental', '2021-06-01'),
(9, 'TCH008', 'Languages', 'English & Oral Communication', '2020-06-01'),
(10, 'TCH009', 'Social Studies', 'History & Civics', '2022-06-01'),
(11, 'TCH010', 'Technology', 'Computer & ICT', '2021-06-01');

-- =====================================================
-- 5. USERS - STUDENTS (40 students: 10 per grade 7-10)
-- =====================================================
INSERT INTO users (username, email, password, role, first_name, last_name, is_active) VALUES
('student001', 'student001@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Liam', 'Anderson', TRUE),
('student002', 'student002@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Emma', 'Wilson', TRUE),
('student003', 'student003@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Noah', 'Martinez', TRUE),
('student004', 'student004@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Olivia', 'Garcia', TRUE),
('student005', 'student005@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Ethan', 'Rodriguez', TRUE),
('student006', 'student006@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Sophia', 'Lopez', TRUE),
('student007', 'student007@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Mason', 'Hernandez', TRUE),
('student008', 'student008@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Ava', 'Perez', TRUE),
('student009', 'student009@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Lucas', 'Torres', TRUE),
('student010', 'student010@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Isabella', 'Rivera', TRUE),
('student011', 'student011@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Benjamin', 'Gomez', TRUE),
('student012', 'student012@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Mia', 'Sanchez', TRUE),
('student013', 'student013@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Henry', 'Morris', TRUE),
('student014', 'student014@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Harper', 'Rogers', TRUE),
('student015', 'student015@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Alexander', 'Morgan', TRUE),
('student016', 'student016@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Evelyn', 'Peterson', TRUE),
('student017', 'student017@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Michael', 'Gray', TRUE),
('student018', 'student018@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Charlotte', 'Ramirez', TRUE),
('student019', 'student019@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Daniel', 'James', TRUE),
('student020', 'student020@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Amelia', 'Watson', TRUE),
('student021', 'student021@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'William', 'Brooks', TRUE),
('student022', 'student022@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Ella', 'Kelly', TRUE),
('student023', 'student023@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'James', 'Sanders', TRUE),
('student024', 'student024@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Abigail', 'Price', TRUE),
('student025', 'student025@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Benjamin', 'Bennett', TRUE),
('student026', 'student026@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Scarlett', 'Wood', TRUE),
('student027', 'student027@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Lucas', 'Ross', TRUE),
('student028', 'student028@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Grace', 'Henderson', TRUE),
('student029', 'student029@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Oliver', 'Coleman', TRUE),
('student030', 'student030@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Chloe', 'Jenkins', TRUE),
('student031', 'student031@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Aiden', 'Perry', TRUE),
('student032', 'student032@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Zoe', 'Powell', TRUE),
('student033', 'student033@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Jackson', 'Long', TRUE),
('student034', 'student034@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Lily', 'Patterson', TRUE),
('student035', 'student035@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Sebastian', 'Hughes', TRUE),
('student036', 'student036@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Luna', 'Flores', TRUE),
('student037', 'student037@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Jacob', 'Washington', TRUE),
('student038', 'student038@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Nora', 'Butler', TRUE),
('student039', 'student039@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Ryan', 'Simmons', TRUE),
('student040', 'student040@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'student', 'Samantha', 'Bryant', TRUE);

-- =====================================================
-- 6. STUDENTS PROFILE (Grades 7-10)
-- =====================================================
INSERT INTO students (user_id, student_id, grade_level, section, guardian_contact, address, date_of_birth, enrollment_date) VALUES
(12, 'STU001', 7, 'A', '0912345678', '123 Elm Street, Manila', '2011-03-15', '2025-06-02'),
(13, 'STU002', 7, 'B', '0912345679', '124 Oak Avenue, Quezon City', '2011-05-22', '2025-06-02'),
(14, 'STU003', 7, 'C', '0912345680', '125 Maple Drive, Manila', '2011-07-10', '2025-06-02'),
(15, 'STU004', 7, 'D', '0912345681', '126 Cedar Lane, Pasig', '2011-09-03', '2025-06-02'),
(16, 'STU005', 7, 'E', '0912345682', '127 Birch Road, Makati', '2011-11-17', '2025-06-02'),
(17, 'STU006', 7, 'F', '0912345683', '128 Spruce Way, Taguig', '2011-02-28', '2025-06-02'),
(18, 'STU007', 7, 'G', '0912345684', '129 Pine Street, Mandaluyong', '2011-04-12', '2025-06-02'),
(19, 'STU008', 7, 'H', '0912345685', '130 Willow Court, Las Piñas', '2011-06-25', '2025-06-02'),
(20, 'STU009', 7, 'I', '0912345686', '131 Ash Boulevard, Muntinlupa', '2011-08-09', '2025-06-02'),
(21, 'STU010', 7, 'J', '0912345687', '132 Oak Hill, Cavite', '2011-10-14', '2025-06-02'),
(22, 'STU011', 8, 'A', '0912345688', '133 Elm Park, Laguna', '2010-12-01', '2025-06-02'),
(23, 'STU012', 8, 'B', '0912345689', '134 Maple Square, Rizal', '2010-01-19', '2025-06-02'),
(24, 'STU013', 8, 'C', '0912345690', '135 Cedar Heights, Manila', '2010-03-26', '2025-06-02'),
(25, 'STU014', 8, 'D', '0912345691', '136 Birch Forest, Quezon City', '2010-05-30', '2025-06-02'),
(26, 'STU015', 8, 'E', '0912345692', '137 Spruce Gardens, Pasig', '2010-07-23', '2025-06-02'),
(27, 'STU016', 8, 'F', '0912345693', '138 Pine Valley, Makati', '2010-09-11', '2025-06-02'),
(28, 'STU017', 8, 'G', '0912345694', '139 Willow Lake, Taguig', '2010-11-05', '2025-06-02'),
(29, 'STU018', 8, 'H', '0912345695', '140 Ash River, Mandaluyong', '2010-02-14', '2025-06-02'),
(30, 'STU019', 8, 'I', '0912345696', '141 Oak Stream, Las Piñas', '2010-04-08', '2025-06-02'),
(31, 'STU020', 8, 'J', '0912345697', '142 Maple Peak, Muntinlupa', '2010-06-20', '2025-06-02'),
(32, 'STU021', 9, 'A', '0912345698', '143 Cedar Canyon, Cavite', '2009-03-15', '2025-06-02'),
(33, 'STU022', 9, 'B', '0912345699', '144 Birch Beach, Laguna', '2009-05-22', '2025-06-02'),
(34, 'STU023', 9, 'C', '0912345700', '145 Spruce Shore, Rizal', '2009-07-10', '2025-06-02'),
(35, 'STU024', 9, 'D', '0912345701', '146 Pine Peninsula, Manila', '2009-09-03', '2025-06-02'),
(36, 'STU025', 9, 'E', '0912345702', '147 Willow Waterfront, Quezon City', '2009-11-17', '2025-06-02'),
(37, 'STU026', 9, 'F', '0912345703', '148 Ash Haven, Pasig', '2009-02-28', '2025-06-02'),
(38, 'STU027', 9, 'G', '0912345704', '149 Oak Orchard, Makati', '2009-04-12', '2025-06-02'),
(39, 'STU028', 9, 'H', '0912345705', '150 Elm Estate, Taguig', '2009-06-25', '2025-06-02'),
(40, 'STU029', 9, 'I', '0912345706', '151 Maple Manor, Mandaluyong', '2009-08-09', '2025-06-02'),
(41, 'STU030', 9, 'J', '0912345707', '152 Cedar Castle, Las Piñas', '2009-10-14', '2025-06-02'),
(42, 'STU031', 10, 'A', '0912345708', '153 Birch Barn, Muntinlupa', '2008-12-01', '2025-06-02'),
(43, 'STU032', 10, 'B', '0912345709', '154 Spruce Stable, Cavite', '2008-01-19', '2025-06-02'),
(44, 'STU033', 10, 'C', '0912345710', '155 Pine Pavilion, Laguna', '2008-03-26', '2025-06-02'),
(45, 'STU034', 10, 'D', '0912345711', '156 Willow Wing, Rizal', '2008-05-30', '2025-06-02'),
(46, 'STU035', 10, 'E', '0912345712', '157 Ash Annex, Manila', '2008-07-23', '2025-06-02'),
(47, 'STU036', 10, 'F', '0912345713', '158 Oak Outpost, Quezon City', '2008-09-11', '2025-06-02'),
(48, 'STU037', 10, 'G', '0912345714', '159 Elm Enclave, Pasig', '2008-11-05', '2025-06-02'),
(49, 'STU038', 10, 'H', '0912345715', '160 Maple Meadow, Makati', '2008-02-14', '2025-06-02'),
(50, 'STU039', 10, 'I', '0912345716', '161 Cedar Cove, Taguig', '2008-04-08', '2025-06-02'),
(51, 'STU040', 10, 'J', '0912345717', '162 Birch Brook, Mandaluyong', '2008-06-20', '2025-06-02');

-- =====================================================
-- 7. USERS - PARENTS (20)
-- =====================================================
INSERT INTO users (username, email, password, role, first_name, last_name, is_active) VALUES
('parent001', 'parent001@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'James', 'Anderson', TRUE),
('parent002', 'parent002@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Margaret', 'Wilson', TRUE),
('parent003', 'parent003@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Robert', 'Martinez', TRUE),
('parent004', 'parent004@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Patricia', 'Garcia', TRUE),
('parent005', 'parent005@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Michael', 'Rodriguez', TRUE),
('parent006', 'parent006@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Linda', 'Lopez', TRUE),
('parent007', 'parent007@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'William', 'Hernandez', TRUE),
('parent008', 'parent008@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Barbara', 'Perez', TRUE),
('parent009', 'parent009@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'David', 'Torres', TRUE),
('parent010', 'parent010@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Susan', 'Rivera', TRUE),
('parent011', 'parent011@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Richard', 'Gomez', TRUE),
('parent012', 'parent012@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Karen', 'Sanchez', TRUE),
('parent013', 'parent013@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Joseph', 'Morris', TRUE),
('parent014', 'parent014@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Nancy', 'Rogers', TRUE),
('parent015', 'parent015@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Thomas', 'Morgan', TRUE),
('parent016', 'parent016@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Lisa', 'Peterson', TRUE),
('parent017', 'parent017@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Christopher', 'Gray', TRUE),
('parent018', 'parent018@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Betty', 'Ramirez', TRUE),
('parent019', 'parent019@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Mark', 'James', TRUE),
('parent020', 'parent020@mnchs.edu.ph', '$2y$10$JlH7g/fwK5dXN5c9Ky.qKuLU5Wt5K5Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9Z9', 'parent', 'Sandra', 'Watson', TRUE);

-- =====================================================
-- 8. PARENTS PROFILE
-- =====================================================
INSERT INTO parents (user_id, phone, occupation) VALUES
(52, '0912345678', 'Engineer'), (53, '0912345679', 'Teacher'), (54, '0912345680', 'Doctor'),
(55, '0912345681', 'Accountant'), (56, '0912345682', 'Businessman'), (57, '0912345683', 'Nurse'),
(58, '0912345684', 'Government Employee'), (59, '0912345685', 'Manager'), (60, '0912345686', 'Lawyer'),
(61, '0912345687', 'Entrepreneur'), (62, '0912345688', 'Professor'), (63, '0912345689', 'Chef'),
(64, '0912345690', 'Pharmacist'), (65, '0912345691', 'Architect'), (66, '0912345692', 'Consultant'),
(67, '0912345693', 'Designer'), (68, '0912345694', 'Analyst'), (69, '0912345695', 'Technician'),
(70, '0912345696', 'Specialist'), (71, '0912345697', 'Administrator');

-- =====================================================
-- 9. PARENT-STUDENT RELATIONSHIPS
-- =====================================================
INSERT INTO parent_student (parent_id, student_id, relationship) VALUES
(1, 1, 'Father'), (2, 2, 'Mother'), (3, 3, 'Father'), (4, 4, 'Mother'), (5, 5, 'Father'),
(6, 6, 'Mother'), (7, 7, 'Father'), (8, 8, 'Mother'), (9, 9, 'Father'), (10, 10, 'Mother'),
(11, 11, 'Father'), (12, 12, 'Mother'), (13, 13, 'Father'), (14, 14, 'Mother'), (15, 15, 'Father'),
(16, 16, 'Mother'), (17, 17, 'Father'), (18, 18, 'Mother'), (19, 19, 'Father'), (20, 20, 'Mother');

-- =====================================================
-- 10. SUBJECTS (8 per grade level: Grades 7-10)
-- =====================================================
INSERT INTO subjects (subject_name, subject_code, description) VALUES
('English 7', 'ENG7', 'Grade 7 English Language & Literature'),
('Mathematics 7', 'MATH7', 'Grade 7 Mathematics - Arithmetic & Pre-Algebra'),
('Science 7', 'SCI7', 'Grade 7 Science - General Science & Biology'),
('Filipino 7', 'FIL7', 'Grade 7 Filipino Language & Literature'),
('Social Studies 7', 'SS7', 'Grade 7 Social Studies & Geography'),
('Physical Education 7', 'PE7', 'Grade 7 Physical Education & Health'),
('Computer Studies 7', 'CS7', 'Grade 7 Information Technology Basics'),
('Values Education 7', 'VE7', 'Grade 7 Values & Civic Education'),
('English 8', 'ENG8', 'Grade 8 English Language & Communication'),
('Mathematics 8', 'MATH8', 'Grade 8 Mathematics - Algebra Basics'),
('Science 8', 'SCI8', 'Grade 8 Science - Chemistry & Biology'),
('Filipino 8', 'FIL8', 'Grade 8 Filipino Communication & Literature'),
('Social Studies 8', 'SS8', 'Grade 8 Social Studies & History'),
('Physical Education 8', 'PE8', 'Grade 8 Physical Education & Wellness'),
('Computer Studies 8', 'CS8', 'Grade 8 Information Technology & Applications'),
('Values Education 8', 'VE8', 'Grade 8 Values & Ethical Leadership'),
('English 9', 'ENG9', 'Grade 9 English Language & Literature'),
('Mathematics 9', 'MATH9', 'Grade 9 Mathematics - Algebra & Geometry'),
('Science 9', 'SCI9', 'Grade 9 Science - Physics, Chemistry & Biology'),
('Filipino 9', 'FIL9', 'Grade 9 Filipino Language & Literature'),
('Social Studies 9', 'SS9', 'Grade 9 Social Studies & World History'),
('Physical Education 9', 'PE9', 'Grade 9 Physical Education & Sports'),
('Computer Studies 9', 'CS9', 'Grade 9 Information Technology & Programming'),
('Values Education 9', 'VE9', 'Grade 9 Values & Civic Responsibility'),
('English 10', 'ENG10', 'Grade 10 English Language & Literature'),
('Mathematics 10', 'MATH10', 'Grade 10 Mathematics - Advanced Algebra & Geometry'),
('Science 10', 'SCI10', 'Grade 10 Science - Advanced Sciences'),
('Filipino 10', 'FIL10', 'Grade 10 Filipino Language & Communication'),
('Social Studies 10', 'SS10', 'Grade 10 Social Studies & Civics'),
('Physical Education 10', 'PE10', 'Grade 10 Physical Education & Health'),
('Computer Studies 10', 'CS10', 'Grade 10 Information Technology & Digital Skills'),
('Values Education 10', 'VE10', 'Grade 10 Values & Personal Development');

-- =====================================================
-- 11. CLASSES (40 classes: Grades 7-10 with sections A-J)
-- =====================================================
INSERT INTO classes (class_name, grade_level, section, teacher_id, academic_year, description) VALUES
('Grade 7 Section A', 7, 'A', 1, '2025-2026', 'Junior High Section A'),
('Grade 7 Section B', 7, 'B', 2, '2025-2026', 'Junior High Section B'),
('Grade 7 Section C', 7, 'C', 3, '2025-2026', 'Junior High Section C'),
('Grade 7 Section D', 7, 'D', 4, '2025-2026', 'Junior High Section D'),
('Grade 7 Section E', 7, 'E', 5, '2025-2026', 'Junior High Section E'),
('Grade 7 Section F', 7, 'F', 6, '2025-2026', 'Junior High Section F'),
('Grade 7 Section G', 7, 'G', 7, '2025-2026', 'Junior High Section G'),
('Grade 7 Section H', 7, 'H', 8, '2025-2026', 'Junior High Section H'),
('Grade 7 Section I', 7, 'I', 9, '2025-2026', 'Junior High Section I'),
('Grade 7 Section J', 7, 'J', 10, '2025-2026', 'Junior High Section J'),
('Grade 8 Section A', 8, 'A', 1, '2025-2026', 'Junior High Section A'),
('Grade 8 Section B', 8, 'B', 2, '2025-2026', 'Junior High Section B'),
('Grade 8 Section C', 8, 'C', 3, '2025-2026', 'Junior High Section C'),
('Grade 8 Section D', 8, 'D', 4, '2025-2026', 'Junior High Section D'),
('Grade 8 Section E', 8, 'E', 5, '2025-2026', 'Junior High Section E'),
('Grade 8 Section F', 8, 'F', 6, '2025-2026', 'Junior High Section F'),
('Grade 8 Section G', 8, 'G', 7, '2025-2026', 'Junior High Section G'),
('Grade 8 Section H', 8, 'H', 8, '2025-2026', 'Junior High Section H'),
('Grade 8 Section I', 8, 'I', 9, '2025-2026', 'Junior High Section I'),
('Grade 8 Section J', 8, 'J', 10, '2025-2026', 'Junior High Section J'),
('Grade 9 Section A', 9, 'A', 1, '2025-2026', 'Junior High Section A'),
('Grade 9 Section B', 9, 'B', 2, '2025-2026', 'Junior High Section B'),
('Grade 9 Section C', 9, 'C', 3, '2025-2026', 'Junior High Section C'),
('Grade 9 Section D', 9, 'D', 4, '2025-2026', 'Junior High Section D'),
('Grade 9 Section E', 9, 'E', 5, '2025-2026', 'Junior High Section E'),
('Grade 9 Section F', 9, 'F', 6, '2025-2026', 'Junior High Section F'),
('Grade 9 Section G', 9, 'G', 7, '2025-2026', 'Junior High Section G'),
('Grade 9 Section H', 9, 'H', 8, '2025-2026', 'Junior High Section H'),
('Grade 9 Section I', 9, 'I', 9, '2025-2026', 'Junior High Section I'),
('Grade 9 Section J', 9, 'J', 10, '2025-2026', 'Junior High Section J'),
('Grade 10 Section A', 10, 'A', 1, '2025-2026', 'Junior High Section A'),
('Grade 10 Section B', 10, 'B', 2, '2025-2026', 'Junior High Section B'),
('Grade 10 Section C', 10, 'C', 3, '2025-2026', 'Junior High Section C'),
('Grade 10 Section D', 10, 'D', 4, '2025-2026', 'Junior High Section D'),
('Grade 10 Section E', 10, 'E', 5, '2025-2026', 'Junior High Section E'),
('Grade 10 Section F', 10, 'F', 6, '2025-2026', 'Junior High Section F'),
('Grade 10 Section G', 10, 'G', 7, '2025-2026', 'Junior High Section G'),
('Grade 10 Section H', 10, 'H', 8, '2025-2026', 'Junior High Section H'),
('Grade 10 Section I', 10, 'I', 9, '2025-2026', 'Junior High Section I'),
('Grade 10 Section J', 10, 'J', 10, '2025-2026', 'Junior High Section J');

-- =====================================================
-- 12. CLASS SUBJECTS (8 subjects per class)
-- =====================================================
INSERT INTO class_subjects (class_id, subject_id, teacher_id) VALUES
(1, 1, 1), (1, 2, 2), (1, 3, 3), (1, 4, 4), (1, 5, 5), (1, 6, 6), (1, 7, 7), (1, 8, 8),
(11, 9, 1), (11, 10, 2), (11, 11, 3), (11, 12, 4), (11, 13, 5), (11, 14, 6), (11, 15, 7), (11, 16, 8),
(21, 17, 1), (21, 18, 2), (21, 19, 3), (21, 20, 4), (21, 21, 5), (21, 22, 6), (21, 23, 7), (21, 24, 8),
(31, 25, 1), (31, 26, 2), (31, 27, 3), (31, 28, 4), (31, 29, 5), (31, 30, 6), (31, 31, 7), (31, 32, 8);

-- =====================================================
-- 13. CLASS ENROLLMENTS (Sample)
-- =====================================================
INSERT INTO class_enrollments (student_id, class_id, enrollment_date) VALUES
(1, 1, '2025-06-02'), (2, 2, '2025-06-02'), (3, 3, '2025-06-02'), (4, 4, '2025-06-02'),
(5, 5, '2025-06-02'), (6, 6, '2025-06-02'), (7, 7, '2025-06-02'), (8, 8, '2025-06-02'),
(9, 9, '2025-06-02'), (10, 10, '2025-06-02'),
(11, 11, '2025-06-02'), (12, 12, '2025-06-02'), (13, 13, '2025-06-02'), (14, 14, '2025-06-02'),
(15, 15, '2025-06-02'), (16, 16, '2025-06-02'), (17, 17, '2025-06-02'), (18, 18, '2025-06-02'),
(19, 19, '2025-06-02'), (20, 20, '2025-06-02'),
(21, 21, '2025-06-02'), (22, 22, '2025-06-02'), (23, 23, '2025-06-02'), (24, 24, '2025-06-02'),
(25, 25, '2025-06-02'), (26, 26, '2025-06-02'), (27, 27, '2025-06-02'), (28, 28, '2025-06-02'),
(29, 29, '2025-06-02'), (30, 30, '2025-06-02'),
(31, 31, '2025-06-02'), (32, 32, '2025-06-02'), (33, 33, '2025-06-02'), (34, 34, '2025-06-02'),
(35, 35, '2025-06-02'), (36, 36, '2025-06-02'), (37, 37, '2025-06-02'), (38, 38, '2025-06-02'),
(39, 39, '2025-06-02'), (40, 40, '2025-06-02');

-- =====================================================
-- 14. SAMPLE GRADES
-- =====================================================
INSERT INTO grades (student_id, class_subject_id, assessment_type, marks_obtained, total_marks, percentage, grade_letter, assessment_date, recorded_by) VALUES
(1, 1, 'midterm', 88, 100, 88, 'A', '2025-08-15', 1),
(1, 1, 'final', 90, 100, 90, 'A', '2025-10-20', 1),
(2, 1, 'midterm', 92, 100, 92, 'A', '2025-08-15', 1),
(2, 1, 'final', 93, 100, 93, 'A', '2025-10-20', 1),
(3, 1, 'midterm', 85, 100, 85, 'B+', '2025-08-15', 1),
(3, 1, 'final', 88, 100, 88, 'B+', '2025-10-20', 1);

-- =====================================================
-- 15. SAMPLE STUDENT VALUES
-- =====================================================
INSERT INTO student_values (student_id, class_id, aspect_name, rating, comments, recorded_by, recorded_date) VALUES
(1, 1, '1. Maka-Diyos (Spiritual)', 5, 'Strong spiritual values', 1, '2025-09-15'),
(1, 1, '2. Makatao (Humanistic)', 5, 'Demonstrates empathy', 1, '2025-09-15'),
(2, 2, '1. Maka-Diyos (Spiritual)', 5, 'Consistently spiritual', 1, '2025-09-15'),
(2, 2, '2. Makatao (Humanistic)', 4, 'Respectful to peers', 1, '2025-09-15'),
(3, 3, '1. Maka-Diyos (Spiritual)', 4, 'Practices spiritual values', 1, '2025-09-15'),
(3, 3, '2. Makatao (Humanistic)', 5, 'Exemplary character', 1, '2025-09-15');

-- Display status
SELECT 'Database population completed!' AS Status;
