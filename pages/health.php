<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle Add Health Log
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_health') {
    $pet_id = (int)$_POST['pet_id'];
    $food = sanitize($_POST['food']);
    $activity = sanitize($_POST['activity']);
    $behavior = sanitize($_POST['behavior']);

    // Verify ownership
    $verify = $conn->prepare("SELECT id FROM pets WHERE id = ? AND user_id = ?");
    $verify->bind_param("ii", $pet_id, $user_id);
    $verify->execute();
    if ($verify->get_result()->num_rows > 0) {
        
        $status = getHealthStatus($food, $activity, $behavior);

        $stmt = $conn->prepare("INSERT INTO health_logs (pet_id, food, activity, behavior, status) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("issss", $pet_id, $food, $activity, $behavior, $status);
        
        if ($stmt->execute()) {
            setFlash('success', 'Health log added successfully! Status: ' . $status);
        } else {
            setFlash('error', 'Failed to add health log.');
        }
    } else {
        setFlash('error', 'Invalid pet selected.');
    }
    
    if (isset($_GET['pet_id'])) {
        redirect('health.php?pet_id=' . $pet_id);
    } else {
        redirect('health.php');
    }
}

// Fetch user's pets
$pets_query = $conn->prepare("SELECT id, pet_name, type FROM pets WHERE user_id = ? ORDER BY pet_name");
$pets_query->bind_param("i", $user_id);
$pets_query->execute();
$pets = $pets_query->get_result()->fetch_all(MYSQLI_ASSOC);

$selected_pet_id = isset($_GET['pet_id']) ? (int)$_GET['pet_id'] : (count($pets) > 0 ? $pets[0]['id'] : 0);

