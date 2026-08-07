function duplicatePost() {
  if (!currentPost) return;
  const clone = JSON.parse(JSON.stringify(currentPost));
  clone.post_id = currentPost.post_id + '_copy';
  posts.push(clone);
  renderPostList();
  loadPost(clone.post_id);
  setStatus(tr('editPosts.duplicated', 'Duplicated as: {id}', { id: clone.post_id }));
}
