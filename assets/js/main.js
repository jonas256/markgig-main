/**
 * MarkGigs Main JS (PHP Version)
 */

document.addEventListener('DOMContentLoaded', () => {
    
    // --- Avatar Dropdown ---
    const avatarToggle = document.getElementById('avatarDropdownToggle');
    const avatarMenu = document.getElementById('avatarDropdown');
    
    if (avatarToggle && avatarMenu) {
        avatarToggle.addEventListener('click', (e) => {
            e.stopPropagation();
            avatarMenu.classList.toggle('show');
        });
        
        document.addEventListener('click', () => {
            avatarMenu.classList.remove('show');
        });
    }

    // --- Flash Messages Auto-dismiss ---
    const flashes = document.querySelectorAll('.flash');
    flashes.forEach(flash => {
        setTimeout(() => {
            flash.style.opacity = '0';
            flash.style.transform = 'translateY(-20px)';
            setTimeout(() => flash.remove(), 500);
        }, 5000);
    });

    // --- Navbar Scroll Effect ---
    const nav = document.getElementById('mainNav');
    window.addEventListener('scroll', () => {
        if (window.scrollY > 20) {
            nav.classList.add('scrolled');
        } else {
            nav.classList.remove('scrolled');
        }
    });

    // --- Chat Auto-scroll ---
    const chatThread = document.getElementById('chatThread');
    if (chatThread) {
        chatThread.scrollTop = chatThread.scrollHeight;
    }

});
