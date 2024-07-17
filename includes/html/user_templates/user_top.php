<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\Object\transformer\URL;
use classes\src\media\Facebook;
use classes\src\AbstractCrudObject;

$crud = new AbstractCrudObject();
$user = $crud->user($_SESSION["uid"]);
$userDir = isset($_SESSION["directory"]) ? $_SESSION["directory"] : $user->directory($_SESSION["uid"]);

if(file_exists(ROOT . $userDir . "/" . USER_BASE_FILE))
    $baseInfo = json_decode(file_get_contents(ROOT . $userDir . "/" . USER_BASE_FILE), true);
else $baseInfo = array("picture" => "images/nopp.png");






?>
<nav class="styles2_navbar">
    <div class="row flex-row-between flex-align-center navbar">

        <a href="#" class="sidebar-toggler">
            <i data-feather="menu"></i>
        </a>
        <div class="navbar-content">
            <form class="search-form position-relative searchParent " action="?" method="post">
                <input type="hidden" autocomplete="off" />
                <div class="input-group">
                    <input type="text" class="form-control font-18" id="username_search_field" name="search_field"
                           placeholder="Find employee" autocomplete="off" data-changeAction="header_search_bar"/>
                    <div id="providerBox" class="flex-row-around flex-align-center">
                        <i class="mdi mdi-account-search font-30"></i>
                    </div>
                </div>
                <div class="suggestion_container no-vis"></div>
            </form>



            <ul class="styles2_nav navbar-nav pl-md-2">

                <li class="nav-item dropdown nav-notifications flex-col-around">
                    <a class="nav-link dropdown-toggle" href="#" id="notificationDropdown" role="button" data-toggle="dropdown" data-clickAction="header_notification"
                       aria-haspopup="true" aria-expanded="false">
                        <i data-feather="bell"></i>
                    </a>
                    <div class="dropdown-menu" aria-labelledby="notificationDropdown" >
                        <div class="dropdown-header flex-row-around pl-0 pr-0 w-100">
                            <p class="mb-0 font-weight-medium">Notifications</p>
                            <a href="javascript:;" class="text-muted hover-underline clearNotifications">Clear all</a>
                        </div>
                    </div>
                </li>



                <li class="nav-item dropdown nav-profile">
                    <a class="nav-link dropdown-toggle" href="#" id="profileDropdown" role="button" data-toggle="dropdown" data-clickAction="header_profile"
                       aria-haspopup="true" aria-expanded="false">
                        <img src="<?=HOST . $baseInfo["picture"]?>" class="border-radius-50 w-30px" alt="profile">
                    </a>
                    <div class="dropdown-menu" aria-labelledby="profileDropdown">
                        <div class="dropdown-header d-flex flex-column align-items-center">
                            <div class="figure mb-3">
                                <img src="<?=HOST . $baseInfo["picture"]?>" class="border-radius-50 w-80px" alt="">
                            </div>
                            <div class="flex-col-around flex-align-center">
                                <p class="name font-weight-bold mb-0"><?=ucfirst($_SESSION["nickname"])?></p>
                                <p class="email text-muted mb-3">@<?=ucfirst($_SESSION["username"])?></p>
                            </div>
                        </div>
                        <div class="dropdown-body">
                            <ul class="profile-nav p-0 pt-3">
                                <li class="nav-item">
                                    <a href="<?=URL::addParam($_SERVER["REQUEST_URI"],array("page" => "general_settings"))?>" class="nav-link">
                                        <i data-feather="user"></i>
                                        <span>Manage profile</span>
                                    </a>
                                </li>
                                <li class="nav-item">
                                    <a href="?logout" class="nav-link">
                                        <i data-feather="log-out"></i>
                                        <span>Log Out</span>
                                    </a>
                                </li>
                            </ul>
                        </div>
                    </div>
                </li>
            </ul>
        </div>
    </div>
</nav>
<div class="flex-row-start flex-align-center w-100 mt-2" id="pageENotice">
    <div class="alert alert-danger eNotice mb-0 hidden" role="alert"></div>
</div>

<?php
$crud->closeConnection();

