
function myPassword() {
  var x = document.getElementById("passwrd");
  if (x.type === "password") {
      x.type = "text";
  } else {
      x.type = "password";
  }
  var y = document.getElementById("myInput");
  if (y.type === "password") {
      y.type = "text";
  } else {
      y.type = "password";
  }
}

//////PICTURE SLIDES////////
var slideIndex = 1;
showSlides(slideIndex);

function plusSlides(n) {
    showSlides(slideIndex += n);
}

function currentSlide(n) {
    showSlides(slideIndex = n);
}

function showSlides(n) {
    var i;
    var slides = document.getElementsByClassName("mySlides");
    var dots = document.getElementsByClassName("dot");
    if (n > slides.length) { slideIndex = 1 }
    if (n < 1) { slideIndex = slides.length }
    for (i = 0; i < slides.length; i++) {
        slides[i].style.display = "none";
    }
    for (i = 0; i < dots.length; i++) {
        dots[i].className = dots[i].className.replace(" active", "");
    }
    slides[slideIndex - 1].style.display = "block";
    dots[slideIndex - 1].className += " active";
}

// When the user clicks, open the popups
function popupDirection() {
    var popup = document.getElementById("myPopup");
    popup.classList.toggle("show");
}

//
function AdjustIframeHeightOnLoad() {
  document.getElementById("myiframe").style.height = document.getElementById("myiframe").contentWindow.document.body.scrollHeight + "px";
}

function AdjustIframeHeight(i) {
  document.getElementById("myiframe").style.height = parseInt(i) + "px";
}
