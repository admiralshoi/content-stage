<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
use classes\src\Auth\Auth;
use classes\src\Object\objects\Integrations;
use classes\src\Object\transformer\Titles;
$crud = new AbstractCrudObject();

$pageTitle = "Users";
$view = "";
if(isset($_GET["view"]) && in_array($_GET["view"], ["creators", "brands", "admins"])) {
    $view = $_GET["view"];
    $accessLevel = match($view) {
        default => 0,
        "creators" => 1,
        "brands" => 2,
        "admins" => [8,9]
    };

    $users = $crud->user()->getByX(["access_level" => $accessLevel], [], "", true);
}
else $users = $crud->user()->getByX([], [], "", true);



$userRoleHandler = $crud->userRoles();


?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="users">

        <div class="flex-col-start">
            <p class="font-22 font-weight-medium">Users</p>
            <p class="font-16">Manage and create</p>
        </div>


        <div class="row mt-5">
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <p class="font-18 font-weight-bold color-primary-cta">Create a user</p>
                            </div>
                            <div class="col-12">
                                <div id="user-creation-third-party" class="row">
                                    <div class="col-12 col-md-6 mt-2 align-items-center">
                                        <input type="text" name="username" placeholder="username" class="form-control mb-0" />
                                    </div>
                                    <div class="col-12 col-md-6 mt-2 align-items-center">
                                        <input type="text" name="full_name" placeholder="Name" class="form-control mb-0" />
                                    </div>
                                    <div class="col-12 col-md-6 mt-2 align-items-center">
                                        <input type="email" name="email" placeholder="email" class="form-control mb-0" />
                                    </div>
                                    <div class="col-8 col-md-4 mt-2 align-items-center flex-row">
                                        <select name="access_level" class="form-control mb-0">
                                            <?php foreach ($userRoleHandler->getByX(["defined" => 1]) as $userRole):
                                                if((int)$userRole["access_level"] > $_SESSION["access_level"]) continue; ?>
                                                <option value="<?=$userRole["access_level"]?>"><?=Titles::clean($userRole["name"])?></option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                    <div class="col-4 col-md-2 mt-2 align-items-center justify-content-end flex-row">
                                        <button class="btn-base btn-prim border-transparent py-1 px-3" name="create_new_user_third_party" style="border-radius: 3px;">Create user</button>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>




        <div class="row mt-5">
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12 col-md-4 col-xl-3" >
                                <form method="get" id="view_users" action="" class="flex-row-start flex-align-center">
                                    <p class="font-18 font-weight-bold color-primary-cta">Users</p>
                                    <input type="hidden" name="page" value="users" />
                                    <select name="view" class="form-control ml-2">
                                        <option value="all" <?=in_array($view, ["", "all"]) ? "selected" : ""?>>All</option>
                                        <option value="brands" <?=$view === "brands" ? "selected" : ""?>>Brands</option>
                                        <option value="creators" <?=$view === "creators" ? "selected" : ""?>>Creators</option>
                                        <option value="admins" <?=$view === "admins" ? "selected" : ""?>>Admins</option>
                                    </select>
                                </form>
                            </div>
                            <div class="col-12 mt-3">
                                <div class="table-responsive">
                                    <table class="w-100 table-padding">
                                        <thead>
                                        <tr class="color-primary-dark bg-wrapper">
                                            <th class="font-weight-normal">id</th>
                                            <th class="font-weight-normal">Username</th>
                                            <th class="font-weight-normal">Name</th>
                                            <th class="font-weight-normal">Role</th>
                                            <th class="font-weight-normal">Member since</th>
                                            <th class="font-weight-normal">Suspend</th>
                                        </tr>
                                        </thead>
                                        <tbody>
                                            <?php if(!empty($users)):
                                                foreach ($users as $user):
                                                    if((int)$user["access_level"] > (int)$_SESSION["access_level"]) continue; ?>
                                                <tr>
                                                    <td><?=$user["id"]?></td>
                                                    <td><?=$user["username"]?></td>
                                                    <td><?=Titles::cleanUcAll($user["full_name"])?></td>
                                                    <td><?=Titles::clean($userRoleHandler->name($user["access_level"]))?></td>
                                                    <td><?=date("M Y", $user["created_at"])?></td>
                                                    <td>
                                                        <?php if((int)$user["deactivated"] === 1): ?>
                                                        <span class="color-primary-cta hover-underline cursor-pointer toggleUserSuspension"
                                                              data-uid="<?=$user["uid"]?>">Unsuspend</span>
                                                        <?php else: ?>
                                                            <span class="color-red hover-underline cursor-pointer toggleUserSuspension"
                                                                  data-uid="<?=$user["uid"]?>">Suspend</span>
                                                        <?php endif; ?>
                                                    </td>
                                                </tr>
                                                <?php endforeach;
                                            endif; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </div>







    </div>

<?php
$crud->closeConnection();