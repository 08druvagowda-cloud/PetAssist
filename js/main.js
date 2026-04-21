/**
 * PetAssist — Main JavaScript
 * Handles: navigation, validation, tabs, modals, emergency help, filters, animations
 */

document.addEventListener('DOMContentLoaded', () => {
    initNavbar();
    initFlashAlerts();
    initFormValidation();
    initTabs();
    initEmergencyHelp();
    initFilters();
    initScrollAnimations();
    initBirthdayModal();
    initAccordion();
    initConfirmDialogs();
});

/* =============================================
   NAVBAR
   ============================================= */
function initNavbar() {
    const toggle = document.getElementById('navToggle');
    const menu = document.getElementById('navMenu');

    if (toggle && menu) {
        toggle.addEventListener('click', () => {
            toggle.classList.toggle('active');
            menu.classList.toggle('active');
        });

        // Close menu on link click (mobile)
        menu.querySelectorAll('.nav-link').forEach(link => {
            link.addEventListener('click', () => {
                toggle.classList.remove('active');
                menu.classList.remove('active');
            });
        });
    }

    // Navbar scroll effect
    window.addEventListener('scroll', () => {
        const navbar = document.getElementById('navbar');
        if (navbar) {
            navbar.classList.toggle('scrolled', window.scrollY > 30);
        }
    });
}

/* =============================================
   FLASH ALERTS AUTO DISMISS
   ============================================= */
function initFlashAlerts() {
    const alerts = document.querySelectorAll('#flash-alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            alert.style.opacity = '0';
            alert.style.transform = 'translateY(-10px)';
            setTimeout(() => alert.remove(), 300);
        }, 5000);
    });
}

/* =============================================
   FORM VALIDATION
   ============================================= */
