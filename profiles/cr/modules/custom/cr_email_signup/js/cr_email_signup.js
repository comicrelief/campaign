/**
 * @file
 * Javascript functionality for the ESU strip
 */


(function($, Drupal) {
  'use strict';

  console.log("i live");


  Drupal.behaviors.myBehavior = {
    attach: function (context, settings) {

      $(context).find('#cr-email-signup-form').once('myCustomBehavior').addClass('processed').addClass('XXX');
   
    }
  };
})(jQuery, Drupal);