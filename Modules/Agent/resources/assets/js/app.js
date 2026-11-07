console.log(1236545)
export function checkRecipient(recipientField, callback) {
    $.ajax({
        method: "GET",
        url: window.routes.checkRecipient,
        dataType: "json",
        data: { recipient: $(recipientField).val() }
    }).done(function (response) {
        callback(response);
    });
}
