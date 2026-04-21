<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireAdmin();

// Handle Appointment Status Update
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'update_appt') {
    $appt_id = (int)$_POST['appt_id'];
    $status = sanitize($_POST['status']);
    
    $stmt = $conn->prepare("UPDATE appointments SET status = ? WHERE id = ?");
    $stmt->bind_param("si", $status, $appt_id);
    if ($stmt->execute()) {
        setFlash('success', 'Appointment status updated to ' . $status);
    }
    redirect('admin.php');
}

// Handle Support Request Resolution
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'resolve_support') {
    $req_id = (int)$_POST['req_id'];
    
    $stmt = $conn->prepare("UPDATE support_requests SET status = 'Resolved' WHERE id = ?");
    $stmt->bind_param("i", $req_id);
    if ($stmt->execute()) {
        setFlash('success', 'Support request marked as resolved.');
    }
    redirect('admin.php?tab=support');
}

// Stats
$total_users = $conn->query("SELECT COUNT(*) FROM users")->fetch_row()[0];
$total_pets = $conn->query("SELECT COUNT(*) FROM pets")->fetch_row()[0];
$pending_appts = $conn->query("SELECT COUNT(*) FROM appointments WHERE status = 'Pending'")->fetch_row()[0];
$open_support = $conn->query("SELECT COUNT(*) FROM support_requests WHERE status = 'Pending'")->fetch_row()[0];

// Fetch Appointments
$appts_query = "
    SELECT a.*, p.pet_name, u.name as user_name, u.email 
    FROM appointments a 
    JOIN pets p ON a.pet_id = p.id 
    JOIN users u ON a.user_id = u.id 
    ORDER BY a.appointment_date DESC 
    LIMIT 50
";
$appointments = $conn->query($appts_query)->fetch_all(MYSQLI_ASSOC);

// Fetch Support
$support_query = "
    SELECT s.*, u.name as user_name, u.email 
    FROM support_requests s 
    JOIN users u ON s.user_id = u.id 
    ORDER BY s.created_at DESC 
    LIMIT 50
";
$support_reqs = $conn->query($support_query)->fetch_all(MYSQLI_ASSOC);

