function deleteSession() {
    // Remove the session data from local storage
    localStorage.removeItem('session');
    
    // Redirect the user to the login page or any other appropriate page
    window.location.href = 'login.html';
  }
  
  // Call the deleteSession function when you want to delete the session
  deleteSession();