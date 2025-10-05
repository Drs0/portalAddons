jQuery(document).ready(function ($) {
  $('#confirm-password').on('input', function () {
    const password = $('#password').val();
    const confirmPassword = $(this).val();

    if (confirmPassword !== password) {
      $('#confirm-error').show();
      $(this).css('border', '2px solid red');
    } else {
      $('#confirm-error').hide();
      $(this).css('border', '');
    }
  });

  $('#signup-form').on('submit', function (e) {
    const password = $('#password').val();
    const confirmPassword = $('#confirm-password').val();

    if (password !== confirmPassword) {
      e.preventDefault();
      $('#confirm-error').show();
      $('#confirm-password').css('border', '2px solid red');
      if (typeof Swal !== 'undefined') {
        Swal.fire({
          icon: "error",
          title: "Oops...",
          text: "Passwords do not match!",
        });
      } else {
        alert("Passwords do not match!");
      }
    }
  });
});
