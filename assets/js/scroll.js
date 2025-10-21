document.addEventListener('DOMContentLoaded', function () {
    const navbar = document.querySelector('.navbar');

    // Function to handle scroll event
    const handleScroll = () => {
        if (window.scrollY > 50) {
            navbar.classList.add('scrolled');
        } else {
            navbar.classList.remove('scrolled');
        }
    };

    // Add scroll event listener
    window.addEventListener('scroll', handleScroll);

    // Initial check in case the page is already scrolled
    handleScroll();
});
