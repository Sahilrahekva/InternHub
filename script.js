// document.addEventListener('DOMContentLoaded', function() {
//     let allPrograms = [];  // Store fetched data

//     // Fetch data from PHP API
//     fetch('api.php')
//         .then(response => response.json())
//         .then(data => {
//             if (data.success) {
//                 allPrograms = data.data;
//                 displayPrograms(allPrograms);
//             } else {
//                 document.getElementById('programs-list').innerHTML = '<p>Error loading data: ' + data.error + '</p>';
//             }
//         })
//         .catch(error => {
//             document.getElementById('programs-list').innerHTML = '<p>Error fetching data: ' + error + '</p>';
//         });

//     // Display programs
//     function displayPrograms(programs) {
//         const container = document.getElementById('programs-list');
//         if (programs.length === 0) {
//             container.innerHTML = '<p>No programs available right now.</p>';
//             return;
//         }

//         container.innerHTML = programs.map(program => `
//             <div class="program-card">
//                 <h3>${program.title}</h3>
//                 <div class="company">${program.company}</div>
//                 <div>Type: ${program.type}</div>
//                 <div class="status status-${program.status.toLowerCase().replace(' ', '-')}">${program.status}</div>
//                 <p>${program.description}</p>
//                 <div class="dates">Dates: ${program.start_date} to ${program.end_date}</div>
//             </div>
//         `).join('');
//     }

//     // Filter events
//     document.getElementById('typeFilter').addEventListener('change', filterPrograms);
//     document.getElementById('statusFilter').addEventListener('change', filterPrograms);

//     function filterPrograms() {
//         const typeFilter = document.getElementById('typeFilter').value;
//         const statusFilter = document.getElementById('statusFilter').value;

//         let filtered = allPrograms.filter(program => {
//             const typeMatch = !typeFilter || program.type === typeFilter;
//             const statusMatch = !statusFilter || program.status === statusFilter;
//             return typeMatch && statusMatch;
//         });

//         displayPrograms(filtered);
//     }
// });




















