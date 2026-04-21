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
        
        // Seed default admin
        const users = JSON.parse(localStorage.getItem('users'));
        if (!users.find(u => u.email === 'admin@petassist.com')) {
            users.push({
                id: Date.now(),
                name: 'Admin',
                email: 'admin@petassist.com',
                password: 'admin123',
                phone: '9999999999',
                role: 'admin'
            });
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
    },

    // --- Auth ---
    register(name, email, password, phone) {
        const users = JSON.parse(localStorage.getItem('users'));
        if (users.find(u => u.email === email)) return { error: 'Email already exists' };
        
        const newUser = { id: Date.now(), name, email, password, phone, role: 'user' };
        users.push(newUser);
        localStorage.setItem('users', JSON.stringify(users));
        return { success: true };
    },

    login(email, password) {
        const users = JSON.parse(localStorage.getItem('users'));
        const user = users.find(u => u.email === email && u.password === password);
        if (user) {
            sessionStorage.setItem('current_user', JSON.stringify(user));
            return { success: true };
        }
        return { error: 'Invalid email or password' };
    },

    logout() {
        sessionStorage.removeItem('current_user');
        window.location.href = '../index.html';
    },

    getCurrentUser() {
        return JSON.parse(sessionStorage.getItem('current_user'));
    },

    // --- Helpers ---
    save(table, data) {
        const records = JSON.parse(localStorage.getItem(table));
        data.id = Date.now();
        data.user_id = this.getCurrentUser().id;
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
        let records = JSON.parse(localStorage.getItem(table));
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
const isPublicPage = path.endsWith('index.html') || path.endsWith('login.html') || path.endsWith('register.html') || path === '/' || path === '';

if (!Store.getCurrentUser() && !isPublicPage) {
    window.location.href = '../pages/login.html';
}
