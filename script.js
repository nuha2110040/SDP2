// Toggle mobile menu
function toggleMenu() {
  const navLinks = document.getElementById("nav-links").querySelector("ul");
  navLinks.classList.toggle("show");
}

// Modal login
const modal = document.getElementById("login-modal");
const loginBtn = document.getElementById("login-btn");

loginBtn.onclick = function (e) {
  e.preventDefault();
  modal.style.display = "flex";
};

function closeModal() {
  modal.style.display = "none";
}

window.onclick = function (event) {
  if (event.target == modal) {
    modal.style.display = "none";
  }
};
