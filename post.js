// show a small preview of the chosen photo
function showPreview() {
    var file = document.getElementById('photo').files[0];
    var preview = document.getElementById('preview');

    if (file) {
        preview.src = URL.createObjectURL(file);
        preview.style.display = 'block';
    } else {
        preview.style.display = 'none';
    }
}
