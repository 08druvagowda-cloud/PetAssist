<?php
require_once 'includes/header.php';
?>

    <!-- Hero Section -->
    <section class="hero">
        <div class="hero-content">
            <div class="hero-badge">
                <i class="fas fa-sparkles"></i>
                Smart Pet Management Platform
            </div>
            <h1>Keep Your Pets <span class="gradient-text">Happy & Healthy</span></h1>
            <p>PetAssist is your all-in-one companion for managing pet health, vaccinations, appointments, 
               and emergencies. Built for modern pet owners who want the best for their furry friends.</p>
            <div class="hero-buttons">
                <?php if (isLoggedIn()): ?>
                    <a href="pages/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-tachometer-alt"></i> Go to Dashboard
                    </a>
                <?php else: ?>
                    <a href="pages/register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-rocket"></i> Get Started Free
                    </a>
                    <a href="pages/login.php" class="btn btn-secondary btn-lg">
                        <i class="fas fa-sign-in-alt"></i> Sign In
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="features" id="features">
        <div class="container">
            <div class="text-center">
                <h2 class="section-title" style="justify-content:center;font-size:1.8rem;">
                    <i class="fas fa-star"></i> Why Choose PetAssist?
                </h2>
                <p class="text-muted" style="max-width:600px;margin:0 auto;">
                    Everything you need to provide the best care for your pets — in one beautiful dashboard.
                </p>
            </div>

            <div class="features-grid">
                <div class="feature-card">
                    <div class="feature-icon icon-purple">
                        <i class="fas fa-paw"></i>
                    </div>
                    <h3>Pet Profiles</h3>
                    <p>Create detailed profiles for each of your pets with automatic age calculation and birthday alerts.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-teal">
                        <i class="fas fa-syringe"></i>
                    </div>
                    <h3>Smart Vaccinations</h3>
                    <p>Track vaccinations with auto-calculated due dates. Never miss a booster shot again.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-coral">
                        <i class="fas fa-heartbeat"></i>
                    </div>
                    <h3>Health Monitoring</h3>
                    <p>Log food intake, activity, and behavior. Get instant health status assessments.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-amber">
                        <i class="fas fa-ambulance"></i>
                    </div>
                    <h3>Emergency Help</h3>
                    <p>Instant first-aid instructions for common pet emergencies. Be prepared for anything.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-green">
                        <i class="fas fa-calendar-check"></i>
                    </div>
                    <h3>Appointments</h3>
                    <p>Book vet appointments and track status from pending to completion — all in one place.</p>
                </div>

                <div class="feature-card">
                    <div class="feature-icon icon-blue">
                        <i class="fas fa-search-location"></i>
                    </div>
                    <h3>Lost & Found</h3>
                    <p>Report lost pets or post found animals to help reunite pets with their families.</p>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section style="padding:60px 20px;">
        <div class="container">
            <div class="glass-card-static text-center" style="padding:60px 40px;">
                <h2 style="font-size:1.8rem;margin-bottom:12px;">Ready to give your pets the best care?</h2>
                <p class="text-muted" style="margin-bottom:28px;max-width:500px;margin-left:auto;margin-right:auto;">
                    Join thousands of pet owners who trust PetAssist for smart, modern pet management.
                </p>
                <?php if (!isLoggedIn()): ?>
                    <a href="pages/register.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-user-plus"></i> Create Free Account
                    </a>
                <?php else: ?>
                    <a href="pages/dashboard.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-arrow-right"></i> Go to Dashboard
                    </a>
                <?php endif; ?>
            </div>
        </div>
    </section>

<?php require_once 'includes/footer.php'; ?>
