window.addEventListener('click', function(e) {
  if (e.target.matches('.btn-toggle-password'))
    togglePassword(e.target)
})

function randomPassword() {
  let passwords = document.querySelectorAll('.password-random');
  let str = randomString();

  for (let i = 0; i < passwords.length; ++i)
    passwords[i].placeholder = str;
}

randomPassword();

function randomString(length = 8) {
  let chars = "abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ0123456789";
  let str = "";

  for (let i = 0; i < length; ++i)
    str += chars.charAt(Math.floor(Math.random() * chars.length));
  return str;
}

function togglePassword(target) {
  let passwords = document.querySelectorAll('input.password')
  let type = '';

  if (target.classList.contains('la-eye-slash')) {
    type = 'text'
  }
  else {
    type = 'password'
  }

  target.classList.toggle('la-eye-slash')
  target.classList.toggle('la-eye')

  for (let i = 0; i < passwords.length; i++) {
    passwords[i].type =  type
  }
}