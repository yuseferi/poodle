(function ($) {
  $(document).ready(function () {
    // alert("I'm loaded");
    $("select").each(function () {
      var color = $(this).val();
      var color_set = drupalSettings.MSF.guideline_color_set[color];
      if(color_set != undefined) {
        $(this).parents("td").find(".color-preview1").css("background-color", color_set.color1);
        $(this).parents("td").find(".color-preview2").css("background-color", color_set.color2);
      }
    });
    $("select").change(function () {
      var color = $(this).val();
      var color_set = drupalSettings.MSF.guideline_color_set[color];
      if(color_set != undefined) {
        $(this).parents("td").find(".color-preview1").css("background-color", color_set.color1);
        $(this).parents("td").find(".color-preview2").css("background-color", color_set.color2);
      }
      console.log(color);
    });
  });

})(jQuery);
