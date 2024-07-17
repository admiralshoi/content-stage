

async function updateUserDetails(btn) {
    let parent = btn.parents(".dataParentContainer").first(), children = parent.find("input.specialTextFieldEditable.edit");
    if(!children.length) return false;

    let collector = {}, error = false;
    children.each(function() {
       let name = $(this).attr("name"), value = $(this).val();
       if(name === "name") name = "full_name";

       if(empty(value)) {
           ePopup("Field " + name + " cannot be empty");
           error = true;
           return false
       }

       if(value === $(this).data("value")) return;
       collector[name] = value.trim();
    });
    if(empty(collector)) return true;

    if(error) return false;
    let uid = findGetParameter("uid");

    if(empty(uid)) {
        ePopup("Field could not find user-id");
        return false
    }

    let result = ensureObject(await requestServer({request: "update_user_fields", uid, fields: collector}));

    if(typeof result !== "object" || ((!("error" in result) && !("message" in result)) || "error" in result)) {
        children.each(function() { $(this).val($(this).attr("data-value")); });

        ePopupTimeout("Error",("error" in result ? result.error : "Something went wrong"));
        return false;
    }


    children.each(function() { $(this).attr("data-value", $(this).val().trim()) });
    ePopupTimeout("Success", result.message, "success", "approve");
}



async function toggleUserSuspension(btn) {
    let id = btn.attr("data-uid");
    if(typeof id === "undefined") return;

    await requestServer({
        request: "toggle_user_suspension",
        uid: id
    })
        .then((res) => {
            res = ensureObject(res);
            if(res.status === "error") {
                ePopupTimeout("Error", res.error.message)
                return;
            }
            window.location = window.location.href;
        })
}

async function updatePlatformIntervals(btn) {
    let parent = btn.parents(".dataParentContainer").first(), lookupCapField = parent.find("input[name=lookup_day_cap]").first(),
        analyticsIntervalField = parent.find("input[name=analytics_interval]").first();
    if(!lookupCapField.length || !analyticsIntervalField.length) return false;

    let lookupCap = parseFloat(lookupCapField.val().replace(",", ".")), analyticsInterval = parseFloat(analyticsIntervalField.val().replace(",", "."));
    if(empty(lookupCap) || empty(analyticsInterval)) return false;
    if(
        isNaN(lookupCap) ||
        isNaN(analyticsInterval) ||
        (!isNormalInteger(lookupCap) && typeof lookupCap !== "number") ||
        (!isNormalInteger(analyticsInterval) && typeof analyticsInterval !== "number")
    ) return false;

    swalConfirmCancel({
        request: "update_platform_intervals",
        data: {
            analytics_interval: analyticsInterval,
            lookup_cap: lookupCap,
        },
        refreshTimeout: 1000,
        visualText: {
            preFireText: {
                title: "Set new ranges?",
                text: "You're trying to change the following ranges- Analytic hourly interval: " + analyticsInterval + " hours; Max cap: " + lookupCap + " days",
                icon: "warning",
                confirmBtnText: "Change"
            },
            successText: {
                title: "Ranges updated",
                text: "Ranges changed accordingly- Analytic hourly interval: " + analyticsInterval + " hours; Max cap: " + lookupCap + " days",
                icon: "success",
                html: ""
            },
            errorText: {
                title: "Failed",
                text: "<_ERROR_MSG_>",
                icon: "error",
                html: ""
            }
        }
    });
}




async function editPage(button) {
    let editId = "richTextOutput";
    let editorField = $(document).find("#" + editId);
    let targetPage = button.attr("data-target-name");

    if(!editorField.length) return false;
    if(!["privacy_policy", "terms_of_use"].includes(targetPage)) return false;



    let htmlContent = editorField.html();
    let params = {
        request: "set_new_page_content",
        target: targetPage,
        content: htmlContent
    };
    let result = ensureObject(await requestServer(params));
    if(result) {
        ePopup("Page updated", prepareProperNameString(targetPage) + " was successfully updated", 0,  "success", "approve")
        window.setTimeout(function () {window.location = window.location.href;}, 1500)
    }

}






async function createCategory(btn) {
    let parent = btn.parents(".dataParentContainer").first(),
        field = parent.find("input[name=new_category]").first();

    if(!field.length) return;
    let result = ensureObject(await requestServer({request: "create_category", data: {category: field.val()}}))

    if(typeof result !== "object" || !("status" in result)) {
        ePopup("Something went wrong", "Please try again later");
        return false;
    }

    if(result.status === "error") {
        ePopup("Something went wrong", result.message);
        return false;
    }

    ePopupTimeout("Action successful", result.message, "success", "approve");
    field.val("");
}






async function createUserThirdParty(btn) {
    let parent = btn.parents('#user-creation-third-party').first();
    let fields = {
        username: "input[name=username]",
        full_name: "input[name=full_name]",
        email: "input[name=email]",
        access_level: "select[name=access_level]",
    };


    for(let fieldName in fields) {
        let el = parent.find(fields[fieldName]).first();

        if(empty(el.val())) {
            ePopup(prepareProperNameString(fieldName) + " error", prepareProperNameString(fieldName) + " must not be empty");
            return false;
        }

        fields[fieldName] = el.val().trim();
    }


    let result = ensureObject(await requestServer({request: "create_user_third_party", fields}));

    if(typeof result !== "object" || (!("error" in result) && !("message" in result))) {
        ePopup("Server error","Something went wrong");
        return false;
    }
    if("error" in result) {
        ePopup("Account creation error", result.error);
        return false;
    }

    ePopup("Success", result.message, 0, "success", "approve")
    window.setTimeout(function (){window.location = window.location.href;}, 1500)
}