document.addEventListener('DOMContentLoaded', function() {
    let allPrograms = [];
    let currentTab = 'all';

    // Mobile Navigation Toggle
    const navToggle = document.querySelector('.nav-toggle');
    const navMenu = document.querySelector('.nav-menu');
    if (navToggle && navMenu) {
        navToggle.addEventListener('click', () => {
            navMenu.classList.toggle('active');
        });
    }

    // Smooth Scrolling for Nav Links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', (e) => {
            e.preventDefault();
            const targetId = link.getAttribute('href').substring(1);
            const targetSection = document.getElementById(targetId);
            if (targetSection) {
                targetSection.scrollIntoView({ behavior: 'smooth' });
            }
            // Update active nav
            document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
            link.classList.add('active');
            // Close mobile menu if open
            navMenu.classList.remove('active');
        });
    });

    // Fetch data from PHP API
    function loadPrograms(tab = 'all') {
        currentTab = tab;
        const container = document.getElementById('programs-list');
        container.innerHTML = '<p class="loading">Loading professional opportunities... (Powered by API)</p>';

        fetch('api.php')
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    allPrograms = data.data;
                    filterAndDisplay();
                } else {
                    container.innerHTML = '<p class="loading error">Error loading data: ' + (data.error || 'Unknown error') + '</p>';
                }
            })
            .catch(error => {
                container.innerHTML = '<p class="loading error">Error fetching data: ' + error.message + '. Please try again later.</p>';
            });
    }

    // Display programs in the grid
    function displayPrograms(programs) {
        const container = document.getElementById('programs-list');
        if (programs.length === 0) {
            container.innerHTML = '<p class="loading no-results">No programs match your filters. Try adjusting your search!</p>';
            return;
        }

        container.innerHTML = programs.map(program => `
            <div class="program-card">
                <h3>${program.title}</h3>
                <div class="company">${program.company}</div>
                <div>Type: <span class="type-badge ${program.type}">${program.type.charAt(0).toUpperCase() + program.type.slice(1)}</span></div>
                <div class="status status-${program.status.toLowerCase().replace(/ /g, '-')}">${program.status}</div>
                <p>${program.description}</p>
                <div class="dates"><i class="fas fa-calendar"></i> ${program.start_date} to ${program.end_date}</div>
            </div>
        `).join('');
    }

    // Tab Switching (exposed globally for onclick handlers)
    window.switchTab = function(tab) {
        currentTab = tab;
        // Update active tab button
        document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
        event.target.classList.add('active');
        // Reload data if needed (for freshness), then filter
        loadPrograms(tab);
    };

    // Search and Filter Logic
    function filterAndDisplay() {
        const searchTerm = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase() : '';
        const typeFilter = document.getElementById('typeFilter') ? document.getElementById('typeFilter').value : '';
        const statusFilter = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';

        let filtered = allPrograms.filter(program => {
            // Search match (company or title)
            const searchMatch = program.company.toLowerCase().includes(searchTerm) || 
                                program.title.toLowerCase().includes(searchTerm) ||
                                program.description.toLowerCase().includes(searchTerm);

            // Type filter from dropdown
            const typeMatch = !typeFilter || program.type === typeFilter;

            // Status filter
            const statusMatch = !statusFilter || program.status === statusFilter;

            // Tab-based type filter (overrides dropdown if tab is specific)
            const tabTypeMatch = currentTab === 'all' || 
                                 (currentTab === 'internships' && program.type === 'internship') ||
                                 (currentTab === 'certifications' && program.type === 'certification');

            return searchMatch && typeMatch && statusMatch && tabTypeMatch;
        });

        displayPrograms(filtered);
    }

    // Event Listeners for Filters and Search
    const searchInput = document.getElementById('searchInput');
    const typeFilter = document.getElementById('typeFilter');
    const statusFilter = document.getElementById('statusFilter');

    if (searchInput) {
        searchInput.addEventListener('input', filterAndDisplay);
    }
    if (typeFilter) {
        typeFilter.addEventListener('change', filterAndDisplay);
    }
    if (statusFilter) {
        statusFilter.addEventListener('change', filterAndDisplay);
    }

    // Initial Load
    loadPrograms('all');

    // CTA Button Handler (if clicked, reload all)
    const ctaButton = document.querySelector('.cta-button');
    if (ctaButton) {
        ctaButton.addEventListener('click', () => {
            loadPrograms('all');
            // Scroll to programs section
            document.querySelector('.tabs-section').scrollIntoView({ behavior: 'smooth' });
        });
    }
});

// 111


// document.addEventListener('DOMContentLoaded', function() {
//     let allPrograms = [];
//     let currentTab = 'all';
//     let isLoggedIn = false;
//     let profileCompleted = false;

//     // Check login status on load
//     checkAuth();

//     // Auth functions
//     function checkAuth() {
//         fetch('api.php?action=check_auth', { method: 'GET' })
//             .then(response => response.json())
//             .then(data => {
//                 if (data.logged_in) {
//                     isLoggedIn = true;
//                     document.getElementById('auth-section').style.display = 'none';
//                     document.getElementById('main-app').style.display = 'block';
//                     checkProfile();
//                 } else {
//                     document.getElementById('auth-section').style.display = 'block';
//                     document.getElementById('main-app').style.display = 'none';
//                 }
//             })
//             .catch(() => {
//                 document.getElementById('auth-section').style.display = 'block';
//                 document.getElementById('main-app').style.display = 'none';
//             });
//     }

//     function toggleAuth() {
//         const title = document.getElementById('auth-title');
//         const emailField = document.getElementById('email');
//         const toggleText = document.getElementById('auth-toggle');
//         if (title.textContent === 'Login') {
//             title.textContent = 'Sign Up';
//             emailField.style.display = 'block';
//             toggleText.innerHTML = 'Already have an account? <a href="#" onclick="toggleAuth()">Login</a>';
//         } else {
//             title.textContent = 'Login';
//             emailField.style.display = 'none';
//             toggleText.innerHTML = 'Don\'t have an account? <a href="#" onclick="toggleAuth()">Sign Up</a>';
//         }
//     }

