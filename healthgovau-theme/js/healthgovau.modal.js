(function ($) {
  Drupal.behaviors.modal = {
    attach: function (context, settings) {

      // Add modal effect. Check http://refills.bourbon.io/components
      $(".modal-state").on("change", function() {
        if ($(this).is(":checked")) {
          $("body").addClass("modal-open");
        } else {
          $("body").removeClass("modal-open");
        }
      });

      $(".modal-fade-screen, .modal-close").on("click", function() {
        $("body").removeClass("modal-open");
      });

      $(".modal-inner").on("click", function(e) {
        e.stopPropagation();
      });

      var minNumber = 1;
      var maxNumber = 2;
      // Specify the random number max
      
      var randomNumber = randomNumberFromRange(minNumber, maxNumber);
      console.log(randomNumber);
      function randomNumberFromRange(min, max) {
        return Math.floor(Math.random() * (max - min + 1) + min);
      }

      if (randomNumber == 2) {
        $("body").addClass("modal-open");
      }

    }
  };
}(jQuery));
