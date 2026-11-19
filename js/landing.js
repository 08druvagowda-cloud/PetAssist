/**
 * PetAssist — Interactive Web Application Landing Controller
 * Powers the live browser workspace preview, vaccination filters,
 * embedded PetAI triage console, and 3-factor Health Scoring sandbox.
 */

document.addEventListener('DOMContentLoaded', () => {
    initLinkRouter();
    initBrowserWorkspaceTabs();
    initPreviewHealthPresets();
    initPreviewVaccineFilter();
    initPreviewAITriage();
    initHealthSandbox();
    initDemoCopy();
    initAITriggers();
});

/* ---- Universal Web App Link Router (Safe on file://, localhost, Vercel & PHP) ---- */
function initLinkRouter() {
    const isFile = window.location.protocol === 'file:';
    const isPhp = window.location.pathname.endsWith('.php');
    const isLocalServer = window.location.hostname === 'localhost' || window.location.hostname === '127.0.0.1';
    const isStaticHost = isFile || isLocalServer || window.location.hostname.endsWith('.github.io');

    document.querySelectorAll('a[data-route]').forEach(link => {
        const route = link.getAttribute('data-route');
        if (isPhp) {
            link.setAttribute('href', route === 'home' ? 'index.php' : `pages/${route}.php`);
        } else if (isStaticHost) {
            link.setAttribute('href', route === 'home' ? 'index.html' : `pages/${route}.html`);
        } else {
            // Vercel / Netlify cleanUrls
            link.setAttribute('href', route === 'home' ? '/' : `pages/${route}.html`);
        }
    });

    // Also fallback any legacy /pages/ links if opened on local file or simple static server
    if (isStaticHost && !isPhp) {
        document.querySelectorAll('a[href^="/pages/"]').forEach(link => {
            const clean = link.getAttribute('href').replace(/^\/pages\//, '').replace(/\.(html|php)$/, '');
            link.setAttribute('href', `pages/${clean}.html`);
        });
        document.querySelectorAll('a[href="/"]').forEach(link => {
            link.setAttribute('href', 'index.html');
        });
    }
}

/* ---- Interactive Browser Window Tabs & Dynamic Address Bar ---- */
function initBrowserWorkspaceTabs() {
    const tabs = document.querySelectorAll('.lp-app-tab[data-pane]');
    const panes = document.querySelectorAll('.lp-pane');
    const urlRouteEl = document.getElementById('lpUrlRoute');

    if (!tabs.length) return;

    tabs.forEach(tab => {
        tab.addEventListener('click', () => {
            const targetId = tab.getAttribute('data-pane');
            const routePath = tab.getAttribute('data-route') || '/dashboard';

            tabs.forEach(t => t.classList.remove('active'));
            panes.forEach(p => p.classList.remove('active'));

            tab.classList.add('active');
            const targetPane = document.getElementById(targetId);
            if (targetPane) targetPane.classList.add('active');
            if (urlRouteEl) urlRouteEl.textContent = routePath;
        });
    });
}

/* ---- Mini Health Engine Presets inside Browser Preview ---- */
function initPreviewHealthPresets() {
    const presets = {
        healthy: {
            pet: 'Bruno • Golden Retriever (Dog)',
            score: 9,
            status: 'Healthy',
            pillClass: 'lp-score-ok',
            foodLabel: 'Normal (3/3)', foodPct: 100,
            actLabel: 'Active (3/3)', actPct: 100,
            behLabel: 'Playful (3/3)', behPct: 100,
            note: 'Optimal daily baseline. Maintain regular hydration, balanced nutrition, and scheduled Rabies booster.'
        },
        warning: {
            pet: 'Gauri • Gir Cattle (Cow)',
            score: 6,
            status: 'Warning',
            pillClass: 'lp-score-warn',
            foodLabel: 'Reduced (2/3)', foodPct: 66,
            actLabel: 'Low (2/3)', actPct: 66,
            behLabel: 'Quiet (2/3)', behPct: 66,
            note: 'Moderate wellness dip detected. Monitor rumen appetite, check temperature, and inspect for early mastitis signs.'
        },
        critical: {
            pet: 'Milo • Persian (Cat)',
            score: 3,
            status: 'Critical',
            pillClass: 'lp-score-crit',
            foodLabel: 'None (1/3)', foodPct: 33,
            actLabel: 'Lethargic (1/3)', actPct: 33,
            behLabel: 'Distressed (1/3)', behPct: 33,
            note: 'Critical threshold triggered (<=4/9). Immediate clinical triage & 24/7 veterinary evaluation recommended.'
        }
    };

    const buttons = document.querySelectorAll('.lp-chip-btn[data-health-preset]');
    if (!buttons.length) return;

    const applyPreset = (key) => {
        const p = presets[key];
        if (!p) return;

        const petEl = document.getElementById('pvHealthPet');
        const scoreEl = document.getElementById('pvHealthScore');
        const badgeEl = document.getElementById('pvHealthBadge');
        const foodLbl = document.getElementById('pvFoodVal');
        const foodBar = document.getElementById('pvFoodBar');
        const actLbl = document.getElementById('pvActVal');
        const actBar = document.getElementById('pvActBar');
        const behLbl = document.getElementById('pvBehVal');
        const behBar = document.getElementById('pvBehBar');
        const noteEl = document.getElementById('pvHealthNote');

        if (petEl) petEl.textContent = p.pet;
        if (scoreEl) scoreEl.textContent = `${p.score}/9`;
        if (badgeEl) {
            badgeEl.textContent = p.status;
            badgeEl.className = `lp-score-pill ${p.pillClass}`;
        }
        if (foodLbl) foodLbl.textContent = p.foodLabel;
        if (foodBar) foodBar.style.width = `${p.foodPct}%`;
        if (actLbl) actLbl.textContent = p.actLabel;
        if (actBar) actBar.style.width = `${p.actPct}%`;
        if (behLbl) behLbl.textContent = p.behLabel;
        if (behBar) behBar.style.width = `${p.behPct}%`;
        if (noteEl) noteEl.textContent = p.note;
    };

    buttons.forEach(btn => {
        btn.addEventListener('click', () => {
            buttons.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            applyPreset(btn.getAttribute('data-health-preset'));
        });
    });

    applyPreset('healthy');
}

/* ---- Vaccination Table Filter inside Browser Preview ---- */
function initPreviewVaccineFilter() {
    const filterBtns = document.querySelectorAll('.lp-chip-btn[data-vac-filter]');
    const rows = document.querySelectorAll('#pvVaccineTable tbody tr');
    if (!filterBtns.length || !rows.length) return;

    filterBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            filterBtns.forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            const filter = btn.getAttribute('data-vac-filter');

            rows.forEach(row => {
                const status = row.getAttribute('data-status');
                const species = row.getAttribute('data-species');
                const show =
                    filter === 'all' ||
                    (filter === 'due' && status === 'due') ||
                    (filter === 'ok' && status === 'ok') ||
                    (filter === 'livestock' && species === 'livestock');
                row.style.display = show ? '' : 'none';
            });
        });
    });
}

