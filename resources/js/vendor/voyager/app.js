$(document).ready(function() {
    var date = new Date();

    $(".datepicker--date-only").datetimepicker({
        format: "DD-MM-YYYY",
        // minDate: startDate
        // format: "YYYY-MM-DD"
    });

    $("#call-date")
        .children(".form-control")
        .val(
            date
                .getDate()
                .toString()
                .padStart(2, 0) +
                "-" +
                (date.getMonth() + 1).toString().padStart(2, 0) +
                "-" +
                date.getFullYear().toString()
        );

    $(".datepicker--time-only").datetimepicker({
        format: "HH:mm"
    });

    // disable chaging the value by mouse scroll
    $("form").on("focus", "input[type=number]", function(e) {
        $(this).on("mousewheel.disableScroll", function(e) {
            e.preventDefault();
        });
    });
    $("form").on("blur", "input[type=number]", function(e) {
        $(this).off("mousewheel.disableScroll");
    });
});
