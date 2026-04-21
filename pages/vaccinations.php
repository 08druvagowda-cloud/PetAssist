<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle Add Vaccination
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_vacc') {
    $pet_id = (int)$_POST['pet_id'];
    $vaccine_type_id = (int)$_POST['vaccine_type_id'];
    $last_date = sanitize($_POST['last_date']);

    // Verify ownership
    $verify = $conn->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
    $verify->bind_param("ii", $pet_id, $user_id);
    $verify->execute();
    if ($verify->get_result()->num_rows > 0) {
        
        // Get interval days for the vaccine
        $vt_query = $conn->prepare("SELECT interval_days FROM vaccine_types WHERE id = ?");
        $vt_query->bind_param("i", $vaccine_type_id);
        $vt_query->execute();
        $interval = $vt_query->get_result()->fetch_assoc()['interval_days'];

        // Calculate next due date
        $next_due_date = date('Y-m-d', strtotime($last_date . " + $interval days"));

        $stmt = $conn->prepare("INSERT INTO vaccinations (pet_id, vaccine_type_id, last_date, next_due_date) VALUES (?, ?, ?, ?)");
        $stmt->bind_param("iiss", $pet_id, $vaccine_type_id, $last_date, $next_due_date);
        
        if ($stmt->execute()) {
            setFlash('success', 'Vaccination record added successfully!');
        } else {
            setFlash('error', 'Failed to add vaccination record.');
        }
    } else {
        setFlash('error', 'Invalid pet selected.');
    }
    
    // Redirect back to the same pet's view if a pet was selected
    if (isset($_GET['pet_id'])) {
        redirect('vaccinations.php?pet_id=' . $pet_id);
    } else {
        redirect('vaccinations.php');
    }
}

// Fetch user's pets for dropdowns and sidebar
$pets_query = $conn->prepare("SELECT id, pet_name, type FROM pets WHERE user_id = ? ORDER BY pet_name");
$pets_query->bind_param("i", $user_id);
$pets_query->execute();
$pets = $pets_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch vaccine types
$vacc_types_result = $conn->query("SELECT * FROM vaccine_types ORDER BY name");
$vacc_types = $vacc_types_result->fetch_all(MYSQLI_ASSOC);

// Determine selected pet
$selected_pet_id = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : (count($pets) > 0 ? $pets[0]['id'] : 0);

