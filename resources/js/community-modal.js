// ==================== GLOBAL COMMUNITY MODAL FUNCTIONS ====================
if (!window.modalCommentFiles) window.modalCommentFiles = {};

/* ================================
   EDIT POST (inside modal)
================================ */
window.editPost = async function(postId){
    const container = document.querySelector(`[data-post-id="${postId}"]`);
    if(!container) return;

    const textP = container.querySelector('p[style*="white-space: pre-wrap"]');
    const currentText = textP ? textP.textContent.trim() : '';

    // Inline edit form
    const editForm = document.createElement('div');
    editForm.id = `edit-form-${postId}`;
    editForm.style = 'position:relative;background:white;padding:16px;border-radius:8px;';
    editForm.innerHTML = `
        <textarea id="edit-text-${postId}" style="width:100%;padding:8px;min-height:80px;">${currentText}</textarea>
        <div style="margin-top:8px;display:flex;gap:8px;">
            <button onclick="savePostEdit(${postId})" style="flex:1;background:#1877f2;color:white;border:none;padding:8px;border-radius:4px;">Save</button>
            <button onclick="cancelPostEdit(${postId})" style="flex:1;background:#f0f2f5;border:1px solid #ccc;padding:8px;border-radius:4px;">Cancel</button>
        </div>
    `;
    container.prepend(editForm);
};

/* ================================
   SAVE POST EDIT
================================ */
window.savePostEdit = async function(postId){
    const textarea = document.getElementById(`edit-text-${postId}`);
    const newText = textarea.value.trim();
    if(!newText) return;

    const container = document.querySelector(`[data-post-id="${postId}"]`);
    const submitBtn = textarea.nextElementSibling.querySelector('button:first-child');
    const originalText = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = 'Saving...';

    try{
        const res = await fetch(`/community/posts/${postId}`,{
            method:'POST',
            headers:{
                'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                'Accept':'application/json',
                'Content-Type':'application/json'
            },
            body: JSON.stringify({description:newText})
        });
        const data = await res.json();
        if(data.success){
            container.innerHTML = data.post_html; // reload post section inside modal
            if(typeof showToast==="function") showToast('Post updated!','success');
        }
    } catch(err){ console.error(err); }
    finally{ submitBtn.disabled=false; submitBtn.innerHTML=originalText; }
};

window.cancelPostEdit = function(postId){
    const form = document.getElementById(`edit-form-${postId}`);
    if(form) form.remove();
};

/* ================================
   DELETE POST
================================ */
window.deletePostFromModal = async function(postId){
    if(!confirm('Delete this post?')) return;
    try{
        const res = await fetch(`/community/posts/${postId}`,{
            method:'DELETE',
            headers:{
                'X-CSRF-TOKEN':document.querySelector('meta[name="csrf-token"]').content,
                'Accept':'application/json'
            }
        });
        const data = await res.json();
        if(data.success){
            const modal = document.querySelector('.modal.show');
            if(modal){ modal.remove(); document.body.classList.remove('modal-open'); document.querySelectorAll('.modal-backdrop').forEach(e=>e.remove()); }
            if(typeof showToast==="function") showToast(data.message,'success');
        }
    } catch(err){ console.error(err); }
};

