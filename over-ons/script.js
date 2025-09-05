// Wait for the DOM to load fully
document.addEventListener("DOMContentLoaded", function () {
  const slideContainers = document.querySelectorAll(".w3-content");

  slideContainers.forEach((container) => {
    let index = 0;
    const slides = container.querySelectorAll(".mySlides");

    function showSlides() {
      slides.forEach((slide) => (slide.style.display = "none"));
      index++;
      if (index > slides.length) index = 1;
      slides[index - 1].style.display = "block";

      setTimeout(showSlides, 2000); // Change image every 2 seconds
    }

    showSlides(); // start the slideshow for this container
  });
});