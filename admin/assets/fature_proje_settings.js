
function email_validation() {
  let email = document.getElementById('paymendo_api_mail').value
  let text = document.getElementById('text')
  let pattern = /^[^ ]+@[^ ]+\.[a-z]{2,3}$/
  if (email.match(pattern)) {
    text.innerHTML = ""
    text.style.color = '#00ff00'
  } 
  else {
    text.innerHTML = "Lütfen Geçerli Bir Mail Adresi Girin"
    text.style.color = '#ff0000'
  }
  if (email == '') {
    text.innerHTML = ""
    text.style.color = '#00ff00'
  }
}