/* ================================
   COMMENT SUBMIT (inside modal)
================================ */
/* ================================
   COMMENT SUBMIT (inside modal) - FIXED FOR BOTH CONTEXTS
================================ */
window.submitCommentModalFixed = async function(event, postId){
    event.preventDefault();
    
    const textarea = document.getElementById(`modal-textarea-${postId}`);
    if (!textarea) {
        console.error('Textarea not found');
        return;
    }
    
    const commentText = textarea.value.trim();
    const file = window.modalCommentFiles ? window.modalCommentFiles[postId] : null;
    
    if(!commentText && !file){ 
        if(typeof window.showToast === "function") {
            window.showToast('Please write something or attach a file','warning');
        }
        return; 
    }

    const formData = new FormData();
    formData.append('_token', document.querySelector('meta[name="csrf-token"]').content);
    formData.append('comment_details', commentText);
    if(file) formData.append('file', file);

    const submitBtn = document.getElementById(`comment-submit-${postId}`);
    if (!submitBtn) return;
    
    const originalHtml = submitBtn.innerHTML;
    submitBtn.disabled = true;
    submitBtn.innerHTML = '<span class="spinner-small"></span>';

    try{
        const res = await fetch(`/community/posts/${postId}/comments`, {
            method:'POST',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });
        
        const data = await res.json();
        
        if(data.success){
            // Find the comments container - it might be in a different context
            const container = document.getElementById(`comments-container-${postId}`);
            if (container) {
                // Clear and reload comments
                container.innerHTML = '';
                
                // Fetch updated comments HTML
                const commentsRes = await fetch(`/community/posts/${postId}/comments`);
                const commentsData = await commentsRes.json();
                
                if (commentsData.html) {
                    container.innerHTML = commentsData.html;
                } else if (commentsData.comments) {
                    // If API returns JSON, you might need to render each comment
                    commentsData.comments.forEach(comment => {
                        // You'll need to have a function to render comment HTML
                        // or use a template
                    });
                }
            }
            
            // Clear input and file preview
            textarea.value = ''; 
            textarea.style.height = 'auto';
            window.clearCommentFileModal(postId);
            
            // Update comment count - find it in both possible locations
            const commentCounts = document.querySelectorAll(`#like-btn-${postId} .comment-count, .comment-count`);
            commentCounts.forEach(el => {
                if (el) el.textContent = data.comment_count;
            });
            
            if(typeof window.showToast === "function") {
                window.showToast('Comment added!','success');
            }
        } else {
            if(typeof window.showToast === "function") {
                window.showToast(data.message || 'Error adding comment','error');
            }
        }
    } catch(err){ 
        console.error("Comment error:", err); 
        if(typeof window.showToast === "function") {
            window.showToast('Error adding comment','error');
        }
    } finally{ 
        submitBtn.disabled = false; 
        submitBtn.innerHTML = originalHtml; 
    }
};

/* ================================
   COMMENT FILE PREVIEW
================================ */
/* ================================
   COMMENT FILE PREVIEW - FIXED
================================ */
window.handleCommentFileSelectModal = function(postId, input){
    const file = input.files[0]; 
    if(!file) return;
    
    const maxSize = 5 * 1024 * 1024; // 5MB
    
    if(file.size > maxSize){ 
        if(typeof window.showToast === "function") {
            window.showToast('File size cannot exceed 5MB','warning');
        }
        input.value = ""; 
        return; 
    }
    
    if (!window.modalCommentFiles) window.modalCommentFiles = {};
    window.modalCommentFiles[postId] = file;

    const previewArea = document.getElementById(`comment-file-preview-${postId}`);
    const previewContent = document.getElementById(`comment-file-preview-content-${postId}`);
    
    if(!previewArea || !previewContent) return;
    
    previewContent.innerHTML = '';

    if(file.type.startsWith('image/')){
        const reader = new FileReader();
        reader.onload = e => { 
            previewContent.innerHTML = `
                <img src="${e.target.result}" style="max-height:40px; border-radius:4px; margin-right:8px;">
                <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
                <span style="font-size:11px; color:#65676b; margin-left:4px;">(${(file.size/1024).toFixed(1)} KB)</span>
            `; 
        };
        reader.readAsDataURL(file);
    } else {
        let icon = 'fa-file-alt';
        if(file.type.includes('pdf')) icon = 'fa-file-pdf';
        else if(file.type.includes('video')) icon = 'fa-file-video';
        else if(file.type.includes('word')) icon = 'fa-file-word';
        else if(file.type.includes('excel')) icon = 'fa-file-excel';
        
        previewContent.innerHTML = `
            <i class="fas ${icon}" style="font-size:20px; color:#1877f2; margin-right:8px;"></i>
            <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
            <span style="font-size:11px; color:#65676b; margin-left:4px;">(${(file.size/1024).toFixed(1)} KB)</span>
        `;
    }
    previewArea.style.display = 'block';
};

