/**
 * main.js - Frontend Application Logic for BlogSpace
 * FINAL VERSION with FULL CRUD, Search, and "Read More" Functionality
 */

document.addEventListener('DOMContentLoaded', () => {
    
    let session = { loggedIn: false, userId: null, username: null };
    let allPosts = [];

    // =================================================================
    // CORE FUNCTIONS
    // =================================================================

    const checkLoginStatus = async () => {
        try {
            const response = await fetch('check_session.php');
            session = await response.json();
        } catch (error) {
            console.error('Error checking session status:', error);
            session = { loggedIn: false };
        }
    };

    const updateNavigationBar = () => {
        const navLinksContainer = document.getElementById('nav-links');
        if (!navLinksContainer) return;
        let navHTML = '';
        if (session.loggedIn) {
            navHTML = `
                <li><a href="index.html">Home</a></li>
                <li><a href="dashboard.html">My Posts</a></li>
                <li><a href="editor.html">Create Post</a></li>
                <li><a href="#" id="logout-btn" title="Logout">Logout (${session.username})</a></li>
            `;
        } else {
            navHTML = `
                <li><a href="index.html">Home</a></li>
                <li><a href="login.html">Login</a></li>
                <li><a href="register.html">Create Account</a></li>
            `;
        }
        navLinksContainer.innerHTML = navHTML;
    };

    const fetchPostsAndRender = async (containerId, apiParams = '') => {
        const postsContainer = document.getElementById(containerId);
        if (!postsContainer) return;
        postsContainer.innerHTML = '<p>Loading posts...</p>';
        try {
            const response = await fetch(`get_posts.php?${apiParams}`);
            const posts = await response.json();
            allPosts = (posts.error || !Array.isArray(posts)) ? [] : posts;
            renderPosts(allPosts);
        } catch (error) {
            console.error('Failed to fetch posts:', error);
            allPosts = [];
            renderPosts(allPosts);
        }
    };

    const renderPosts = (postsToRender) => {
        const postsContainer = document.getElementById('posts-container');
        if (!postsContainer) return;
        if (postsToRender.length === 0) {
            postsContainer.innerHTML = '<p>No matching posts found.</p>';
            return;
        }
        postsContainer.innerHTML = '';
        postsToRender.forEach(post => {
            const postCard = document.createElement('div');
            postCard.className = 'post-card';
            const contentPreview = post.content.substring(0, 150) + (post.content.length > 150 ? '...' : '');
            const imageHTML = post.image_filename ? `<img src="${post.image_filename}" alt="${post.title}" class="post-card-image">` : '';
            postCard.innerHTML = `
                ${imageHTML}
                <div class="post-card-content">
                    <h2>${post.title}</h2>
                    <p class="post-meta">By <strong>${post.author}</strong> on ${new Date(post.created_at).toLocaleDateString()}</p>
                    <p class="post-content-preview">${contentPreview}</p>
                    <div class="post-actions">
                        <a href="single_post.html?id=${post.id}" class="btn read-more-btn" data-post-id="${post.id}">Read More</a>
                        ${session.loggedIn && session.userId === post.user_id ? `
                        <div class="btn-group">
                            <button class="btn-edit" data-post-id="${post.id}" title="Edit Post"><span class="material-symbols-outlined">edit</span></button>
                            <button class="btn-delete" data-post-id="${post.id}" title="Delete Post"><span class="material-symbols-outlined">delete</span></button>
                        </div>
                        ` : ''}
                    </div>
                </div>
            `;
            postsContainer.appendChild(postCard);
        });
    };
    
    // NEW: Function to fetch and display a single post
    const fetchSinglePost = async () => {
        const container = document.getElementById('single-post-container');
        if (!container) return;

        const urlParams = new URLSearchParams(window.location.search);
        const postId = urlParams.get('id');

        if (!postId) {
            container.innerHTML = '<h1>Error</h1><p>No post ID provided.</p>';
            return;
        }

        try {
            const response = await fetch(`get_posts.php?post_id=${postId}`);
            const post = await response.json();

            if (post.error || !post) {
                container.innerHTML = '<h1>Error</h1><p>Post not found.</p>';
                return;
            }
            
            // Set the page title to the post title
            document.title = post.title + " - BlogSpace";
            
            const imageHTML = post.image_filename ? `<img src="${post.image_filename}" alt="${post.title}" class="single-post-image">` : '';

            // Convert newlines in the content to <br> tags for proper formatting
            const formattedContent = post.content.replace(/\n/g, '<br>');

            container.innerHTML = `
                <div class="single-post">
                    <h1>${post.title}</h1>
                    <p class="post-meta">By <strong>${post.author}</strong> on ${new Date(post.created_at).toLocaleDateString()}</p>
                    ${imageHTML}
                    <div class="single-post-content">
                        ${formattedContent}
                    </div>
                </div>
            `;
        } catch (error) {
            console.error("Failed to fetch single post:", error);
            container.innerHTML = '<h1>Error</h1><p>Could not load post.</p>';
        }
    };


    // =================================================================
    // EVENT HANDLERS
    // =================================================================

    const handleLogout = async (e) => {
        e.preventDefault();
        try {
            const response = await fetch('logout.php');
            const result = await response.json();
            if (result.success) {
                window.location.href = 'login.html';
            }
        } catch (error) {
            console.error('Logout failed, forcing redirect:', error);
            window.location.href = 'login.html';
        }
    };

    const handleJsonFormSubmit = async (e, apiUrl, redirectUrl = null) => {
        e.preventDefault();
        const form = e.target;
        const messageElement = document.getElementById('form-message');
        const formData = new FormData(form);
        const data = Object.fromEntries(formData.entries());
        try {
            const response = await fetch(apiUrl, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(data),
            });
            const result = await response.json();
            if (messageElement) {
                messageElement.textContent = result.message;
                messageElement.className = `form-message ${result.success ? 'success' : 'error'}`;
            }
            if (result.success && redirectUrl) {
                setTimeout(() => { window.location.href = redirectUrl; }, 1000);
            }
        } catch (error) {
            console.error('Form submission error:', error);
            if (messageElement) {
                messageElement.textContent = 'A network error occurred.';
                messageElement.className = 'form-message error';
            }
        }
    };
    
    const handleDeletePost = async (postId) => {
        if (!confirm('Are you sure you want to delete this post?')) return;
        try {
            const response = await fetch('delete_post.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ id: postId }),
            });
            const result = await response.json();
            if (result.success) {
                pageRouter();
            } else {
                alert(`Error: ${result.message}`);
            }
        } catch (error) {
            console.error('Delete post error:', error);
            alert('An error occurred while trying to delete the post.');
        }
    };
    
    // =================================================================
    // PAGE ROUTER & INITIALIZATION
    // =================================================================

    const pageRouter = async () => {
        await checkLoginStatus();
        updateNavigationBar();
        const currentPage = window.location.pathname.split("/").pop();
        switch (currentPage) {
            case 'index.html':
            case '':
                fetchPostsAndRender('posts-container');
                break;
            case 'dashboard.html':
                if (!session.loggedIn) { window.location.href = 'login.html'; return; }
                fetchPostsAndRender('posts-container', `user_id=${session.userId}`);
                break;
            case 'editor.html':
                if (!session.loggedIn) { window.location.href = 'login.html'; return; }
                const urlParams = new URLSearchParams(window.location.search);
                const postId = urlParams.get('id');
                if (postId) {
                    document.getElementById('editor-title').innerHTML = '<span class="material-symbols-outlined">edit_note</span> Edit Post';
                    document.getElementById('save-post-btn').innerHTML = '<span class="material-symbols-outlined">save</span> Save Changes';
                    document.getElementById('post-id').value = postId;
                    try {
                        const response = await fetch(`get_posts.php?post_id=${postId}`);
                        const post = await response.json();
                        if (post && post.user_id === session.userId) {
                            document.getElementById('title').value = post.title;
                            document.getElementById('content').value = post.content;
                            const imageInput = document.getElementById('image');
                            imageInput.disabled = true;
                            imageInput.parentElement.querySelector('label').textContent = 'Header Image (Cannot be changed)';
                        } else {
                            document.querySelector('.form-container').innerHTML = '<h1>Error</h1><p>You are not authorized to edit this post.</p>';
                        }
                    } catch (error) {
                         document.querySelector('.form-container').innerHTML = '<h1>Error</h1><p>Could not load post data.</p>';
                    }
                }
                break;
            // NEW: Route for the single post page
            case 'single_post.html':
                fetchSinglePost();
                break;
        }
    };
    
    // =================================================================
    // GLOBAL EVENT LISTENERS
    // =================================================================

    const registerForm = document.getElementById('register-form');
    if (registerForm) {
        registerForm.addEventListener('submit', (e) => handleJsonFormSubmit(e, 'register.php', 'login.html'));
    }

    const loginForm = document.getElementById('login-form');
    if (loginForm) {
        loginForm.addEventListener('submit', (e) => handleJsonFormSubmit(e, 'login.php', 'dashboard.html'));
    }
    
    const postForm = document.getElementById('post-form');
    if (postForm) {
        postForm.addEventListener('submit', async (e) => {
            e.preventDefault();
            const messageElement = document.getElementById('form-message');
            const formData = new FormData(postForm);
            const postId = formData.get('post-id');
            const apiUrl = postId ? 'update_post.php' : 'create_post.php';
            try {
                const response = await fetch(apiUrl, {
                    method: 'POST',
                    body: formData,
                });
                const result = await response.json();
                if (messageElement) {
                    messageElement.textContent = result.message;
                    messageElement.className = `form-message ${result.success ? 'success' : 'error'}`;
                }
                if (result.success) {
                    setTimeout(() => { window.location.href = 'dashboard.html'; }, 1000);
                }
            } catch (error) {
                console.error('Form submission error:', error);
                if (messageElement) {
                    messageElement.textContent = 'A network error occurred.';
                    messageElement.className = 'form-message error';
                }
            }
        });
    }
    
    const searchForm = document.getElementById('search-form');
    if (searchForm) {
        const searchInput = document.getElementById('search-input');
        searchInput.addEventListener('input', (e) => {
            const searchTerm = e.target.value.toLowerCase();
            const filteredPosts = allPosts.filter(post => {
                const titleMatch = post.title.toLowerCase().includes(searchTerm);
                const authorMatch = post.author.toLowerCase().includes(searchTerm);
                return titleMatch || authorMatch;
            });
            renderPosts(filteredPosts);
        });
        searchForm.addEventListener('submit', (e) => e.preventDefault());
    }

    // UPDATED: Removed the "Read More" click handler from here, as it's now a direct link.
    document.body.addEventListener('click', (e) => {
        if (e.target.id === 'logout-btn') {
            handleLogout(e);
        }
        const editButton = e.target.closest('.btn-edit');
        if (editButton) {
            const postId = editButton.dataset.postId;
            window.location.href = `editor.html?id=${postId}`;
        }
        const deleteButton = e.target.closest('.btn-delete');
        if (deleteButton) {
            const postId = deleteButton.dataset.postId;
            handleDeletePost(postId);
        }
    });

    // =================================================================
    // INITIALIZE THE APPLICATION
    // =================================================================
    pageRouter();

});