/* ---- Embedded PetAI Heuristic Console inside Browser Preview ---- */
function initPreviewAITriage() {
    const input = document.getElementById('pvTriageInput');
    const sendBtn = document.getElementById('pvTriageSend');
    const output = document.getElementById('pvTriageOutput');
    const chips = document.querySelectorAll('.lp-chip-btn[data-triage-query]');

    if (!output) return;

    const runDiagnosis = (query) => {
        const cleanQuery = (query || '').trim();
        if (!cleanQuery) return;

        let result = null;
        if (typeof PetAI !== 'undefined' && typeof PetAI.diagnose === 'function') {
            result = PetAI.diagnose(cleanQuery);
        }

        if (!result) {
            output.innerHTML = `<p style="color:var(--text-secondary);font-size:0.84rem;">PetAI engine ready — enter any symptom, food item, or livestock care query.</p>`;
            return;
        }

        const pillClass =
            result.severity === 'critical'
                ? 'lp-score-crit'
                : result.severity === 'warning'
                    ? 'lp-score-warn'
                    : 'lp-score-ok';

        const safeQuery = cleanQuery.replace(/</g, '&lt;').replace(/>/g, '&gt;');
        const formattedText = result.text
            .replace(/\n/g, '<br>')
            .replace(/\*\*(.*?)\*\*/g, '<strong style="color:#fff;">$1</strong>');

        output.innerHTML = `
            <div style="display:flex;align-items:center;justify-content:space-between;gap:8px;flex-wrap:wrap;margin-bottom:10px;">
                <span style="font-family:var(--font-mono);font-size:0.72rem;color:var(--text-muted);">Query: "${safeQuery}"</span>
                <span class="lp-score-pill ${pillClass}">${result.badge || 'TRIAGE RESULT'}</span>
            </div>
            <h4 style="font-size:0.94rem;color:#fff;margin-bottom:6px;">${result.title}</h4>
            <p style="font-size:0.82rem;color:var(--text-secondary);line-height:1.55;margin-bottom:10px;">${formattedText}</p>
            ${result.action ? `<div style="font-size:0.78rem;color:var(--secondary);background:rgba(0,210,211,0.08);border:1px solid rgba(0,210,211,0.2);padding:8px 10px;border-radius:8px;"><i class="fas fa-bolt"></i> ${result.action}</div>` : ''}
        `;
    };

    chips.forEach(chip => {
        chip.addEventListener('click', () => {
            const q = chip.getAttribute('data-triage-query');
            if (input) input.value = q;
            runDiagnosis(q);
        });
    });

    if (sendBtn && input) {
        sendBtn.addEventListener('click', () => runDiagnosis(input.value));
        input.addEventListener('keydown', (e) => {
            if (e.key === 'Enter') {
                e.preventDefault();
                runDiagnosis(input.value);
            }
        });
    }

    // Initial live demo output
    runDiagnosis('Can my dog eat chocolate?');
}

