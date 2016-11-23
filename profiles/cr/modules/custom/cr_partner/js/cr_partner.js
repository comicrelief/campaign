/**
 * @file
 */

(function ($) {

	Drupal.behaviors.crPartner = {
		
    settings : {
      gridClass: '.touchevents .partners-grid',
			partnerClass: '.node__content',
      activePartnerClass: '.node__content.active'
		},

    attach: function (context, settings) {

      var _base = Drupal.behaviors.crPartner;
      var _settings = _base.settings;

      $(_settings.gridClass).once('crPartner').each(function () {
        $(this).addClass("crPartner-processed");
        _base.handleTouch();
      });
    },

    handleTouch: function () {
      
      var _base = Drupal.behaviors.crPartner;
      var _settings = _base.settings;
      
      $( _settings.partnerClass ).on('click touchend', function(e){


        if ( !$(this).hasClass('active') ) {

          // Stop the link from firing
          e.preventDefault();

          // 'Unactivate' all partners
          $(_settings.partnerClass).removeClass('active');

          // Set this one to active only
          $(this).toggleClass('active');
        } 
      });
    },
	};
})(jQuery);
