/**
 * @file
 * Provides CrazyEgg script.
 */

(function (drupalSettings) {

  setTimeout(
    function () {
      var a = document.createElement("script");
      var b = document.getElementsByTagName('script')[0];

      a.src = document.location.protocol +
      "//script.crazyegg.com/" + drupalSettings.crazyegg.crazyegg.account_path +
      "?" + Math.floor(new Date().getTime() / 3600000);
      a.async = true;
      a.type = "text/javascript";
      b.parentNode.insertBefore(a, b)
    }, 1
  );
})(drupalSettings);
