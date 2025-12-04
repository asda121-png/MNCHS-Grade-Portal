<?php
// Set timezone
date_default_timezone_set('Asia/Manila');

// Get current filter from URL (optional)
$filter = isset($_GET['filter']) ? $_GET['filter'] : 'all';

// --- MOCK DATABASE DATA ---
// In a real app: "SELECT * FROM attendance WHERE student_id = ? ORDER BY date DESC"
$attendanceRecords = [
    ['date' => '2025-10-15', 'day' => 'Wednesday', 'subject' => 'Daily Attendance', 'status' => 'Present', 'time' => '07:15 AM', 'remarks' => 'On time'],
    ['date' => '2025-10-14', 'day' => 'Tuesday', 'subject' => 'Daily Attendance', 'status' => 'Present', 'time' => '07:20 AM', 'remarks' => 'On time'],
    ['date' => '2025-10-13', 'day' => 'Monday', 'subject' => 'Daily Attendance', 'status' => 'Late', 'time' => '08:05 AM', 'remarks' => 'Flag ceremony missed'],
    ['date' => '2025-10-10', 'day' => 'Friday', 'subject' => 'Daily Attendance', 'status' => 'Present', 'time' => '07:10 AM', 'remarks' => 'On time'],
    ['date' => '2025-10-09', 'day' => 'Thursday', 'subject' => 'Daily Attendance', 'status' => 'Absent', 'time' => '--', 'remarks' => 'Sick leave filed'],
    ['date' => '2025-10-08', 'day' => 'Wednesday', 'subject' => 'Daily Attendance', 'status' => 'Present', 'time' => '07:14 AM', 'remarks' => 'On time'],
    ['date' => '2025-10-07', 'day' => 'Tuesday', 'subject' => 'Daily Attendance', 'status' => 'Present', 'time' => '07:30 AM', 'remarks' => 'On time'],
];

?>

<div class="bg-white rounded-lg shadow-sm overflow-hidden border border-gray-200">
    <!-- Header / Filters -->
    <div class="px-6 py-4 border-b border-gray-100 flex flex-col sm:flex-row sm:items-center justify-between gap-4">
        <h3 class="text-lg font-bold text-gray-800">Attendance Log</h3>
        
        <div class="flex gap-2 text-sm">
            <span class="px-3 py-1 bg-maroon-50 text-maroon-900 rounded-full font-bold border border-maroon-100">
                Total Present: 15
            </span>
            <span class="px-3 py-1 bg-red-50 text-red-700 rounded-full font-bold border border-red-100">
                Absent: 1
            </span>
             <span class="px-3 py-1 bg-orange-50 text-orange-700 rounded-full font-bold border border-orange-100">
                Late: 1
            </span>
        </div>
    </div>

    <!-- List Table -->
    <div class="overflow-x-auto">
        <table class="w-full text-left text-sm">
            <thead class="bg-gray-50 text-gray-500 uppercase">
                <tr>
                    <th class="px-6 py-3 font-medium">Date</th>
                    <th class="px-6 py-3 font-medium">Day</th>
                    <th class="px-6 py-3 font-medium">Time In</th>
                    <th class="px-6 py-3 font-medium">Status</th>
                    <th class="px-6 py-3 font-medium">Remarks</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-100">
                <?php foreach ($attendanceRecords as $record): 
                    // Determine Color based on status
                    $statusColor = 'bg-gray-100 text-gray-800'; // Default
                    if($record['status'] == 'Present') $statusColor = 'bg-green-100 text-green-700 border border-green-200';
                    if($record['status'] == 'Absent') $statusColor = 'bg-red-100 text-red-700 border border-red-200';
                    if($record['status'] == 'Late') $statusColor = 'bg-orange-100 text-orange-700 border border-orange-200';
                ?>
                <tr class="hover:bg-gray-50 transition">
                    <td class="px-6 py-4 font-semibold text-gray-800">
                        <?php echo date('M d, Y', strtotime($record['date'])); ?>
                    </td>
                    <td class="px-6 py-4 text-gray-600">
                        <?php echo $record['day']; ?>
                    </td>
                    <td class="px-6 py-4 font-mono text-gray-600">
                        <?php echo $record['time']; ?>
                    </td>
                    <td class="px-6 py-4">
                        <span class="px-3 py-1 rounded-full text-xs font-bold uppercase tracking-wider <?php echo $statusColor; ?>">
                            <?php echo $record['status']; ?>
                        </span>
                    </td>
                    <td class="px-6 py-4 text-gray-500 italic">
                        <?php echo $record['remarks']; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
                
                <?php if(empty($attendanceRecords)): ?>
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-gray-500">
                        No attendance records found for this period.
                    </td>
                </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <!-- Footer / Pagination -->
    <div class="px-6 py-3 bg-gray-50 border-t border-gray-200 flex justify-center">
        <button class="text-maroon-900 text-sm font-medium hover:underline">View Older Records</button>
    </div>
</div>