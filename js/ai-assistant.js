/**
 * PetAssist AI Assistant & Veterinary Care Engine
 * Provides intelligent symptom triage, nutrition recommendations,
 * first-aid advice, and personalized pet health insights.
 */

const PetAI = {
    // Knowledge Base & Triage Rules
    knowledgeBase: {
        emergencies: [
            { keywords: ['unconscious', 'collapsed', 'passed out', 'not breathing', 'choking', 'seizure', 'convulsion', 'heavy bleeding', 'hit by car', 'bloat', 'swollen stomach', 'pale gums', 'antifreeze', 'rat poison', 'snake bite'],
              title: '🚨 CRITICAL VET EMERGENCY',
              severity: 'critical',
              advice: 'Immediate veterinary intervention required! Keep the pet calm, warm, and transport them immediately to the nearest 24/7 animal hospital.',
              action: 'Book Emergency Consultation now in Care Center or call emergency veterinary helpline.'
            }
        ],
        symptoms: [
            {
                keywords: ['vomit', 'vomiting', 'throwing up', 'puking'],
                title: 'Vomiting & Digestive Distress',
                severity: 'warning',
                advice: 'Withhold food for 6-12 hours while keeping fresh water accessible. If vomiting persists more than 24 hours, contains blood, or is accompanied by lethargy or abdominal pain, consult a vet immediately.',
                triage: '🟡 Monitor closely. If >2 episodes in 24h, see a veterinarian.'
            },
            {
                keywords: ['diarrhea', 'loose stool', 'watery poop'],
                title: 'Diarrhea / Gastrointestinal Upset',
                severity: 'warning',
                advice: 'Ensure adequate hydration to prevent dehydration. Feed a bland diet (boiled chicken and white rice for dogs, fresh hay/electrolytes for livestock). Avoid dairy or fatty foods.',
                triage: '🟡 Common issue. Consult vet if watery/bloody or lasts > 48h.'
            },
            {
                keywords: ['scratch', 'scratching', 'itchy', 'itching', 'flea', 'tick', 'hair loss', 'skin rash'],
                title: 'Skin Irritation & Parasite Check',
                severity: 'safe',
                advice: 'Check for fleas, ticks, or red patches. Bathe with a soothing oatmeal/hypoallergenic pet shampoo. Ensure regular anti-parasitic spot-on treatments.',
                triage: '🟢 Manageable at home. Schedule a routine check if skin is raw or infected.'
            },
            {
                keywords: ['fever', 'hot ears', 'shivering', 'warm nose', 'high temp'],
                title: 'Elevated Temperature / Fever',
                severity: 'warning',
                advice: 'Normal dog/cat body temp is 38.3°C–39.2°C (101°F–102.5°F). Never give human medications (like Paracetamol/Ibuprofen/Aspirin) as they are toxic to pets! Offer cool water and consult a vet.',
                triage: '🟡 Warning: Do not give human painkillers. Seek veterinary guidance.'
            },
            {
                keywords: ['limp', 'limping', 'leg hurt', 'swollen paw', 'walking strange'],
                title: 'Mobility & Lameness',
                severity: 'warning',
                advice: 'Inspect paws for thorns, cuts, or cracked nails. Restrict physical exercise and jumping for 24-48 hours. If pet cannot put any weight on the leg, a fracture/sprain X-ray may be needed.',
                triage: '🟡 Restrict physical activity. Check paw pads.'
            },
            {
                keywords: ['cough', 'coughing', 'sneezing', 'runny nose', 'kennel cough'],
                title: 'Respiratory Symptoms',
                severity: 'warning',
                advice: 'Keep pet in a humidified, warm room away from cold drafts or smoke. If breathing is labored, wheezing, or gums look blue/pale, seek urgent vet care.',
                triage: '🟡 Common in boarding environments (Kennel Cough). Isolate from other pets.'
            },
            {
                keywords: ['not eating', 'loss of appetite', 'refusing food', 'anorexia', 'wont eat'],
                title: 'Loss of Appetite',
                severity: 'warning',
                advice: 'Warm the food slightly to enhance aroma. Check mouth for dental issues or foreign objects. If a pet refuses food for more than 24-36 hours, clinical diagnosis is essential.',
                triage: '🟡 Monitor closely. Seek checkup if fasting persists past 24 hours.'
            },
            {
                keywords: ['lethargic', 'lazy', 'no energy', 'sluggish', 'sleeping all day', 'weak'],
                title: 'Lethargy & Weakness',
                severity: 'warning',
                advice: 'Lethargy is a non-specific indicator of underlying infection, metabolic issue, or pain. Ensure drinking water and monitor for fever or appetite drop.',
                triage: '🟡 Observe for 24 hours. If accompanied by refusal to drink, visit clinic.'
            }
        ],
        toxicFoods: [
            { name: 'Chocolate & Caffeine', danger: 'Contains theobromine which causes cardiac arrhythmias and seizures in dogs and cats.' },
            { name: 'Grapes & Raisins', danger: 'Can trigger acute, irreversible kidney failure even in small doses.' },
            { name: 'Onions, Garlic & Chives', danger: 'Destroys red blood cells, leading to severe hemolytic anemia.' },
            { name: 'Xylitol (Artificial Sweetener)', danger: 'Triggers rapid, life-threatening hypoglycemia and acute liver failure.' },
            { name: 'Avocado', danger: 'Contains persin, which can cause toxic vomiting and diarrhea in birds and livestock.' }
        ],
        livestockTips: [
            {
                keywords: ['cow', 'cattle', 'milk', 'mastitis', 'fmd', 'rumen', 'bloat cow'],
                title: 'Bovine (Cow/Cattle) Care Guidelines',
                advice: 'Ensure clean milking hygiene to prevent Mastitis. Vaccinate for FMD (Foot & Mouth Disease) and Blackquarter every 6 months. Provide adequate mineral mixture and fresh clean water.'
            },
            {
                keywords: ['sheep', 'goat', 'fleece', 'deworming sheep', 'ppr', 'clostridial'],
                title: 'Ovine (Sheep/Goat) Health Protocol',
                advice: 'Follow a strict 3-month deworming cycle. Vaccinate against PPR, Enterotoxaemia, and Sheep Pox. Monitor hoof health regularly to prevent contagious foot rot.'
            }
        ]
    },

    // Process user input
    diagnose(query) {
        const lower = query.toLowerCase().trim();
        if (!lower) return null;

        // 1. Check for Critical Emergencies
        for (const em of this.knowledgeBase.emergencies) {
            if (em.keywords.some(k => lower.includes(k))) {
                return {
                    type: 'emergency',
                    badge: '🚨 CRITICAL EMERGENCY',
                    severity: 'critical',
                    title: em.title,
                    text: em.advice,
                    action: em.action,
                    emergencyUrl: '../pages/care-center.php'
                };
            }
        }

        // 2. Check for Toxic Food Queries
        if (lower.includes('eat') || lower.includes('food') || lower.includes('toxic') || lower.includes('safe to eat') || lower.includes('can dog eat') || lower.includes('chocolate') || lower.includes('grape') || lower.includes('onion')) {
            for (const item of this.knowledgeBase.toxicFoods) {
                if (lower.includes(item.name.toLowerCase().split(' ')[0])) {
                    return {
                        type: 'toxic',
                        badge: '⚠️ TOXIC FOOD WARNING',
                        severity: 'warning',
                        title: `Toxicity Alert: ${item.name}`,
                        text: item.danger,
                        action: 'If your pet recently ingested this in large quantities, induce vomiting ONLY under a vet\'s phone instructions or rush to a clinic.'
                    };
                }
            }
            if (lower.includes('toxic') || lower.includes('poison') || lower.includes('dangerous food')) {
                const listStr = this.knowledgeBase.toxicFoods.map(t => `• **${t.name}**: ${t.danger}`).join('\n');
                return {
                    type: 'toxic_list',
                    badge: '🛡️ Pet Nutrition Safety',
                    severity: 'safe',
                    title: 'Top Toxic Foods to Never Feed Pets',
                    text: listStr,
                    action: 'Always feed balanced commercial pet food or vet-formulated home meals.'
                };
            }
        }

        // 3. Check Livestock specifics
        for (const ls of this.knowledgeBase.livestockTips) {
            if (ls.keywords.some(k => lower.includes(k))) {
                return {
                    type: 'livestock',
                    badge: '🐄 Farm & Livestock Care',
                    severity: 'safe',
                    title: ls.title,
                    text: ls.advice,
                    action: 'Check our Care Center for livestock vaccination schedules and seasonal deworming schedules.'
                };
            }
        }

        // 4. Check Matched Symptoms
        const matchedSymptoms = [];
        for (const s of this.knowledgeBase.symptoms) {
            if (s.keywords.some(k => lower.includes(k))) {
                matchedSymptoms.push(s);
            }
        }

        if (matchedSymptoms.length > 0) {
            const primary = matchedSymptoms[0];
            return {
                type: 'symptom',
                badge: primary.triage.split(':')[0] || '🩺 Symptom Assessment',
                severity: primary.severity,
                title: primary.title,
                text: primary.advice,
                action: primary.triage
            };
        }

        // 5. Vaccination queries
        if (lower.includes('vaccin') || lower.includes('shot') || lower.includes('rabies') || lower.includes('deworm')) {
            return {
                type: 'vaccine',
                badge: '💉 Vaccination & Preventative Care',
                severity: 'safe',
                title: 'Smart Immunization Guidelines',
                text: '• **Dogs**: Core vaccines include Rabies (Annual), DHPP (Distemper, Parvo, Adenovirus, Parainfluenza), and Bordetella for kennel stay.\n• **Cows/Sheep**: FMD (every 6 months), Brucellosis (heifers), and Blackquarter.\n• Keep records updated in PetAssist Vaccinations tab for automated due alerts.',
                action: 'Visit the Vaccinations section to calculate next due dates automatically.'
            };
        }

        // 6. General Care Advice
        return {
            type: 'general',
            badge: '🤖 PetAssist AI Assistant',
            severity: 'safe',
            title: 'Pet Care & Wellness Guidance',
            text: `I've analyzed your question regarding "${query}". For optimal health:\n1. Maintain a high-protein, species-appropriate diet.\n2. Ensure fresh water is always available.\n3. Track daily appetite and energy on the Health page.\n4. If your pet exhibits abnormal behavior or discomfort lasting over 24 hours, schedule an exam with a licensed vet.`,
            action: 'Try asking about symptoms (e.g. "dog vomiting"), toxic foods, vaccinations, or emergency first aid!'
        };
    },

    // Health Log Analyzer (Used in Health & Dashboard pages)
    analyzePetHealth(petName, petType, ageStr, food, activity, behavior) {
        let score = 0;
        const foodScores = { 'Good': 3, 'Normal': 2, 'Low': 1 };
        const actScores = { 'High': 3, 'Medium': 2, 'Low': 1 };
        const behScores = { 'Happy': 3, 'Normal': 2, 'Aggressive': 1 };

        score += foodScores[food] || 2;
        score += actScores[activity] || 2;
        score += behScores[behavior] || 2;

        let status = 'Healthy';
        let badgeClass = 'status-healthy';
        let tips = [];

        if (score >= 7) {
            status = 'Optimal Health & Wellness';
            badgeClass = 'status-healthy';
            tips.push(`🌟 **Excellent Vitality**: ${petName}'s nutrition and activity markers are strong.`);
            tips.push(`💪 **Recommendation**: Continue daily exercise and maintain regular preventative deworming.`);
            if (petType === 'Dog') tips.push(`🎾 **Mental Stimulation**: Incorporate interactive puzzle toys and 30-45 mins daily walking.`);
        } else if (score >= 4) {
            status = 'Moderate / Needs Attention';
            badgeClass = 'status-warning';
            tips.push(`⚠️ **Alert**: Moderate reduction observed in ${petName}'s appetite or activity levels.`);
            if (food === 'Low') tips.push(`🍲 **Dietary Adjustment**: Try warming meals or offering easily digestible single-protein meals.`);
            if (activity === 'Low') tips.push(`🛋️ **Rest & Check**: Monitor joint stiffness or signs of mild fever.`);
            tips.push(`📋 **Action**: Re-evaluate symptoms tomorrow; book a checkup if lethargy persists.`);
        } else {
            status = 'Critical / Veterinary Attention Advised';
            badgeClass = 'status-critical';
            tips.push(`🚨 **Urgent**: Severe drop in combined appetite, activity, or behavioral distress.`);
            tips.push(`🩺 **Immediate Step**: Schedule a veterinary clinical examination to rule out systemic infection or pain.`);
            tips.push(`💧 **Hydration**: Ensure pet is drinking fluids; avoid force-feeding solids.`);
        }

        return {
            petName,
            petType,
            ageStr,
            status,
            badgeClass,
            score,
            tips
        };
    }
};

