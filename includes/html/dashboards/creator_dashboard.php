<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
use classes\src\Auth\Auth;
use classes\src\Object\objects\Integrations;
$crud = new AbstractCrudObject();

$pageTitle = "My page";

$mediaHandler = $crud->mediaLookup();
$lookupList = $crud->lookupList();

$integration = $crud->integrations()->getMyIntegration();
if(empty($integration)) {
    require_once ROOT . "includes/html/global_utility/404.php";
    exit;
}


$uid = (int)$_SESSION["uid"];
$creatorId = $lookupList->getUserCreatorId($uid);
$campaignId = isset($_GET["campaign"]) && !empty($_GET["campaign"]) ? (int)$_GET["campaign"] : 0;
$creator = !empty($creatorId) ? $lookupList->getWithMergedData($creatorId, $campaignId) : [];





?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="creators">

        <div class="flex-col-start">
            <p class="font-22 font-weight-medium"><?=$creator["username"]?></p>
        </div>




        <?php  include_once ROOT . "includes/html/other/display-creators.php"; ?>







    </div>

<?php
$crud->closeConnection();