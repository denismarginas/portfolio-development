function duplicatePost() {
  if (!currentPost) return;
  const clone = JSON.parse(JSON.stringify(currentPost));
  clone._id = (currentPost._id || currentPost.post_id) + '_copy';
  posts.push(clone);
  renderPostList();
  loadPost(clone._id);
  setStatus(tr('editPosts.duplicated', 'Duplicated as: {id}', { id: clone._id }));
}
