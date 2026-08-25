<?php
require_once 'php/db_connect.php';
require_once 'php/auth.php';

requireRole(['staff', 'admin'], 'staff-login.php');

// Fetch all events for the current month (simplified for demo)
$stmt = $pdo->query("SELECT * FROM events ORDER BY start_datetime ASC");
$events = $stmt->fetchAll();

// Index events by date component
$eventMap = [];
foreach ($events as $event) {
    $dateKey = date('Y-m-d', strtotime($event['start_datetime']));
    $eventMap[$dateKey][] = $event;
}

$currentMonth = date('F Y');
$currentYearMonth = date('Y-m');
$daysInMonth = date('t');
$todayDay = date('j');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Calendar | DataSphere Staff Portal</title>
    <link rel="icon" type="image/png" href="assets/images/logo.png">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link rel="stylesheet" href="css/styles.css">
    <link rel="stylesheet" href="css/dashboard.css">
    <style>
        .calendar-grid { display: grid; grid-template-columns: repeat(7, 1fr); gap: 1px; background: rgba(255,255,255,0.05); border: 1px solid rgba(255,255,255,0.05); border-radius: var(--radius-xl); overflow: hidden; }
        .calendar-day-head { padding: 15px; text-align: center; background: var(--dark-700); color: var(--gray-500); font-weight: 600; font-size: 0.8rem; text-transform: uppercase; }
        .calendar-cell { min-height: 120px; background: var(--dark-800); padding: 10px; position: relative; }
        .calendar-cell.today { background: rgba(139, 92, 246, 0.05); }
        .date-num { font-size: 0.9rem; font-weight: 600; color: var(--gray-400); }
        .calendar-cell.today .date-num { color: var(--accent-purple); }
        .event-tag { font-size: 0.65rem; padding: 4px 8px; border-radius: 4px; margin-top: 5px; display: block; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; cursor: pointer; }
        .event-meeting { background: rgba(139, 92, 246, 0.2); color: var(--accent-purple); border-left: 3px solid var(--accent-purple); }
        .event-deadline { background: rgba(244, 63, 94, 0.2); color: #f43f5e; border-left: 3px solid #f43f5e; }
        .event-general { background: rgba(16, 185, 129, 0.2); color: var(--accent-green); border-left: 3px solid var(--accent-green); }
    </style>
</head>
<body>
    <div class="dashboard-layout staff-dashboard">
        <?php include 'php/includes/sidebar.php'; ?>
        <main class="dashboard-main">
            <?php 
            $header_title = "Company Calendar";
            $header_subtitle = $currentMonth;
            $header_actions = '<button class="btn btn-primary" onclick="document.getElementById(\'addEventModal\').style.display=\'flex\'"><i class="fas fa-plus"></i> New Event</button>';
            include 'php/includes/dashboard_header.php'; 
            ?>
            <div class="calendar-grid">
                <?php $days = ['Mon', 'Tue', 'Wed', 'Thu', 'Fri', 'Sat', 'Sun']; foreach($days as $d): ?>
                <div class="calendar-day-head"><?php echo $d; ?></div>
                <?php endforeach; ?>
                
                <?php for($i=1; $i<=$daysInMonth; $i++): 
                    $dateKey = $currentYearMonth . "-" . str_pad($i, 2, '0', STR_PAD_LEFT);
                    $isToday = ($i == $todayDay);
                ?>
                <div class="calendar-cell <?php echo $isToday ? 'today' : ''; ?>">
                    <span class="date-num"><?php echo str_pad($i, 2, '0', STR_PAD_LEFT); ?></span>
                    <?php if(isset($eventMap[$dateKey])): ?>
                        <?php foreach($eventMap[$dateKey] as $ev): ?>
                            <span class="event-tag event-<?php echo $ev['category']; ?>" title="<?php echo htmlspecialchars($ev['description']); ?>" onclick="openEditEventModal(<?php echo htmlspecialchars(json_encode($ev)); ?>)">
                                <?php echo htmlspecialchars($ev['title']); ?>
                            </span>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
                <?php endfor; ?>
            </div>
        </main>
    </div>

    <!-- Add Event Modal -->
    <div id="addEventModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.5rem;">Schedule New Event</h3>
                <i class="fas fa-times" style="cursor: pointer; color: var(--gray-500);" onclick="document.getElementById('addEventModal').style.display='none'"></i>
            </div>
            <form id="addEventForm">
                <input type="hidden" name="action" value="create_event">
                <div class="form-group">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="title" class="form-input" placeholder="e.g. Design Sync" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" class="form-input">
                        <option value="meeting">Meeting</option>
                        <option value="deadline">Deadline</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" class="form-input" placeholder="Event details..."></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" name="start_datetime" class="form-input" required>
                </div>
                <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">Add to Calendar</button>
            </form>
        </div>
    </div>

    <!-- Edit Event Modal -->
    <div id="editEventModal" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.8); backdrop-filter: blur(5px); z-index: 10000; display: none; align-items: center; justify-content: center; padding: 20px;">
        <div class="dashboard-card" style="width: 100%; max-width: 500px; padding: var(--space-2xl);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: var(--space-xl);">
                <h3 style="font-size: 1.5rem;">Edit Event</h3>
                <i class="fas fa-times" style="cursor: pointer; color: var(--gray-500);" onclick="document.getElementById('editEventModal').style.display='none'"></i>
            </div>
            <form id="editEventForm">
                <input type="hidden" name="action" value="update_event">
                <input type="hidden" name="id" id="editEventId">
                <div class="form-group">
                    <label class="form-label">Event Title</label>
                    <input type="text" name="title" id="editEventTitle" class="form-input" required>
                </div>
                <div class="form-group">
                    <label class="form-label">Category</label>
                    <select name="category" id="editEventCategory" class="form-input">
                        <option value="meeting">Meeting</option>
                        <option value="deadline">Deadline</option>
                        <option value="general">General</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Description</label>
                    <textarea name="description" id="editEventDesc" class="form-input"></textarea>
                </div>
                <div class="form-group">
                    <label class="form-label">Date & Time</label>
                    <input type="datetime-local" name="start_datetime" id="editEventStart" class="form-input" required>
                </div>
                <div style="display: flex; gap: var(--space-md);">
                    <button type="button" class="btn btn-secondary btn-lg" style="flex: 1; color: var(--accent-pink); border-color: rgba(244, 63, 94, 0.2); background: rgba(244, 63, 94, 0.1);" onclick="deleteEvent()">Delete Event</button>
                    <button type="submit" class="btn btn-primary btn-lg" style="flex: 2;">Save Changes</button>
                </div>
            </form>
        </div>
    </div>

    <script src="js/notifications.js"></script>
    <script>
        function openEditEventModal(eventObj) {
            document.getElementById('editEventId').value = eventObj.id;
            document.getElementById('editEventTitle').value = eventObj.title;
            document.getElementById('editEventCategory').value = eventObj.category;
            document.getElementById('editEventDesc').value = eventObj.description;
            
            // Format datetime for the input (YYYY-MM-DDThh:mm)
            if (eventObj.start_datetime) {
                const dt = new Date(eventObj.start_datetime);
                const tzoffset = (new Date()).getTimezoneOffset() * 60000;
                const localISOTime = (new Date(dt - tzoffset)).toISOString().slice(0, 16);
                document.getElementById('editEventStart').value = localISOTime;
            }

            document.getElementById('editEventModal').style.display = 'flex';
        }

        async function deleteEvent() {
            if (!confirm('Are you sure you want to delete this event?')) return;
            
            const eventId = document.getElementById('editEventId').value;
            try {
                const response = await fetch('api/actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'delete_event', id: eventId })
                });
                
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Error deleting event');
                }
            } catch (error) {
                alert('An error occurred while deleting the event.');
            }
        }

        document.getElementById('addEventForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('api/actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Error adding event');
                }
            } catch (error) {
                alert('An error occurred while adding the event.');
            }
        });

        document.getElementById('editEventForm').addEventListener('submit', async function(e) {
            e.preventDefault();
            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            
            try {
                const response = await fetch('api/actions.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(data)
                });
                
                const result = await response.json();
                if (result.success) {
                    window.location.reload();
                } else {
                    alert(result.message || 'Error updating event');
                }
            } catch (error) {
                alert('An error occurred while updating the event.');
            }
        });
    </script>
</body>
</html>

