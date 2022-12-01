(function (GF_Partial_Submissions_Admin, $) {

    "use strict";

    GF_Partial_Submissions_Admin.drawCharts = function() {

        $('.gform_partial_entry_percent').each(function () {
            var percent = $(this).data('percentage');
            var data = google.visualization.arrayToDataTable([
                ['Complete', 'Percentage'],
                ['Complete', percent],
                ['Incomplete', 100 - percent],
            ]);

            var options = {
                legend: 'none',
                slices: {
                    0: { color: '#888888' },
                    1: { color: 'transparent' }
                },
                pieSliceText: 'none',
                tooltip: { trigger: 'none' },
                backgroundColor: 'transparent',
                pieSliceBorderColor: '#AAAAAA',
                enableInteractivity: false
            };

            var chart = new google.visualization.PieChart( this );

            chart.draw(data, options);
        })
    }

}(window.GF_Partial_Submissions_Admin = window.GF_Partial_Submissions_Admin || {}, jQuery));


google.load("visualization", "1", {packages:["corechart"]});
google.setOnLoadCallback(GF_Partial_Submissions_Admin.drawCharts);