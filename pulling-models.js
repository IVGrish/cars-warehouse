let carArray = {
    "Audi": ['A4', 'A6', 'A7', 'Q3', 'Q5', 'TT'],
    "Renault": ['Scenic', 'Duster', 'Fluence', 'Megane', 'Sandero', 'Logan'],
    "BMW": ['1', '3', '5', '7', 'X1', 'X3', 'X5'],
};

const brandInput = document.querySelector('#brand');
const modelSelect = document.querySelector('#model');

brandInput.addEventListener('change', (event) => {
    modelSelect.disabled = false;
    removeModels(event.target.value, modelSelect);
    appendModels(event.target.value, modelSelect, carArray);
});

function removeModels(brand, model)
{
    const optGroups = document.querySelectorAll('#model optgroup');
    for (let optGroup of optGroups) {
        if (optGroup.label !== brand) {

            model.removeChild(optGroup);
        }
    }
}

function appendModels(brand, model, arr)
{
    let opt = document.createElement('optgroup');
    for (let prop in arr) {
        if (brand === prop) {
            opt.label = prop;

            model.appendChild(opt);

            for (let value of arr[prop]) {
                let optionElement = document.createElement('option');

                optionElement.textContent = value;

                opt.appendChild(optionElement);
            }
        }
    }
}