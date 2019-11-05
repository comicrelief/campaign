(function ($) {

  // Using the same cookie name as the contrib module to 'extend' its functionality
  var DISMISSED_COOKIE = 'cookieconsent_dismissed';
  var MARKETING_COOKIE = 'marketing_accept';
  var domain = window.location.hostname;
  var acceptedMarketing = false;

  // Handle clicks on either button
  $(document).on('click', 'a.deny-cookies, a.accept-cookies', function(e) {

    e.preventDefault();

    // Sets our 'dismissed' cookie to stop banner being shown again
    setCookie(DISMISSED_COOKIE, 'yes', domain);

    // Remove the banner itself from the DOM
    $('.cc_banner-wrapper').remove();
    
    // Change our default 'declined' value if they've accepted
    if ($(this).hasClass('accept-cookies')){
      acceptedMarketing = true;
    }

    // Set the marketing cookie for all subsequent page views
    setCookie(MARKETING_COOKIE, acceptedMarketing, domain);

    // Pass the user's choice to the Data Layer function
    setMarketing(acceptedMarketing);
  });

  // Recreate original function from contrib module to ensure existing functionality still works
  function setCookie(name, value, domain) {

    var exdate = new Date();
    
    exdate.setDate(exdate.getDate() + 365);

    var cookie = [
      name + '=' + value,
      'expires=' + exdate.toUTCString(),
      'path=/',
    ];

    if (domain) {
      cookie.push('domain=' + domain);
    }

    document.cookie = cookie.join(';');
  }
  
  function setMarketing(userChoice) {

    // Fire GTM/Data Layer event
    var dataLayer = window.dataLayer = window.dataLayer || [];
    
    dataLayer.push({
      'event': 'cr-cookie-banner',
      'action': 'cookie_opt-in_marketing',
      'label': userChoice
    });
  },
})(jQuery);
