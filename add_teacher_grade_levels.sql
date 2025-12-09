-- Add grade levels column to teachers table if it doesn't exist
ALTER TABLE teachers ADD COLUMN IF NOT EXISTS grade_levels VARCHAR(50) COMMENT 'Comma-separated grade levels (7,8,9,10,11,12)';

-- Assign teachers to grade levels based on their specialization
-- Mathematics teachers (TCH001, TCH006) - handle Grade 8 and Grade 11
UPDATE teachers SET grade_levels = '8,11' WHERE teacher_id = 'TCH001';
UPDATE teachers SET grade_levels = '8,11' WHERE teacher_id = 'TCH006';

-- Science teachers (TCH002, TCH007) - handle Grade 9 and Grade 12
UPDATE teachers SET grade_levels = '9,12' WHERE teacher_id = 'TCH002';
UPDATE teachers SET grade_levels = '9,12' WHERE teacher_id = 'TCH007';

-- English teachers (TCH003, TCH008) - handle Grade 7 and Grade 10
UPDATE teachers SET grade_levels = '7,10' WHERE teacher_id = 'TCH003';
UPDATE teachers SET grade_levels = '7,10' WHERE teacher_id = 'TCH008';

-- Languages/Social Studies teachers (TCH004, TCH009) - handle Grade 8 and Grade 11
UPDATE teachers SET grade_levels = '8,11' WHERE teacher_id = 'TCH004';
UPDATE teachers SET grade_levels = '8,11' WHERE teacher_id = 'TCH009';

-- PE and Technology teachers (TCH005, TCH010) - handle Grade 7 and Grade 12
UPDATE teachers SET grade_levels = '7,12' WHERE teacher_id = 'TCH005';
UPDATE teachers SET grade_levels = '7,12' WHERE teacher_id = 'TCH010';
