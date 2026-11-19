/**
 * PetAssist LocalStorage Database Engine
 * Simulates a backend API using the browser's local memory.
 */

const Store = {
    init() {
        if (!localStorage.getItem('users')) localStorage.setItem('users', JSON.stringify([]));
        if (!localStorage.getItem('pets')) localStorage.setItem('pets', JSON.stringify([]));
        if (!localStorage.getItem('vaccinations')) localStorage.setItem('vaccinations', JSON.stringify([]));
        if (!localStorage.getItem('health_logs')) localStorage.setItem('health_logs', JSON.stringify([]));
        if (!localStorage.getItem('appointments')) localStorage.setItem('appointments', JSON.stringify([]));
        if (!localStorage.getItem('support_requests')) localStorage.setItem('support_requests', JSON.stringify([]));
        if (!localStorage.getItem('lost_found')) localStorage.setItem('lost_found', JSON.stringify([]));
        
        // Seed default accounts
        const users = JSON.parse(localStorage.getItem('users')) || [];
        let usersUpdated = false;
        if (!users.find(u => u.email === 'admin@petassist.com')) {
            users.push({
                id: 1,
                name: 'Administrator',
                email: 'admin@petassist.com',
                password: 'admin123',
                phone: '9999999999',
                role: 'admin'
            });
            usersUpdated = true;
        }
        if (!users.find(u => u.email === 'user@petassist.com')) {
            users.push({
                id: 2,
                name: 'Pet Parent',
                email: 'user@petassist.com',
                password: 'user123',
                phone: '9876543210',
                role: 'user'
            });
            usersUpdated = true;
        }
        if (usersUpdated) {
            localStorage.setItem('users', JSON.stringify(users));
        }

        // Seed vaccine types
        if (!localStorage.getItem('vaccine_types')) {
            localStorage.setItem('vaccine_types', JSON.stringify([
                { id: 1, name: 'Rabies', interval: 365 },
                { id: 2, name: 'Parvovirus', interval: 365 },
                { id: 3, name: 'Bordetella', interval: 180 },
            ]));
        }

        // Seed default demo pets if empty
        const pets = JSON.parse(localStorage.getItem('pets')) || [];
        if (pets.length === 0) {
            pets.push({
                id: 101,
                user_id: 1,
                pet_name: 'Bruno',
                name: 'Bruno',
                species: 'Dog',
                breed: 'Golden Retriever',
                dob: '2022-04-12',
                weight: '28.5',
                created_at: new Date().toISOString()
            });
            pets.push({
                id: 102,
                user_id: 1,
                pet_name: 'Gauri',
                name: 'Gauri',
                species: 'Cow',
                breed: 'Gir Cattle',
                dob: '2021-08-20',
                weight: '380',
                created_at: new Date().toISOString()
            });
            localStorage.setItem('pets', JSON.stringify(pets));
        }

        // Seed default appointments if empty
        const appts = JSON.parse(localStorage.getItem('appointments')) || [];
        if (appts.length === 0) {
            appts.push({
                id: 201,
                user_id: 1,
                pet_id: 101,
                issue: 'Annual Rabies booster & routine physical exam',
                service_type: 'Vaccination Consultation',
                date: '2026-11-28',
                time: '10:30 AM',
                status: 'Confirmed',
                created_at: new Date().toISOString()
            });
            localStorage.setItem('appointments', JSON.stringify(appts));
        }

        // Seed default support requests if empty
        const support = JSON.parse(localStorage.getItem('support_requests')) || [];
        if (support.length === 0) {
            support.push({
                id: 301,
                user_id: 1,
                issue_type: 'Medical Question',
                subject: 'Dietary Guidance for Senior Retriever',
                message: 'Consultation request regarding joint supplements and high-protein pasture feed.',
                status: 'Resolved',
                created_at: new Date().toISOString()
            });
            localStorage.setItem('support_requests', JSON.stringify(support));
        }
    },

    // --- Auth ---
    register(name, email, password, phone) {
        const users = JSON.parse(localStorage.getItem('users')) || [];
        const cleanEmail = (email || '').toLowerCase().trim();
        if (users.find(u => u.email.toLowerCase() === cleanEmail)) {
            return { error: 'An account with this email already exists' };
        }
        
        const newUser = {
            id: Date.now(),
            name: (name || 'Pet Parent').trim(),
            email: cleanEmail,
            password: password,
            phone: (phone || '').trim(),
            role: 'user'
        };
        users.push(newUser);
        localStorage.setItem('users', JSON.stringify(users));
        // Auto sign-in newly registered user
        sessionStorage.setItem('current_user', JSON.stringify(newUser));
        return { success: true, user: newUser };
    },

    login(email, password) {
        const users = JSON.parse(localStorage.getItem('users')) || [];
        const cleanEmail = (email || '').toLowerCase().trim();
        const user = users.find(u => u.email.toLowerCase() === cleanEmail && u.password === password);
        if (user) {
            sessionStorage.setItem('current_user', JSON.stringify(user));
            return { success: true, user };
        }
        return { error: 'Invalid email or password' };
    },

    logout() {
        sessionStorage.removeItem('current_user');
        if (window.location.pathname.includes('/pages/')) {
            window.location.href = '../index.html';
        } else {
            window.location.href = 'index.html';
        }
    },

    getCurrentUser() {
        try {
            return JSON.parse(sessionStorage.getItem('current_user'));
        } catch (e) {
            return null;
        }
    },

    // --- Helpers ---
    save(table, data) {
        const records = JSON.parse(localStorage.getItem(table)) || [];
        data.id = Date.now();
        const user = this.getCurrentUser();
        data.user_id = user ? user.id : 1;
        data.created_at = new Date().toISOString();
        records.push(data);
        localStorage.setItem(table, JSON.stringify(records));
        return data;
    },

    getAll(table) {
        const user = this.getCurrentUser();
        let records = JSON.parse(localStorage.getItem(table)) || [];
        if (user && user.role !== 'admin') {
            // Filter by user ID except for lost/found and standard lookups
            if (['pets', 'appointments', 'support_requests', 'health_logs'].includes(table)) {
                records = records.filter(r => r.user_id === user.id);
            }
        }
        return records;
    },

    delete(table, id) {
        let records = JSON.parse(localStorage.getItem(table)) || [];
        records = records.filter(r => r.id !== id);
        localStorage.setItem(table, JSON.stringify(records));
    }
};

// Initialize DB structure automatically when included
Store.init();

// Global utility functions for the UI
function calculateAge(dob) {
    const diff = new Date(Date.now() - new Date(dob).getTime());
    return Math.abs(diff.getUTCFullYear() - 1970) + " years, " + diff.getUTCMonth() + " months";
}

// Ensure pages are protected
const path = window.location.pathname;
// Support both .html URLs (local) and clean URLs (Vercel/Netlify with cleanUrls)
const isPublicPage = 
    path.endsWith('index.html') || path === '/' || path === '' || path === '/index' ||
    path.endsWith('login.html') || path.endsWith('/login') || path.includes('login') ||
    path.endsWith('register.html') || path.endsWith('/register') || path.includes('register') ||
    path.includes('care-center') ||
    path.includes('guide') ||
    path.includes('presentation') ||
    path.includes('lost-found');

if (!Store.getCurrentUser() && !isPublicPage) {
    // Only redirect to login from private app pages (dashboard, health, pets, vaccinations, admin)
    const loginTarget = window.location.pathname.includes('/pages/') ? 'login.html' : 'pages/login.html';
    window.location.href = loginTarget;
}
