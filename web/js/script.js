var $loading = $('.loader').hide();
$(document)
    .ajaxStart(function () {
        $loading.show();
    })
    .ajaxStop(function () {
        $loading.hide();
    });
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
    });
    var email_container = $('.user-emails');
    $('.details').on("click", function() {
        var modal_label = $('#myModalLabel');
        modal_label.empty();
        email_container.empty();
        var user_id = {
            "id": $(this).attr('data-id')
        };
        $('.modal-body').attr('data-user-id', user_id.id);
        $.ajax({
            type: 'post',
            dataType: "json",
            url: "/Admin/web/getEmails",
            data: user_id,
            success: function (response) {
                modal_label.text(response.name+' '+'<'+response.email+'>');
                if(response.emails.length > 0) {
                    var emails = [];
                    $(response.emails).each(function( index, element ) {
                        emails += "<li>"+element.label+" <a data-id='"+element.id+"' class='btn btn-default btn-xs actions deleteEmail pull-right' href='#'><i class='fa fa-times'></i></a></li>";
                    });
                    email_container.append(emails);
                } else {
                    email_container.append("<li class='no-email'>No emails found</li>");
                }
            }
        });

        $('#myModal').modal();
    });

    var modal = $('.modal');

    modal.on("click", ".deleteEmail", function() {
        var id = $(this).attr('data-id');
        var element = $(this);
        $.ajax({
            type: 'post',
            dataType: "json",
            url: "/Admin/web/deleteEmail/"+id,
            success: function (response) {
                element.closest('li').slideUp(300,function() {
                    element.closest('li').remove();
                    if($('ol li').length == 0) {
                        email_container.append("<li class='no-email'>No emails found</li>");
                    }
                });
            }
        });
    });
    modal.on("click", ".submit_email", function() {
        var error_container = $('.error');
        var email_input = $('.email_input').val();
        if(isEmailValid(email_input)) {
            error_container.hide();
            var id = $('.modal-body').attr('data-user-id');
            var element = $(this);
            var data = {
                "id": id,
                "email": email_input
            };
            $.ajax({
                type: 'post',
                dataType: "json",
                url: "/Admin/web/addEmail",
                data: data,
                success: function (response) {
                    if(response.success == true) {
                        $('.no-email').remove();
                        $('.modal-body').find('ol').append("<li>"+response.label+" <a data-id='"+response.id+"' class='btn btn-default btn-xs actions deleteEmail pull-right' href='#'><i class='fa fa-times'></i></a></li>").slideDown(300);
                    } else {
                        error_container.text('Email already exists').show();
                    }
                }
            });
        } else {
            error_container.text('Email is no valid').show();
        }
    });
});

function isEmailValid(email) {
    var regex = /^([a-zA-Z0-9_.+-])+\@(([a-zA-Z0-9-])+\.)+([a-zA-Z0-9]{2,4})+$/;
    return regex.test(email);
}