//     document.getElementById('auth-form').addEventListener('submit', function(e) {
//         e.preventDefault();
//         const username = document.getElementById('username').value;
//         const email = document.getElementById('email').value;
//         const password = document.getElementById('password').value;
//         const isSignup = document.getElementById('auth-title').textContent === 'Sign Up';

//         fetch('api.php?action=' + (isSignup ? 'signup' : 'login'), {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: `username=${username}&email=${email}&password=${password}`
//         })
//         .then(response => response.json())
//         .then(data => {
//             document.getElementById('auth-message').textContent = data.message;
//             if (data.success) checkAuth();
//         })
//         .catch(() => {
//             document.getElementById('auth-message').textContent = 'Network error. Try again.';
//         });
//     });

//     // Profile functions
//     function checkProfile() {
//         fetch('api.php?action=check_profile')
//             .then(response => response.json())
//             .then(data => {
//                 profileCompleted = data.completed;
//                 if (profileCompleted) {
//                     document.getElementById('profile').style.display = 'none';
//                     document.getElementById('projects').style.display = 'block';
//                 } else {
//                     document.getElementById('profile').style.display = 'block';
//                     document.getElementById('projects').style.display = 'none';
//                 }
//             });
//     }

//     document.getElementById('profile-form').addEventListener('submit', function(e) {
//         e.preventDefault();
//         const degree = document.getElementById('degree').value;
//         const passingYear = document.getElementById('passing_year').value;
//         const skills = document.getElementById('skills').value;

//         fetch('api.php?action=save_profile', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: `degree=${degree}&passing_year=${passingYear}&skills=${skills}`
//         })
//         .then(response => response.json())
//         .then(data => {
//             document.getElementById('profile-message').textContent = data.success ? 'Profile saved!' : 'Error saving profile.';
//             if (data.success) checkProfile();
//         });
//     });

//     // Projects functions
//     document.getElementById('project-form').addEventListener('submit', function(e) {
//         e.preventDefault();
//         const title = document.getElementById('project-title').value;
//         const description = document.getElementById('project-description').value;

//         fetch('api.php?action=save_project', {
//             method: 'POST',
//             headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
//             body: `title=${title}&description=${description}`
//         })
//         .then(response => response.json())
//         .then(data => {
//             document.getElementById('project-message').textContent = data.success ? 'Project submitted!' : 'Error submitting project.';
//         });
//     });

//     // Mobile Navigation Toggle
//     const navToggle = document.querySelector('.nav-toggle');
//     const navMenu = document.querySelector('.nav-menu');
//     if (navToggle && navMenu) {
//         navToggle.addEventListener('click', () => {
//             navMenu.classList.toggle('active');
//         });
//     }

//     // Smooth Scrolling for Nav Links
//     document.querySelectorAll('.nav-link').forEach(link => {
//         link.addEventListener('click', (e) => {
//             e.preventDefault();
//             const targetId = link.getAttribute('href').substring(1);
//             const targetSection = document.getElementById(targetId);
//             if (targetSection) {
//                 targetSection.scrollIntoView({ behavior: 'smooth' });
//             }
//             // Update active nav
//             document.querySelectorAll('.nav-link').forEach(l => l.classList.remove('active'));
//             link.classList.add('active');
//             // Close mobile menu if open
//             navMenu.classList.remove('active');
//         });
//     });

//     // Fetch data from PHP API (with profile-based filtering)
//     function loadPrograms(tab = 'all') {
//         if (!isLoggedIn) return;  // Only load if logged in
//         currentTab = tab;
//         const container = document.getElementById('programs-list');
//         container.innerHTML = '<p class="loading">Loading professional opportunities... (Powered by API)</p>';

