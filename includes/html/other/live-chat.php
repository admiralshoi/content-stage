<?php
use classes\src\AbstractCrudObject;
$crud = new AbstractCrudObject();
$creators = $crud->lookupList()->getByX();

if($crud->isBrand(0, false)):
?>

<div id="chat-area" class="" data-viewing="conversation">
    <div id="chat-toggle-box">
        <i class="mdi mdi-chat"></i>
    </div>
    <div id="chat-box-container">

        <div id="chat-conversation">
            <div class="chat-header">
                <i class="mdi mdi-arrow-left navigate-chat-btn"></i>
                <p class="chat-title"></p>
                <i class="close-chat mdi mdi-close"></i>
            </div>
            <div class="chat-body-wrapper">
                <div class="chat-logs"></div>


                <div class="chat-footer">
                    <div>
                        <input type="text" id="chat-input" placeholder="Send a message..."/>
                        <button class="chat-submit" id="chat-submit"><i class="mdi mdi-send"></i></button>
                        <div id="loader" class="spinner-border spinner-border-sm text-primary" role="status" style="display: none"><span class="sr-only"></span></div>
                    </div>

                </div>




            </div>
        </div>

        <div id="chat-list">
            <div class="chat-header">
                <span>&nbsp;</span>
                <p class="chat-title">Conversations</p>
                <i class="close-chat mdi mdi-close"></i>
            </div>
            <div class="chat-body-wrapper"></div>
        </div>







        <script id="other-msg-unsent-template" type="text/x-handlebars-template">
            <li class="clearfix">
                <div class="message-data mb-3">
                    <span class="message-meta-notification">Message was deleted</span>
                </div>
            </li>
        </script>
        <script id="my-msg-unsent-template" type="text/x-handlebars-template">
            <li class="clearfix">
                <div class="message-data align-right mb-3">
                    <span class="message-meta-notification">Message was deleted</span>
                </div>
            </li>
        </script>

        <script id="conversation-ul-template" type="text/x-handlebars-template">
            <ul data-conversation-id="{{conversationId}}"></ul>
        </script>

        <script id="other-message-template" type="text/x-handlebars-template">
            <li class="clearfix">
                <div class="message-data">
                    <span class="message-data-time" >{{time}}</span> &nbsp; &nbsp;

                </div>
                <div class="message other-message ">{{messageOutput}}</div>
            </li>
        </script>

        <script id="my-message-template" type="text/x-handlebars-template">
            <li class="clearfix">
                <div class="message-data align-right">
                    <span class="message-data-time">{{time}}</span>
                </div>
                <div class="message my-message">{{messageOutput}}</div>
            </li>
        </script>


        <script id="other-story-mention-template" type="text/x-handlebars-template">
            <li class="clearfix">
                <div class="message-data">
                    <span class="message-data-time">{{time}}</span>
                </div>
                <img src="{{mediaSrc}}" class="media clearfix"/>
                <div class="sub-message">{{messageOutput}}</div>
            </li>
        </script>

        <script id="other-media-attachment-template" type="text/x-handlebars-template">
            <li class="clearfix">
                <div class="message-data">
                    <span class="message-data-time">{{time}}</span>
                </div>
                <img src="{{mediaSrc}}" class="media clearfix other-message"/>
            </li>
        </script>

        <script id="my-media-attachment-template" type="text/x-handlebars-template">
            <li class="clearfix">
                <div class="message-data align-right">
                    <span class="message-data-time">{{time}}</span>
                </div>
                <img src="{{mediaSrc}}" class="media clearfix my-message"/>
            </li>
        </script>

        <script id="inbox-list-item-template" type="text/x-handlebars-template">
            <div class="chat-item" data-conversation-id="{{id}}" data-name="{{name}}">
                <div class="flex-row-between flex-align-center flex-nowrap">
                    <div class="flex-row-start flex-align-center flex-nowrap">
                        <img src="{{profilePictureSrc}}" class="square-30 noSelect border-radius-50">
                        <p class="font-14 font-weight-bold ml-2">{{username}}</p>
                    </div>
                    <p class="font-12 text-gray last-msg-date">{{time}}</p>
                </div>

                <p class="font-13 text-gray font-italic mt-2 last-msg-shorttext">{{shortText}}</p>
            </div>
        </script>
    </div>
</div>

<?php  endif; ?>


