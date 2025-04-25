document.addEventListener('DOMContentLoaded', function() {
    const switchers = [...document.querySelectorAll('.switcher')];

    // Event listener for each switcher (Login/Register buttons)
    switchers.forEach(item => {
        item.addEventListener('click', function() {
            // Remove the 'is-active' class from all form wrappers
            switchers.forEach(item => item.parentElement.classList.remove('is-active'));

            // Add the 'is-active' class to the clicked form wrapper
            this.parentElement.classList.add('is-active');
        });
    });

    // Check if the form is being submitted
    const loginForm = document.querySelector('.form-login');
    if (loginForm) {
        loginForm.addEventListener('submit', function(event) {
            console.log("Form is being submitted...");

            // Optionally, you could add a pre-submit check or custom validation here
            // event.preventDefault(); // Uncomment this if you want to prevent the default submit action for testing
        });
    }
});