//         fetch('api.php?action=load_programs')
//             .then(response => response.json())
//             .then(data => {
//                 if (data.success) {
//                     allPrograms = data.data;
//                     filterAndDisplay();
//                 } else {
//                     container.innerHTML = '<p class="loading error">Error loading data: ' + (data.error || 'Unknown error') + '</p>';
//                 }
//             })
//             .catch(error => {
//                 container.innerHTML = '<p class="loading error">Error fetching data: ' + error.message + '. Please try again later.</p>';
//             });
//     }

//     // Display programs in the grid (updated to include link)
//     function displayPrograms(programs) {
//         const container = document.getElementById('programs-list');
//         if (programs.length === 0) {
//             container.innerHTML = '<p class="loading no-results">No programs match your filters. Try adjusting your search!</p>';
//             return;
//         }

//         container.innerHTML = programs.map(program => `
//             <div class="program-card">
//                 <h3>${program.title}</h3>
//                 <div class="company">${program.company}</div>
//                 <div>Type: <span class="type-badge ${program.type}">${program.type.charAt(0).toUpperCase() + program.type.slice(1)}</span></div>
//                 <div class="status status-${program.status.toLowerCase().replace(/ /g, '-')}">${program.status}</div>
//                 <p>${program.description}</p>
//                 <div class="dates"><i class="fas fa-calendar"></i> ${program.start_date} to ${program.end_date}</div>
//                 ${program.link ? `<a href="${program.link}" target="_blank" class="apply-btn"><i class="fas fa-external-link-alt"></i> Apply/Learn More</a>` : ''}
//             </div>
//         `).join('');
//     }

//     // Tab Switching (exposed globally for onclick handlers)
//     window.switchTab = function(tab) {
//         currentTab = tab;
//         // Update active tab button
//         document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
//         event.target.classList.add('active');
//         // Reload data if needed (for freshness), then filter
//         loadPrograms(tab);
//     };

//     // Search and Filter Logic
//     function filterAndDisplay() {
//         const searchTerm = document.getElementById('searchInput') ? document.getElementById('searchInput').value.toLowerCase() : '';
//         const typeFilter = document.getElementById('typeFilter') ? document.getElementById('typeFilter').value : '';
//         const statusFilter = document.getElementById('statusFilter') ? document.getElementById('statusFilter').value : '';

//         let filtered = allPrograms.filter(program => {
//             // Search match (company or title)
//             const searchMatch = program.company.toLowerCase().includes(searchTerm) || 
//                                 program.title.toLowerCase().includes(searchTerm) ||
//                                 program.description.toLowerCase().includes(searchTerm);

//             // Type filter from dropdown
//             const typeMatch = !typeFilter || program.type === typeFilter;

//             // Status filter
//             const statusMatch = !statusFilter || program.status === statusFilter;

//             // Tab-based type filter (overrides dropdown if tab is specific)
//             const tabTypeMatch = currentTab === 'all' || 
//                                  (currentTab === 'internships' && program.type === 'internship') ||
//                                  (currentTab === 'certifications' && program.type === 'certification');

//             return searchMatch && typeMatch && statusMatch && tabTypeMatch;
//         });

//         displayPrograms(filtered);
//     }

//     // Event Listeners for Filters and Search
//     const searchInput = document.getElementById('searchInput');
//     const typeFilter = document.getElementById('typeFilter');
//     const statusFilter = document.getElementById('statusFilter');

//     if (searchInput) {
//         searchInput.addEventListener('input', filterAndDisplay);
//     }
//     if (typeFilter) {
//         typeFilter.addEventListener('change', filterAndDisplay);
//     }
//     if (statusFilter) {
//         statusFilter.addEventListener('change', filterAndDisplay);
//     }

//     // Initial Load (only if logged in)
//     if (isLoggedIn) loadPrograms('all');

//     // CTA Button Handler (if clicked, reload all)
//     const ctaButton = document.querySelector('.cta-button');
//     if (ctaButton) {
//         ctaButton.addEventListener('click', () => {
//             loadPrograms('all');
//             // Scroll to programs section
//             document.querySelector('.tabs-section').scrollIntoView({ behavior: 'smooth' });
//         });
//     }

//     // Expose toggleAuth globally for HTML onclick
//     window.toggleAuth = toggleAuth;
// });