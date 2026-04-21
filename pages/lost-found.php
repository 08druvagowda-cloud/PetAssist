<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

$user_id = isset($_SESSION['user_id']) ? $_SESSION['user_id'] : null;

// Handle Add Post
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'add_lf') {
    requireLogin(); // Ensure user is logged in before posting
    
    $type = sanitize($_POST['type']);
    $pet_details = sanitize($_POST['pet_details']);
    $desc = sanitize($_POST['description']);
    $contact = sanitize($_POST['contact']);

    $stmt = $conn->prepare("INSERT INTO lost_found (user_id, type, pet_details, description, contact) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("issss", $user_id, $type, $pet_details, $desc, $contact);
    
    if ($stmt->execute()) {
        setFlash('success', 'Post added successfully!');
    } else {
        setFlash('error', 'Failed to add post.');
    }
    // Redirect to prevent form resubmission
    redirect('lost-found.php');
}

// Handle Delete Post
if (isset($_GET['delete'])) {
    requireLogin();
    $post_id = (int)$_GET['delete'];
    
    // Admins can delete anything; Users can only delete their own
    if (isAdmin()) {
        $stmt = $conn->prepare("DELETE FROM lost_found WHERE id = ?");
        $stmt->bind_param("i", $post_id);
    } else {
        $stmt = $conn->prepare("DELETE FROM lost_found WHERE id = ? AND user_id = ?");
        $stmt->bind_param("ii", $post_id, $user_id);
    }
    
    if ($stmt->execute()) {
        setFlash('success', 'Post removed.');
    }
    redirect('lost-found.php');
}

// Fetch all posts
$query = "
    SELECT lf.*, u.name as poster_name 
    FROM lost_found lf 
    JOIN users u ON lf.user_id = u.id 
    ORDER BY lf.created_at DESC
";
$posts = $conn->query($query)->fetch_all(MYSQLI_ASSOC);

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Lost & Found Board</h1>
        <p>Help reunite pets with their owners. Post details of lost or found animals here.</p>
    </div>

    <!-- Filter & Action Row -->
    <div style="display:flex; justify-content:space-between; align-items:center; flex-wrap:wrap; margin-bottom: 24px;">
        <div class="filter-bar" style="margin-bottom:0;">
            <button class="filter-btn active" data-filter="all">All Posts</button>
            <button class="filter-btn" data-filter="Lost">Lost Pets</button>
            <button class="filter-btn" data-filter="Found">Found Pets</button>
        </div>
        
        <?php if (isLoggedIn()): ?>
            <button class="btn btn-primary" onclick="document.getElementById('addPostModal').classList.add('active');">
                <i class="fas fa-plus"></i> Create Post
            </button>
        <?php else: ?>
            <a href="login.php" class="btn btn-secondary"><i class="fas fa-sign-in-alt"></i> Login to Post</a>
        <?php endif; ?>
    </div>

    <?php if (empty($posts)): ?>
        <div class="empty-state glass-card-static">
            <i class="fas fa-search-location"></i>
            <h3>No posts yet</h3>
            <p>The board is clear. Be sure to check back often.</p>
        </div>
    <?php else: ?>
        <div class="lf-grid">
            <?php foreach ($posts as $post): ?>
                <div class="lf-card" data-type="<?php echo $post['type']; ?>">
                    <div class="lf-card-header">
                        <?php if ($post['type'] == 'Lost'): ?>
                            <span class="lf-type lost"><i class="fas fa-search"></i> Lost</span>
                        <?php else: ?>
                            <span class="lf-type found"><i class="fas fa-map-marker-alt"></i> Found</span>
                        <?php endif; ?>
                        
                        <?php if (isLoggedIn() && ($user_id == $post['user_id'] || isAdmin())): ?>
                            <a href="?delete=<?php echo $post['id']; ?>" class="text-muted" data-confirm="Delete this post?" style="font-size:0.85rem;"><i class="fas fa-trash hover-danger"></i></a>
                        <?php endif; ?>
                    </div>
                    
                    <h3><?php echo htmlspecialchars($post['pet_details']); ?></h3>
                    <p><?php echo nl2br(htmlspecialchars($post['description'])); ?></p>
                    
                    <div style="margin-top:auto; padding-top:16px; border-top:1px solid var(--border);">
                        <div class="lf-contact mb-8">
                            <i class="fas fa-phone-alt"></i> 
                            <?php echo htmlspecialchars($post['contact']); ?>
                        </div>
                        <div style="display:flex; justify-content:space-between; align-items:center;">
                            <span class="lf-date"><i class="fas fa-user-circle"></i> <?php echo htmlspecialchars($post['poster_name']); ?></span>
                            <span class="lf-date"><?php echo date('M d, g:i A', strtotime($post['created_at'])); ?></span>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<!-- Add Post Modal -->
<?php if (isLoggedIn()): ?>
<div class="modal-overlay" id="addPostModal">
    <div class="modal text-left" style="max-width:600px;">
        <h2 class="section-title mb-24"><i class="fas fa-bullhorn"></i> Create Post</h2>
        <form action="" method="POST" id="lfForm">
            <input type="hidden" name="action" value="add_lf">
            
            <div class="form-group">
                <label class="form-label">Post Type</label>
                <select name="type" class="form-control" required>
                    <option value="">Select Type</option>
                    <option value="Lost">I Lost a Pet</option>
                    <option value="Found">I Found a Pet</option>
                </select>
            </div>
            
            <div class="form-group">
                <label class="form-label">Pet Details / Name & Breed</label>
                <input type="text" name="pet_details" class="form-control" placeholder="e.g. Golden Retriever Name: Max OR Unknown Black Cat" required>
            </div>
            
            <div class="form-group">
                <label class="form-label">Description & Location</label>
                <textarea name="description" class="form-control" placeholder="Where was it lost/found? Collar details? Distinctive marks?" required></textarea>
            </div>
            
            <div class="form-group">
                <label class="form-label">Contact Information</label>
                <input type="text" name="contact" class="form-control" placeholder="Phone number or email" required>
            </div>
            
            <div style="display:flex; gap:16px; margin-top:24px;">
                <button type="submit" class="btn btn-primary" style="flex:1;">Post Now</button>
                <button type="button" class="btn btn-secondary" style="flex:1;" onclick="document.getElementById('addPostModal').classList.remove('active');">Cancel</button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php require_once '../includes/footer.php'; ?>
