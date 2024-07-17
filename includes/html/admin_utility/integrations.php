<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
use classes\src\Auth\Auth;
use classes\src\Object\transformer\Titles;
$crud = new AbstractCrudObject();

$pageTitle = "Integrations";




/*
 * Init the authentication class
 */
$auth = new Auth($crud);
if(!$auth->init("facebook")) {
    echo "Could not be initiated. Check type";
    exit;
}


/**
 * The auth link is tied to the button or link that somebody will click
 */
$authLink = $auth->oAuthLink(URL::addParam(HOST, array("page"=>"integrations"), true));

$integrationHandler = $crud->integrations();
$params = $crud->isAdmin() ? [] : ["user_id" => $_SESSION["uid"]];
$integrations = $integrationHandler->getByX($params);






?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>

    <div class="page-content position-relative" data-page="integrations">







        <div class="flex-row-start flex-align-center font-22 font-weight-medium">
            <p>Integrations</p>
        </div>
        <div class="flex-row-start flex-align-center mt-3">
            <a href="<?=$authLink?>" class="btn-base btn-prim font-weight-bold">
                Integrate through Facebook
            </a>
        </div>

        <?php if($crud->isCreator() && !$crud->registrationIsComplete()): ?>
            <?php if($crud->integrationUnderway()): ?>
                <div class="py-3 px-4 bg-dark color-white font-16 w-100 mt-3">
                    The integration is being processed. May take up to 10 minutes before the registration is complete
                </div>
            <?php else: ?>
            <div class="py-3 px-4 bg-dark color-white font-16 w-100 mt-3">
                Please integrate your Instagram account(s) to start as a creator
            </div>
            <?php endif; ?>
        <?php else: ?>
            <div class="row ">
                <div class="col-12">
                    <div class="card border-radius-10px mt-4">
                        <div class="card-body">
                            <p class="font-18 font-weight-bold color-primary-cta">Integrated accounts</p>
                            <div class="table-responsive container-fluid overflow-x-hidden mt-3">
                                <table class="table table-hover dataTable prettyTable plainDataTable" id="integrations_table"
                                       data-pagination-limit="30" data-sorting-col="4" data-sorting-order="desc">
                                    <thead>
                                    <tr>
                                        <th>Name</th>
                                        <th>Id</th>
                                        <th>Integrated by</th>
                                        <th>Provider</th>
                                        <th>Enabled</th>
                                        <th>Remove</th>
                                    </tr>
                                    </thead>
                                    <tbody>
                                    <?php
                                    if(!empty($integrations)):
                                        foreach ($integrations as $integration): ?>

                                            <tr>
                                                <td><?=$integration["item_name"]?></td>
                                                <td><?=$integration["item_id"]?></td>
                                                <td><?=(int)$integration["user_id"] === (int)$_SESSION["uid"] ? "You" : "user_" . $integration["user_id"]?></td>
                                                <td><?=$integration["provider"]?></td>
                                                <td data-sort="<?=(int)$integration["active"]?>">
                                                    <p class="mb-0 font-16 hover-underline cursor-pointer toggleIntegrationActive
                                                    <?=(int)$integration['active'] ? 'color-red' : 'color-green'?>" data-id="<?=$integration['id']?>">
                                                        <?=(int)$integration['active'] ? 'Disable' : 'Enable'?>
                                                    </p>
                                                </td>
                                                <td>
                                                    <p class="removeIntegration color-red hover-underline cursor-pointer mb-0 font-16" data-id="<?=$integration['id']?>">Remove</p>
                                                </td>
                                            </tr>

                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        <?php endif; ?>




    </div>

<?php
$crud->closeConnection();