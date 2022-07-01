<?php
/**
 * @var codeit\WooCommerce_Dimensions_Calculator\Woo_Calculator $this
 */
$heading = $this->options->get_option('heading', 'form-settings') ?? 'ENTER SIZES (INCLUDING MARGIN)';
$measurement_label_x = $this->options->get_option('measurement-x', 'form-settings') ?? 'cm';
$measurement_label_y = $this->options->get_option('measurement-y', 'form-settings') ?? 'cm';
?>
<form class="woo-product-dimensions" id="_woo-product-dimensions_form">
    <div class="woo-product-dimensions-description">
        <strong>
            <?php echo $heading; ?>
        </strong>
    </div>
    <div class="woo-product-dimensions-form">
        <div class="woo-product-dimensions-form_col">
            <label for="_woo_product_dimensions_x"><?php echo $measurement_label_x; ?></label>
            <input type="number" id="_woo_product_dimensions_x" name="_woo_product_dimensions_x" />
        </div>
        <div class="woo-product-dimensions-form_col">
            <label for="_woo_product_dimensions_y"><?php echo $measurement_label_y; ?></label>
            <input type="number" id="_woo_product_dimensions_y" name="_woo_product_dimensions_y" />
        </div>
    </div>
</form>