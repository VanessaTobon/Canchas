document.addEventListener("DOMContentLoaded", function () {
    const sliders = document.querySelectorAll(".cancha-slider");

    sliders.forEach((slider) => {
        let index = 0;
        const images = slider.querySelectorAll("img");

        function showNextImage() {
            images[index].classList.remove("active");
            index = (index + 1) % images.length;
            images[index].classList.add("active");
        }

        setInterval(showNextImage, 3000); // Cambia cada 3 segundos
    });
});
