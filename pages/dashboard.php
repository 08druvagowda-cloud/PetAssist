<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];
$has_birthday = false;
$birthday_pet_name = '';

// Fetch stats
$stat_pets = $conn->query("SELECT COUNT(*) FROM pets WHERE user_id = $user_id")->fetch_row()[0];
$stat_appts = $conn->query("SELECT COUNT(*) FROM appointments WHERE user_id = $user_id AND status = 'Pending'")->fetch_row()[0];

// Fetch pets for dashboard cards
$pets_query = $conn->prepare("SELECT * FROM pets WHERE user_id = ? ORDER BY created_at DESC");
$pets_query->bind_param("i", $user_id);
$pets_query->execute();
$pets_result = $pets_query->get_result();
$pets = $pets_result->fetch_all(MYSQLI_ASSOC);

// Check for birthdays
foreach ($pets as $pet) {
    if (isBirthday($pet['dob'])) {
        $has_birthday = true;
        $birthday_pet_name = htmlspecialchars($pet['pet_name']);
        break; // Show for at least one pet if multiple have birthdays today
    }
}

// Check if we should show birthday popup
$show_birthday_modal = false;
if ($has_birthday && !isset($_SESSION['birthday_shown'])) {
    $show_birthday_modal = true;
    $_SESSION['birthday_shown'] = true;
}

