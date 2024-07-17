<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT")) exit;

use classes\src\AbstractCrudObject;
use classes\src\Object\transformer\URL;
$crud = new AbstractCrudObject();

$pageTitle = "Analytics";

$lookupList = $crud->lookupList();
$campaignHandler = $crud->campaigns();
$campaignRelations = $crud->campaignRelations();
$mediaHandler = $crud->mediaLookup();



$postsCount = count($mediaHandler->getMediaWithCampaign());
$creatorCount = count($lookupList->getByX(["deactivated" => 0]));





?>
    <script>
        var pageTitle = <?=json_encode($pageTitle)?>;
    </script>
    <div class="page-content position-relative" data-page="dashboard">



        <div class="row">
            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <p class="font-32 font-weight-bold"><?=$postsCount?></p>
                        <p class="font-16 mt-1">Total mentions</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <p class="font-32 font-weight-bold"><?=count($mediaHandler->getMediaWithCampaignToday())?></p>
                        <p class="font-16 mt-1">Mentions today</p>
                    </div>
                </div>
            </div>
            <div class="col-12 col-md-6 col-lg-4 col-xl-3 mt-1">
                <div class="card border-radius-10px">
                    <div class="card-body position-relative">
                        <p class="font-32 font-weight-bold"><?=count($campaignHandler->getActiveCampaigns())?></p>
                        <p class="font-16 mt-1">Active campaigns</p>
                    </div>
                </div>
            </div>



        </div>










        <div class="row mt-5">
            <div class="col-12">
                <div class="card mt-2">
                    <div class="card-body">
                        <div class="row">
                            <div class="col-12">
                                <p class="font-18 font-weight-bold color-primary-cta">Live tracking</p>
                            </div>
                            <div class="col-12 mt-1">

                                <div class="table-responsive container-fluid overflow-x-hidden mt-3">
                                    <table class="table table-hover dataTable prettyTable" id="live_mention_table">
                                        <thead>
                                        <tr>
                                            <th>Id</th>
                                            <th>Username</th>
                                            <th>Type</th>
                                            <th>Campaign</th>
                                            <th>Time</th>
                                        </tr>
                                        </thead>
                                        <tbody>

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