function initFormValidation() {
    // Registration form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => {
            clearErrors(registerForm);
            let valid = true;

            const name = registerForm.querySelector('[name="name"]');
            const email = registerForm.querySelector('[name="email"]');
            const password = registerForm.querySelector('[name="password"]');
            const phone = registerForm.querySelector('[name="phone"]');

            if (!name.value.trim()) {
                showError(name, 'Name is required');
                valid = false;
            }

            if (!email.value.trim() || !isValidEmail(email.value)) {
                showError(email, 'Enter a valid email');
                valid = false;
            }

            if (password.value.length < 6) {
                showError(password, 'Password must be at least 6 characters');
                valid = false;
            }

            if (!phone.value.trim() || phone.value.length < 10) {
                showError(phone, 'Enter a valid phone number');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => {
            clearErrors(loginForm);
            let valid = true;

            const email = loginForm.querySelector('[name="email"]');
            const password = loginForm.querySelector('[name="password"]');

            if (!email.value.trim() || !isValidEmail(email.value)) {
                showError(email, 'Enter a valid email');
                valid = false;
            }

            if (!password.value.trim()) {
                showError(password, 'Password is required');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // Pet form
    const petForm = document.getElementById('petForm');
    if (petForm) {
        petForm.addEventListener('submit', (e) => {
            clearErrors(petForm);
            let valid = true;

            const petName = petForm.querySelector('[name="pet_name"]');
            const type = petForm.querySelector('[name="type"]');
            const dob = petForm.querySelector('[name="dob"]');
            const ownerName = petForm.querySelector('[name="owner_name"]');

            if (!petName.value.trim()) {
                showError(petName, 'Pet name is required');
                valid = false;
            }
            if (!type.value) {
                showError(type, 'Select animal type');
                valid = false;
            }
            if (!dob.value) {
                showError(dob, 'Date of birth is required');
                valid = false;
            }
            if (!ownerName.value.trim()) {
                showError(ownerName, 'Owner name is required');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // Vaccination form
    const vaccForm = document.getElementById('vaccForm');
    if (vaccForm) {
        vaccForm.addEventListener('submit', (e) => {
            clearErrors(vaccForm);
            let valid = true;

            const petId = vaccForm.querySelector('[name="pet_id"]');
            const vaccineType = vaccForm.querySelector('[name="vaccine_type_id"]');
            const lastDate = vaccForm.querySelector('[name="last_date"]');

            if (!petId.value) {
                showError(petId, 'Select a pet');
                valid = false;
            }
            if (!vaccineType.value) {
                showError(vaccineType, 'Select vaccine type');
                valid = false;
            }
            if (!lastDate.value) {
                showError(lastDate, 'Enter vaccination date');
                valid = false;
            }

            if (!valid) e.preventDefault();
        });
    }

    // Appointment form
    const apptForm = document.getElementById('appointmentForm');
    if (apptForm) {
        apptForm.addEventListener('submit', (e) => {
            clearErrors(apptForm);
            let valid = true;

            const petId = apptForm.querySelector('[name="pet_id"]');
            const issue = apptForm.querySelector('[name="issue"]');
            const date = apptForm.querySelector('[name="appointment_date"]');

            if (!petId.value) { showError(petId, 'Select a pet'); valid = false; }
            if (!issue.value.trim()) { showError(issue, 'Describe the issue'); valid = false; }
            if (!date.value) { showError(date, 'Select a date'); valid = false; }

            if (!valid) e.preventDefault();
        });
    }

    // Support form
    const supportForm = document.getElementById('supportForm');
    if (supportForm) {
        supportForm.addEventListener('submit', (e) => {
            clearErrors(supportForm);
            let valid = true;

            const issueType = supportForm.querySelector('[name="issue_type"]');
            const message = supportForm.querySelector('[name="message"]');

            if (!issueType.value) { showError(issueType, 'Select issue type'); valid = false; }
            if (!message.value.trim()) { showError(message, 'Enter your message'); valid = false; }

            if (!valid) e.preventDefault();
        });
    }

    // Lost & Found form
    const lfForm = document.getElementById('lfForm');
    if (lfForm) {
        lfForm.addEventListener('submit', (e) => {
            clearErrors(lfForm);
            let valid = true;

            const type = lfForm.querySelector('[name="type"]');
            const petDetails = lfForm.querySelector('[name="pet_details"]');
            const description = lfForm.querySelector('[name="description"]');
            const contact = lfForm.querySelector('[name="contact"]');

            if (!type.value) { showError(type, 'Select type'); valid = false; }
            if (!petDetails.value.trim()) { showError(petDetails, 'Enter pet details'); valid = false; }
            if (!description.value.trim()) { showError(description, 'Enter description'); valid = false; }
            if (!contact.value.trim()) { showError(contact, 'Enter contact info'); valid = false; }

            if (!valid) e.preventDefault();
        });
    }
}

function isValidEmail(email) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email);
}

function showError(input, message) {
    const formGroup = input.closest('.form-group');
    if (formGroup) {
        const errorEl = document.createElement('p');
        errorEl.className = 'form-error';
        errorEl.textContent = message;
        formGroup.appendChild(errorEl);
        input.style.borderColor = 'var(--danger)';
    }
}

function clearErrors(form) {
    form.querySelectorAll('.form-error').forEach(e => e.remove());
    form.querySelectorAll('.form-control').forEach(input => {
        input.style.borderColor = '';
    });
}

/* =============================================
   TABS
   ============================================= */
function initTabs() {
    const tabButtons = document.querySelectorAll('.tab-btn');
    tabButtons.forEach(btn => {
        btn.addEventListener('click', () => {
            const tabGroup = btn.closest('.tabs');
            const container = tabGroup ? tabGroup.parentElement : document;

            // Deactivate all in this group
            tabGroup.querySelectorAll('.tab-btn').forEach(b => b.classList.remove('active'));
            container.querySelectorAll('.tab-content').forEach(c => c.classList.remove('active'));

            // Activate clicked
            btn.classList.add('active');
            const target = document.getElementById(btn.dataset.tab);
            if (target) target.classList.add('active');
        });
    });
}

/* =============================================
   EMERGENCY HELP — Client-Side Logic
   ============================================= */
function initEmergencyHelp() {
    const animalSelect = document.getElementById('emergencyAnimal');
    const issueSelect = document.getElementById('emergencyIssue');
    const searchBtn = document.getElementById('emergencySearch');
    const resultDiv = document.getElementById('emergencyResult');

    if (!animalSelect || !issueSelect || !searchBtn) return;

    const emergencyData = {
        Dog: {
            'Choking': {
                title: 'Dog Choking — Emergency First Aid',
                steps: [
                    'Stay calm and restrain your dog gently.',
                    'Open the mouth carefully and look for visible obstructions.',
                    'If visible, try to remove it with tweezers (avoid pushing deeper).',
                    'For small dogs: hold upside down by the hind legs and apply gentle back blows.',
                    'For large dogs: perform the Heimlich maneuver — place fist behind the rib cage and push upward firmly.',
                    'If unsuccessful, rush to the nearest vet immediately.',
                    'Monitor breathing and keep airways clear during transport.'
                ]
            },
            'Poisoning': {
                title: 'Dog Poisoning — Emergency First Aid',
                steps: [
                    'Identify the poison if possible (keep the packaging).',
                    'Do NOT induce vomiting unless instructed by a vet.',
                    'Call your vet or pet poison helpline immediately.',
                    'If poison is on skin/fur, wash with warm water and mild soap.',
                    'If ingested, note the time and estimated amount consumed.',
                    'Keep the dog warm and calm during transport to the vet.',
                    'Bring the poison sample/packaging to the vet.'
                ]
            },
            'Bleeding': {
                title: 'Dog Bleeding — Emergency First Aid',
                steps: [
                    'Apply direct pressure with a clean cloth or gauze.',
                    'Keep pressure for at least 5 minutes without removing the cloth.',
                    'If bleeding through, add more layers on top.',
                    'For limb injuries, apply a bandage above the wound to slow bleeding.',
                    'Elevate the injured area if possible.',
                    'Do not attempt to remove embedded objects.',
                    'Transport to vet immediately for stitching or treatment.'
                ]
            },
            'Seizure': {
                title: 'Dog Seizure — Emergency First Aid',
                steps: [
                    'Stay calm and keep others away from the dog.',
                    'Do NOT hold the dog down or put anything in its mouth.',
                    'Move furniture or objects away to prevent injury.',
                    'Time the seizure — if it lasts more than 3 minutes, this is an emergency.',
                    'After the seizure, keep the dog warm and in a quiet room.',
                    'Speak softly and comfort gently.',
                    'Contact your vet for follow up, especially for first-time seizures.'
                ]
            },
            'Heatstroke': {
                title: 'Dog Heatstroke — Emergency First Aid',
                steps: [
                    'Move the dog to a cool, shaded area immediately.',
                    'Apply cool (not cold) water to the body, especially neck, armpits, and groin.',
                    'Place wet towels on the body and fan the dog.',
                    'Offer small amounts of cool water to drink.',
                    'Do NOT use ice or very cold water (can cause shock).',
                    'Monitor body temperature — stop cooling when it reaches 103°F (39.4°C).',
                    'Rush to vet even if the dog appears to recover.'
                ]
            }
        },
        Cow: {
            'Bloating': {
                title: 'Cow Bloating — Emergency First Aid',
                steps: [
                    'Keep the cow standing and walking if possible.',
                    'Position the cow uphill (front legs higher than rear).',
                    'Administer bloat medicine (oral anti-foaming agent) if available.',
                    'Gentle massage of the left flank area to encourage gas release.',
                    'If severe, a stomach tube may be inserted carefully by trained personnel.',
                    'In critical cases, a trocar may be needed — contact a vet immediately.',
                    'Prevent future bloating by managing diet and avoiding lush pasture.'
                ]
            },
            'Difficulty Breathing': {
                title: 'Cow Breathing Difficulty — Emergency First Aid',
                steps: [
                    'Keep the cow calm and in a well-ventilated area.',
                    'Clear the nostrils of any mucus or obstructions.',
                    'Extend the neck to open the airway.',
                    'If choking, attempt to dislodge the object carefully.',
                    'Monitor for signs of pneumonia (fever, coughing, nasal discharge).',
                    'Contact a veterinarian immediately.',
                    'Isolate if infectious disease is suspected.'
                ]
            },
            'Wound/Injury': {
                title: 'Cow Wound/Injury — Emergency First Aid',
                steps: [
                    'Restrain the cow safely to prevent further injury.',
                    'Clean the wound with clean water or saline solution.',
                    'Apply antiseptic solution (dilute iodine or chlorhexidine).',
                    'Cover with clean bandage if possible.',
                    'Apply pressure for bleeding wounds.',
                    'Administer anti-tetanus if wound is deep or dirty.',
                    'Contact a vet for suturing or antibiotics.'
                ]
            }
        },
        Sheep: {
            'Fly Strike': {
                title: 'Sheep Fly Strike — Emergency First Aid',
                steps: [
                    'Shear the affected area carefully to expose the wound.',
                    'Remove all visible maggots by hand or with tweezers.',
                    'Clean the area thoroughly with antiseptic solution.',
                    'Apply appropriate fly strike treatment/insecticide.',
                    'Administer anti-inflammatory and pain relief as advised by vet.',
                    'Isolate the affected sheep.',
                    'Monitor for secondary infections and treat with antibiotics if needed.'
                ]
            },
            'Lameness': {
                title: 'Sheep Lameness — Emergency First Aid',
                steps: [
                    'Catch and restrain the sheep gently.',
                    'Examine the affected foot for stones, thorns, or abscesses.',
                    'Trim overgrown hooves carefully with proper hoof trimmers.',
                    'Clean between the toes and look for foot rot signs.',
                    'Apply antiseptic spray or foot rot treatment.',
                    'Walk the sheep through a footbath (zinc sulphate solution).',
                    'If persistent, consult a vet for antibiotic treatment.'
                ]
            },
            'Bloating': {
                title: 'Sheep Bloating — Emergency First Aid',
                steps: [
                    'Stand the sheep up and keep it moving.',
                    'Gently massage the left side of the abdomen.',
                    'Administer bloat drench or vegetable oil orally.',
                    'Position the sheep with front legs elevated.',
                    'If severe, contact a vet immediately for trocarization.',
                    'Remove access to the feed causing bloat.',
                    'Monitor the flock for signs of similar issues.'
                ]
            }
        }
    };

    // Update issues when animal changes
    animalSelect.addEventListener('change', () => {
        const animal = animalSelect.value;
        issueSelect.innerHTML = '<option value="">Select Issue</option>';
        if (animal && emergencyData[animal]) {
            Object.keys(emergencyData[animal]).forEach(issue => {
                const opt = document.createElement('option');
                opt.value = issue;
                opt.textContent = issue;
                issueSelect.appendChild(opt);
            });
        }
        if (resultDiv) resultDiv.classList.remove('show');
    });

    // Show first aid instructions
    searchBtn.addEventListener('click', () => {
        const animal = animalSelect.value;
        const issue = issueSelect.value;

        if (!animal || !issue) {
            alert('Please select both animal type and issue.');
            return;
        }

        const data = emergencyData[animal][issue];
        if (data && resultDiv) {
            resultDiv.innerHTML = `
                <h3><i class="fas fa-first-aid"></i> ${data.title}</h3>
                <ol>${data.steps.map(s => `<li>${s}</li>`).join('')}</ol>
                <p style="margin-top:16px;color:var(--warning);font-size:0.85rem;">
                    <i class="fas fa-exclamation-triangle"></i> 
                    Always contact a veterinarian for professional guidance in emergencies.
                </p>
            `;
            resultDiv.classList.add('show');
        }
    });
}

/* =============================================
   FILTERS (Lost & Found)
   ============================================= */
function initFilters() {
    const filterBtns = document.querySelectorAll('.filter-btn');
    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            const filterGroup = btn.closest('.filter-bar');
            filterGroup.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
            btn.classList.add('active');

            const filter = btn.dataset.filter;
            const cards = document.querySelectorAll('.lf-card');
            cards.forEach(card => {
                if (filter === 'all' || card.dataset.type === filter) {
                    card.style.display = '';
                    card.style.animation = 'fadeIn 0.4s ease';
                } else {
                    card.style.display = 'none';
                }
            });
        });
    });
}

/* =============================================
   SCROLL ANIMATIONS
   ============================================= */
function initScrollAnimations() {
    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
                observer.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 });

    document.querySelectorAll('.feature-card, .guide-card, .lf-card').forEach((el, i) => {
        el.style.opacity = '0';
        el.style.transform = 'translateY(30px)';
        el.style.transition = `all 0.6s ease ${i * 0.1}s`;
        observer.observe(el);
    });
}

