(function ($) {
  Drupal.behaviors.modal = {
    attach: function (context, settings) {

      var surveyID = settings.health.campaign_id + '-2';
      
      $(".modal-fade-screen, .modal-close, .modal-close-link", context).on("click", function() {
        $("body", context).removeClass("modal-open");
        Cookies.set('survey-dismiss', surveyID);
      });

      $(".modal-inner", context).on("click", function(e) {
        e.stopPropagation();
      });

      if(Cookies.get('survey-dismiss') != surveyID){

        var minNumber = 1;
        var maxNumber = 5; // every nth page laod if no cookie

        var randomNumber = randomNumberFromRange(minNumber, maxNumber);
        function randomNumberFromRange(min, max) {
          return Math.floor(Math.random() * (max - min + 1) + min);
        }

        if (randomNumber == 2) {
          $("body", context).addClass("modal-open");
        }
      }

    }
  };
}(jQuery));
