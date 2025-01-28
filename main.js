// News and Events Data
const newsData = [
    {
        title: "New Academic Programs Announced",
        date: "March 15, 2024",
        description: "Exciting new programs in Data Science and Environmental Studies."
    },
    {
        title: "Research Grant Awarded",
        date: "March 10, 2024",
        description: "University receives major research grant for sustainable agriculture."
    },
    {
        title: "International Conference",
        date: "March 5, 2024",
        description: "Annual International Conference on Education Innovation."
    }
];

// Populate News Grid
function populateNews() {
    const newsGrid = document.getElementById('newsGrid');
    
    newsData.forEach(news => {
        const newsCard = document.createElement('div');
        newsCard.className = 'news-card';
        newsCard.innerHTML = `
            <h3>${news.title}</h3>
            <p class="date">${news.date}</p>
            <p>${news.description}</p>
        `;
        newsGrid.appendChild(newsCard);
    });
}

// Initialize when DOM is loaded
document.addEventListener('DOMContentLoaded', () => {
    populateNews();
});

// Smooth scroll for navigation links
document.querySelectorAll('a[href^="#"]').forEach(anchor => {
    anchor.addEventListener('click', function (e) {
        e.preventDefault();
        document.querySelector(this.getAttribute('href')).scrollIntoView({
            behavior: 'smooth'
        });
    });
});


document.addEventListener('DOMContentLoaded', () => {
    const hamburger = document.querySelector('.hamburger');
    const navLinks = document.querySelector('.nav-links');
    
    hamburger.addEventListener('click', () => {
        navLinks.classList.toggle('active');
    });
});
