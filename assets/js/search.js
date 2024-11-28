function searchMenu() {
    const searchInput = document.getElementById('searchInput');
    const searchResults = document.getElementById('searchResults');
    const query = searchInput.value.trim().toLowerCase();

    if (query.length < 1) {
        searchResults.classList.add('d-none');
        return;
    }

    const posts = document.querySelectorAll('.card.px-4.mb-4');
    let resultsHtml = '';
    let matchCount = 0;

    posts.forEach(post => {
        const content = post.querySelector('.card-body p').textContent.toLowerCase();
        const username = post.querySelector('.text-sm').textContent.toLowerCase();
        const postId = post.getAttribute('data-post-id');
        const timeAgo = post.querySelector('.text-xs.text-secondary.mb-0').textContent;
        const commentCount = post.querySelector('button[onclick^="showComments"] span').textContent;
        const likeCount = post.querySelector('button[onclick^="handleLike"] span').textContent;
        const avatarSrc = post.querySelector('.avatar').src;

        if ((content.includes(query) || username.includes(query)) && matchCount < 5) {
            matchCount++;
            resultsHtml += `
                <div class="search-result p-3 hover-bg-light cursor-pointer" 
                     onclick="scrollToPost('${postId}')">
                    <div class="d-flex align-items-center mb-2">
                        <img src="${avatarSrc}" 
                             class="avatar rounded-circle me-2"
                             style="width: 32px; height: 32px;"
                             onerror="this.src='../../assets/img/default-avatar.png'">
                        <div>
                            <div class="fw-bold" style="font-size: 14px; line-height: 1;">
                                ${username}
                            </div>
                            <div class="text-xs text-secondary">
                                ${timeAgo}
                            </div>
                        </div>
                    </div>
                    <div class="text-sm mb-1" style="color: #344767;">
                        ${content.length > 100 ? content.substring(0, 100) + '...' : content}
                    </div>
                    <div class="d-flex align-items-center gap-3">
                        <small class="text-secondary d-flex align-items-center gap-1">
                            <i class="material-symbols-rounded text-sm">chat_bubble_outline</i>
                            ${commentCount}
                        </small>
                        <small class="text-secondary d-flex align-items-center gap-1">
                            <i class="material-symbols-rounded text-sm">favorite_border</i>
                            ${likeCount}
                        </small>
                    </div>
                </div>
            `;
        }
    });

    if (matchCount === 0) {
        resultsHtml = `
            <div class="text-center p-4">
                <div class="text-secondary mb-2">
                    <i class="material-symbols-rounded" style="font-size: 48px;">search_off</i>
                </div>
                <div class="text-sm text-secondary">No results found</div>
            </div>
        `;
    }

    searchResults.innerHTML = resultsHtml;
    searchResults.classList.remove('d-none');
}

function scrollToPost(postId) {
    const post = document.querySelector(`[data-post-id="${postId}"]`);
    if (post) {
        post.scrollIntoView({ behavior: 'smooth', block: 'center' });
        post.classList.add('highlight-post');
        setTimeout(() => post.classList.remove('highlight-post'), 2000);
        
        // Hide search results
        document.getElementById('searchResults').classList.add('d-none');
        document.getElementById('searchInput').value = '';
    }
}

// Close search results when clicking outside
document.addEventListener('click', function(e) {
    const searchResults = document.getElementById('searchResults');
    const searchInput = document.getElementById('searchInput');
    
    if (!searchResults.contains(e.target) && !searchInput.contains(e.target)) {
        searchResults.classList.add('d-none');
    }
}); 