window.clearCommentFileModal = function(postId){
    if(window.modalCommentFiles){ window.modalCommentFiles[postId]=null; delete window.modalCommentFiles[postId]; }
    const input = document.getElementById(`comment-file-${postId}`);
    if(input) input.value='';
    const previewArea = document.getElementById(`comment-file-preview-${postId}`);
    if(previewArea) previewArea.style.display='none';
};
// Global storage for comment files
if (!window.modalCommentFiles) {
    window.modalCommentFiles = {};
}

window.setupModalInteractions = function(postId) {
    // Auto-resize textareas in modal
    document.querySelectorAll('#postModalBody textarea').forEach(textarea => {
        textarea.addEventListener('input', function() {
            this.style.height = 'auto';
            this.style.height = (this.scrollHeight) + 'px';
        });
    });
};

// ==================== COMMENT SUBMIT ====================
window.submitCommentModalFixed = async function(event, postId) {
    event.preventDefault();

    const textarea = document.getElementById(`modal-textarea-${postId}`);
    if (!textarea) return;
    
    const comment = textarea.value.trim();
    const fileInput = document.getElementById(`comment-file-${postId}`);
    const file = fileInput ? fileInput.files[0] : null;

    if (!comment && !file) {
        if (typeof window.showToast === 'function') {
            window.showToast('Please write something or attach a file', 'warning');
        }
        return;
    }

    const formData = new FormData();
    formData.append("_token", document.querySelector('meta[name="csrf-token"]').content);
    formData.append("comment_details", comment);

    if (file) {
        formData.append("file", file);
    }

    const submitBtn = document.getElementById(`comment-submit-${postId}`);
    if (submitBtn) {
        submitBtn.disabled = true;
        submitBtn.innerHTML = '<span class="spinner-small"></span>';
    }

    try {
        const response = await fetch(`/community/posts/${postId}/comments`, {
            method: "POST",
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                'Accept': 'application/json'
            },
            body: formData
        });

        const data = await response.json();

        if (data.success) {
            const container = document.getElementById(`comments-container-${postId}`);
            if (container) {
                if (container.innerHTML.includes("No comments")) {
                    container.innerHTML = "";
                }
                container.insertAdjacentHTML("beforeend", data.html);
            }

            if (textarea) {
                textarea.value = "";
                textarea.style.height = "auto";
            }
            
            if (fileInput) fileInput.value = "";

            // Clear file preview if exists
            if (typeof window.clearCommentFileModal === 'function') {
                window.clearCommentFileModal(postId);
            }

            // Update comment count
            const count = document.querySelector(`#like-btn-${postId} .comment-count, .comment-count`);
            if (count) count.textContent = data.comment_count;

            if (typeof window.showToast === 'function') {
                window.showToast('Comment added!', 'success');
            }
        } else {
            if (typeof window.showToast === 'function') {
                window.showToast(data.message || 'Error adding comment', 'error');
            }
        }
    } catch (error) {
        console.error(error);
        if (typeof window.showToast === 'function') {
            window.showToast('Error adding comment', 'error');
        }
    } finally {
        if (submitBtn) {
            submitBtn.disabled = false;
            submitBtn.innerHTML = '<i class="fas fa-paper-plane"></i>';
        }
    }
};

