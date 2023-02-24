const form = document.querySelector('#car-form');

form.addEventListener('submit', (event) => {
    fetch('ajax-send-form.php', {
        method: 'POST',
        body: new FormData(form),
    }).then(
        response => {
            if (!response.ok) {
                throw new Error(`HTTP error! Status: ${response.status}`);
            }
            return response.json();
        }
    ).then(
        data => {
            if (form.contains(document.querySelector('.form-text'))) {
                form.removeChild(document.querySelector('.form-text'));
            }
            if (data['databaseResult'] === 'success' && data['mailResult'] === 'success') {
                let newElem = document.createElement('div');
                newElem.innerHTML = "Спасибо, что выбрали именно наш сервис!<br>"
                                  + "Запрос добавлен в нашу базу данных<br>"
                                  + "Ремонт будет выполнен в ближайшее время<br>"
                                  + "Сообщение отправлено на почту";
                newElem.classList.add('form-text', 'text-center', 'mt-2');
                form.appendChild(newElem);
            } else {
                let newElem = document.createElement('div');
                newElem.innerHTML = "К сожалениию что-то пошло не так, <br>"
                                  + "попробуйте отправить запрос заново";
                newElem.classList.add('form-text', 'text-center', 'mt-2');
                form.appendChild(newElem);
            }
            console.log(data);
        }
    );

    event.preventDefault();
});