// Fetch logs for selected pet
$health_logs = [];
if ($selected_pet_id > 0) {
    $h_query = $conn->prepare("SELECT * FROM health_logs WHERE pet_id = ? ORDER BY log_date DESC");
    $h_query->bind_param("i", $selected_pet_id);
    $h_query->execute();
    $health_logs = $h_query->get_result()->fetch_all(MYSQLI_ASSOC);
}

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Health & Mood Tracker</h1>
        <p>Monitor your pet's daily health and get smart status evaluations.</p>
    </div>

    <?php if (empty($pets)): ?>
        <div class="empty-state glass-card-static">
            <i class="fas fa-paw"></i>
            <h3>No pets found</h3>
            <p>You need to add a pet before tracking health.</p>
            <a href="pets.php" class="btn btn-primary mt-16">Add Pet First</a>
        </div>
    <?php else: ?>
        <div class="tabs">
            <button class="tab-btn active" data-tab="history"><i class="fas fa-history"></i> Health History</button>
            <button class="tab-btn" data-tab="log-health"><i class="fas fa-plus"></i> New Daily Log</button>
        </div>

        <div id="history" class="tab-content active">
            <div style="display: flex; gap: 24px; flex-wrap: wrap;">
                
                <!-- Pet Selector -->
                <div style="flex: 1; min-width: 250px;">
                    <div class="glass-card-static" style="position: sticky; top: 90px;">
                        <h3 class="mb-16"><i class="fas fa-filter"></i> Select Pet</h3>
                        <div style="display:flex; flex-direction:column; gap:8px;">
                            <?php foreach ($pets as $p): ?>
                                <a href="health.php?pet_id=<?php echo $p['id']; ?>" 
                                   class="btn <?php echo ($p['id'] == $selected_pet_id) ? 'btn-primary' : 'btn-secondary'; ?>" 
                                   style="justify-content:flex-start;">
                                    <?php echo htmlspecialchars($p['pet_name']); ?>
                                </a>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- History -->
                <div style="flex: 3; min-width: 300px;">
                    <div class="glass-card-static">
                        <?php 
                            $selected_pet_name = '';
                            foreach($pets as $p) { if($p['id'] == $selected_pet_id) $selected_pet_name = $p['pet_name']; }
                        ?>
                        <div class="section-title">
                            <i class="fas fa-clipboard-list"></i> Logs for <?php echo htmlspecialchars($selected_pet_name); ?>
                        </div>

                        <?php if (empty($health_logs)): ?>
                            <div class="empty-state">
                                <i class="fas fa-notes-medical"></i>
                                <p>No health logs found for this pet.</p>
                                <button class="btn btn-outline mt-16" onclick="document.querySelector('[data-tab=\'log-health\']').click()">Create first log</button>
                            </div>
                        <?php else: ?>
                            <div style="display:flex; flex-direction:column; gap:16px;">
                                <?php foreach ($health_logs as $log): 
                                    $badge = $log['status'] == 'Healthy' ? 'badge-success' : ($log['status'] == 'Warning' ? 'badge-warning' : 'badge-danger');
                                ?>
                                    <div class="glass-card" style="padding: 20px;">
                                        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:16px;">
                                            <div style="font-size:0.85rem; color:var(--text-muted);">
                                                <i class="fas fa-calendar-alt"></i> <?php echo date('M d, Y - h:i A', strtotime($log['log_date'])); ?>
                                            </div>
                                            <span class="badge <?php echo $badge; ?>"><?php echo $log['status']; ?></span>
                                        </div>
                                        
                                        <div class="grid-2" style="gap: 12px;">
                                            <div style="background:var(--bg-glass); padding:12px; border-radius:var(--radius-sm);">
                                                <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Food Intake</div>
                                                <div style="font-weight:600;"><i class="fas fa-bone"></i> <?php echo htmlspecialchars($log['food']); ?></div>
                                            </div>
                                            <div style="background:var(--bg-glass); padding:12px; border-radius:var(--radius-sm);">
                                                <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Activity Level</div>
                                                <div style="font-weight:600;"><i class="fas fa-running"></i> <?php echo htmlspecialchars($log['activity']); ?></div>
                                            </div>
                                            <div style="background:var(--bg-glass); padding:12px; border-radius:var(--radius-sm);">
                                                <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase;">Behavior</div>
                                                <div style="font-weight:600;"><i class="fas fa-smile"></i> <?php echo htmlspecialchars($log['behavior']); ?></div>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Add Log Tab -->
        <div id="log-health" class="tab-content">
            <div class="glass-card-static" style="max-width: 600px; margin: 0 auto;">
                <h2 class="section-title"><i class="fas fa-heartbeat"></i> New Daily Log</h2>
                <form action="health.php<?php echo isset($_GET['pet_id']) ? '?pet_id='.$_GET['pet_id'] : ''; ?>" method="POST">
                    <input type="hidden" name="action" value="add_health">
                    
                    <div class="form-group">
                        <label class="form-label">Select Pet</label>
                        <select name="pet_id" class="form-control" required>
                            <option value="">Choose pet</option>
                            <?php foreach ($pets as $p): ?>
                                <option value="<?php echo $p['id']; ?>" <?php echo $p['id'] == $selected_pet_id ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($p['pet_name']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Food Intake</label>
                        <select name="food" class="form-control" required>
                            <option value="">How well did they eat?</option>
                            <option value="Good">Good (Ate everything normally)</option>
                            <option value="Normal">Normal (Ate most of it)</option>
                            <option value="Low">Low (Barely ate/Loss of appetite)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Activity Level</label>
                        <select name="activity" class="form-control" required>
                            <option value="">How active were they?</option>
                            <option value="High">High (Very energetic/Playful)</option>
                            <option value="Medium">Medium (Normal activity)</option>
                            <option value="Low">Low (Lethargic/Sleeping excessively)</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Behavior/Mood</label>
                        <select name="behavior" class="form-control" required>
                            <option value="">How are they acting?</option>
                            <option value="Happy">Happy (Tail wagging/Affectionate)</option>
                            <option value="Normal">Normal (Calm/Standard)</option>
                            <option value="Aggressive">Aggressive/Stressed (Hiding, growling)</option>
                        </select>
                    </div>

                    <div class="mt-16 mb-24" style="background:var(--primary-glow); padding:16px; border-radius:var(--radius-sm); border:1px solid var(--border);">
                        <i class="fas fa-robot"></i> 
                        <span style="font-size:0.85rem; color:var(--text-primary); margin-left:8px;">PetAssist's algorithm will analyze these metrics to evaluate overall health status.</span>
                    </div>

                    <button type="submit" class="btn btn-primary btn-lg" style="width: 100%;">
                        <i class="fas fa-plus"></i> Save Log
                    </button>
                </form>
            </div>
        </div>

    <?php endif; ?>
</div>

<?php require_once '../includes/footer.php'; ?>
