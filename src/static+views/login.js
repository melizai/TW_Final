$(document).ready(function() {
    $('#loginForm').submit(function(event) {
      event.preventDefault(); // Prevent the form from submitting normally

      var username = $('#username').val();
      var password = $('#password').val();

      console.log(username, password);

      // Make an AJAX request to the API for authentication
      $.ajax({
        url: 'http://localhost/api/users/login',
        method: 'POST',
        data: JSON.stringify({
          username: username,
          password: password
        }),
        success: function(response) {
          //console.log(response);
          // Save the session token in localStorage
          localStorage.setItem('session', JSON.stringify({
            Authorization: response.auth,
            username: response.data.username,
            user_type: response.data.type
          }));

          window.location.href = 'movies.html';
        },
        error: function(xhr, status, error) {
          // Handle the error response here
          console.log(xhr, error, status);
        }
      });
    });
  });
