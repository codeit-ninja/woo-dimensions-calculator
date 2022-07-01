document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('_woo-product-dimensions_form');

    form.addEventListener('keyup', (event) => {
        if( event.keyCode !== 13 ) return false;

        const quantityInput = document.querySelector('[name=quantity]');

        const data = new FormData( form );
        const x = parseInt(data.get('_woo_product_dimensions_x'));
        const y = parseInt(data.get('_woo_product_dimensions_y'));

        quantityInput.value = Math.ceil(x * y / 10000);
    })
})