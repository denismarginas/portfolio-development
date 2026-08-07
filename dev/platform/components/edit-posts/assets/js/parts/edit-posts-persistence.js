async function loadComponents() {
  try {
    const data = await apiFetch(componentsApiUrl);
    components = data.components || [];
  } catch (e) {
    components = [];
  }
}

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
