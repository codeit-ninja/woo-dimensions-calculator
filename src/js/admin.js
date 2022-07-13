document.addEventListener('DOMContentLoaded', () => {
    const formCheckbox = document.getElementById('_woo_calculator_show_form');
    const fromQuantityTotal = document.querySelector('._woo_calculator_square_meter_total_field');

    toggleQuantityField();

    formCheckbox.addEventListener('change', toggleQuantityField)

    function toggleQuantityField() {
        if (formCheckbox.checked) {
            fromQuantityTotal.style.display = 'block';
        } else {
            fromQuantityTotal.style.display = 'none';
        }
    }
})