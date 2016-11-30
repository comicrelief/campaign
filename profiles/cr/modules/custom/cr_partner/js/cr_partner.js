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
      var scrolling = false;

      // Keep track of scroll events
      $("body").on("touchmove touchend", function(e){
        if (e.type == 'touchmove') { scrolling = true; }
        if (e.type == 'touchend') { scrolling = false; }
      });

      // Update active states
      $(_settings.partnerClass).on('touchend', function(e) {

        // Only update things if we're not tapping on an 'active' partner showing the link
        if (!$(this).hasClass('active') && !scrolling) {
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
