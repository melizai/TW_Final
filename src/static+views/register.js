$(document).ready(function() {
    $('#registrationForm').submit(function(event) {
      event.preventDefault(); // Prevent the form from submitting normally

      var username = $('#username').val();
      var password = $('#password').val();
      var email = $('#email').val();
      var age = $('#age').val();
      var country = $('#country').val();

    //   console.log(username, password);

      // Make an AJAX request to the API for authentication
      $.ajax({
        url: 'http://localhost/api/users/register',
        method: 'POST',
        data: JSON.stringify({
          username: username,
          password: password,
          email: email, 
          age: age,
          country: country
        }),
        success: function(response) {
          // Handle the successful response here
          console.log(response);
          
          // Save the session token in localStorage

          // Redirect to another page or perform other actions
          window.location.href = 'login.html';
        },
        error: function(xhr, status, error) {
          // Handle the error response here
          console.log(xhr, error, status);
        }
      });
    });
  });


// function registerUser(event) {
//     event.preventDefault(); // Prevent form submission

//     // Get form inputs
//     const username = document.getElementById("username").value;
//     const password = document.getElementById("password").value;
//     const email = document.getElementById("email").value;
//     const age = document.getElementById("age").value;
//     const country = document.getElementById("country").value;

//     // Create an object with user data
//     const userData = {
//         username: username,
//         password: password,
//         email: email,
//         age: age,
//         country: country
//     };

//     // Make a POST request to the API endpoint
//     fetch("http://localhost/api/users/register", {
//         method: "POST",
//         headers: {
//             "Content-Type": "application/json"
//         },
//         body: JSON.stringify(userData)
//     })
//     .then(response => response.json())
//     .then(data => {
//         // Assuming the API returns a session token upon successful registration
//         if (data.sessionToken) {
//             // Save the session token in the browser's session storage
//             sessionStorage.setItem("sessionToken", data.sessionToken);
//             alert("Registration successful. You are now logged in.");
//             // Redirect the user to the dashboard or another page
//             window.location.href = "shows.html";
//         } else {
//             alert("Registration failed. Please try again.");
//         }
//     })
//     .catch(error => {
//         console.error("Error:", error);
//         alert("An error occurred. Please try again.");
//     });
// }

// // Attach the registerUser function to the form's submit event
// const registrationForm = document.getElementById("registrationForm");
// registrationForm.addEventListener("submit", registerUser);