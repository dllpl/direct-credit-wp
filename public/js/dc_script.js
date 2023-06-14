if (!dcData?.error) {
    jQuery(function ($) {
        $('body').append("" +
            "<link rel='stylesheet' href='//dcapi.direct-credit.ru/style.css' type='text/css'/> " +
            "<script src='//dcapi.direct-credit.ru/JsHttpRequest.js' type='text/javascript'></script> " +
            "<script src='//dcapi.direct-credit.ru/dc.js' charset='utf-8' type='text/javascript'></script>")

        const click_on_credit_id = dcData.click_on_credit_id
        const price_id = dcData.price_id
        const phone_id = dcData.phone_id
        const partnerID = dcData.partnerID
        const name_product_id = dcData.name_product_id

        const data = {
            price: $(`#${price_id}`).value(),
            phone: $(`#${phone_id}`).value(),
            name_product: $(`#${name_product_id}`).value()
        }

        $(`#${click_on_credit_id}`).on('click', () => {
            $.ajax({
                url: dcData.createOrderUri,
                method: 'POST',
                headers: {'Content-Type': 'application/json'},
                body: JSON.stringify(data)
            }).success(function (response) {
                if (response.success) {
                    DCLoans (partnerID, 'orderByToken', {token : response.data.token}, function(result){});
                } else {
                    console.log('error');
                }
            })
        })
    })
} else {
    console.log(dcData.error)
}



