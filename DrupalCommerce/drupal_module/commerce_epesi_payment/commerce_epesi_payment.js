Drupal.behaviors.commerce_epesi_payment = {
  attach: function(context, settings) {
    jQuery('#commerce-epesi-payment-redirect-form', context).once('commerce_epesi_payment').delay(100).submit();
  }
}