// Fetch recent health logs
$health_query = $conn->prepare("
    SELECT h.*, p.pet_name 
    FROM health_logs h 
    JOIN pets p ON h.pet_id = p.id 
    WHERE p.user_id = ? 
    ORDER BY h.log_date DESC LIMIT 3
");
$health_query->bind_param("i", $user_id);
$health_query->execute();
$recent_health = $health_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Fetch upcoming vaccinations
$vacc_query = $conn->prepare("
    SELECT v.*, p.pet_name, vt.name as vaccine_name 
    FROM vaccinations v 
    JOIN pets p ON v.pet_id = p.id 
    JOIN vaccine_types vt ON v.vaccine_type_id = vt.id
    WHERE p.user_id = ? 
    ORDER BY v.next_due_date ASC LIMIT 3
");
$vacc_query->bind_param("i", $user_id);
$vacc_query->execute();
$upcoming_vaccinations = $vacc_query->get_result()->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Dashboard</h1>
        <p>Welcome back, <?php echo htmlspecialchars($_SESSION['user_name']); ?>! Here's an overview of your pets.</p>
    </div>

    <!-- Birthday Banner -->
    <?php if ($has_birthday): ?>
    <div class="birthday-banner animate-slide">
        <i class="fas fa-birthday-cake"></i>
        <div>
            <h3>Happy Birthday, <?php echo $birthday_pet_name; ?>!</h3>
            <p>Wish your furry friend a wonderful day today.</p>
        </div>
    </div>
    <?php endif; ?>

    <!-- Stats -->
    <div class="dashboard-grid animate-fade">
        <div class="stat-card">
            <div class="stat-icon icon-purple">
                <i class="fas fa-paw"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stat_pets; ?></h3>
                <p>Total Pets</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-amber">
                <i class="fas fa-calendar-check"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo $stat_appts; ?></h3>
                <p>Pending Appointments</p>
            </div>
        </div>
        <div class="stat-card">
            <div class="stat-icon icon-teal">
                <i class="fas fa-syringe"></i>
            </div>
            <div class="stat-info">
                <h3><?php echo count($upcoming_vaccinations); ?></h3>
                <p>Upcoming Vaccinations</p>
            </div>
        </div>
    </div>

    <div class="grid-2 animate-fade" style="animation-delay: 0.2s;">
        <!-- My Pets Summary -->
        <div class="glass-card-static">
            <div class="section-title">
                <i class="fas fa-dog"></i> My Pets
                <a href="pets.php" class="btn btn-sm btn-secondary" style="margin-left: auto;">Manage</a>
            </div>
            
            <?php if (empty($pets)): ?>
                <div class="empty-state">
                    <i class="fas fa-paw"></i>
                    <p>You haven't added any pets yet.</p>
                    <a href="pets.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Pet</a>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach (array_slice($pets, 0, 3) as $pet): ?>
                        <div style="display:flex; align-items:center; justify-content:space-between; padding:12px; background:var(--bg-glass); border-radius:var(--radius-sm); border:1px solid var(--border);">
                            <div style="display:flex; align-items:center; gap:12px;">
                                <div class="pet-avatar icon-purple" style="width:40px;height:40px;font-size:1.2rem;">
                                    <?php if($pet['type'] == 'Dog') echo '<i class="fas fa-dog"></i>'; ?>
                                    <?php if($pet['type'] == 'Cow') echo '<i class="fas fa-cow"></i>'; ?>
                                    <?php if($pet['type'] == 'Sheep') echo '<i class="fas fa-sheep"></i>'; ?>
                                </div>
                                <div>
                                    <h4 style="font-size:1rem;font-weight:600;"><?php echo htmlspecialchars($pet['pet_name']); ?></h4>
                                    <span style="font-size:0.75rem;color:var(--text-muted);"><?php echo calculateAge($pet['dob']); ?></span>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Upcoming Vaccinations Summary -->
        <div class="glass-card-static">
            <div class="section-title">
                <i class="fas fa-syringe"></i> Upcoming Vaccinations
                <a href="vaccinations.php" class="btn btn-sm btn-secondary" style="margin-left: auto;">View All</a>
            </div>

            <?php if (empty($upcoming_vaccinations)): ?>
                <div class="empty-state">
                    <i class="fas fa-check-circle"></i>
                    <p>All vaccinations are up to date.</p>
                </div>
            <?php else: ?>
                <div style="display:flex; flex-direction:column; gap:12px;">
                    <?php foreach ($upcoming_vaccinations as $vacc): 
                        $status = getVaccinationStatus($vacc['next_due_date']);
                        $border_class = $status == 'Overdue' ? 'vacc-overdue' : ($status == 'Due Soon' ? 'vacc-due-soon' : 'vacc-completed');
                    ?>
                        <div class="<?php echo $border_class; ?>" style="display:flex; align-items:center; justify-content:space-between; padding:12px 12px 12px 16px; background:var(--bg-glass); border-radius:var(--radius-sm); border-top:1px solid var(--border); border-right:1px solid var(--border); border-bottom:1px solid var(--border);">
                            <div>
                                <h4 style="font-size:0.95rem;font-weight:600;"><?php echo htmlspecialchars($vacc['vaccine_name']); ?></h4>
                                <span style="font-size:0.75rem;color:var(--text-muted);">For <?php echo htmlspecialchars($vacc['pet_name']); ?></span>
                            </div>
                            <div style="text-align:right;">
                                <div style="font-size:0.85rem;font-weight:500;"><?php echo date('M d, Y', strtotime($vacc['next_due_date'])); ?></div>
                                <span class="badge badge-<?php echo $status=='Overdue'?'danger':($status=='Due Soon'?'warning':'success'); ?> mt-8" style="font-size:0.65rem;">
                                    <?php echo $status; ?>
                                </span>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
    
    <!-- Quick Actions -->
    <div class="mt-32 mb-32 animate-fade" style="animation-delay: 0.3s;">
        <h3 style="font-size:1.1rem; margin-bottom:12px; color:var(--text-secondary);">Quick Links</h3>
        <div class="quick-nav">
            <a href="care-center.php" class="quick-nav-card">
                <i class="fas fa-first-aid text-center"></i>
                <span>Emergency Help</span>
            </a>
            <a href="health.php" class="quick-nav-card">
                <i class="fas fa-heartbeat"></i>
                <span>Log Health</span>
            </a>
            <a href="care-center.php" class="quick-nav-card">
                <i class="fas fa-calendar-plus"></i>
                <span>Book Vet</span>
            </a>
            <a href="guide.php" class="quick-nav-card">
                <i class="fas fa-book-open"></i>
                <span>Care Guide</span>
            </a>
        </div>
    </div>

</div>

<!-- Birthday Modal Pop-up -->
<?php if ($show_birthday_modal): ?>
<div class="modal-overlay" id="birthdayModal">
    <div class="modal">
        <div class="modal-icon text-center" style="color:var(--warning);">
            <i class="fas fa-birthday-cake"></i>
        </div>
        <h2>Happy Birthday!</h2>
        <p>Today is <strong><?php echo $birthday_pet_name; ?>'s</strong> birthday. Give them some extra love and treats today!</p>
        <button class="modal-close"><i class="fas fa-times"></i> Close</button>
    </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
