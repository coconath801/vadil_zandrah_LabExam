// Show / hide password buttons
document.querySelectorAll('.toggle-pw').forEach(function (btn) {
    btn.addEventListener('click', function () {
        var input = document.getElementById(btn.dataset.target);
        var show = input.type === 'password';
        input.type = show ? 'text' : 'password';
        btn.textContent = show ? 'Hide' : 'Show';
    });
});

// Update the "Admin ID / Employee ID" label when the role switch changes
var idLabel = document.getElementById('idLabel');
document.querySelectorAll('input[name="role"]').forEach(function (radio) {
    radio.addEventListener('change', function () {
        if (idLabel) {
            idLabel.textContent = (radio.value === 'admin' ? 'Admin' : 'Employee') + ' ID:';
        }
    });
});

// Light client-side check on the registration form (server still validates everything)
var reg = document.getElementById('registerForm');
if (reg) {
    reg.addEventListener('submit', function (e) {
        var pw = document.getElementById('password').value;
        var cf = document.getElementById('confirm_password').value;
        if (pw !== cf) {
            e.preventDefault();
            alert('Passwords do not match.');
        }
    });
}