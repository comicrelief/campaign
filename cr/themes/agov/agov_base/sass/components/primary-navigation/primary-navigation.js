(function ($) {
  Drupal.behaviors.agov_base_primary_navigation = {
    attach: function (context, settings) {
      $('.primary-navigation', context).once().each(function () {
        var $menu = $(this),
          $title = $menu.find('.primary-navigation__title'),
          $level_2_list = $menu.find('.primary-navigation__list-level-2');
        var toggle = true;
        $title.click(function () {
          if (toggle) {
            $menu.addClass('primary-navigation--expanded');
            $title.text('Close');
            toggle = false;
          }
          else {
            $menu.removeClass('primary-navigation--expanded');
            $title.text('Menu');
            toggle = true;
          }
        });

        // Enable keyboard navigation for accessibility.
        $level_2_list.find('a').focus(function() {
          $(this).closest($level_2_list).closest('li').addClass('opened');
        });
        $level_2_list.find('a').blur(function() {
          $(this).closest($level_2_list).closest('li').removeClass('opened');
        });
      });
    }
  };
})(jQuery);
