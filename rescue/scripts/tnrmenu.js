window.addEventListener('DOMContentLoaded', (click) => {


    const navSlide = () => {
    const burger = document.querySelector('.burger-tnr');
    const nav = document.querySelector('.menu');
    const navLinks = document.querySelectorAll('.menu li');

        burger.addEventListener('click', () => {
            
            //Toggle Nav
            nav.classList.toggle('nav-active');

            //Animate links
            navLinks.forEach((link, index) => {
                if (link.style.animation) {
                    link.style.animation = '';
                } else {
                    link.style.animation = `navLinkFade 0.5s ease forwards ${index / 7 + 0.5}s`;
                }
            });

            //Burger animation
            burger.classList.toggle('toggle');
      
        });
    };

navSlide ();

});
