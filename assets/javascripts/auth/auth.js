document.addEventListener('DOMContentLoaded', function () {
  const passwordInput = document.getElementById('password');
  const confirmInput = document.getElementById('confirm-password');
  const confirmError = document.getElementById('confirm-error');
  const signupForm = document.getElementById('signup-form');

  if (!passwordInput || !confirmInput || !signupForm) return;
  confirmInput.addEventListener('input', function () {
    const password = passwordInput.value;
    const confirmPassword = confirmInput.value;

    if (confirmPassword !== password) {
      confirmError.style.display = 'block';
      confirmInput.style.border = '2px solid red';
    } else {
      confirmError.style.display = 'none';
      confirmInput.style.border = '';
    }
  });

  signupForm.addEventListener('submit', function (e) {
    const password = passwordInput.value;
    const confirmPassword = confirmInput.value;

    if (password !== confirmPassword) {
      e.preventDefault();
      confirmError.style.display = 'block';
      confirmInput.style.border = '2px solid red';
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: 'error',
          title: 'Oops...',
          text: 'Passwords do not match!',
        });
      } else {
        alert('Passwords do not match!');
      }
    }
  });
});