/* =============================================
   BIRTHDAY MODAL
   ============================================= */
function initBirthdayModal() {
    const modal = document.getElementById('birthdayModal');
    if (!modal) return;

    // Show modal
    setTimeout(() => {
        modal.classList.add('active');
    }, 500);

    // Close modal
    const closeBtn = modal.querySelector('.modal-close');
    if (closeBtn) {
        closeBtn.addEventListener('click', () => {
            modal.classList.remove('active');
        });
    }

    // Close on overlay click
    modal.addEventListener('click', (e) => {
        if (e.target === modal) {
            modal.classList.remove('active');
        }
    });
}

/* =============================================
   ACCORDION
   ============================================= */
function initAccordion() {
    document.querySelectorAll('.accordion-header').forEach(header => {
        header.addEventListener('click', () => {
            const item = header.closest('.accordion-item');
            const isActive = item.classList.contains('active');

            // Close all
            document.querySelectorAll('.accordion-item').forEach(i => i.classList.remove('active'));

            // Toggle current
            if (!isActive) {
                item.classList.add('active');
            }
        });
    });
}

/* =============================================
   CONFIRM DIALOGS (Delete actions)
   ============================================= */
function initConfirmDialogs() {
    document.querySelectorAll('[data-confirm]').forEach(el => {
        el.addEventListener('click', (e) => {
            const message = el.dataset.confirm || 'Are you sure?';
            if (!confirm(message)) {
                e.preventDefault();
            }
        });
    });
}

/* =============================================
   HELPER: Show status update dropdown
   ============================================= */
function updateStatus(formId) {
    const form = document.getElementById(formId);
    if (form) form.submit();
}
