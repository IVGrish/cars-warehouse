let phoneInput = document.querySelector('#phone');

let phoneMask = new IMask(phoneInput, {
    mask: '+375(00)000-00-00',
    lazy: false,
});
