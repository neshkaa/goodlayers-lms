window.addEventListener('DOMContentLoaded', () => {
  // Original navigation slide functionality
  const navSlide = () => {
    const burger = document.querySelector('.burger-tnr');
    const nav = document.querySelector('.menu');
    const navLinks = document.querySelectorAll('.menu li');
    
    burger.addEventListener('click', () => {
      // Toggle Nav
      nav.classList.toggle('nav-active');
      
      // Animate links
      navLinks.forEach((link, index) => {
        if (link.style.animation) {
          link.style.animation = '';
        } else {
          link.style.animation = `navLinkFade 0.5s ease forwards ${index / 7 + 0.5}s`;
        }
      });
      
      // Burger animation
      burger.classList.toggle('toggle');
    });
  };
  
  // New submenu toggle functionality
  const submenuToggle = () => {
    // Only run on mobile screens
    if (window.innerWidth <= 768) {
      const menuItemsWithChildren = document.querySelectorAll('.tnr-nav ul.menu li.menu-item-has-children');
      
      // Add chevron icons
      menuItemsWithChildren.forEach(item => {
        // Check if chevron already exists
        if (!item.querySelector('.chevron-icon')) {
          const chevron = document.createElement('span');
          chevron.className = 'chevron-icon';
          item.appendChild(chevron);
        }
        
        // Get the anchor element and chevron within the menu item
        const link = item.querySelector('a');
        const chevron = item.querySelector('.chevron-icon');
        
        // Remove any existing click handlers
        link.removeEventListener('click', handleLinkClick);
        if (chevron) {
          chevron.removeEventListener('click', handleChevronClick);
        }
        
        // Add new click handlers
        link.addEventListener('click', handleLinkClick);
        if (chevron) {
          chevron.addEventListener('click', handleChevronClick);
        }
      });
    }
  };
  
  function handleLinkClick(event) {
    // Let the link behave normally (navigate to the page)
  }
  
  function handleChevronClick(event) {
    event.preventDefault();
    event.stopPropagation();
    
    const menuItem = this.parentNode;
    
    // Close other open submenus
    const allItems = document.querySelectorAll('.tnr-nav ul.menu li.menu-item-has-children');
    allItems.forEach(item => {
      if (item !== menuItem && item.classList.contains('submenu-active')) {
        item.classList.remove('submenu-active');
      }
    });
    
    // Toggle this submenu
    menuItem.classList.toggle('submenu-active');
  }
  
  // Call both functions
  navSlide();
  submenuToggle();
  
  // Re-run submenu toggle on window resize
  window.addEventListener('resize', () => {
    submenuToggle();
  });
});