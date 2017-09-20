$(document).ready(function() {
    $('.delete').on("click", function(e) {
        e.preventDefault();
        var href = $(this).attr('href');
        if(href.length > 0) {
            var firstname = $(this).closest('tr').find('.firstname').text();
            var surname = $(this).closest('tr').find('.surname').text();
            if(confirm('Are you sure that you want to delete '+firstname+' '+surname+'?')) {
                window.location.href = href;
            }
        }
    })
});

