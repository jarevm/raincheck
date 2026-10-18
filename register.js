// show the access code box only when Admin is picked
function showCodeBox() {
    var role = document.getElementById('role').value;
    var box = document.getElementById('codebox');
    var code = document.getElementById('code');

    if (role == 'ADMIN') {
        box.style.display = 'block';
        code.required = true;
    } else {
        box.style.display = 'none';
        code.required = false;
    }
}

function checkPasswords() {
    var pass = document.getElementById('password').value;
    var confirm = document.getElementById('confirm').value;
    if (pass != confirm) {
        alert('Passwords do not match');
        return false;
    }
    return true;
}
