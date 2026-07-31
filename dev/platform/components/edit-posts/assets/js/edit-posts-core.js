// Post Types

async function loadPostTypes() {
  try {
    setStatus(tr('editPosts.loadingPostTypes', 'Loading post types...'));
    const data = await apiFetch(apiUrl);
    postTypes = data.types || [];
    renderPostTypes();
    setStatus(tr('editPosts.ready', 'Ready'));
  } catch (e) {
    setStatus(tr('editPosts.error', 'Error: {message}', { message: e.message }));
    postTypesEl.innerHTML = '<div class="platform-list-item" style="color:var(--platform-color-status-error)">' + escapeHtml(tr('editPosts.error', 'Error: {message}', { message: e.message })) + '</div>';
  }
}

function renderPostTypes() {
  postTypesEl.innerHTML = '';
  if (!postTypes.length) {
    postTypesEl.innerHTML = '<div class="platform-list-item" style="color:var(--platform-color-text-secondary)">' + escapeHtml(tr('editPosts.noPostTypes', 'No post types found. Configure SelectFile cards in the workflow first.')) + '</div>';
    return;
  }
  postTypes.forEach(t => {
    const item = document.createElement('div');
    item.className = 'platform-list-item ep-post-type-item' + (t.post_type === currentPostType ? ' is-active' : '');
    item.dataset.epPostType = t.post_type;
    item.innerHTML = `<span>${escapeHtml(t.title || t.post_type)}</span><span class="ep-post-type-count">${t.count}</span>`;
    item.addEventListener('click', () => selectPostType(t.post_type));
    postTypesEl.appendChild(item);
  });
}

async function selectPostType(postType) {
  currentPostType = postType;
  currentPost = null;
  editorEl.style.display = 'none';

  const typeDef = postTypes.find(t => t.post_type === postType);
  const globalContentPath = typeDef?.global_content_path || '';
  const globalImgPath = typeDef?.global_img_path || '';
  const globalVidPath = typeDef?.global_vid_path || '';
  const imgDir = globalImgPath.split('/').pop() || postType;
  const vidDir = globalVidPath.split('/').pop() || postType;
  epRoot.dataset.globalContentDir = globalContentPath.split('/').filter(Boolean).pop() || '';
  epRoot.dataset.globalImgDir = imgDir;
  epRoot.dataset.globalVidDir = vidDir;

  renderPostTypes();
  setStatus(tr('editPosts.loadingPostTypes', 'Loading...'));
  const data = await apiFetch(`${apiUrl}?post_type=${encodeURIComponent(postType)}`);
  posts = data.posts || [];
  currentFile = data.file || '';
  renderPostList();
  postListWrap.style.display = 'block';
  setStatus(tr('editPosts.postsLoaded', '{count} {type} posts loaded', { count: posts.length, type: postType }));
}

function renderPostList() {
  postListEl.innerHTML = '';
  const q = (searchInput.value || '').toLowerCase();
  const filtered = posts.filter(p => p.title.toLowerCase().includes(q) || p.post_id.toLowerCase().includes(q));
  if (!filtered.length) {
    postListEl.innerHTML = '<div class="platform-list-item" style="color:var(--platform-color-text-secondary)">' + escapeHtml(tr('editPosts.noPostsMatch', 'No posts match your search.')) + '</div>';
    return;
  }
  filtered.forEach(p => {
    const item = document.createElement('div');
    item.className = 'platform-list-item ep-post-item' + (currentPost && currentPost.post_id === p.post_id ? ' is-active' : '');
    item.innerHTML = `<div class="ep-post-item-title">${escapeHtml(p.title || '(no title)')}</div><div class="ep-post-item-id">${escapeHtml(p.post_id)}</div>`;
    item.addEventListener('click', () => loadPost(p.post_id));
    postListEl.appendChild(item);
  });
}

// Components

async function loadComponents() {
  try {
    const data = await apiFetch(componentsApiUrl);
    components = data.components || [];
  } catch (e) {
    components = [];
  }
}

// Persistence

function markDirty() {
}

async function savePost() {
  if (!currentPost || !currentFile) {
    setStatus('No post to save');
    return;
  }
  setStatus(tr('editPosts.saving', 'Saving...'));
  try {
    const data = await apiFetch(apiUrl, {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: currentPost.post_id, file: currentFile, data: currentPost, original_post_id: originalPostId }),
    });
    setStatus(tr('editPosts.saved', 'Post saved'));
  } catch (e) {
    setStatus(tr('editPosts.saveFailed', 'Save failed: {message}', { message: e.message }));
  }
}

async function deletePost() {
  if (!currentPost || !currentFile) return;
  if (!confirm(tr('editPosts.confirmDelete', 'Delete this post permanently?'))) return;
  const postId = currentPost.post_id;
  try {
    await apiFetch(apiUrl, {
      method: 'DELETE',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ post_id: postId, file: currentFile }),
    });
    setStatus(tr('editPosts.deleted', 'Post deleted'));
    currentPost = null;
    editorEl.style.display = 'none';
    const idx = posts.findIndex(p => p.post_id === postId);
    if (idx !== -1) posts.splice(idx, 1);
    await loadPostTypes();
  } catch (e) {
    setStatus(tr('editPosts.deleteFailed', 'Delete failed: {message}', { message: e.message }));
  }
}