// ==================== FILE HANDLING ====================
window.handleCommentFileSelectModal = function(postId, input) {
    const file = input.files[0];
    if (!file) return;

    const maxSize = 5 * 1024 * 1024; // 5MB

    if (file.size > maxSize) {
        if (typeof window.showToast === 'function') {
            window.showToast('File size cannot exceed 5MB', 'warning');
        }
        input.value = "";
        return;
    }

    if (!window.modalCommentFiles) window.modalCommentFiles = {};
    window.modalCommentFiles[postId] = file;

    const previewArea = document.getElementById(`comment-file-preview-${postId}`);
    const previewContent = document.getElementById(`comment-file-preview-content-${postId}`);

    if (!previewArea || !previewContent) return;

    previewContent.innerHTML = '';

    if (file.type.startsWith('image/')) {
        const reader = new FileReader();
        reader.onload = function(e) {
            previewContent.innerHTML = `
                <img src="${e.target.result}" style="max-height:40px; border-radius:4px; margin-right:8px;">
                <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
                <span style="font-size:11px; color:#65676b; margin-left:4px;">(${(file.size/1024).toFixed(1)} KB)</span>
            `;
        };
        reader.readAsDataURL(file);
    } else {
        let icon = 'fa-file-alt';
        if (file.type.includes('pdf')) icon = 'fa-file-pdf';
        else if (file.type.includes('video')) icon = 'fa-file-video';
        else if (file.type.includes('word')) icon = 'fa-file-word';
        else if (file.type.includes('excel')) icon = 'fa-file-excel';
        
        previewContent.innerHTML = `
            <i class="fas ${icon}" style="font-size:20px; color:#1877f2; margin-right:8px;"></i>
            <span style="font-size:12px;">${file.name.length > 20 ? file.name.substring(0,17)+'...' : file.name}</span>
            <span style="font-size:11px; color:#65676b; margin-left:4px;">(${(file.size/1024).toFixed(1)} KB)</span>
        `;
    }
    previewArea.style.display = 'block';
};

window.clearCommentFileModal = function(postId) {
    if (window.modalCommentFiles) {
        window.modalCommentFiles[postId] = null;
        delete window.modalCommentFiles[postId];
    }

    const fileInput = document.getElementById(`comment-file-${postId}`);
    if (fileInput) fileInput.value = '';

    const previewArea = document.getElementById(`comment-file-preview-${postId}`);
    if (previewArea) previewArea.style.display = 'none';
};

// ==================== LIKE FUNCTIONS ====================
window.toggleLike = async function(postId, button) {
    try {
        const response = await fetch(`/community/posts/${postId}/like`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json",
                "Content-Type": "application/json"
            }
        });

        const data = await response.json();

        if (data.success) {
            const countSpan = button.querySelector(".like-count");
            if (countSpan) countSpan.textContent = data.count;

            const icon = button.querySelector("i");
            
            if (data.liked) {
                icon.classList.remove("far");
                icon.classList.add("fas");
                button.classList.add("liked");
                icon.style.color = "#dc3545";
            } else {
                icon.classList.remove("fas");
                icon.classList.add("far");
                button.classList.remove("liked");
                icon.style.color = "inherit";
            }
        }
    } catch (err) {
        console.error("Like error:", err);
    }
};

window.toggleCommentLike = async function(commentId, button) {
    try {
        const response = await fetch(`/community/comments/${commentId}/like`, {
            method: "POST",
            headers: {
                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]').content,
                "Accept": "application/json",
                "Content-Type": "application/json"
            }
        });

        const data = await response.json();

        if (data.success) {
            const countSpan = button.querySelector(".like-count");
            if (countSpan) countSpan.textContent = data.count;

            const icon = button.querySelector("i");
            
            if (data.liked) {
                icon.classList.remove("far");
                icon.classList.add("fas");
                button.classList.add("liked");
                icon.style.color = "#dc3545";
            } else {
                icon.classList.remove("fas");
                icon.classList.add("far");
                button.classList.remove("liked");
                icon.style.color = "inherit";
            }
        }
    } catch (err) {
        console.error("Comment like error:", err);
    }
};

// ==================== USER MODAL ====================
window.showUserModal = function(userId) {
    const modalBody = document.getElementById('userModalBody');
    if (!modalBody) return;
    
    modalBody.innerHTML = '<div class="text-center py-4"><div class="spinner-border text-primary" role="status"></div></div>';
    
    const userModal = new bootstrap.Modal(document.getElementById('userModal'));
    userModal.show();
    
    fetch(`/community/users/${userId}`)
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                renderUserModal(data.user);
            } else {
                modalBody.innerHTML = '<div class="alert alert-danger">Error loading user details</div>';
            }
        })
        .catch(error => {
            console.error('Error:', error);
            modalBody.innerHTML = '<div class="alert alert-danger">Failed to load user details</div>';
        });
};

