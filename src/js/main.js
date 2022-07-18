document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('_woo-product-dimensions_form');

    function updateQuantity() {
        if ( ! ci_wc_settings || ! ci_wc_product ) return;

        const quantityInput = document.querySelector('[name=quantity]');
        let multiplier;

        const inputUnit = ci_wc_settings['form-settings']['measurement-unit-input'];
        const measuringUnit = ci_wc_settings['form-settings']['measurement-unit'];

        if( 'm' === inputUnit && measuringUnit === 'm2' ) multiplier = 1;
        if( 'cm' === inputUnit && measuringUnit === 'm2' ) multiplier = 100;
        if( 'mm' === inputUnit && measuringUnit === 'm2' ) multiplier = 1000;
        if( 'µm' === inputUnit && measuringUnit === 'm2' ) multiplier = 1000000;

        if( 'm' === inputUnit && measuringUnit === 'cm2' ) multiplier = 0.01;
        if( 'cm' === inputUnit && measuringUnit === 'cm2' ) multiplier = 1;
        if( 'mm' === inputUnit && measuringUnit === 'cm2' ) multiplier = 10;
        if( 'µm' === inputUnit && measuringUnit === 'cm2' ) multiplier = 10000;

        if( 'm' === inputUnit && measuringUnit === 'mm2' ) multiplier = 0.001;
        if( 'cm' === inputUnit && measuringUnit === 'mm2' ) multiplier = 0.01;
        if( 'mm' === inputUnit && measuringUnit === 'mm2' ) multiplier = 1;
        if( 'µm' === inputUnit && measuringUnit === 'mm2' ) multiplier = 1000;

        const data = new FormData( form );

        const total = parseInt(data.get('_woo_calculator_square_meter_total'))
        const x = parseInt(data.get('_woo_product_dimensions_x'));
        const y = parseInt(data.get('_woo_product_dimensions_y'));

        if ( ! x || ! y ) return;

        const converted = {
            x: x / multiplier,
            y: y / multiplier
        }

        quantityInput.value = Math.ceil(converted.x * converted.y / total);

        const currencySymbol = document.querySelector('.woocommerce-Price-currencySymbol').innerText;
        const previewContainer = document.getElementById('_woo-product-dimensions_preview');

        while( previewContainer.firstChild )
            previewContainer.removeChild( previewContainer.firstChild );

        const previewPrice = document.createElement('span');
              previewPrice.classList.add('woocommerce-Price-amount', 'price', 'woo-product-dimensions-price');
              previewPrice.style.fontSize = '1.3rem';

        const previewText = document.createElement('span');
              previewText.classList.add('woo-product-dimensions-total-squared');

        previewPrice.appendChild(document.createTextNode(`${currencySymbol} ${(ci_wc_product.price * quantityInput.value)}`))
        previewText.appendChild(document.createTextNode(`${ci_wc_settings['form-settings']['measurement-unit']}: ${quantityInput.value * total}`))

        previewContainer.append(previewPrice, previewText);
    }

    form.addEventListener('change', updateQuantity);
    form.addEventListener('keyup', updateQuantity);
})