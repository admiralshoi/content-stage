$(document).ready(function (){

    if(typeof pageTitle !== "undefined") document.querySelector("title").innerText = pageTitle;

    if(typeof setDateRangePicker == "function") {
        setDateRangePicker().then(() => {

        })
    }


    if(typeof hasUserSession == "function") hasUserSession();
    setActiveNavItemLabels();



    $(document).on("click", "button[name=user_reset_pwd]",function (e) { e.preventDefault(); resetPwd($(this)); });
    $(document).on("click", "button[name=user_create_new_password]",function (e) { e.preventDefault(); createNewPwdFromReset($(this)); });
    $(document).on("click", "button[name=saveEditUserBtn]",function () { updateUserDetails($(this)); });
    $(document).on("click", ".toggleUserSuspension",function () { toggleUserSuspension($(this)); });
    $(document).on("click", ".close-popup",function () { closePopup($(this)); });
    $(document).on("click", "button[name=login_user]",function () { loginUser($(this)); });
    $(document).on("click", ".selectable-el",function () { selectElements($(this), false, true); });


    $(document).on("change", "form#view_users select[name=view]",function () { $(this).parents("form").first().submit(); });
    $(document).on("click", "button[name=create_new_user_third_party]",function () { createUserThirdParty($(this)) });
    $(document).on("click", "button[name=signup_user]",function () { createUser($(this)) });

    $(document).on("change", "select[name=bulk_action]",function () { bulkAction($(this)); });
    $(document).on("click", "input[type=checkbox].masterBox",function () { multiCheckBoxes($(this)); });
    $(document).on("click", "[data-href]",function () { window.location = $(this).attr("data-href"); });


    $(document).on("click", ".toggleIntegrationActive",function () {toggleIntegrationDefault($(this)); });
    $(document).on("click", ".removeIntegration",function () {removeIntegration($(this)); });



    //Goodbrandslove

    $(document).on("click", ".campaign-csv-export",function () { exportCampaignToCsv() });
    if(findGetParameter("code") && findGetParameter("state") === "some-state-192484") connectFB();
    if($(document).find(".drawChart").length) {
        $(document).find(".drawChart").each(function () { initChartDraw($(this)); })
    }

    $(document).on("click", "button[name=new_creator_load_user]",function () { newCreatorLoadUsername() });
    $(document).on("click", "button[name=store_new_creator]",function () { storeLoadedCreator() });
    $(document).on("click", "[data-toggle-creator]",function () { toggleCreator($(this)) });
    $(document).on("click", "button[name=create_campaign]",function () { createCampaign($(this)) });
    $(document).on("click", "button[name=toggle_campaign_creation_view]",function () { toggleCampaignCreationContainer() });
    $(document).on("click", "#edit_campaign_btn, button[name=edit_campaign_btn], button[name=update_campaign_btn]",function () { campaignUpdate($(this)) });
    $(document).on("click", ".removeCreatorFromCampaign",function () { removeCreatorFromCampaign($(this)) });
    if($(document).find("table#live_mention_table").length)
        mentionLiveTableTracking($(document).find("table#live_mention_table").first())
            .then(() => { window.setInterval(function () { mentionLiveTableTracking($(document).find("table#live_mention_table").first()) }, 15000); })







    var isSwitching = false;
    $(document).on("click", ".switchViewBtn", function (){

        if(isSwitching) return false;
        isSwitching = true;
        switchView($(this))
            .then(() => { isSwitching = false; })
            .catch(() => { isSwitching = false; })
    })





    if($(document).find(".select2Multi").length) { select2MultiInit(); }


    if($(document).find(".plainDataTable").length) {
        if(typeof setDataTable == "function") {
            $(document).find(".plainDataTable").each(function () {
                let table = $(this), paginationLimit = table.data("pagination-limit"), sortingColumn = table.data("sorting-col"), sortingOrder = table.data("sorting-order");

                if(paginationLimit === undefined || empty(paginationLimit)) paginationLimit = 100;
                if(sortingColumn === undefined || empty(sortingColumn)) sortingColumn = 0;
                if(sortingOrder === undefined || empty(sortingOrder)) sortingOrder = "desc";

                setDataTable(table, [sortingColumn, sortingOrder], false,[], paginationLimit);
            });
        }
    }



    $(document).on("click", ".title-box .title-box-header",function () {
        let header = $(this), titleBoxContent = header.parent().find(".title-box-content").first();
        if(header.find(".expand-title-box").length) return true;

        if(titleBoxContent.length === 0) return false;
        let open = header.hasClass("open");
        if(open) titleBoxContent.slideUp( 250, function() { header.removeClass("open"); });
        else titleBoxContent.css('opacity', 0).slideDown(250).animate({ opacity: 1 },{ queue: false, duration: 250,
            complete: function () { header.addClass("open"); }});
    });


    $(document).on("click", ".title-box .title-box-header .expand-title-box",function () {
        let expandLink = $(this), header = expandLink.parents(".title-box-header").first(),
            titleBoxContent = header.parent().find(".title-box-content").first();
        if(titleBoxContent.length === 0) return false;
        let open = header.hasClass("open");
        if(open) {
            titleBoxContent.slideUp(250, function () {
                header.removeClass("open");
            });
            expandLink.text("Expand");
        }
        else {
            titleBoxContent.css('opacity', 0).slideDown(250).animate({opacity: 1}, {
                queue: false, duration: 250,
                complete: function () {
                    header.addClass("open");
                }
            });
            expandLink.text("Close");
        }
    });



    $(document).on("click",".copyBtn",function () {
        let copyString = $(this).parents(".copyContainer").first().find(".copyElement").first().text();
        copyString = copyString.replaceAll("&amp;","&");
        copyString = copyString.replaceAll("&lt;","<");
        copyString = copyString.replaceAll("&gt;",">");
        copyString = copyString.replaceAll("&quot;",'"');
        copyString = copyString.replaceAll("&apos;","'");
        copyString = copyString.toString();

        copyToClipboard(copyString);
    });

});



docReady(function() {
    let page = $(document).find(".page-content[data-page]").first();
    if(page.length) trackEvent("page_view", page.attr("data-page"))

    let eventTypes = {
        click: {dataAttribute: "data-clickAction", selector: "[data-clickAction]"},
        change: {dataAttribute: "data-changeAction", selector: "[data-changeAction]"}
    }

    for(let event in eventTypes) {
        let eventOpt = eventTypes[event]
        $(document).on(event,eventOpt.selector, function () {
            trackEvent(event, $(this).attr(eventOpt.dataAttribute))
        })
    }


    if($(document).find("button[name=login_user]").length) {
        document.addEventListener("keypress", function (event) {
            if (event.key === "Enter") $(document).find("button[name=login_user]").first().trigger("click");
        });
    }


    $(document).off("dblclick");



    /**
     * Features for responsiveness
     */

    togglePasswordVisibility();

    $(document).on("click", "#leftSidebarOpenBtn", function () {
        $(document).find("#sidebar").addClass("mb-open");
        $(document).find(".page-wrapper").first().addClass("overlay-blur-dark-small-screen");
    })
    $(document).on("click", "#leftSidebarCloseBtn", function () {
        $(document).find("#sidebar").removeClass("mb-open");
        $(document).find(".page-wrapper").first().removeClass("overlay-blur-dark-small-screen");
    })

})




window.addEventListener("load", function() {
    for (let i = 0; i < document.images.length; i++) {
        if (!IsImageOk(document.images[i])) {
            document.images[i].setAttribute('src', serverHost + 'images/storyExpired.jpg');
            // document.images[i].style.visibility = "hidden";
        }
    }
});










