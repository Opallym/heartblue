let position = 0;

const slider = document.getElementById('testimonial-slider');
const totalCards = slider.children.length;
const visibleCards = 5;

function slideTestimonials(direction) {
    const maxPosition = Math.ceil(totalCards / visibleCards) - 1;

    position += direction;

    if (position < 0) position = 0;
    if (position > maxPosition) position = maxPosition;

    const slideWidth = slider.children[0].offsetWidth + 16; // includes gap between cards
    slider.style.transform = `translateX(-${position * slideWidth * visibleCards}px)`;
}