/* ---- Live Interactive 3-Factor Health & Mood Scoring Sandbox ---- */
function initHealthSandbox() {
    const state = {
        species: 'Dog',
        food: 'Normal',
        activity: 'Active',
        behavior: 'Playful'
    };

    const scoreMap = {
        food: { Normal: 3, Reduced: 2, None: 1 },
        activity: { Active: 3, Low: 2, Lethargic: 1 },
        behavior: { Playful: 3, Quiet: 2, Aggressive: 1 }
    };

    const ring = document.getElementById('sbRingProgress');
    const scoreText = document.getElementById('sbScoreText');
    const statusBadge = document.getElementById('sbStatusBadge');
    const summaryText = document.getElementById('sbSummaryText');
    const recList = document.getElementById('sbRecList');
    const askAiBtn = document.getElementById('sbAskAiBtn');

    if (!ring || !scoreText) return;

    const circumference = 2 * Math.PI * 42; // r=42 -> ~263.89
    ring.style.strokeDasharray = circumference;

    const render = () => {
        const total =
            (scoreMap.food[state.food] || 2) +
            (scoreMap.activity[state.activity] || 2) +
            (scoreMap.behavior[state.behavior] || 2);

        const offset = circumference - (total / 9) * circumference;
        ring.style.strokeDashoffset = offset;
        scoreText.textContent = `${total}/9`;

        let color = '#00C853';
        let pillClass = 'lp-score-ok';
        let label = 'Healthy Baseline';

        if (total <= 3) {
            color = '#FF5252';
            pillClass = 'lp-score-crit';
            label = 'Critical — Vet Attention Needed';
        } else if (total <= 6) {
            color = '#FFB300';
            pillClass = 'lp-score-warn';
            label = 'Warning — Monitor Closely';
        }

        ring.style.stroke = color;
        if (statusBadge) {
            statusBadge.textContent = label;
            statusBadge.className = `lp-score-pill ${pillClass}`;
        }

        if (summaryText) {
            summaryText.textContent = `${state.species} • Food: ${state.food} • Activity: ${state.activity} • Behavior: ${state.behavior}`;
        }

        const aiFoodMap = { Normal: 'Good', Reduced: 'Normal', None: 'Low' };
        const aiActMap = { Active: 'High', Low: 'Medium', Lethargic: 'Low' };
        const aiBehMap = { Playful: 'Happy', Quiet: 'Normal', Aggressive: 'Aggressive' };

        let tips = [];
        if (typeof PetAI !== 'undefined' && typeof PetAI.analyzePetHealth === 'function') {
            const analysis = PetAI.analyzePetHealth(
                state.species,
                state.species,
                'Adult',
                aiFoodMap[state.food] || 'Normal',
                aiActMap[state.activity] || 'Medium',
                aiBehMap[state.behavior] || 'Normal'
            );
            tips = analysis.tips || [];
        } else {
            tips = [
                'Maintain consistent hydration and scheduled veterinary checkups.',
                'Track daily food and mobility logs to detect early behavioral shifts.'
            ];
        }

        if (recList) {
            recList.innerHTML = tips
                .slice(0, 3)
                .map(tip => `<div class="lp-rec-item">${tip.replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')}</div>`)
                .join('');
        }
    };

    document.querySelectorAll('[data-sb-group]').forEach(btn => {
        btn.addEventListener('click', () => {
            const group = btn.getAttribute('data-sb-group');
            const val = btn.getAttribute('data-sb-val');
            state[group] = val;

            document.querySelectorAll(`[data-sb-group="${group}"]`).forEach(b => b.classList.remove('active'));
            btn.classList.add('active');
            render();
        });
    });

    if (askAiBtn) {
        askAiBtn.addEventListener('click', () => {
            if (typeof PetAIChatWidget !== 'undefined') {
                PetAIChatWidget.open();
                PetAIChatWidget.sendChip(
                    `Care advice for my ${state.species} with ${state.food} food intake, ${state.activity} activity, and ${state.behavior} behavior`
                );
            }
        });
    }

    render();
}

/* ---- Global Launch PetAI Chat Triggers ---- */
function initAITriggers() {
    document.querySelectorAll('[data-open-petai]').forEach(btn => {
        btn.addEventListener('click', (e) => {
            e.preventDefault();
            if (typeof PetAIChatWidget !== 'undefined') {
                PetAIChatWidget.open();
            }
        });
    });
}

/* ---- Copy Demo Credentials ---- */
function initDemoCopy() {
    const copyBtn = document.getElementById('lpCopyDemo');
    if (!copyBtn) return;

    copyBtn.addEventListener('click', async () => {
        try {
            await navigator.clipboard.writeText('admin@petassist.com');
            const prev = copyBtn.innerHTML;
            copyBtn.innerHTML = '<i class="fas fa-check"></i> Copied';
            setTimeout(() => { copyBtn.innerHTML = prev; }, 1800);
        } catch {
            copyBtn.textContent = 'admin@petassist.com';
        }
    });
}