// UI Controller for Chat Widget
const PetAIChatWidget = {
    isOpen: false,

    init() {
        if (document.getElementById('petai-chat-root')) return;

        // Render Widget HTML
        const root = document.createElement('div');
        root.id = 'petai-chat-root';
        root.innerHTML = `
            <!-- Floating AI Trigger Button -->
            <button id="petai-trigger-btn" class="petai-trigger-btn" aria-label="Open PetAssist AI Assistant">
                <div class="petai-pulse-ring"></div>
                <i class="fas fa-robot"></i>
                <span class="petai-trigger-badge">AI</span>
            </button>

            <!-- Floating Glassmorphic AI Chat Panel -->
            <div id="petai-chat-panel" class="petai-chat-panel" style="display:none;">
                <div class="petai-header">
                    <div class="petai-title-wrap">
                        <div class="petai-avatar">
                            <i class="fas fa-paw"></i>
                        </div>
                        <div>
                            <h4 class="petai-title">PetAssist AI Care Bot</h4>
                            <span class="petai-status-indicator"><span class="petai-dot"></span> Online &bull; Vet Knowledge Base</span>
                        </div>
                    </div>
                    <div class="petai-header-actions">
                        <button id="petai-clear-btn" title="Clear Chat" class="petai-icon-btn"><i class="fas fa-trash-alt"></i></button>
                        <button id="petai-close-btn" title="Close" class="petai-icon-btn"><i class="fas fa-times"></i></button>
                    </div>
                </div>

                <!-- Emergency Banner -->
                <div id="petai-emergency-banner" class="petai-emergency-banner" style="display:none;">
                    <i class="fas fa-exclamation-triangle"></i>
                    <span><strong>Urgent:</strong> Possible emergency detected! <a href="${this.getCareCenterUrl()}">Book Emergency Vet &rarr;</a></span>
                </div>

                <!-- Chat Messages Body -->
                <div id="petai-messages" class="petai-messages">
                    <div class="petai-msg petai-bot-msg">
                        <div class="petai-bubble">
                            <p>🐾 <strong>Hello! I am your PetAssist AI Assistant.</strong></p>
                            <p>I can help evaluate pet symptoms, check toxic foods, provide dietary tips, or offer first aid guidance for dogs, cows, and sheep.</p>
                            <div class="petai-quick-chips">
                                <button class="petai-chip" onclick="PetAIChatWidget.sendChip('My dog is vomiting, what should I do?')">🩺 Dog Vomiting</button>
                                <button class="petai-chip" onclick="PetAIChatWidget.sendChip('Is chocolate toxic for dogs?')">🍫 Toxic Foods</button>
                                <button class="petai-chip" onclick="PetAIChatWidget.sendChip('Vaccination schedule for puppies')">💉 Vaccines</button>
                                <button class="petai-chip" onclick="PetAIChatWidget.sendChip('Cow mastitis and milk care')">🐄 Cow Care</button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Quick Prompts bar -->
                <div class="petai-chips-bar">
                    <button class="petai-mini-chip" onclick="PetAIChatWidget.sendChip('Emergency First Aid advice')">🚨 First Aid</button>
                    <button class="petai-mini-chip" onclick="PetAIChatWidget.sendChip('Dog not eating food')">🍲 Loss of Appetite</button>
                    <button class="petai-mini-chip" onclick="PetAIChatWidget.sendChip('Flea and tick prevention')">🪲 Fleas/Ticks</button>
                </div>

                <!-- Input Box -->
                <form id="petai-form" class="petai-input-form" onsubmit="PetAIChatWidget.handleSubmit(event)">
                    <input type="text" id="petai-input" class="petai-input" placeholder="Ask about symptoms, foods, care..." autocomplete="off" />
                    <button type="submit" class="petai-send-btn" id="petai-send-btn" aria-label="Send message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </form>
            </div>
        `;
        document.body.appendChild(root);

        // Attach Event Listeners
        document.getElementById('petai-trigger-btn').addEventListener('click', () => this.toggle());
        document.getElementById('petai-close-btn').addEventListener('click', () => this.close());
        document.getElementById('petai-clear-btn').addEventListener('click', () => this.clearChat());
    },

    getCareCenterUrl() {
        const isRoot = window.location.pathname.endsWith('index.php') || window.location.pathname.endsWith('index.html') || window.location.pathname.endsWith('/') || !window.location.pathname.includes('/pages/');
        return isRoot ? 'pages/care-center.php' : 'care-center.php';
    },

    toggle() {
        this.isOpen ? this.close() : this.open();
    },

    open() {
        this.isOpen = true;
        const panel = document.getElementById('petai-chat-panel');
        if (panel) {
            panel.style.display = 'flex';
            setTimeout(() => {
                panel.classList.add('petai-visible');
                document.getElementById('petai-input').focus();
            }, 10);
        }
    },

    close() {
        this.isOpen = false;
        const panel = document.getElementById('petai-chat-panel');
        if (panel) {
            panel.classList.remove('petai-visible');
            setTimeout(() => panel.style.display = 'none', 250);
        }
    },

    clearChat() {
        const messages = document.getElementById('petai-messages');
        if (messages) {
            messages.innerHTML = `
                <div class="petai-msg petai-bot-msg">
                    <div class="petai-bubble">
                        <p>🐾 <strong>Chat cleared.</strong> How can I assist with your pets today?</p>
                        <div class="petai-quick-chips">
                            <button class="petai-chip" onclick="PetAIChatWidget.sendChip('Check dog symptoms')">🩺 Check Symptoms</button>
                            <button class="petai-chip" onclick="PetAIChatWidget.sendChip('Toxic foods to avoid')">⚠️ Toxic Foods</button>
                            <button class="petai-chip" onclick="PetAIChatWidget.sendChip('Vaccine schedule')">💉 Vaccines</button>
                        </div>
                    </div>
                </div>
            `;
            document.getElementById('petai-emergency-banner').style.display = 'none';
        }
    },

    sendChip(text) {
        const input = document.getElementById('petai-input');
        if (input) {
            input.value = text;
            this.handleSubmit(new Event('submit'));
        }
    },

    handleSubmit(e) {
        if (e && e.preventDefault) e.preventDefault();
        const input = document.getElementById('petai-input');
        const text = input.value.trim();
        if (!text) return;

        // Append User Message
        this.appendUserMessage(text);
        input.value = '';

        // Show typing indicator
        this.showTypingIndicator();

        // Process AI Logic
        setTimeout(() => {
            this.removeTypingIndicator();
            const result = PetAI.diagnose(text);
            this.appendBotResponse(result);
        }, 550);
    },

    appendUserMessage(text) {
        const messages = document.getElementById('petai-messages');
        const div = document.createElement('div');
        div.className = 'petai-msg petai-user-msg';
        div.innerHTML = `<div class="petai-bubble"><p>${this.escapeHtml(text)}</p></div>`;
        messages.appendChild(div);
        this.scrollToBottom();
    },

    appendBotResponse(result) {
        const messages = document.getElementById('petai-messages');
        const banner = document.getElementById('petai-emergency-banner');

        if (result.severity === 'critical') {
            banner.style.display = 'flex';
        }

        const div = document.createElement('div');
        div.className = 'petai-msg petai-bot-msg';

        let severityBadge = '';
        if (result.severity === 'critical') {
            severityBadge = `<span class="petai-badge petai-badge-critical"><i class="fas fa-ambulance"></i> ${result.badge}</span>`;
        } else if (result.severity === 'warning') {
            severityBadge = `<span class="petai-badge petai-badge-warning"><i class="fas fa-exclamation-circle"></i> ${result.badge}</span>`;
        } else {
            severityBadge = `<span class="petai-badge petai-badge-safe"><i class="fas fa-check-circle"></i> ${result.badge}</span>`;
        }

        const formattedText = result.text.replace(/\n/g, '<br/>').replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>');

        div.innerHTML = `
            <div class="petai-bubble">
                <div class="petai-bubble-header">
                    ${severityBadge}
                </div>
                <h5 class="petai-response-title">${result.title}</h5>
                <p class="petai-response-body">${formattedText}</p>
                ${result.action ? `<div class="petai-action-box"><i class="fas fa-lightbulb"></i> <span>${result.action}</span></div>` : ''}
                ${result.severity === 'critical' ? `<a href="${this.getCareCenterUrl()}" class="btn-emergency-action"><i class="fas fa-phone-alt"></i> Connect with Care Center</a>` : ''}
            </div>
        `;

        messages.appendChild(div);
        this.scrollToBottom();
    },

    showTypingIndicator() {
        const messages = document.getElementById('petai-messages');
        const indicator = document.createElement('div');
        indicator.id = 'petai-typing';
        indicator.className = 'petai-msg petai-bot-msg petai-typing-indicator';
        indicator.innerHTML = `
            <div class="petai-bubble">
                <span class="petai-dot-typing"></span>
                <span class="petai-dot-typing"></span>
                <span class="petai-dot-typing"></span>
            </div>
        `;
        messages.appendChild(indicator);
        this.scrollToBottom();
    },

    removeTypingIndicator() {
        const ind = document.getElementById('petai-typing');
        if (ind) ind.remove();
    },

    scrollToBottom() {
        const messages = document.getElementById('petai-messages');
        if (messages) messages.scrollTop = messages.scrollHeight;
    },

    escapeHtml(str) {
        return str.replace(/&/g, "&amp;").replace(/</g, "&lt;").replace(/>/g, "&gt;").replace(/"/g, "&quot;").replace(/'/g, "&#039;");
    }
};

// Global helper for opening pet health diagnosis modal
window.showPetAIAnalysisModal = function(petName, petType, ageStr, food, activity, behavior) {
    const analysis = PetAI.analyzePetHealth(petName, petType, ageStr, food, activity, behavior);
    
    // Remove existing modal if any
    const existing = document.getElementById('petai-insight-modal');
    if (existing) existing.remove();

    const modal = document.createElement('div');
    modal.id = 'petai-insight-modal';
    modal.className = 'modal modal-open';
    modal.innerHTML = `
        <div class="modal-overlay" onclick="document.getElementById('petai-insight-modal').remove()"></div>
        <div class="modal-content modal-ai-insight">
            <div class="modal-header">
                <div class="modal-ai-title">
                    <i class="fas fa-brain" style="color: var(--primary); font-size: 1.4rem;"></i>
                    <h3>AI Pet Health Insight &bull; ${analysis.petName}</h3>
                </div>
                <button class="modal-close" onclick="document.getElementById('petai-insight-modal').remove()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body">
                <div class="ai-insight-hero">
                    <div class="ai-score-ring">
                        <span class="ai-score-num">${analysis.score}/9</span>
                        <span class="ai-score-label">Wellness Score</span>
                    </div>
                    <div class="ai-score-details">
                        <span class="badge ${analysis.badgeClass}">${analysis.status}</span>
                        <p class="text-muted" style="margin-top: 6px;">Profile: ${analysis.petType} &bull; Age: ${analysis.ageStr}</p>
                        <p class="text-muted">Metrics: Food (${food}) &bull; Activity (${activity}) &bull; Behavior (${behavior})</p>
                    </div>
                </div>

                <div class="ai-recommendations-list">
                    <h4><i class="fas fa-stethoscope"></i> AI Vet Insights & Action Plan</h4>
                    <ul>
                        ${analysis.tips.map(t => `<li>${t.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')}</li>`).join('')}
                    </ul>
                </div>

                <div class="ai-insight-footer">
                    <button class="btn btn-secondary" onclick="document.getElementById('petai-insight-modal').remove()">Close</button>
                    <button class="btn btn-primary" onclick="document.getElementById('petai-insight-modal').remove(); PetAIChatWidget.open(); PetAIChatWidget.sendChip('Tell me more care tips for a ${analysis.petType} with ${food} food and ${activity} activity')">
                        <i class="fas fa-comments"></i> Ask Pet AI More
                    </button>
                </div>
            </div>
        </div>
    `;
    document.body.appendChild(modal);
};

// Auto-initialize when DOM is ready
document.addEventListener('DOMContentLoaded', () => {
    PetAIChatWidget.init();
});
