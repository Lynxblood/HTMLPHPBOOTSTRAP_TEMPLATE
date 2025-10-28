// main.js
document.addEventListener('DOMContentLoaded', () => {
    const button = document.getElementById('myButton');

    button.addEventListener('click', () => {
        alert('JavaScript is working!');
        // You could use fetch() here to talk to a PHP API endpoint (e.g., api/data.php)
    });
});