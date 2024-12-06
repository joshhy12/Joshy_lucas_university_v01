document.addEventListener('DOMContentLoaded', () => {
    const slides = document.querySelector('.slides');
    const slideItems = document.querySelectorAll('.slide');
    const prevBtn = document.querySelector('.prev');
    const nextBtn = document.querySelector('.next');
    const dotsContainer = document.querySelector('.dots');
    
    let currentSlide = 0;
    
    // Create dots
    slideItems.forEach((_, index) => {
      const dot = document.createElement('div');
      dot.classList.add('dot');
      if (index === 0) dot.classList.add('active');
      dot.addEventListener('click', () => goToSlide(index));
      dotsContainer.appendChild(dot);
    });
    
    const dots = document.querySelectorAll('.dot');
    
    function updateDots() {
      dots.forEach((dot, index) => {
        dot.classList.toggle('active', index === currentSlide);
      });
    }
    
    function goToSlide(index) {
      currentSlide = index;
      slides.style.transform = `translateX(-${currentSlide * 100}%)`;
      updateDots();
    }
    
    prevBtn.addEventListener('click', () => {
      currentSlide = (currentSlide - 1 + slideItems.length) % slideItems.length;
      goToSlide(currentSlide);
    });
    
    nextBtn.addEventListener('click', () => {
      currentSlide = (currentSlide + 1) % slideItems.length;
      goToSlide(currentSlide);
    });
    
    // Auto-slide every 5 seconds
    setInterval(() => {
      currentSlide = (currentSlide + 1) % slideItems.length;
      goToSlide(currentSlide);
    }, 5000);
  });
  