<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Fetch user's pets for dropdowns
$pets_query = $conn->prepare("SELECT id, pet_name, type FROM pets WHERE user_id = ? ORDER BY pet_name");
$pets_query->bind_param("i", $user_id);
$pets_query->execute();
$pets = $pets_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Handle Add Appointment
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_appt') {
    $pet_id = (int)$_POST['pet_id'];
    $issue = sanitize($_POST['issue']);
    $apt_date = sanitize($_POST['appointment_date']);

    $verify = $conn->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
    $verify->bind_param("ii", $pet_id, $user_id);
    $verify->execute();
    
    if ($verify->get_result()->num_rows > 0) {
        $stmt = $conn->prepare("INSERT INTO appointments (user_id, pet_id, issue, appointment_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $user_id, $pet_id, $issue, $apt_date);
        if ($stmt->execute()) {
            setFlash('success', 'Appointment booked! We will confirm the status soon.');
        } else {
            setFlash('error', 'Failed to book appointment.');
        }
    } else {
        setFlash('error', 'Invalid pet selected.');
    }
    // Stay on appointments tab
    redirect('care-center.php?tab=appointments');
}

// Handle Add Support Request
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_support') {
    $issue_type = sanitize($_POST['issue_type']);
    $message = sanitize($_POST['message']);

    $stmt = $conn->prepare("INSERT INTO support_requests (user_id, issue_type, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $user_id, $issue_type, $message);
    if ($stmt->execute()) {
        setFlash('success', 'Support request sent. Our team will look into it.');
    } else {
        setFlash('error', 'Failed to send request.');
    }
    // Stay on support tab
    redirect('care-center.php?tab=support');
}