// Fetch vaccinations for selected pet
$vaccinations = [];
if ($selected_pet_id > 0) {
    $v_query = $conn->prepare("
        SELECT v.*, vt.name as vaccine_name, vt.interval_days 
        FROM vaccinations v 
        JOIN vaccine_types vt ON v.vaccine_type_id = vt.id 
        WHERE v.pet_id = ? 
        ORDER BY v.next_due_date ASC
    ");
    $v_query->bind_param("i", $selected_pet_id);
    $v_query->execute();
    $vaccinations = $v_query->get_result()->fetch_all(MYSQLI_ASSOC);
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Vaccination Tracking</h1>
        <p>Keep track of your pets' vaccination schedules automatically.</p>
    </div>

    <?php if (empty($pets)): ?>
        <div class="empty-state glass-card-static">
            <i class="fas fa-paw"></i>
            <h3>No pets found</h3>
            <p>You need to add a pet before tracking vaccinations.</p>
            <a href="pets.php" class="btn btn-primary mt-16">Add Pet First</a>
        </div>
    <?php else: ?>
        <div class="tabs">
            <button class="tab-btn active" data-tab="records"><i class="fas fa-list"></i> Vaccination Records</button>
            <button class="tab-btn" data-tab="add-record"><i class="fas fa-plus"></i> Add Record</button>
        </div>

        <div id="records" class="tab-content active">
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                
                <!-- Pet Selector Sticky Sidebar -->
                <div style="flex: 1; min-width: 250px;">
                    <div class="glass-card-static" style="position: sticky; top: 90px;">
                        <h3 class="mb-16"><i class="fas fa-filter"></i> Select Pet</h3>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <?php foreach ($pets as $p): ?>
                                <a href="vaccinations.php?pet_id=<?php echo $p['id']; ?>" 
                                   class="btn <?php echo ($p['id'] == $selected_pet_id) ? 'btn-primary' : 'btn-secondary'; ?>" 
                                   style="justify-content:flex-start;">
                                    <?php if($p['type'] == 'Dog') echo '<i class="fas fa-dog"></i>'; ?>
                                    <?php if($p['type'] == 'Cow') echo '<i class="fas fa-cow"></i>'; ?>
                                    <?php if($p['type'] == 'Sheep') echo '<i class="fas fa-sheep"></i>'; ?>
                                    <?php echo htmlspecialchars($p['pet_name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Vaccination Data -->
                <div style="flex: 3; min-width: 300px;">
                    <div class="glass-card-static">
                        <?php 
                            $selected_pet_name = '';
                            foreach($pets as $p) { if($p['id'] == $selected_pet_id) $selected_pet_name = $p['pet_name']; }
                        ?>
                        <div class="section-title">
                            <i class="fas fa-shield-alt"></i> Records for <?php echo htmlspecialchars($selected_pet_name); ?>
                        </div>

                        <?php if (empty($vaccinations)): ?>
                            <div class="empty-state">
                                <i class="fas fa-folder-open"></i>
                                <p>No vaccination records found for this pet.</p>
                                <button class="btn btn-outline mt-16" onclick="document.querySelector('[data-tab=\'add-record\']').click()">Log their first vaccination</button>
                            </div>
                        <?php else: ?>
                            <div class="table-wrapper">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>Vaccine</th>
                                            <th>Last Date</th>
                                            <th>Next Due Date</th>
                                            <th>Status</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($vaccinations as $v): 
                                            $status = getVaccinationStatus($v['next_due_date']);
                                            $badge_class = $status == 'Overdue' ? 'badge-danger' : ($status == 'Due Soon' ? 'badge-warning' : 'badge-success');
                                        ?>
                                            <tr>
                                                <td style="font-weight:600;"><?php echo htmlspecialchars($v['vaccine_name']); ?></td>
                                                <td><?php echo date('M d, Y', strtotime($v['last_date'])); ?></td>
                                                <td style="font-weight:500;"><?php echo date('M d, Y', strtotime($v['next_due_date'])); ?></td>
                                                <td><span class="badge <?php echo $badge_class; ?>"><?php echo $status; ?></span></td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Record Tab -->
        <div id="add-record" class="tab-content">
            <div class="glass-card-static" style="max-width: 600px; margin: 0 auto;">
                <h2 class="section-title"><i class="fas fa-syringe"></i> Log Vaccination</h2>
                <form action="vaccinations.php<?php echo isset($_GET['pet_id']) ? '?pet_id='.$_GET['pet_id'] : ''; ?>" method="POST" id="vaccForm">
                    <input type="hidden" name="action" value="add_vacc">
                    
                    <div class="form-group">
                        <label class="form-label">Select Pet</label>
                        <select name="pet_id" class="form-control" required>
                            <option value="">Choose pet</option>
                            <?php foreach ($pets as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $selected_pet_id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['pet_name']); ?> (<?php echo htmlspecialchars($p['type']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Vaccine Type</label>
                        <select name="vaccine_type_id" class="form-control" required>
                            <option value="">Choose vaccine</option>
                            <?php foreach ($vacc_types as $vt): ?>
                                <option value="<?php echo $vt['id']; ?>">
                                    <?php echo htmlspecialchars($vt['name']); ?> (Interval: <?php echo $vt['interval_days']; ?> days)
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Date Administered</label>
                        <input type="date" name="last_date" class="form-control" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>

                    <div class="mt-16 mb-24" style="background:var(--info-bg); padding:16px; border-radius:var(--radius-sm); border:1px solid rgba(68, 138, 255, 0.3);">
                        <i class="fas fa-info-circle" style="color:var(--info);"></i> 
                        <span style="font-size:0.85rem; color:var(--text-secondary); margin-left:8px;">The next due date will be calculated automatically based on the vaccine interval.</span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-save"></i> Save Record
                    </button>
                </form>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
