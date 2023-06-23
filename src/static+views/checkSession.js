function deleteSession() {
  // Remove the session data from local storage
  localStorage.removeItem('session');
  
  // Redirect the user to the login page or any other appropriate page
  window.location.href = 'login.html';
}

function checkSession() {
    // Get the session data from local storage
    var session = localStorage.getItem('session');
    
    // Check if the session data exists
    if (session) {
      // Parse the session data from JSON
      session = JSON.parse(session);
      
      // Use the session data as needed (e.g., display username)
      console.log('Logged in as: ' + session.username);
      var log = document.getElementById('log');
      // Redirect the user to the home page or any other authenticated page
    //   window.location.href = 'home.html';
    document.getElementById('log').innerHTML = 'Logout';
    log.addEventListener('click', deleteSession);

    }
    // else {
    //   document.getElementById('log').innerHTML = 'Login';
    // }
  }
  
  // Call the checkSession function when the page loads to check if a session exists
  checkSession();