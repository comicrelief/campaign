/**
 * @file eu_cookie_compliance.js
 *
 * Defines the behavior of the eu cookie compliance popup.
 */

(function ($, Drupal, drupalSettings) {

  "use strict";

  Drupal.behaviors.eu_cookie_compliance_popup = {
    attach: function () {
      var settings = drupalSettings.eu_cookie_compliance;
      try {
        var enabled = settings.popup_enabled;
        if (!enabled) {
          return;
        }
        if (!Drupal.eu_cookie_compliance.cookiesEnabled()) {
          return;
        }
        var status = Drupal.eu_cookie_compliance.getCurrentStatus();
        var clicking_confirms = settings.popup_clicking_confirmation;
        var agreed_enabled = settings.popup_agreed_enabled;
        var popup_hide_agreed = settings.popup_hide_agreed;
        if (status === 0) {
          var next_status = 1;
          if (clicking_confirms) {
          $('a, input[type=submit]').bind('click.eu_cookie_compliance', function() {
            if (!agreed_enabled) {
              Drupal.eu_cookie_compliance.setStatus(1);
              next_status = 2;
            }
            Drupal.eu_cookie_compliance.changeStatus(next_status);
          });
          }
          $('.agree-button').click(function() {
            if (!agreed_enabled) {
              Drupal.eu_cookie_compliance.setStatus(1);
              next_status = 2;
            }
            Drupal.eu_cookie_compliance.changeStatus(next_status);
          });

          Drupal.eu_cookie_compliance.createPopup(settings.popup_html_info);
        }
        else if (status === 1) {
          Drupal.eu_cookie_compliance.createPopup(settings.popup_html_agreed);
          if (popup_hide_agreed) {
            $('a, input[type=submit]').bind('click.eu_cookie_compliance_hideagreed', function() {
              Drupal.eu_cookie_compliance.changeStatus(2);
            });
          }
        }
      }
      catch(e) {
      }
    }
  };

  Drupal.eu_cookie_compliance = {};

  Drupal.eu_cookie_compliance.createPopup = function(html) {
    var popup = $(html)
      .attr({"id": "sliding-popup"})
      .height(drupalSettings.eu_cookie_compliance.popup_height)
      .width(drupalSettings.eu_cookie_compliance.popup_width)
      .hide();
    var height = popup.height();
    if (drupalSettings.eu_cookie_compliance.popup_position) {
      popup.prependTo("body");
      popup.show()
        .attr({"class": "sliding-popup-top clearfix"})
        .css({
          "top": -1 * height
        })
        .animate({top: 0}, drupalSettings.eu_cookie_compliance.popup_delay);
    }
    else {
      popup.appendTo("body");
      popup.show()
        .attr({"class": "sliding-popup-bottom"})
        .css({
          "bottom": -1 * height
        })
        .animate({bottom: 0}, drupalSettings.eu_cookie_compliance.popup_delay);
    }
    popup.css({
      "background": '#' + drupalSettings.eu_cookie_compliance.popup_bg_hex
    }).find('h2, p').css('color', '#' + drupalSettings.eu_cookie_compliance.popup_text_hex);
    Drupal.eu_cookie_compliance.attachEvents();
  };

  Drupal.eu_cookie_compliance.attachEvents = function() {
	var clicking_confirms = drupalSettings.eu_cookie_compliance.popup_clicking_confirmation;
    var agreed_enabled = drupalSettings.eu_cookie_compliance.popup_agreed_enabled;
    $('.find-more-button').bind('click', function() {
    	if (drupalSettings.eu_cookie_compliance.popup_link_new_window) {
    		window.open(drupalSettings.eu_cookie_compliance.popup_link);
    	}
    	else {
            window.location.href = drupalSettings.eu_cookie_compliance.popup_link;
    	}
    });
    $('.agree-button').bind('click', function() {
      var next_status = 1;
      if (!agreed_enabled) {
        Drupal.eu_cookie_compliance.setStatus(1);
        next_status = 2;
      }
      if (clicking_confirms) {
        $('a, input[type=submit]').unbind('click.eu_cookie_compliance');
      }
      Drupal.eu_cookie_compliance.changeStatus(next_status);
    });
    $('.hide-popup-button').bind('click', function() {
      Drupal.eu_cookie_compliance.changeStatus(2);
    });
  };

  Drupal.eu_cookie_compliance.getCurrentStatus = function() {
	var name = 'cookie-agreed';
	var result = Drupal.eu_cookie_compliance.getCookie(name);
    return parseInt(result);
  };

  Drupal.eu_cookie_compliance.changeStatus = function(value) {
    var status = Drupal.eu_cookie_compliance.getCurrentStatus();
    if (status === value) {
      return;
    }
    if (drupalSettings.eu_cookie_compliance.popup_position) {
      $(".sliding-popup-top").animate({top: $("#sliding-popup").height() * -1}, drupalSettings.eu_cookie_compliance.popup_delay, function () {
        if (status === 0) {
          $("#sliding-popup").html(drupalSettings.eu_cookie_compliance.popup_html_agreed).animate({top: 0}, drupalSettings.eu_cookie_compliance.popup_delay);
          Drupal.eu_cookie_compliance.attachEvents();
        }
        if (status === 1) {
          $("#sliding-popup").remove();
        }
      });
    } else {
      $(".sliding-popup-bottom").animate({bottom: $("#sliding-popup").height() * -1}, drupalSettings.eu_cookie_compliance.popup_delay, function () {
        if (status === 0) {
          $("#sliding-popup").html(drupalSettings.eu_cookie_compliance.popup_html_agreed).animate({bottom: 0}, drupalSettings.eu_cookie_compliance.popup_delay);
          Drupal.eu_cookie_compliance.attachEvents();
        }
        if (status === 1) {
          $("#sliding-popup").remove();
        }
      });
    }
    Drupal.eu_cookie_compliance.setStatus(value);
  };

  Drupal.eu_cookie_compliance.setStatus = function(status) {
    var date = new Date();
    date.setDate(date.getDate() + 100);
    var cookie = "cookie-agreed=" + status + ";expires=" + date.toUTCString() + ";path=" + drupalSettings.path.baseUrl;
    if(drupalSettings.eu_cookie_compliance.domain) {
      cookie += ";domain="+drupalSettings.eu_cookie_compliance.domain;
    }
    document.cookie = cookie;
  };

  Drupal.eu_cookie_compliance.hasAgreed = function() {
    var status = Drupal.eu_cookie_compliance.getCurrentStatus();
    if (status === 1 || status === 2) {
      return true;
    }
    return false;
  };

  /**
   * Verbatim copy of Drupal.comment.getCookie().
   */
  Drupal.eu_cookie_compliance.getCookie = function(name) {
    var search = name + '=';
    var returnValue = '0';

    if (document.cookie.length > 0) {
      var offset = document.cookie.indexOf(search);
      if (offset != -1) {
        offset += search.length;
        var end = document.cookie.indexOf(';', offset);
        if (end == -1) {
          end = document.cookie.length;
        }
        returnValue = decodeURIComponent(document.cookie.substring(offset, end).replace(/\+/g, '%20'));
      }
    }
    return returnValue;
  };

  Drupal.eu_cookie_compliance.cookiesEnabled = function() {
    var cookieEnabled = (navigator.cookieEnabled);
    if (typeof navigator.cookieEnabled === "undefined" && !cookieEnabled) {
      document.cookie = "testcookie";
      cookieEnabled = (document.cookie.indexOf("testcookie") !== -1);
    }
    return (cookieEnabled);
  };

})(jQuery, Drupal, drupalSettings);
