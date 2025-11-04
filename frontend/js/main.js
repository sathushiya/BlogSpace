/**
 * =================================================================
 * Main JavaScript File for BlogSpacee - FINAL & COMPLETE VERSION
 * =================================================================
 * This version includes all features:
 * - Registration, Login, Logout, and Session Management
 * - Creating, Reading, Updating, and Deleting Posts
 * - **NEW:** Image Uploads for Posts
 * - Secure authorization checks on the frontend
 * =================================================================
 */

// --- CONFIGURATION ---
// Central place to define the base URL for the API.
const API_BASE_URL = 'http://localhost/BlogSpacee/backend/api';

// --- GLOBAL: Runs when the entire HTML document has been loaded ---
document.addEventListener('DOMContentLoaded', () => {
    // Check login state and update the UI on EVERY page load.
    checkLoginState();

    // --- Simple Router: Run the correct function based on the current page ---
    const page = window.location.pathname.split("/").pop();

    switch (page) {
        case 'index.html':
        case '':
            fetchAndDisplayPosts();
            break;
        case 'register.html':
            handleRegistration();
            break;
        case 'login.html':
            handleLogin();
            break;
        case 'editor.html':
            handleEditor();
            break;
        case 'blog.html':
            fetchSinglePost();
            break;
    }
});


/**
 * =================================================================
 * SECTION 1: USER AUTHENTICATION & SESSION MANAGEMENT
 * =================================================================
 */

function checkLoginState() {
    const user = getSessionUser();
    const navLinksContainer = document.getElementById('nav-links-container');
    if (!navLinksContainer) return;

    if (user) {
        // User is LOGGED IN
        navLinksContainer.innerHTML = `
            <a href="index.html">Home</a>
            <a href="editor.html" class="btn">New Post</a>
            <a href="#" id="logout-btn">Logout</a>
        `;
        document.getElementById('logout-btn').addEventListener('click', handleLogout);
    } else {
        // User is LOGGED OUT
        navLinksContainer.innerHTML = `
            <a href="index.html">Home</a>
            <a href="login.html">Login</a>
            <a href="register.html">Register</a>
        `;
    }
}

