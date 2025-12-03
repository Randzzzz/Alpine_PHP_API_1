document.addEventListener("DOMContentLoaded", () => {
  const scrollToTopBtn = document.getElementById("scrollToTopBtn");

  window.addEventListener("scroll", () => {
    if (window.scrollY > 200) {
      scrollToTopBtn.classList.remove("hidden");
    } else {
      scrollToTopBtn.classList.add("hidden");
    }
  });

  scrollToTopBtn.addEventListener("click", () => {
    window.scrollTo({ top: 0 });
  });
});
