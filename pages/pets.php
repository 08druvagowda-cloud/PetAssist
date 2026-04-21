<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
requireLogin();

$user_id = $_SESSION['user_id'];

// Handle Add Pet
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_pet') {
    $pet_name = sanitize($_POST['pet_name']);
    $type = sanitize($_POST['type']);
    $breed = sanitize($_POST['breed']);
    $dob = sanitize($_POST['dob']);
    $owner_name = sanitize($_POST['owner_name']);

    $stmt = $conn->prepare("INSERT INTO pets (user_id, pet_name, type, breed, dob, owner_name) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssss", $user_id, $pet_name, $type, $breed, $dob, $owner_name);
    
    if ($stmt->execute()) {
        setFlash('success', 'Pet added successfully!');
    } else {
        setFlash('error', 'Failed to add pet.');
    }
    redirect('pets.php');
}

// Handle Delete Pet
if (isset($_GET['delete'])) {
    $pet_id = (int)$_GET['delete'];
    
    // Verify ownership
    $stmt = $conn->prepare("DELETE FROM pets WHERE id = ? AND user_id = ?");
    $stmt->bind_param("ii", $pet_id, $user_id);
    if ($stmt->execute()) {
        setFlash('success', 'Pet removed successfully.');
    } else {
        setFlash('error', 'Failed to remove pet.');
    }
    redirect('pets.php');
}

// Fetch user's pets
$pets_query = $conn->prepare("SELECT * FROM pets WHERE user_id = ? ORDER BY created_at DESC");
$pets_query->bind_param("i", $user_id);
$pets_query->execute();
$pets = $pets_query->get_result()->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>My Pets</h1>
        <p>Manage profiles for all your furry friends.</p>
    </div>

    <div class="tabs">
        <button class="tab-btn active" data-tab="list-pets"><i class="fas fa-list"></i> View Pets</button>
        <button class="tab-btn" data-tab="add-pet"><i class="fas fa-plus"></i> Add New Pet</button>
    </div>

    <!-- View Pets Tab -->
    <div id="list-pets" class="tab-content active">
        <?php if (empty($pets)): ?>
            <div class="empty-state glass-card-static">
                <i class="fas fa-paw"></i>
                <h3>No pets found</h3>
                <p>Register your first pet to get started.</p>
                <button class="btn btn-primary mt-16" onclick="document.querySelector('[data-tab=\'add-pet\']').click()">
                    Add Pet Now
                </button>
            </div>
        <?php else: ?>
            <div class="pet-grid">
                <?php foreach ($pets as $pet): ?>
                    <div class="pet-card">
                        <div class="pet-card-header">
                            <div class="pet-avatar icon-purple">
                                <?php if($pet['type'] == 'Dog') echo '<i class="fas fa-dog"></i>'; ?>
                                <?php if($pet['type'] == 'Cow') echo '<i class="fas fa-cow"></i>'; ?>
                                <?php if($pet['type'] == 'Sheep') echo '<i class="fas fa-sheep"></i>'; ?>
                            </div>
                            <div>
                                <h3><?php echo htmlspecialchars($pet['pet_name']); ?></h3>
                                <span><?php echo htmlspecialchars($pet['type']); ?> • <?php echo htmlspecialchars($pet['breed']); ?></span>
                            </div>
                        </div>
                        <div class="pet-details">
                            <div class="pet-detail">
                                <i class="fas fa-birthday-cake"></i>
                                <span><?php echo date('M d, Y', strtotime($pet['dob'])); ?> (Age: <?php echo calculateAge($pet['dob']); ?>)</span>
                            </div>
                            <div class="pet-detail">
                                <i class="fas fa-user"></i>
                                <span>Owner: <?php echo htmlspecialchars($pet['owner_name']); ?></span>
                            </div>
                        </div>
                        <div class="pet-actions">
                            <a href="vaccinations.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-secondary" style="flex:1;"><i class="fas fa-syringe"></i> Vax</a>
                            <a href="health.php?pet_id=<?php echo $pet['id']; ?>" class="btn btn-sm btn-secondary" style="flex:1;"><i class="fas fa-heartbeat"></i> Health</a>
                            <a href="pets.php?delete=<?php echo $pet['id']; ?>" class="btn btn-sm btn-danger" data-confirm="Are you sure you want to remove <?php echo htmlspecialchars($pet['pet_name']); ?>? This will delete all related records." style="padding: 8px;"><i class="fas fa-trash"></i></a>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>

    <!-- Add Pet Tab -->
    <div id="add-pet" class="tab-content">
        <div class="glass-card-static" style="max-width: 600px; margin: 0 auto;">
            <h2 class="section-title"><i class="fas fa-plus-circle"></i> Add a New Pet</h2>
            <form action="" method="POST" id="petForm">
                <input type="hidden" name="action" value="add_pet">
                
                <div class="form-group">
                    <label class="form-label">Pet Name</label>
                    <input type="text" name="pet_name" class="form-control" required>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Animal Type</label>
                        <select name="type" class="form-control" required>
                            <option value="">Select Type</option>
                            <option value="Dog">Dog</option>
                            <option value="Cow">Cow</option>
                            <option value="Sheep">Sheep</option>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Breed</label>
                        <input type="text" name="breed" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Date of Birth</label>
                        <input type="date" name="dob" class="form-control" max="<?php echo date('Y-m-d'); ?>" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Owner Name</label>
                        <input type="text" name="owner_name" class="form-control" value="<?php echo htmlspecialchars($_SESSION['user_name']); ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn btn-primary btn-lg mt-16" style="width: 100%;">
                    <i class="fas fa-save"></i> Save Pet
                </button>
            </form>
        </div>
    </div>

</div>

<?php require_once '../includes/footer.php'; ?>