function handleLogin() {
    const loginForm = document.getElementById('loginForm');
    if (!loginForm) return;
    loginForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const email = document.getElementById('email').value;
        const password = document.getElementById('password').value;
        try {
            const response = await fetch(`${API_BASE_URL}/auth/login.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ email, password })
            });
            const result = await response.json();
            if (response.ok) {
                sessionStorage.setItem('loggedInUser', JSON.stringify(result.user));
                displayMessage('Login successful! Redirecting...', 'success');
                setTimeout(() => { window.location.href = 'index.html'; }, 1500);
            } else {
                displayMessage(result.message, 'error');
            }
        } catch (error) {
            displayMessage('A network error occurred. Please try again.', 'error');
        }
    });
}

function handleRegistration() {
    const registerForm = document.getElementById('registerForm');
    if (!registerForm) return;
    registerForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        const username = document.getElementById('username').value;
        const formWrapper = document.getElementById('registration-form-wrapper');
        const successPanel = document.getElementById('registration-success-panel');
        const successUsername = document.getElementById('success-username');
        try {
            const response = await fetch(`${API_BASE_URL}/auth/register.php`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ username, email: document.getElementById('email').value, password: document.getElementById('password').value })
            });
            const result = await response.json();
            if (response.ok) {
                formWrapper.style.display = 'none';
                successUsername.textContent = username;
                successPanel.style.display = 'block';
            } else {
                displayMessage(result.message, 'error');
            }
        } catch (error) {
            displayMessage('A network error occurred. Please try again.', 'error');
        }
    });
}

function handleLogout(event) {
    event.preventDefault();
    sessionStorage.removeItem('loggedInUser');
    window.location.href = 'login.html';
}


/**
 * =================================================================
 * SECTION 2: BLOG POST MANAGEMENT (with IMAGES & DELETE)
 * =================================================================
 */

async function fetchAndDisplayPosts() {
    const postsContainer = document.getElementById('blog-posts-container');
    const welcomeContainer = document.getElementById('welcome-message-container');
    const searchInput = document.getElementById('searchInput'); // Get the search input
    if (!postsContainer) return;

    // Welcome message logic
    const user = getSessionUser();
    if (user && welcomeContainer) {
        welcomeContainer.innerHTML = `<p>Hello, <strong>${user.username}</strong>! Welcome back to BlogSpacee.</p>`;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/blogs/read.php`);
        if (response.status === 404) {
             postsContainer.innerHTML = '<p>No blog posts yet. Be the first to write one!</p>';
             return;
        }
        if (!response.ok) throw new Error(`HTTP error! status: ${response.status}`);
        
        const data = await response.json();
        const allPosts = data.records; // Store all posts in a variable

        // --- NEW: Function to render posts based on a filter ---
        const renderPosts = (filterText = '') => {
            postsContainer.innerHTML = ''; // Clear the container first

            // Filter the posts array
            const filteredPosts = allPosts.filter(post => {
                const title = post.title.toLowerCase();
                const author = (post.author || '').toLowerCase();
                const searchTerm = filterText.toLowerCase();

                // Return true if the title OR the author includes the search term
                return title.includes(searchTerm) || author.includes(searchTerm);
            });

            // If there are no matching posts, show a message
            if (filteredPosts.length === 0) {
                postsContainer.innerHTML = '<div id="no-results-message"><h3>No posts found</h3><p>Try a different search term.</p></div>';
                return;
            }

            // Render each of the filtered posts
            filteredPosts.forEach(post => {
                const postElement = document.createElement('article');
                postElement.className = 'card post-card';
                postElement.setAttribute('data-aos', 'fade-up');
                
                const excerpt = post.content.substring(0, 120) + '...';
                const imageContainerHtml = post.image_filename
                    ? `<div class="post-card-image"><a href="blog.html?id=${post.id}"><img src="../uploads/${post.image_filename}" alt="${post.title}"></a></div>`
                    : '';

                postElement.innerHTML = `
                    ${imageContainerHtml}
                    <div class="post-card-content">
                        <h2><a href="blog.html?id=${post.id}">${post.title}</a></h2>
                        <p>By <strong>${post.author || 'Anonymous'}</strong> on <time>${new Date(post.created_at).toLocaleDateString()}</time></p>
                        <p>${excerpt}</p>
                        <a href="blog.html?id=${post.id}" class="btn">Read More</a>
                    </div>
                `;
                postsContainer.appendChild(postElement);
            });
        };

        // --- NEW: Add an event listener to the search input ---
        searchInput.addEventListener('input', (event) => {
            renderPosts(event.target.value);
            AOS.refresh(); // Refresh AOS animations for the newly rendered items
        });

        // Initial render of all posts
        renderPosts();
        AOS.refresh();

    } catch (error) {
        postsContainer.innerHTML = '<p class="message error">Could not fetch posts.</p>';
        console.error('Error fetching posts:', error);
    }
}

async function fetchSinglePost() {
    const container = document.getElementById('single-post-container');
    if (!container) return;
    const urlParams = new URLSearchParams(window.location.search);
    const postId = urlParams.get('id');
    if (!postId) {
        container.innerHTML = '<p class="message error">No post ID provided.</p>';
        return;
    }

    try {
        const response = await fetch(`${API_BASE_URL}/blogs/read_single.php?id=${postId}`);
        if (!response.ok) throw new Error('Post not found.');
        
        const post = await response.json();
        const user = getSessionUser();

        const imageHtml = post.image_filename
            ? `<img src="../uploads/${post.image_filename}" alt="${post.title}" class="post-image">`
            : '';

        document.title = `${post.title} - BlogSpacee`;
        container.innerHTML = `
            ${imageHtml}
            <h1>${post.title}</h1>
            <p class="post-meta">
                Posted by <strong>${post.author || 'Anonymous'}</strong> on 
                <time>${new Date(post.created_at).toLocaleDateString()}</time>
            </p>
            <div id="post-content">${post.content.replace(/\n/g, '<br>')}</div>
            <div id="author-controls" style="margin-top: 2rem; display: flex; gap: 1rem;"></div>
        `;

        if (user && user.id === post.user_id) {
            const controlsContainer = document.getElementById('author-controls');
            const editButton = document.createElement('a');
            editButton.href = `editor.html?edit=${post.id}`;
            editButton.className = 'btn';
            editButton.textContent = 'Edit Post';
            const deleteButton = document.createElement('button');
            deleteButton.className = 'btn btn-danger';
            deleteButton.textContent = 'Delete Post';
            deleteButton.addEventListener('click', () => handleDeletePost(post.id, user.id));
            controlsContainer.appendChild(editButton);
            controlsContainer.appendChild(deleteButton);
        }
    } catch (error) {
        container.innerHTML = `<p class="message error">${error.message}</p>`;
        console.error('Error fetching single post:', error);
    }
}

