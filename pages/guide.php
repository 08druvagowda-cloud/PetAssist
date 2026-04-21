<?php
require_once '../includes/db.php';
require_once '../includes/functions.php';
session_start();

require_once '../includes/header.php';
?>

<div class="container">
    <div class="page-header">
        <h1>Pet Care Guide</h1>
        <p>Expert tips, tutorials, and advice to help you provide the best care.</p>
    </div>

    <!-- Featured Guides -->
    <div class="guide-grid mb-32">
        <!-- Dog Guide -->
        <div class="guide-card">
            <div class="guide-card-header">
                <i class="fas fa-dog text-primary"></i>
                <h3>Essential Dog Care</h3>
            </div>
            <div class="guide-card-body">
                <img src="https://images.unsplash.com/photo-1543466835-00a7907e9de1?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                    alt="Dog"
                    style="width:100%; height:150px; object-fit:cover; border-radius:var(--radius-sm); margin-bottom:16px;">
                <p>Dogs thrive on routine, exercise, and balanced nutrition.</p>
                <ul>
                    <li>Walk at least 30-60 minutes daily.</li>
                    <li>Avoid feeding chocolate, grapes, and onions.</li>
                    <li>Brush teeth 2-3 times a week.</li>
                </ul>
            </div>
        </div>

        <!-- Cow Guide -->
        <div class="guide-card">
            <div class="guide-card-header">
                <i class="fas fa-cow text-success"></i>
                <h3>Dairy & Beef Cow Basics</h3>
            </div>
            <div class="guide-card-body">
                <img src="https://images.unsplash.com/photo-1570042225831-d98fa7577f1e?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                    alt="Cow"
                    style="width:100%; height:150px; object-fit:cover; border-radius:var(--radius-sm); margin-bottom:16px;">
                <p>Proper pasture management and hydration are key.</p>
                <ul>
                    <li>A cow drinks 30-50 gallons of water daily.</li>
                    <li>Ensure access to mineral blocks.</li>
                    <li>Watch for signs of bloating after fresh pasture.</li>
                </ul>
            </div>
        </div>

        <!-- Sheep Guide -->
        <div class="guide-card">
            <div class="guide-card-header">
                <i class="fas fa-sheep text-info"></i>
                <h3>Sheep Flock Management</h3>
            </div>
            <div class="guide-card-body">
                <img src="https://images.unsplash.com/photo-1484557985045-edf25e08da73?ixlib=rb-4.0.3&auto=format&fit=crop&w=800&q=80"
                    alt="Sheep"
                    style="width:100%; height:150px; object-fit:cover; border-radius:var(--radius-sm); margin-bottom:16px;">
                <p>Preventative care is crucial for herd animals.</p>
                <ul>
                    <li>Shear at least once a year before summer.</li>
                    <li>Check hooves regularly for foot rot.</li>
                    <li>Provide shelter from extreme sun or rain.</li>
                </ul>
            </div>
        </div>
    </div>

    <div class="grid-2">
        <!-- Training & Behavior Accordion -->
        <div class="glass-card-static">
            <h2 class="section-title"><i class="fas fa-brain"></i> Behavior & Training</h2>

            <div class="accordion-item active">
                <div class="accordion-header">
                    Positive Reinforcement Basics <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="accordion-body">
                    <p>Reward desired behaviors rather than punishing bad ones. Use high-value treats (like small bits
                        of chicken or cheese) to teach new commands. Keep training sessions short (5-10 minutes) but
                        frequent.</p>
                </div>
            </div>

            <div class="accordion-item">
                <div class="accordion-header">
                    Crate Training Tips <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="accordion-body">
                    <p>Introduce the crate slowly. Place treats and favorite toys inside. Never use the crate as a
                        punishment. It should act as a safe, comfortable den for the animal.</p>
                </div>
            </div>

            <div class="accordion-item">
                <div class="accordion-header">
                    Managing Separation Anxiety <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="accordion-body">
                    <p>Practice leaving for very short periods. Leave a piece of clothing that smells like you. Provide
                        engaging puzzles (like a frozen Kong) when you depart. Avoid making a big fuss when leaving or
                        returning.</p>
                </div>
            </div>
        </div>

        <!-- Home Remedies / Safety -->
        <div class="glass-card-static">
            <h2 class="section-title"><i class="fas fa-leaf"></i> Safe Home Remedies</h2>

            <div class="accordion-item">
                <div class="accordion-header">
                    Upset Stomach (Dogs) <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="accordion-body">
                    <p>Fasting for 12-24 hours followed by a bland diet of boiled plain white chicken breast and white
                        rice can help settle minor digestive issues. Plain unsweetened pumpkin puree (1-2 tablespoons)
                        is also great for diarrhea or constipation.</p>
                </div>
            </div>

            <div class="accordion-item">
                <div class="accordion-header">
                    Minor Cuts & Scrapes <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="accordion-body">
                    <p>Clean the area with warm water or a dilute saline solution. You can apply a very thin layer of
                        plain antibiotic ointment (without pain relievers like Neosporin), but ensure the pet cannot
                        lick it off.</p>
                </div>
            </div>

            <div class="accordion-item">
                <div class="accordion-header">
                    Flea Prevention <i class="fas fa-chevron-down chevron"></i>
                </div>
                <div class="accordion-body">
                    <p>While vet-prescribed preventatives are best, maintaining a clean environment helps. Vacuuming
                        frequently, washing pet bedding in hot water, and keeping the yard trimmed reduces flea
                        populations.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once '../includes/footer.php'; ?>