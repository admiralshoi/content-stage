<?php
if (session_status() == PHP_SESSION_NONE && !headers_sent()) session_start();
if(!defined("IN_VIEW") || !defined("ROOT"))
    exit;

$pageTitle = "Access points";
?>
<script> var pageTitle = <?=json_encode($pageTitle)?>; </script>

<div class="page-content" data-page="access_points">
    <div class="row" id="dataContainer">

        <div class="col-sm-12 mt-2">
            <div class="flex-row-start flex-align-center w-100 mt-2">
                <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
            </div>
            <div class="card border-radius-15px hidden dataParentContainer" data-time-from="" data-time-to="">
                <div class="card-body">
                    <div class="flex-row-between eNoticeAutoLoad" style="min-height: 50px;">
                        <p class="pb-5 font-16 font-weight-bold">User roles</p>
                        <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover dataTable prettyTable" id="userRoleTable">
                            <thead>
                            <th>Access level</th>
                            <th>Name</th>
                            <th>Is defined</th>
                            <th>Description</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-sm-12 mt-2">
            <div class="flex-row-start flex-align-center w-100 mt-2">
                <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
            </div>
            <div class="card border-radius-15px hidden dataParentContainer" data-time-from="" data-time-to="">
                <div class="card-body">
                    <div class="flex-row-between eNoticeAutoLoad" style="min-height: 50px;">
                        <div class="flex-col-start">
                            <p class="font-16 font-weight-bold">Access points</p>
                            <p class="pb-5 font-14 text-gray">No value means everybody has access. Use comma to separate. No spaces.</p>
                        </div>
                        <div class="alert alert-danger eNotice mb-2 hidden" role="alert"></div>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-hover dataTable prettyTable" id="accessPointTable">
                            <thead>
                                <th>Type</th>
                                <th>Name</th>
                                <th>Action level</th>
                                <th>Access levels</th>
                                <th>Description</th>
                            </thead>
                            <tbody></tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

