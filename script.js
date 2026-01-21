// ========================================
// Mobile Menu Toggle
// ========================================
const menuToggle = document.getElementById('menuToggle');
const navLinks = document.querySelector('.nav-links');

menuToggle.addEventListener('click', () => {
    menuToggle.classList.toggle('active');
    navLinks.classList.toggle('active');
});

// Close menu when clicking on a nav link
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', () => {
        menuToggle.classList.remove('active');
        navLinks.classList.remove('active');
    });
});

// ========================================
// Header Scroll Effect
// ========================================
const header = document.querySelector('.header');
let lastScroll = 0;

window.addEventListener('scroll', () => {
    const currentScroll = window.pageYOffset;
    
    if (currentScroll > 100) {
        header.style.background = 'rgba(15, 15, 35, 0.95)';
        header.style.boxShadow = '0 4px 20px rgba(0, 0, 0, 0.3)';
    } else {
        header.style.background = 'rgba(15, 15, 35, 0.8)';
        header.style.boxShadow = 'none';
    }
    
    lastScroll = currentScroll;
});

// ========================================
// Smooth Scroll for Navigation Links
// ========================================
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function(e) {
        e.preventDefault();
        const target = document.querySelector(this.getAttribute('href'));
        if (target) {
            const headerHeight = document.querySelector('.header').offsetHeight;
            const targetPosition = target.offsetTop - headerHeight;
            
            window.scrollTo({
                top: targetPosition,
                behavior: 'smooth'
            });
        }
    });
});

// ========================================
// Active Navigation Link on Scroll
// ========================================
const sections = document.querySelectorAll('section[id]');

window.addEventListener('scroll', () => {
    const scrollY = window.pageYOffset;
    
    sections.forEach(section => {
        const sectionHeight = section.offsetHeight;
        const sectionTop = section.offsetTop - 100;
        const sectionId = section.getAttribute('id');
        const navLink = document.querySelector(`.nav-link[href="#${sectionId}"]`);
        
        if (scrollY > sectionTop && scrollY <= sectionTop + sectionHeight) {
            document.querySelectorAll('.nav-link').forEach(link => link.classList.remove('active'));
            if (navLink) navLink.classList.add('active');
        }
    });
});

// ========================================
// Category Filter
// ========================================
const categoryCards = document.querySelectorAll('.category-card');
const articleCards = document.querySelectorAll('.article-card');

categoryCards.forEach(card => {
    card.addEventListener('click', () => {
        const category = card.dataset.category;
        
        // Toggle active state
        categoryCards.forEach(c => c.classList.remove('selected'));
        card.classList.add('selected');
        
        // Filter articles (for demo, we'll just add animation)
        articleCards.forEach(article => {
            article.style.opacity = '0';
            article.style.transform = 'translateY(20px)';
            
            setTimeout(() => {
                article.style.opacity = '1';
                article.style.transform = 'translateY(0)';
            }, 300);
        });
        
        // Show toast notification
        showToast(`تم اختيار تصنيف: ${card.querySelector('h3').textContent}`);
    });
});

// ========================================
// Load More Articles
// ========================================
const loadMoreBtn = document.getElementById('loadMoreBtn');
const articlesGrid = document.querySelector('.articles-grid');

const moreArticles = [
    {
        image: 'https://images.unsplash.com/photo-1504639725590-34d0984388bd?w=800',
        category: 'التقنية',
        date: '15 يناير 2026',
        readTime: '6 دقائق قراءة',
        title: 'أساسيات تصميم تجربة المستخدم UX',
        excerpt: 'تعلم المبادئ الأساسية لتصميم تجربة مستخدم مميزة وفعالة...'
    },
    {
        image: 'https://images.unsplash.com/photo-1512453979798-5ea266f8880c?w=800',
        category: 'السفر',
        date: '14 يناير 2026',
        readTime: '5 دقائق قراءة',
        title: 'استكشف جمال دبي: دليل سياحي شامل',
        excerpt: 'اكتشف أفضل الأماكن السياحية والتجارب الفريدة في دبي...'
    },
    {
        image: 'https://images.unsplash.com/photo-1498837167922-ddd27525d352?w=800',
        category: 'الصحة',
        date: '13 يناير 2026',
        readTime: '4 دقائق قراءة',
        title: 'أفضل الأطعمة لتعزيز المناعة',
        excerpt: 'قائمة بأهم الأطعمة التي تساعد على تقوية جهاز المناعة...'
    }
];