window.renderUserModal = function(user) {
    const modalBody = document.getElementById('userModalBody');
    if (!modalBody) return;
    
    modalBody.innerHTML = `
        <div class="text-center">
            ${user.avatar 
                ? `<img src="${user.avatar}" alt="${user.name}" class="user-modal-avatar">`
                : `<div class="user-modal-placeholder">${user.name.charAt(0).toUpperCase()}</div>`
            }
            <h5 class="mb-1">${user.name}</h5>
            <p class="text-muted small mb-2">${user.email}</p>
            <p class="small text-muted mb-2"><i class="fas fa-map-marker-alt me-1"></i> ${user.location}</p>
            <p class="small text-muted mb-3"><i class="fas fa-calendar-alt me-1"></i> Joined ${user.joined}</p>
            <p class="mb-3 px-3">${user.bio}</p>
            
            <div class="row g-2 mb-3 px-3">
                <div class="col-6">
                    <div class="user-stat-card">
                        <div class="user-stat-value">${user.posts_count}</div>
                        <div class="user-stat-label">Posts</div>
                    </div>
                </div>
                <div class="col-6">
                    <div class="user-stat-card">
                        <div class="user-stat-value">${user.comments_count}</div>
                        <div class="user-stat-label">Comments</div>
                    </div>
                </div>
            </div>
        </div>
    `;
};

// ==================== UTILITY FUNCTIONS ====================
window.formatFileSize = function(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
};

window.openImageModal = function(src) {
    const modalImage = document.getElementById('modalImage');
    if (modalImage) {
        modalImage.src = src;
        new bootstrap.Modal(document.getElementById('imageModal')).show();
    }
};

window.openVideoModal = function(type, source, isReel = false) {
    const modalContent = document.getElementById('videoModalContent');
    if (!modalContent) return;
    
    if (type === 'youtube') {
        if (isReel) {
            modalContent.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
                    <div style="width: 400px; max-width: 100%;">
                        <div style="position: relative; width: 100%; padding-bottom: 177.78%;">
                            <iframe 
                                style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; border-radius: 12px;"
                                src="https://www.youtube.com/embed/${source}?autoplay=1&rel=0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen>
                            </iframe>
                        </div>
                    </div>
                </div>
            `;
        } else {
            modalContent.innerHTML = `
                <div style="position: relative; width: 100%; padding-bottom: 56.25%;">
                    <iframe 
                        style="position: absolute; top: 0; left: 0; width: 100%; height: 100%; border: none; border-radius: 8px;"
                        src="https://www.youtube.com/embed/${source}?autoplay=1&rel=0"
                        allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                        allowfullscreen>
                    </iframe>
                </div>
            `;
        }
    } else if (type === 'file') {
        if (isReel) {
            modalContent.innerHTML = `
                <div style="display: flex; justify-content: center; align-items: center; min-height: 80vh;">
                    <div style="width: 400px; max-width: 100%;">
                        <div style="position: relative; width: 100%; padding-bottom: 177.78%; background: #000; border-radius: 12px; overflow: hidden;">
                            <video controls autoplay style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                                <source src="${source}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        </div>
                    </div>
                </div>
            `;
        } else {
            modalContent.innerHTML = `
                <div style="position: relative; width: 100%; padding-bottom: 56.25%; background: #000; border-radius: 8px; overflow: hidden;">
                    <video controls autoplay style="position: absolute; top: 0; left: 0; width: 100%; height: 100%;">
                        <source src="${source}" type="video/mp4">
                        Your browser does not support the video tag.
                    </video>
                </div>
            `;
        }
    }
    
    new bootstrap.Modal(document.getElementById('videoModal')).show();
};