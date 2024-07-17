








$(function() {

    var chat = {
        messageResponses: [],
        isInit: false,
        init: function(conversationId = "", bind = true) {
            this.isInit = true;
            this.cacheDOM(conversationId);
            if(bind) this.bindEvents();
            this.render();
            return this;
        },
        cacheDOM: function(conversationId) {
            this.$chatHistory = $(`.chat-logs`);
            this.$button = $('button#chat-submit');
            this.$textarea = $('#chat-input');
            this.$chatHistoryList =  this.$chatHistory.find(`ul[data-conversation-id=${conversationId}]`);
            this.cursor = ""
            this.conversationId = conversationId
            this.otherMessageTemplate = $("#other-message-template")
            this.myMessageTemplate = $("#my-message-template")
            this.otherMsgUnsentTemplate = $("#other-msg-unsent-template")
            this.myMsgUnsentTemplate = $("#my-msg-unsent-template")
            this.otherStoryMentionTemplate = $("#other-story-mention-template")
            this.otherMediaAttachmentTemplate = $("#other-media-attachment-template")
            this.myMediaAttachmentTemplate = $("#my-media-attachment-template")
            this.$loadingIcon = $('#loader')
        },
        bindEvents: function() {
            this.$button.on('click', this.addMessage.bind(this));
            this.$textarea.on('keyup', this.addMessageEnter.bind(this));
        },
        loadNewMessages: async function () {
            let result = ensureObject(await requestServer({
                request: 'load_new_conversation_messages',
                conversation_id: this.conversationId,
                cursor: this.cursor,
            }));
            if(empty(result)) return;
            if(empty(result.data)) return;

            this.cursor = result.cursor;
            this.messageResponses = result.data;
            this.render();
        },
        render: async function() {
            if(empty(this.messageResponses)) return;
            for (let messageData of this.messageResponses) {
                let template;

                if (messageData.outbound === 1 && !messageData.is_deleted && ['attachment'].includes(messageData.type))
                    template = Handlebars.compile( this.myMediaAttachmentTemplate.html());
                else if (messageData.outbound === 1 && !messageData.is_deleted) template = Handlebars.compile( this.myMessageTemplate.html());
                else if (messageData.outbound === 1 && messageData.is_deleted) template = Handlebars.compile( this.myMsgUnsentTemplate.html());
                else if (messageData.outbound === 0 && !messageData.is_deleted && ['story_mention', 'story_reply'].includes(messageData.type))
                    template = Handlebars.compile( this.otherStoryMentionTemplate.html());
                else if (messageData.outbound === 0 && !messageData.is_deleted && ['attachment'].includes(messageData.type))
                    template = Handlebars.compile( this.otherMediaAttachmentTemplate.html());
                else if (messageData.outbound === 0 && !messageData.is_deleted) template = Handlebars.compile( this.otherMessageTemplate.html());
                else if (messageData.outbound === 0 && messageData.is_deleted) template = Handlebars.compile( this.otherMsgUnsentTemplate.html());

                this.$chatHistoryList.append(template({
                    messageOutput: messageData.text,
                    time: this.formatTimestamp(messageData.timestamp),
                    mediaSrc: messageData.attached_media
                }));
            }

            this.messageResponses = []
            this.scrollToBottom();
        },

        addMessage: async function() {
            let message = this.$textarea.val();
            if(empty(message.trim())) return;

            this.$button.hide();
            this.$loadingIcon.show();
            this.$textarea.attr("disabled", true)

            let result = ensureObject(await requestServer({
                request: 'send_new_social_message',
                conversation_id: this.conversationId,
                message
            }));


            this.$textarea.removeAttr("disabled")
            this.$loadingIcon.hide();
            this.$button.show();
            this.$textarea.val('');
        },
        addMessageEnter: function(event) {
            // enter was pressed
            if (event.keyCode === 13) {
                this.addMessage();
            }
        },
        scrollToBottom: function() {
            this.$chatHistory.scrollTop(this.$chatHistory[0].scrollHeight);
        },
        formatTimestamp: function(timestamp) {
            let dateObj = new Date(timestamp * 1000);
            let todayObj = new Date();
            let yesterdayObj = (new Date()).setDate(todayObj.getDate() - 1);
            yesterdayObj = new Date(yesterdayObj)
            let secondaryString;


            if(dateObj.getDate() === todayObj.getDate()) secondaryString = "Today"
            else if(dateObj.getDate() === yesterdayObj.getDate()) secondaryString = "Yesterday"
            else  secondaryString = dateObj.toLocaleString('en-us', {month: 'short', day: '2-digit'})

            return dateObj.toLocaleTimeString().
            replace(/([\d]+:[\d]{2})(:[\d]{2})(.*)/, "$1$3") + ", " + secondaryString;
        }

    };








    var inbox = {
        init: function() {
            this.cacheDOM();
            this.bindClosedEvents();
            this.render();
        },
        cacheDOM: function() {
            this.$chatArea = $(`#chat-area`);
            this.$buttonClass = ".chat-item";
            this.$closeConversationBtn = $(".navigate-chat-btn");
            this.$conversationTitle = $("#chat-conversation .chat-title");
            this.$chatLogs = $(`.chat-logs`)
            this.$closeInbox = $('.close-chat');
            this.$openInbox = $('#chat-toggle-box');
            this.$conversationTemplate = $("#conversation-ul-template")
            this.$inboxBody = $("#chat-list .chat-body-wrapper")
            this.$inboxItemTemplate = $("#inbox-list-item-template")
            this.intervals = {}
            this.$loadedConversations = {}
            this.$currentCatIntervalId = undefined
            this.cursor = ''
        },
        bindClosedEvents: function() {
            this.$inboxBody.off('click');
            this.$closeInbox.off('click');
            this.$closeConversationBtn.off('click')
            this.$openInbox.on('click', this.open.bind(this));
        },
        bindOpenEvents: function() {
            let Tdoc = this;
            this.$inboxBody.on('click', this.$buttonClass, function () { Tdoc.openConversation($(this)) });
            this.$closeInbox.on('click', this.close.bind(this));
            this.$closeConversationBtn.on('click', this.closeConversation.bind(this));
            this.$openInbox.off('click');

        },
        loadNewConversations: async function () {
            let result = ensureObject(await requestServer({
                request: 'load_user_conversations',
                cursor: this.cursor
            }))
            console.log(result)
            if(empty(result)) return;
            if(empty(result.data)) return;

            this.cursor = result.cursor;
            for(let conversationId in result.data)
                this.$loadedConversations[conversationId] = result.data[conversationId];

            this.render()
        },
        closeConversation: function () {
            this.$conversationTitle.text("")
            this.$chatLogs.html("")
            this.$chatArea.attr("data-viewing", "list")
            this.clear(this.$currentCatIntervalId)
        },
        orderConversationList: function () {
            let elements, switching, i, x, y, shouldSwitch, table = this.$inboxBody.get(0)
            switching = true;

            while (switching) {
                switching = false;
                elements = table.querySelectorAll('.chat-item');
                for (i = 1; i < (elements.length - 1); i++) {
                    x = $(elements[i]);
                    y = $(elements[i +1]);
                    let idX = x.attr("data-conversation-id");
                    let idY = y.attr("data-conversation-id");

                    shouldSwitch = false;
                    if (this.$loadedConversations[idX].timestamp > this.$loadedConversations[idY].timestamp) {
                        shouldSwitch = true;
                        break;
                    }
                }
                if (shouldSwitch) {
                    elements[i].insertBefore(elements[i + 1], elements[i]);
                    switching = true;
                }
            }
        },
        openConversation: function (btn) {
            this.$chatArea.attr("data-viewing", "conversation")
            let conversationId = btn.attr("data-conversation-id")
            let name = btn.attr("data-name")
            if(empty(conversationId)) return;

            this.$conversationTitle.text(name)
            let template = Handlebars.compile( this.$conversationTemplate.html());
            this.$chatLogs.html(template({
                conversationId,
            }));


            let doBind = !chat.isInit;
            let newChat = chat.init(conversationId, doBind);
            newChat.loadNewMessages();
            this.$currentCatIntervalId = window.setInterval(function () {
                newChat.loadNewMessages()
            }, 5000)
            this.intervals[this.$currentCatIntervalId] = 1

        },
        render: async function() {
            if(empty(this.$loadedConversations)) return;
            for(let conversationId in this.$loadedConversations) {
                let conversation = this.$loadedConversations[conversationId];

                let existingElement = this.$inboxBody.find(`.chat-item[data-conversation-id=${conversationId}]`).first();
                if(existingElement.length) {
                    existingElement.find(".last-msg-date").first().text(this.formatTimestamp(conversation.timestamp))
                    existingElement.find(".last-msg-shorttext").first().text(conversation.short_text)
                    this.blinkNewChatItem(existingElement);
                    continue;
                }

                let template = Handlebars.compile( this.$inboxItemTemplate.html());
                this.$inboxBody.append(template({
                    id: conversation.conversation_id,
                    name: conversation.name,
                    profilePictureSrc: resolveAssetPath(conversation.profile_picture),
                    username: conversation.username,
                    time: this.formatTimestamp(conversation.timestamp),
                    shortText: conversation.short_text,
                }));

                if(!existingElement.length) existingElement = this.$inboxBody.find(`.chat-item[data-conversation-id=${conversationId}]`).first();
                this.blinkNewChatItem(existingElement);
            }
            this.orderConversationList();
        },
        blinkNewChatItem: function (existingElement) {
            if(!existingElement.length) return;
            let shortText = existingElement.find(".last-msg-shorttext").first()
            shortText.addClass("blink-txt")
            window.setTimeout(function () { shortText.removeClass("blink-txt"); }, 2000)
        },
        open: function () {
            this.$chatArea.attr("data-viewing", "list")
            this.$chatArea.addClass("open")
            this.bindOpenEvents()
            this.loadNewConversations()
            let Tdoc = this;
            let intervalId = window.setInterval(function () {
                Tdoc.loadNewConversations()
            }, 5000)
            this.intervals[intervalId] = 1;
        },
        close: function () {
            this.$chatArea.attr("data-viewing", "")
            this.$chatArea.removeClass("open")
            this.clear()
            this.bindClosedEvents()
        },
        clear: function (intervalId = undefined) {
            if(!empty(intervalId)) window.clearInterval(intervalId)
            else {
                for(let id in this.intervals) {
                    if(empty(id)) continue;
                    if (typeof id === "string") id = parseInt(id)
                    window.clearInterval(id)
                }
            }

        },
        formatTimestamp: function(timestamp) {
            let dateObj = new Date(timestamp * 1000);
            let todayObj = new Date();
            let yesterdayObj = (new Date()).setDate(todayObj.getDate() - 1);
            yesterdayObj = new Date(yesterdayObj)
            let secondaryString;


            if(dateObj.getDate() === todayObj.getDate()) secondaryString = "Today"
            else if(dateObj.getDate() === yesterdayObj.getDate()) secondaryString = "Yesterday"
            else  secondaryString = dateObj.toLocaleString('en-us', {month: 'short', day: '2-digit'})

            return dateObj.toLocaleTimeString().
            replace(/([\d]+:[\d]{2})(:[\d]{2})(.*)/, "$1$3") + ", " + secondaryString;
        }
    }

    inbox.init();








    // var searchFilter = {
    //     options: { valueNames: ['name'] },
    //     init: function() {
    //         var userList = new List('people-list', this.options);
    //         var noItems = $('<li id="no-items-found">No items found</li>');
    //
    //         userList.on('updated', function(list) {
    //             if (list.matchingItems.length === 0) {
    //                 $(list.list).append(noItems);
    //             } else {
    //                 noItems.detach();
    //             }
    //         });
    //     }
    // };
    //
    // searchFilter.init();

});







