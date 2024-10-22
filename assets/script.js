function submitDeleteForm(event) {
    if (confirm('Are you sure you want to delete this blog?')) {
        event.preventDefault();
        document.getElementById('delete-form').submit();
    }
}
