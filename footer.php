    </main>
    <footer>
        <div class="container">
            <p>&copy; 2026 Блог. Все права защищены.</p>
        </div>
    </footer>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const commentForm = document.getElementById('comment-form');
            
            if(commentForm) {
                commentForm.addEventListener('submit', async function(e) {
                    e.preventDefault();
                    
                    const postId = this.dataset.postId;
                    const content = document.getElementById('comment-content').value;
                    
                    if(!content.trim()) {
                        alert('Комментарий не может быть пустым');
                        return;
                    }
                    
                    try {
                        const response = await fetch('add_comment.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                            },
                            body: JSON.stringify({
                                post_id: postId,
                                content: content
                            })
                        });
                        
                        const data = await response.json();
                        
                        if(data.success) {
                            const commentsList = document.getElementById('comments-list');
                            const newComment = document.createElement('div');
                            newComment.className = 'comment';
                            newComment.dataset.commentId = data.comment.id;
                            newComment.innerHTML = `
                                <div class="comment-meta">
                                    <span class="comment-author">${data.comment.username}</span>
                                    <span class="comment-date">${data.comment.created_at}</span>
                                </div>
                                <div class="comment-content">
                                    ${data.comment.content.replace(/\n/g, '<br>')}
                                </div>
                                <button class="like-btn" onclick="likeComment(this)" data-id="${data.comment.id}">❤️ <span>0</span></button>
                            `;
                            commentsList.insertBefore(newComment, commentsList.firstChild);
                            
                            document.getElementById('comment-content').value = '';
                            
                            const commentsCount = document.querySelector('.comments h2');
                            const currentCount = parseInt(commentsCount.textContent.match(/\d+/)) || 0;
                            commentsCount.textContent = `Комментарии (${currentCount + 1})`;
                        } else {
                            alert(data.error || 'Ошибка при добавлении комментария');
                        }
                    } catch(error) {
                        console.error('Error:', error);
                        alert('Произошла ошибка при отправке комментария');
                    }
                });
            }
        });
        
        function likeComment(btn) {
            const span = btn.querySelector('span');
            const currentLikes = parseInt(span.textContent) || 0;
            span.textContent = currentLikes + 1;
            btn.style.opacity = '0.5';
            btn.disabled = true;
        }
    </script>
</body>
</html>