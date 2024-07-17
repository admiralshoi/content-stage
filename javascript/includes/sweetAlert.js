function swalConfirmCancel(args={}) {
    if(!("request" in args && ("fields" in args || "data" in args) && "visualText" in args && "refreshTimeout" in args)) return false;

    let visual = args.visualText, timeOut = args.refreshTimeout;
    if(!("preFireText" in visual && "successText" in visual && "errorText" in visual)) return false;

    let preFireText = visual.preFireText,
        successText = visual.successText,
        errorText = visual.errorText,
        isExpectingInput = ("input" in preFireText);

    Swal.fire({
        ...{
            showCancelButton: true,
            confirmButtonColor: '#3085d6',
            cancelButtonColor: '#d33',
        },
        ...preFireText
    })
        .then(result => {
            let swalNewValues={};
            if(("dismiss" in result) && ["cancel", "backdrop"].includes(result.dismiss)) {}
            else if (result.value || (isExpectingInput && result.value !== "")) {
                let requestParams = {request: args.request};
                if("data" in args) requestParams.data = args.data;
                if("fields" in args) requestParams.fields = args.fields;
                if(isExpectingInput) {
                    if(!("data" in args)) requestParams.data = {custom_value: result.value};
                    else requestParams.data.custom_value = result.value;
                }
                console.log(requestParams);
                requestServer(requestParams)
                    .then((response) => {
                        response = ensureObject(response);
                        var doRefresh = true;

                        switch (response.status) {
                            case "success":
                                swalNewValues.title = successText.title;
                                swalNewValues.text = successText.text;
                                swalNewValues.icon = successText.icon;
                                swalNewValues.html = successText.html;
                                break;
                            case "error":
                                swalNewValues.title = errorText.title;
                                swalNewValues.text = (errorText.text.replace("<_ERROR_MSG_>",response.error));
                                swalNewValues.icon = errorText.icon;
                                swalNewValues.html = (errorText.html.replace("<_ERROR_MSG_>",response.error));

                                doRefresh = false;
                                break;
                            default:
                                swalNewValues.title = "Unknown error";
                                swalNewValues.text = "Something unexpected happened";
                                swalNewValues.icon = "error";
                                swalNewValues.html = "";

                                doRefresh = false;
                        }
                        Swal.fire(swalNewValues)
                            .then(() => {
                                if(timeOut !== false && doRefresh) {
                                    setTimeout(function () {
                                        window.location = (window.location.href).replace("#","")
                                    },timeOut)
                                }
                            });
                    })
                    .catch((response) => {
                        if(timeOut !== false) {
                            setTimeout(function () {
                                window.location = (window.location.href).replace("#","")
                            },timeOut)
                        }
                    });
            }
            else {
                swalNewValues.title = "Error";
                swalNewValues.text = isExpectingInput ? "Input value cannot be empty" : "Unknown error";
                swalNewValues.icon = "error";
                swalNewValues.html = "";
                Swal.fire(swalNewValues)

            }
        })
}