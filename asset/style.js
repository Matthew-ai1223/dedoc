// Wait for the DOM to be fully loaded
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const menuIcon = document.querySelector('.menu-icon');
    const navLinks = document.querySelector('.nav-links');
    
    menuIcon.addEventListener('click', () => {
        navLinks.classList.toggle('active');
        const icon = menuIcon.querySelector('i');
        icon.classList.toggle('fa-bars');
        icon.classList.toggle('fa-times');
    });

    // Close mobile menu when clicking outside
    document.addEventListener('click', (e) => {
        if (!menuIcon.contains(e.target) && !navLinks.contains(e.target) && navLinks.classList.contains('active')) {
            navLinks.classList.remove('active');
            const icon = menuIcon.querySelector('i');
            icon.classList.add('fa-bars');
            icon.classList.remove('fa-times');
        }
    });

    // Close mobile menu when clicking on a link
    navLinks.querySelectorAll('a').forEach(link => {
        link.addEventListener('click', () => {
            navLinks.classList.remove('active');
            const icon = menuIcon.querySelector('i');
            icon.classList.add('fa-bars');
            icon.classList.remove('fa-times');
        });
    });

    // Set current year in footer
    document.getElementById('currentYear').textContent = new Date().getFullYear();
    
    // Handle splash screen
    const splashScreen = document.querySelector('.splash-screen');
    
    // Hide splash screen after animation
    setTimeout(() => {
        splashScreen.style.display = 'none';
        // Start hero animations after splash screen
        startHeroAnimations();
    }, 3500);

    // Start hero animations
    function startHeroAnimations() {
        const heroContent = document.querySelector('.hero-content');
        const heroImage = document.querySelector('.hero-image');
        const stats = document.querySelectorAll('.stat-number');
        
        // Animate hero content
        heroContent.style.opacity = '0';
        heroContent.style.transform = 'translateY(20px)';
        setTimeout(() => {
            heroContent.style.transition = 'all 0.8s ease-out';
            heroContent.style.opacity = '1';
            heroContent.style.transform = 'translateY(0)';
        }, 100);

        // Animate hero image
        if (heroImage) {
            heroImage.style.opacity = '0';
            heroImage.style.transform = 'translateX(20px)';
            setTimeout(() => {
                heroImage.style.transition = 'all 0.8s ease-out 0.3s';
                heroImage.style.opacity = '1';
                heroImage.style.transform = 'translateX(0)';
            }, 100);
        }

        // Animate statistics with counting effect
        stats.forEach(stat => {
            const targetNumber = parseInt(stat.textContent);
            animateNumber(stat, targetNumber);
        });
    }

    // Animate numbers
    function animateNumber(element, target) {
        let current = 0;
        const increment = target / 50; // Adjust speed
        const duration = 1500; // Animation duration in milliseconds
        const interval = duration / 50;

        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                element.textContent = target + (target.toString().includes('K') ? 'K+' : '%');
                clearInterval(timer);
            } else {
                element.textContent = Math.floor(current) + (target.toString().includes('K') ? 'K+' : '%');
            }
        }, interval);
    }

    // Smooth scroll for navigation links
    document.querySelectorAll('a[href^="#"]').forEach(anchor => {
        anchor.addEventListener('click', function(e) {
            e.preventDefault();
            const target = document.querySelector(this.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        });
    });

    // Parallax effect for hero shape
    const heroShape = document.querySelector('.hero-shape');
    if (heroShape) {
        window.addEventListener('mousemove', (e) => {
            const mouseX = e.clientX / window.innerWidth - 0.5;
            const mouseY = e.clientY / window.innerHeight - 0.5;
            
            heroShape.style.transform = `translate(${mouseX * 20}px, ${mouseY * 20}px)`;
        });
    }

    // Header scroll effect
    const header = document.querySelector('.header');
    let lastScroll = 0;

    window.addEventListener('scroll', () => {
        const currentScroll = window.pageYOffset;

        // Add blur effect to header on scroll
        if (currentScroll > 50) {
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.9)';
            header.style.backdropFilter = 'blur(10px)';
        } else {
            header.style.backgroundColor = 'rgba(255, 255, 255, 0.95)';
            header.style.backdropFilter = 'blur(0px)';
        }

        if (currentScroll <= 0) {
            header.classList.remove('scroll-up');
            return;
        }

        if (currentScroll > lastScroll && !header.classList.contains('scroll-down')) {
            // Scroll down
            header.classList.remove('scroll-up');
            header.classList.add('scroll-down');
        } else if (currentScroll < lastScroll && header.classList.contains('scroll-down')) {
            // Scroll up
            header.classList.remove('scroll-down');
            header.classList.add('scroll-up');
        }

        lastScroll = currentScroll;
    });

    // Feature cards animation on scroll
    const featureCards = document.querySelectorAll('.feature-card');
    
    const observerOptions = {
        threshold: 0.2,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }
        });
    }, observerOptions);

    featureCards.forEach((card, index) => {
        card.style.opacity = '0';
        card.style.transform = 'translateY(20px)';
        card.style.transition = 'all 0.5s ease';
        card.style.transitionDelay = `${index * 0.1}s`;
        observer.observe(card);
    });
});
