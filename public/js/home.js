let currentSlide = 0;

function slideTestimonials(direction) {
    const slider = document.getElementById('testimonial-slider');
    const slides = slider.children.length;
    const slideWidth = slider.children[0].offsetWidth + 32; // 32 = gap between cards

    currentSlide += direction;
    if (currentSlide < 0) currentSlide = slides - 1;
    if (currentSlide >= slides) currentSlide = 0;

    slider.style.transform = `translateX(${-slideWidth * currentSlide}px)`;
}
