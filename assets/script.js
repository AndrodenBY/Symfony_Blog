function submitDeleteForm(event) {
    if (confirm('Are you sure you want to delete this blog?')) {
        event.preventDefault();
        document.getElementById('delete-form').submit();
    }
}

/*function submitCommentForm() {
    document.querySelector('#create-comment-form textarea[name="content"]').value = document.getElementById('comment-content').value;
    document.getElementById('create-comment-form').submit();
}*/

function submitCommentForm() {
    var content = document.getElementById('comment-content').value;
    var form = document.getElementById('comment-form');

    var hiddenField = document.createElement('input');
    hiddenField.type = 'hidden';
    hiddenField.name = 'content';
    hiddenField.value = content;
    form.appendChild(hiddenField);

    form.submit();
}