loadMoreBtn.addEventListener('click', () => {
    loadMoreBtn.classList.add('loading');
    
    // Simulate loading delay
    setTimeout(() => {
        moreArticles.forEach((article, index) => {
            const articleHTML = `
                <article class="article-card" style="animation-delay: ${index * 0.1}s">
                    <div class="article-image">
                        <img src="${article.image}" alt="${article.title}">
                        <span class="article-category">${article.category}</span>
                    </div>
                    <div class="article-content">
                        <div class="article-meta">
                            <span class="article-date">📅 ${article.date}</span>
                            <span class="article-read-time">⏱️ ${article.readTime}</span>
                        </div>
                        <h3 class="article-title">${article.title}</h3>
                        <p class="article-excerpt">${article.excerpt}</p>
                        <a href="#" class="read-more">
                            قراءة المزيد
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                <path d="M19 12H5M12 19l-7-7 7-7"/>
                            </svg>
                        </a>
                    </div>
                </article>
            `;
            
            articlesGrid.insertAdjacentHTML('beforeend', articleHTML);
        });
        
        loadMoreBtn.classList.remove('loading');
        loadMoreBtn.textContent = 'لا يوجد المزيد';
        loadMoreBtn.disabled = true;
        loadMoreBtn.style.opacity = '0.5';
        loadMoreBtn.style.cursor = 'not-allowed';
        
        showToast('تم تحميل المزيد من المقالات');
    }, 1500);
});

// ========================================
// Newsletter Form
// ========================================
const newsletterForm = document.getElementById('newsletterForm');

newsletterForm.addEventListener('submit', (e) => {
    e.preventDefault();
    
    const email = newsletterForm.querySelector('input[type="email"]').value;
    
    if (email) {
        // Simulate subscription
        newsletterForm.querySelector('button').innerHTML = '⏳';
        
        setTimeout(() => {
            newsletterForm.querySelector('button').innerHTML = '✅';
            newsletterForm.querySelector('input').value = '';
            showToast('تم الاشتراك بنجاح! شكراً لك');
            
            setTimeout(() => {
                newsletterForm.querySelector('button').innerHTML = 'اشترك الآن';
            }, 2000);
        }, 1000);
    }
});

// ========================================
// Toast Notification
// ========================================
function showToast(message) {
    // Remove existing toast if any
    const existingToast = document.querySelector('.toast');
    if (existingToast) {
        existingToast.remove();
    }
    
    // Create toast element
    const toast = document.createElement('div');
    toast.className = 'toast';
    toast.textContent = message;
    document.body.appendChild(toast);
    
    // Show toast
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // Hide toast after 3 seconds
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}

// ========================================
// Intersection Observer for Animations
// ========================================
const observerOptions = {
    threshold: 0.1,
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

// Observe elements for animation
document.querySelectorAll('.article-card, .category-card').forEach(el => {
    observer.observe(el);
});

// ========================================
// Parallax Effect for Hero Shapes
// ========================================
document.addEventListener('mousemove', (e) => {
    const shapes = document.querySelectorAll('.shape');
    const mouseX = e.clientX / window.innerWidth;
    const mouseY = e.clientY / window.innerHeight;
    
    shapes.forEach((shape, index) => {
        const speed = (index + 1) * 20;
        const x = (mouseX - 0.5) * speed;
        const y = (mouseY - 0.5) * speed;
        
        shape.style.transform = `translate(${x}px, ${y}px)`;
    });
});

// ========================================
// Article Card Hover Effect
// ========================================
document.querySelectorAll('.article-card').forEach(card => {
    card.addEventListener('mouseenter', function(e) {
        this.style.transform = 'translateY(-10px)';
    });
    
    card.addEventListener('mouseleave', function(e) {
        this.style.transform = 'translateY(0)';
    });
});

// ========================================
// Console Welcome Message
// ========================================
console.log('%c مرحباً بك في موقع مقالاتي! 📚', 'font-size: 20px; font-weight: bold; color: #6366f1;');
console.log('%c تم تطوير هذا الموقع بكل ❤️', 'font-size: 14px; color: #ec4899;');