$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'dashboard';

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header" style="text-align: left; display: flex; align-items: center; gap: 16px;">
        <i class="fas fa-shield-alt text-warning" style="font-size: 2.5rem;"></i>
        <div>
            <h1 style="margin: 0; display: inline-block;">Admin Control Panel</h1>
            <p style="margin: 0;">Manage system data and user requests.</p>
        </div>
    </div>

    <div class="tabs">
        <button class="tab-btn <?php echo $active_tab=='dashboard'?'active':''; ?>" data-tab="admin-dash"><i class="fas fa-tachometer-alt"></i> Overview</button>
        <button class="tab-btn <?php echo $active_tab=='appointments'?'active':''; ?>" data-tab="admin-appts">
            <i class="fas fa-calendar-alt"></i> Appointments 
            <?php if($pending_appts > 0) echo '<span class="badge badge-warning" style="padding:2px 6px;margin-left:6px;">'.$pending_appts.'</span>'; ?>
        </button>
        <button class="tab-btn <?php echo $active_tab=='support'?'active':''; ?>" data-tab="admin-support">
            <i class="fas fa-headset"></i> Support 
            <?php if($open_support > 0) echo '<span class="badge badge-danger" style="padding:2px 6px;margin-left:6px;">'.$open_support.'</span>'; ?>
        </button>
    </div>

    <!-- Overview Tab -->
    <div id="admin-dash" class="tab-content <?php echo $active_tab=='dashboard'?'active':''; ?>">
        <div class="admin-stats">
            <div class="admin-stat-card">
                <div class="stat-number text-primary"><?php echo $total_users; ?></div>
                <div class="stat-label">Total Users</div>
            </div>
            <div class="admin-stat-card">
                <div class="stat-number text-success"><?php echo $total_pets; ?></div>
                <div class="stat-label">Registered Pets</div>
            </div>
            <div class="admin-stat-card">
                <div class="stat-number text-warning"><?php echo $pending_appts; ?></div>
                <div class="stat-label">Pending Appointments</div>
            </div>
            <div class="admin-stat-card">
                <div class="stat-number text-danger"><?php echo $open_support; ?></div>
                <div class="stat-label">Open Support Tickets</div>
            </div>
        </div>
    </div>

    <!-- Appointments Tab -->
    <div id="admin-appts" class="tab-content <?php echo $active_tab=='appointments'?'active':''; ?>">
        <div class="glass-card-static">
            <h3 class="section-title"><i class="fas fa-calendar-check"></i> Manage Appointments</h3>
            
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User (Email)</th>
                            <th>Pet</th>
                            <th>Issue</th>
                            <th>Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($appointments as $a): 
                            $badge = $a['status']=='Pending'?'badge-warning':($a['status']=='Confirmed'?'badge-primary':($a['status']=='Completed'?'badge-success':'badge-danger'));
                        ?>
                        <tr>
                            <td style="white-space:nowrap;"><?php echo date('M d, Y', strtotime($a['appointment_date'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($a['user_name']); ?></strong><br>
                                <span style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($a['email']); ?></span>
                            </td>
                            <td><?php echo htmlspecialchars($a['pet_name']); ?></td>
                            <td style="max-width:250px;"><?php echo htmlspecialchars($a['issue']); ?></td>
                            <td>
                                <form action="" method="POST" style="display:flex; align-items:center; gap:8px;">
                                    <input type="hidden" name="action" value="update_appt">
                                    <input type="hidden" name="appt_id" value="<?php echo $a['id']; ?>">
                                    <select name="status" class="form-control" style="padding:6px; font-size:0.8rem; width:120px;">
                                        <option value="Pending" <?php echo $a['status']=='Pending'?'selected':''; ?>>Pending</option>
                                        <option value="Confirmed" <?php echo $a['status']=='Confirmed'?'selected':''; ?>>Confirmed</option>
                                        <option value="Completed" <?php echo $a['status']=='Completed'?'selected':''; ?>>Completed</option>
                                        <option value="Cancelled" <?php echo $a['status']=='Cancelled'?'selected':''; ?>>Cancelled</option>
                                    </select>
                                    <button type="submit" class="btn btn-sm btn-primary">Update</button>
                                </form>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- Support Tab -->
    <div id="admin-support" class="tab-content <?php echo $active_tab=='support'?'active':''; ?>">
        <div class="glass-card-static">
            <h3 class="section-title"><i class="fas fa-inbox"></i> Support Tickets</h3>
            
            <div class="table-wrapper">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Date</th>
                            <th>User</th>
                            <th>Category</th>
                            <th>Message</th>
                            <th>Status / Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach($support_reqs as $s): ?>
                        <tr>
                            <td style="white-space:nowrap;"><?php echo date('M d, Y', strtotime($s['created_at'])); ?></td>
                            <td>
                                <strong><?php echo htmlspecialchars($s['user_name']); ?></strong><br>
                                <span style="font-size:0.75rem; color:var(--text-muted);"><?php echo htmlspecialchars($s['email']); ?></span>
                            </td>
                            <td><span class="badge badge-info"><?php echo htmlspecialchars($s['issue_type']); ?></span></td>
                            <td style="max-width:300px;"><?php echo htmlspecialchars($s['message']); ?></td>
                            <td>
                                <?php if($s['status'] == 'Pending'): ?>
                                    <form action="" method="POST">
                                        <input type="hidden" name="action" value="resolve_support">
                                        <input type="hidden" name="req_id" value="<?php echo $s['id']; ?>">
                                        <button type="submit" class="btn btn-sm btn-success" data-confirm="Mark as resolved?"><i class="fas fa-check"></i> Resolve</button>
                                    </form>
                                <?php else: ?>
                                    <span class="badge badge-success"><i class="fas fa-check"></i> Resolved</span>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