// Fetch Appointments
$appts_query = $conn->prepare("
    SELECT a.*, p.pet_name 
    FROM appointments a 
    JOIN pets p ON a.pet_id = p.id 
    WHERE a.user_id = ? 
    ORDER BY a.appointment_date DESC
");
$appts_query->bind_param("i", $user_id);
$appts_query->execute();
$appointments = $appts_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch Support Requests
$support_query = $conn->prepare("SELECT * FROM support_requests WHERE user_id = ? ORDER BY created_at DESC");
$support_query->bind_param("i", $user_id);
$support_query->execute();
$support_reqs = $support_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Determine active tab
$active_tab = isset($_GET['tab']) ? $_GET['tab'] : 'emergency';

$tab_class_em = $active_tab == 'emergency' ? 'active' : '';
$tab_class_ap = $active_tab == 'appointments' ? 'active' : '';
$tab_class_su = $active_tab == 'support' ? 'active' : '';

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Care & Support Center</h1>
        <p>Your hub for emergency first aid, vet appointments, and platform support.</p>
    </div>

    <div class="tabs">
        <button class="tab-btn <?php echo $tab_class_em; ?>" data-tab="emergency"><i class="fas fa-first-aid text-danger"></i> Emergency Help</button>
        <button class="tab-btn <?php echo $tab_class_ap; ?>" data-tab="appointments"><i class="fas fa-calendar-alt"></i> Vet Appointments</button>
        <button class="tab-btn <?php echo $tab_class_su; ?>" data-tab="support"><i class="fas fa-headset"></i> Support</button>
    </div>

    <!-- 1. Emergency Tab -->
    <div id="emergency" class="tab-content <?php echo $tab_class_em; ?>">
        <div class="glass-card-static" style="max-width: 800px; margin: 0 auto;">
            <div class="text-center mb-24">
                <div class="icon-coral" style="width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:2rem;margin:0 auto 16px;">
                    <i class="fas fa-ambulance"></i>
                </div>
                <h2>Pet Emergency First Aid</h2>
                <p class="text-muted">Select animal and issue to instantly view first aid steps. <br><strong>Always contact a vet for severe situations.</strong></p>
            </div>

            <div class="grid-2">
                <div class="form-group">
                    <label class="form-label">Animal Type</label>
                    <select id="emergencyAnimal" class="form-control">
                        <option value="">Select Type</option>
                        <option value="Dog">Dog</option>
                        <option value="Cow">Cow</option>
                        <option value="Sheep">Sheep</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Emergency Issue</label>
                    <select id="emergencyIssue" class="form-control">
                        <option value="">Select Issue</option>
                        <!-- Populated by JS -->
                    </select>
                </div>
            </div>
            
            <button id="emergencySearch" class="btn btn-accent btn-lg btn-block mt-16">
                <i class="fas fa-search"></i> Get First Aid Instructions
            </button>

            <!-- Results container -->
            <div id="emergencyResult" class="emergency-result" style="border-color: var(--accent);"></div>
        </div>
    </div>


    <!-- 2. Appointments Tab -->
    <div id="appointments" class="tab-content <?php echo $tab_class_ap; ?>">
        <div class="grid-2">
            <!-- Book Form -->
            <div class="glass-card-static">
                <h3 class="section-title"><i class="fas fa-calendar-plus"></i> Book Appointment</h3>
                <?php if (empty($pets)): ?>
                    <p class="text-muted">You must add a pet before booking.</p>
                <?php else: ?>
                <form action="care-center.php" method="POST" id="appointmentForm">
                    <input type="hidden" name="action" value="add_appt">
                    <div class="form-group">
                        <label class="form-label">Select Pet</label>
                        <select name="pet_id" class="form-control" required>
                            <option value="">Choose pet</option>
                            <?php foreach ($pets as $p): ?>
                                <option value="<?php echo $p['id']; ?>"><?php echo htmlspecialchars($p['pet_name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Reason / Issue</label>
                        <textarea name="issue" class="form-control" placeholder="Describe why you need to see a vet..." required></textarea>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Preferred Date</label>
                        <input type="date" name="appointment_date" class="form-control" min="<?php echo date('Y-m-d', strtotime('+1 day')); ?>" required>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Submit Request</button>
                </form>
                <?php endif; ?>
            </div>

            <!-- List -->
            <div class="glass-card-static">
                <h3 class="section-title"><i class="fas fa-clock"></i> My Appointments</h3>
                <?php if (empty($appointments)): ?>
                    <div class="empty-state">
                        <p>No appointments booked.</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px; max-height:400px; overflow-y:auto; padding-right:10px;">
                        <?php foreach($appointments as $a): 
                            $bg = 'var(--bg-glass)';
                            if($a['status'] == 'Confirmed') $bg = 'rgba(0, 200, 83, 0.1)';
                            if($a['status'] == 'Cancelled') $bg = 'rgba(255, 82, 82, 0.1)';
                        ?>
                        <div style="background:<?php echo $bg; ?>; padding:16px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <strong style="font-size:1.05rem;"><?php echo htmlspecialchars($a['pet_name']); ?></strong>
                                <span class="badge <?php 
                                    echo $a['status']=='Pending'?'badge-warning':
                                        ($a['status']=='Confirmed'?'badge-primary':
                                        ($a['status']=='Completed'?'badge-success':'badge-danger'));
                                ?>"><?php echo $a['status']; ?></span>
                            </div>
                            <div style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:8px;">
                                <i class="fas fa-calendar"></i> <?php echo date('M d, Y', strtotime($a['appointment_date'])); ?>
                            </div>
                            <p style="font-size:0.9rem; margin:0;">"<?php echo htmlspecialchars($a['issue']); ?>"</p>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>


    <!-- 3. Support Tab -->
    <div id="support" class="tab-content <?php echo $tab_class_su; ?>">
        <div class="grid-2">
            <!-- Ask Support Form -->
            <div class="glass-card-static">
                <h3 class="section-title"><i class="fas fa-envelope-open-text"></i> Contact Support</h3>
                <form action="care-center.php" method="POST" id="supportForm">
                    <input type="hidden" name="action" value="add_support">
                    <div class="form-group">
                        <label class="form-label">Issue Category</label>
                        <select name="issue_type" class="form-control" required>
                            <option value="">Select Category</option>
                            <option value="Medical Question">Medical Question</option>
                            <option value="Technical Issue">App/Technical Issue</option>
                            <option value="Account Query">Account Query</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Message</label>
                        <textarea name="message" class="form-control" placeholder="How can we help you?" style="min-height: 150px;" required></textarea>
                    </div>
                    <button type="submit" class="btn btn-primary btn-block"><i class="fas fa-paper-plane"></i> Send Message</button>
                </form>
            </div>

            <!-- List -->
            <div class="glass-card-static">
                <h3 class="section-title"><i class="fas fa-reply-all"></i> My Requests</h3>
                <?php if (empty($support_reqs)): ?>
                    <div class="empty-state">
                        <p>No support requests submitted.</p>
                    </div>
                <?php else: ?>
                    <div style="display:flex; flex-direction:column; gap:12px; max-height:400px; overflow-y:auto; padding-right:10px;">
                        <?php foreach($support_reqs as $s): ?>
                        <div style="background:var(--bg-glass); padding:16px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                            <div style="display:flex; justify-content:space-between; margin-bottom:8px;">
                                <strong><?php echo htmlspecialchars($s['issue_type']); ?></strong>
                                <span class="badge <?php echo $s['status']=='Resolved'?'badge-success':'badge-warning'; ?>">
                                    <?php echo $s['status']; ?>
                                </span>
                            </div>
                            <p style="font-size:0.85rem; color:var(--text-secondary); margin-bottom:8px;">
                                <?php echo htmlspecialchars($s['message']); ?>
                            </p>
                            <div style="font-size:0.75rem; color:var(--text-muted);">
                                Submitted: <?php echo date('M d, Y', strtotime($s['created_at'])); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