async function handleEditor() {
    const postForm = document.getElementById('blogPostForm');
    const user = getSessionUser();
    if (!user) { window.location.href = 'login.html'; return; }
    if (!postForm) return;

    const urlParams = new URLSearchParams(window.location.search);
    const postIdToEdit = urlParams.get('edit');
    const pageTitle = document.querySelector('h2');
    const submitButton = postForm.querySelector('button[type="submit"]');

    // NOTE: Editing with images is complex. This version does not re-upload images on edit.
    // For simplicity, we are focusing on creating posts with new images.
    if (postIdToEdit) {
        pageTitle.innerHTML = '<i class="fas fa-edit"></i> Edit Your Post';
        submitButton.textContent = 'Update Post';
        // Hide image upload on edit for simplicity
        document.getElementById('postImage').parentElement.style.display = 'none';

        try {
            const response = await fetch(`${API_BASE_URL}/blogs/read_single.php?id=${postIdToEdit}`);
            if (!response.ok) throw new Error('Could not fetch post data to edit.');
            const post = await response.json();
            if (post.user_id !== user.id) { window.location.href = 'index.html'; return; }
            document.getElementById('title').value = post.title;
            document.getElementById('content').value = post.content;
        } catch (error) {
            displayMessage(error.message, 'error');
        }
    }

    postForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        displayMessage('Submitting...', 'info');
        
        const title = document.getElementById('title').value;
        const content = document.getElementById('content').value;
        const imageFile = document.getElementById('postImage').files[0];
        let imageFilename = null;

        // --- Step 1: Upload image if creating a new post and file is selected ---
        if (!postIdToEdit && imageFile) {
            const formData = new FormData();
            formData.append('postImage', imageFile);
            try {
                const uploadResponse = await fetch(`${API_BASE_URL}/blogs/upload.php`, { method: 'POST', body: formData });
                const uploadResult = await uploadResponse.json();
                if (!uploadResponse.ok) throw new Error(uploadResult.message || 'Image upload failed.');
                imageFilename = uploadResult.filename;
            } catch (error) {
                displayMessage(error.message, 'error');
                return;
            }
        }

        // --- Step 2: Submit the rest of the post data ---
        let apiUrl, payload;
        if (postIdToEdit) {
            apiUrl = `${API_BASE_URL}/blogs/update.php`;
            payload = { id: postIdToEdit, title, content, user_id: user.id };
        } else {
            apiUrl = `${API_BASE_URL}/blogs/create.php`;
            payload = { title, content, user_id: user.id, image_filename: imageFilename };
        }

        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();
            if (response.ok) {
                const action = postIdToEdit ? 'updated' : 'created';
                displayMessage(`Post ${action} successfully! Redirecting...`, 'success');
                setTimeout(() => { window.location.href = 'index.html'; }, 2000);
            } else {
                displayMessage(result.message, 'error');
            }
        } catch (error) {
             displayMessage('A network error occurred.', 'error');
        }
    });
}

async function handleDeletePost(postId, userId) {
    const isConfirmed = confirm('Are you sure you want to delete this post? This action cannot be undone.');
    if (!isConfirmed) return;

    try {
        const response = await fetch(`${API_BASE_URL}/blogs/delete.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ post_id: postId, user_id: userId })
        });
        const result = await response.json();
        if (response.ok) {
            alert('Post deleted successfully!');
            window.location.href = 'index.html';
        } else {
            alert(`Error: ${result.message}`);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('A network error occurred while trying to delete the post.');
    }
}


/**
 * =================================================================
 * SECTION 3: HELPER FUNCTIONS
 * =================================================================
 */

function getSessionUser() {
    const userStr = sessionStorage.getItem('loggedInUser');
    return userStr ? JSON.parse(userStr) : null;
}

function displayMessage(message, type) {
    const container = document.getElementById('message-container');
    if (!container) return;
    container.innerHTML = `<div class="message ${type}">${message}</div>`;
}