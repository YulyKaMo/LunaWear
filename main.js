document.addEventListener("DOMContentLoaded", function () {

  const emailInput = document.getElementById("email");
  const phoneInput = document.getElementById("telefono");
  const messageText = document.getElementById("consulta");
  const submitButton = document.getElementById("submitButton");

  emailInput.addEventListener("focus", function () {
    phoneInput.disabled = false;
    messageText.disabled = false;
  });

  emailInput.addEventListener("input", function () {
    if (emailInput.value.trim() !== "") {
      submitButton.disabled = false;
    } else {
      submitButton.disabled = true;
    }
  });

});